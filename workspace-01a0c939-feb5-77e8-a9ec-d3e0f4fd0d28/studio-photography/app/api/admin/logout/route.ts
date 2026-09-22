import { NextResponse } from "next/server";

/** POST → clears the admin cookie */
export async function POST() {
  const res = NextResponse.json({ ok: true });
  res.cookies.set("studio_admin", "", { httpOnly: true, maxAge: 0, path: "/" });
  return res;
}
