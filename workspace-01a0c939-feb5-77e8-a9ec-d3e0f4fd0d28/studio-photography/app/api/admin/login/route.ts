import { NextRequest, NextResponse } from "next/server";

/** POST { password } → sets the HttpOnly admin cookie */
export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const password = String(body?.password ?? "");
  const expectedPassword = process.env.ADMIN_PASSWORD ?? "";
  const token = process.env.ADMIN_TOKEN ?? "";

  if (!expectedPassword || !token) {
    return NextResponse.json({ error: "Admin login is not configured (set ADMIN_PASSWORD + ADMIN_TOKEN)." }, { status: 500 });
  }
  if (password !== expectedPassword) {
    return NextResponse.json({ error: "Incorrect password." }, { status: 401 });
  }

  const res = NextResponse.json({ ok: true });
  res.cookies.set("studio_admin", token, {
    httpOnly: true,
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
    maxAge: 60 * 60 * 24 * 7, // 7 days
    path: "/",
  });
  return res;
}
