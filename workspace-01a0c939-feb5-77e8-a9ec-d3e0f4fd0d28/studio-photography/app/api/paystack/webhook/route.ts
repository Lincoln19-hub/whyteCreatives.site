import { NextRequest, NextResponse } from "next/server";
import { createHmac } from "crypto";
import { db } from "@/db";
import { bookings, galleries, invoices } from "@/lib/schema";
import { eq } from "drizzle-orm";

/**
 * Paystack webhook — the source of truth for payments.
 * Configure in Paystack Dashboard → Settings → API Keys & Webhooks:
 *   https://yourdomain.com/api/paystack/webhook
 */
export async function POST(req: NextRequest) {
  const raw = await req.text();
  const signature = req.headers.get("x-paystack-signature") ?? "";

  const secret = process.env.PAYSTACK_SECRET_KEY ?? "";
  const expected = createHmac("sha512", secret).update(raw, "utf8").digest("hex");
  if (!signature || signature !== expected) {
    return NextResponse.json({ error: "Invalid signature" }, { status: 401 });
  }

  let event: { event?: string; data?: Record<string, unknown> };
  try { event = JSON.parse(raw); } catch { return NextResponse.json({ error: "Bad payload" }, { status: 400 }); }

  if (event.event === "charge.success") {
    const meta = ((event.data?.metadata ?? {}) as Record<string, unknown>);
    const reference = String(event.data?.reference ?? "");
    const purpose = String(meta.purpose ?? "");

    if (purpose === "booking_deposit") {
      const bookingId = Number(meta.booking_id ?? 0);
      if (bookingId) {
        await db.update(bookings).set({ status: "confirmed", paystackRef: reference }).where(eq(bookings.id, bookingId));
        const invs = await db.select().from(invoices).where(eq(invoices.bookingId, bookingId)).limit(1);
        if (invs[0]) await db.update(invoices).set({ status: "paid", paystackRef: reference }).where(eq(invoices.id, invs[0].id));
      }
    }

    if (purpose === "gallery_balance") {
      const slug = String(meta.gallery_slug ?? "");
      if (slug) {
        const gs = await db.select().from(galleries).where(eq(galleries.slug, slug)).limit(1);
        if (gs[0]) {
          await db.update(galleries).set({ status: "paid", paystackRef: reference }).where(eq(galleries.id, gs[0].id));
          const invs = await db.select().from(invoices).where(eq(invoices.galleryId, gs[0].id)).limit(1);
          if (invs[0]) await db.update(invoices).set({ status: "paid", paystackRef: reference }).where(eq(invoices.id, invs[0].id));
        }
      }
    }
  }

  return NextResponse.json({ received: true });
}
