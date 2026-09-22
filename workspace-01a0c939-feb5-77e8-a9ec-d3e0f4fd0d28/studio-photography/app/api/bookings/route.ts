import { NextRequest, NextResponse } from "next/server";
import { db } from "@/db";
import { bookings, invoices, packages, sessions } from "@/lib/schema";
import { eq } from "drizzle-orm";
import { initializeTransaction } from "@/lib/paystack";

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const {
      packageId, clientName, clientEmail, clientPhone,
      eventDate, eventLocation, deliveryDate,
    } = body ?? {};

    if (!packageId || !clientName || !clientEmail || !eventDate) {
      return NextResponse.json({ error: "Missing required booking details." }, { status: 400 });
    }

    // Load package + session (server-authoritative pricing)
    const pkgRows = await db.select().from(packages).where(eq(packages.id, Number(packageId))).limit(1);
    const pkg = pkgRows[0];
    if (!pkg) return NextResponse.json({ error: "Package not found." }, { status: 404 });

    const sessionRows = await db.select().from(sessions).where(eq(sessions.id, pkg.sessionId)).limit(1);
    const session = sessionRows[0];

    const basePrice = Number(pkg.price);

    // Rush/priority delivery surcharge (same rules as the WP theme)
    let surchargePct = 0;
    if (deliveryDate && eventDate) {
      const d1 = new Date(String(eventDate).replace(/-/g, "/"));
      const d2 = new Date(String(deliveryDate).replace(/-/g, "/"));
      if (!isNaN(d1.getTime()) && !isNaN(d2.getTime()) && d2 >= d1) {
        const diffDays = Math.round((d2.getTime() - d1.getTime()) / 86400000);
        if (diffDays >= 0 && diffDays <= 2) surchargePct = 40;
        else if (diffDays <= 5) surchargePct = 35;
      }
    }
    const surchargeAmount = basePrice * (surchargePct / 100);
    const total = basePrice + surchargeAmount;
    const deposit = total * (pkg.depositPercentage / 100);

    // Persist the booking (pending until Paystack confirms)
    const inserted = await db.insert(bookings).values({
      sessionId: pkg.sessionId,
      sessionName: session?.name ?? "Session",
      packageId: pkg.id,
      packageName: pkg.name,
      clientName: String(clientName),
      clientEmail: String(clientEmail),
      clientPhone: String(clientPhone ?? ""),
      eventDate: String(eventDate),
      eventLocation: String(eventLocation ?? ""),
      deliveryDate: String(deliveryDate ?? ""),
      basePrice: basePrice.toFixed(2),
      surchargePct,
      surchargeAmount: surchargeAmount.toFixed(2),
      total: total.toFixed(2),
      deposit: deposit.toFixed(2),
      depositPercentage: pkg.depositPercentage,
      status: "pending",
    }).returning();
    const booking = inserted[0];

    // Auto-create the matching invoice
    const invNumber = `INV-${new Date().toISOString().slice(0, 10).replace(/-/g, "")}-${booking.id}`;
    await db.insert(invoices).values({
      number: invNumber,
      bookingId: booking.id,
      purpose: "booking_deposit",
      clientName: booking.clientName,
      clientEmail: booking.clientEmail,
      total: deposit.toFixed(2),
      status: "unpaid",
    });

    // Initialize Paystack deposit checkout
    const origin = req.nextUrl.origin;
    const reference = `BK-${booking.id}-${Date.now()}`;
    const txn = await initializeTransaction({
      email: booking.clientEmail,
      amountGhs: deposit,
      reference,
      metadata: { purpose: "booking_deposit", booking_id: booking.id, client_name: booking.clientName },
      callbackUrl: `${origin}/api/paystack/callback`,
    });

    await db.update(bookings).set({ paystackRef: reference }).where(eq(bookings.id, booking.id));

    return NextResponse.json({ authorizationUrl: txn.authorization_url, reference });
  } catch (err) {
    console.error("[bookings] failed:", err);
    return NextResponse.json({ error: err instanceof Error ? err.message : "Booking failed." }, { status: 500 });
  }
}
