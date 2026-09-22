import { db } from '@/lib/db';
import { bookings } from '@/lib/schema';
import { desc } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

/** GET → all bookings, newest first */
export async function GET() {
  try {
    const rows = await db.select().from(bookings).orderBy(desc(bookings.createdAt));
    return Response.json(rows);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to load bookings' }, { status: 500 });
  }
}
