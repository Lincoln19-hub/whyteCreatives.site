import { NextRequest, NextResponse } from "next/server";
import { db } from "@/db";
import { bookings } from "@/lib/schema";
import { eq } from "drizzle-orm";
import Link from "next/link";

export default async function SuccessPage({ searchParams }: { searchParams: Promise<{ ref?: string }> }) {
  const { ref } = await searchParams;
  let booking: typeof bookings.$inferSelect | undefined;

  if (ref) {
    const rows = await db.select().from(bookings).where(eq(bookings.paystackRef, ref)).limit(1);
    booking = rows[0];
  }

  return (
    <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6">
      <div className="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 shadow-xl">
        <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-3xl">✅</div>
        <h1 className="text-center text-2xl font-extrabold text-slate-900">Booking Confirmed!</h1>
        <p className="mt-2 text-center text-sm leading-relaxed text-slate-500">
          Your deposit was received and your session is locked in. A confirmation has been recorded — we can&apos;t wait to capture your story!
        </p>

        {booking && (
          <div className="mt-6 space-y-2.5 rounded-xl bg-slate-50 p-5 text-sm">
            <div className="flex justify-between"><span className="text-slate-500">Client</span><strong className="text-slate-900">{booking.clientName}</strong></div>
            <div className="flex justify-between"><span className="text-slate-500">Session</span><strong className="text-slate-900">{booking.sessionName}</strong></div>
            <div className="flex justify-between"><span className="text-slate-500">Package</span><strong className="text-slate-900">{booking.packageName}</strong></div>
            <div className="flex justify-between"><span className="text-slate-500">Shoot Date</span><strong className="text-slate-900">{booking.eventDate}</strong></div>
            <div className="flex justify-between border-t pt-2.5"><span className="text-slate-500">Total</span><strong className="text-slate-900">GHS {Number(booking.total).toFixed(2)}</strong></div>
            <div className="flex justify-between text-green-600"><span>Deposit Paid</span><strong>GHS {Number(booking.deposit).toFixed(2)}</strong></div>
            <div className="flex justify-between"><span className="text-slate-500">Balance (due on delivery)</span><strong className="text-slate-900">GHS {(Number(booking.total) - Number(booking.deposit)).toFixed(2)}</strong></div>
            <div className="pt-1 text-xs text-slate-400">Payment reference: {booking.paystackRef}</div>
          </div>
        )}

        <div className="mt-6 flex justify-center gap-3">
          <Link href="/" className="inline-flex h-11 items-center rounded-full bg-slate-900 px-6 text-xs font-bold uppercase tracking-widest text-white hover:bg-slate-800">
            Back to Homepage
          </Link>
        </div>
      </div>
    </main>
  );
}
