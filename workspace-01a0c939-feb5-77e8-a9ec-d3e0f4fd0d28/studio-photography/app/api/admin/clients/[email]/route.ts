import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { bookings, invoices } from '@/lib/schema';
import { eq } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

/** DELETE → remove a client's bookings and invoices (galleries are kept) */
export async function DELETE(_req: NextRequest, ctx: { params: Promise<{ email: string }> }) {
  const { email } = await ctx.params;
  const clientEmail = decodeURIComponent(email);

  try {
    const rows = await db.select().from(bookings).where(eq(bookings.clientEmail, clientEmail));
    for (const b of rows) {
      await db.delete(invoices).where(eq(invoices.bookingId, b.id));
    }
    await db.delete(invoices).where(eq(invoices.clientEmail, clientEmail));
    await db.delete(bookings).where(eq(bookings.clientEmail, clientEmail));

    if (rows.length === 0) return Response.json({ error: 'Client not found' }, { status: 404 });
    return Response.json({ ok: true, removed: rows.length });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to delete client' }, { status: 500 });
  }
}
