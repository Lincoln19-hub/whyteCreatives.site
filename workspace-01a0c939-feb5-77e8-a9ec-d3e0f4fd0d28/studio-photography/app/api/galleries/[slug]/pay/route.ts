import { NextRequest, NextResponse } from "next/server";
import { db } from "@/db";
import { galleries, invoices } from "@/lib/schema";
import { eq } from "drizzle-orm";
import { initializeTransaction } from "@/lib/paystack";

/** POST → initialize a Paystack checkout for the gallery's outstanding balance */
export async function POST(req: NextRequest, ctx: { params: Promise<{ slug: string }> }) {
  const { slug } = await ctx.params;

  const rows = await db.select().from(galleries).where(eq(galleries.slug, slug)).limit(1);
  const gallery = rows[0];
  if (!gallery) return NextResponse.json({ error: "Gallery not found." }, { status: 404 });
  if (gallery.status === "paid" || Number(gallery.balance) <= 0) {
    return NextResponse.json({ error: "This gallery is already fully paid." }, { status: 400 });
  }

  // Balance invoice (idempotent: reuse if one already exists unpaid)
  const existing = await db.select().from(invoices).where(eq(invoices.galleryId, gallery.id)).limit(1);
  if (!existing[0]) {
    await db.insert(invoices).values({
      number: `BAL-${gallery.slug}-${Date.now()}`,
      galleryId: gallery.id,
      purpose: "gallery_balance",
      clientName: gallery.clientName,
      clientEmail: gallery.clientEmail,
      total: gallery.balance,
      status: "unpaid",
    });
  }

  const reference = `BAL-${gallery.slug}-${Date.now()}`;
  const txn = await initializeTransaction({
    email: gallery.clientEmail || "billing@whytecreatives.site",
    amountGhs: Number(gallery.balance),
    reference,
    metadata: { purpose: "gallery_balance", gallery_slug: gallery.slug },
    callbackUrl: `${req.nextUrl.origin}/api/paystack/callback`,
  });

  return NextResponse.json({ authorizationUrl: txn.authorization_url });
}
