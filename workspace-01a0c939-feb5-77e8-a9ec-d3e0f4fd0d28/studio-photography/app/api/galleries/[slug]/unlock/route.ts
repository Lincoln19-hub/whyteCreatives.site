import { NextRequest, NextResponse } from "next/server";
import { createHash, createHmac } from "crypto";
import { db } from "@/db";
import { galleries } from "@/lib/schema";
import { eq } from "drizzle-orm";

function appSecret(): string {
  return process.env.APP_SECRET || process.env.PAYSTACK_SECRET_KEY || "studio-dev-secret";
}

function tokenFor(slug: string): string {
  return createHmac("sha256", appSecret()).update(`gallery:${slug}`).digest("hex").slice(0, 40);
}

/** POST { password } → sets an HttpOnly unlock cookie for this gallery */
export async function POST(req: NextRequest, ctx: { params: Promise<{ slug: string }> }) {
  const { slug } = await ctx.params;
  const body = await req.json().catch(() => ({}));
  const password = String(body?.password ?? "");

  const rows = await db.select().from(galleries).where(eq(galleries.slug, slug)).limit(1);
  const gallery = rows[0];
  if (!gallery) return NextResponse.json({ error: "Gallery not found." }, { status: 404 });

  if (!gallery.passwordHash) {
    // No password configured — always unlockable
    const res = NextResponse.json({ ok: true });
    res.cookies.set(`gal_${slug}`, tokenFor(slug), { httpOnly: true, sameSite: "lax", maxAge: 60 * 60 * 24 * 30, path: "/" });
    return res;
  }

  const attempt = createHash("sha256").update(appSecret() + password).digest("hex");
  if (attempt !== gallery.passwordHash) {
    return NextResponse.json({ error: "Incorrect password. Please try again." }, { status: 401 });
  }

  const res = NextResponse.json({ ok: true });
  res.cookies.set(`gal_${slug}`, tokenFor(slug), { httpOnly: true, sameSite: "lax", maxAge: 60 * 60 * 24 * 30, path: "/" });
  return res;
}
