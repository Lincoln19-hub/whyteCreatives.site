import { NextRequest, NextResponse } from "next/server";
import { db } from "@/db";
import { bookings, galleries, invoices } from "@/lib/schema";
import { eq } from "drizzle-orm";
import { verifyTransaction } from "@/lib/paystack";

/** Browser return from Paystack checkout → verify, then redirect to the right screen */
export async function GET(req: NextRequest) {
  const reference = req.nextUrl.searchParams.get("reference") ?? "";
  const origin = req.nextUrl.origin;
  if (!reference) return NextResponse.redirect(`${origin}/book?payment=missing-ref`);

  try {
    const txn = await verifyTransaction(reference);
    if (txn.status !== "success") {
      return NextResponse.redirect(`${origin}/book?payment=failed&ref=${encodeURIComponent(reference)}`);
    }

    const purpose = (txn.metadata?.purpose as string) ?? "";
    if (purpose === "booking_deposit") {
      const bookingId = Number(txn.metadata?.booking_id ?? 0);
      if (bookingId) {
        await db.update(bookings).set({ status: "confirmed" }).where(eq(bookings.id, bookingId));
        const invs = await db.select().from(invoices).where(eq(invoices.bookingId, bookingId)).limit(1);
        if (invs[0]) await db.update(invoices).set({ status: "paid", paystackRef: reference }).where(eq(invoices.id, invs[0].id));
      }
      return NextResponse.redirect(`${origin}/book/success?ref=${encodeURIComponent(reference)}`);
    }

    if (purpose === "gallery_balance") {
      const slug = String(txn.metadata?.gallery_slug ?? "");
      if (slug) {
        const gs = await db.select().from(galleries).where(eq(galleries.slug, slug)).limit(1);
        if (gs[0]) {
          await db.update(galleries).set({ status: "paid", paystackRef: reference }).where(eq(galleries.id, gs[0].id));
          const invs = await db.select().from(invoices).where(eq(invoices.galleryId, gs[0].id)).limit(1);
          if (invs[0]) await db.update(invoices).set({ status: "paid", paystackRef: reference }).where(eq(invoices.id, invs[0].id));
        }
        return NextResponse.redirect(`${origin}/gallery/${slug}?paid=1`);
      }
    }

    return NextResponse.redirect(`${origin}/?payment=unknown`);
  } catch (err) {
    console.error("[paystack/callback]", err);
    return NextResponse.redirect(`${origin}/book?payment=error`);
  }
}
