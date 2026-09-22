import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { bookings, invoices } from '@/lib/schema';
import { eq } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

/** DELETE → remove a booking and its linked invoices */
export async function DELETE(_req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  try {
    await db.delete(invoices).where(eq(invoices.bookingId, numId));
    const deleted = await db.delete(bookings).where(eq(bookings.id, numId)).returning();
    if (deleted.length === 0) return Response.json({ error: 'Booking not found' }, { status: 404 });
    return Response.json({ ok: true });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to delete booking' }, { status: 500 });
  }
}
