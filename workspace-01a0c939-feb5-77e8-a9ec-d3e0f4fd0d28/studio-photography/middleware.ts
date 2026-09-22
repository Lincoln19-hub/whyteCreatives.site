import { NextRequest, NextResponse } from "next/server";

/**
 * Studio Admin Gate — protects every /admin page and /api/admin route.
 * Visitors (no valid cookie) never see the admin UI; they're sent to the login screen.
 */
export function middleware(req: NextRequest) {
  const { pathname } = req.nextUrl;
  const token = req.cookies.get("studio_admin")?.value ?? "";
  const expected = process.env.ADMIN_TOKEN ?? "";
  const authed = !!expected && token === expected;

  // Public paths (login screen + its API)
  const isLoginPath = pathname === "/admin/login";
  const isLoginApi = pathname === "/api/admin/login" || pathname === "/api/admin/logout";

  // ── API protection: JSON 401, no redirects ──
  if (pathname.startsWith("/api/admin/") && !isLoginApi && !authed) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  // ── Page protection: redirect to login ──
  if (pathname.startsWith("/admin") && !isLoginPath && !authed) {
    const login = new URL("/admin/login", req.url);
    login.searchParams.set("next", pathname);
    return NextResponse.redirect(login);
  }

  // Already authed → don't show the login page again
  if (isLoginPath && authed) {
    return NextResponse.redirect(new URL("/admin", req.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/admin/:path*", "/api/admin/:path*"],
};
