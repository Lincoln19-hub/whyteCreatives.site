import { db } from '@/lib/db';
import { bookings, invoices } from '@/lib/schema';
import { desc } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

interface ClientRow {
  email: string;
  name: string;
  phone: string;
  bookings: number;
  spent: number;
  lastEventDate: string;
}

/** GET → clients derived from real bookings (grouped by email) */
export async function GET() {
  try {
    const rows = await db.select().from(bookings).orderBy(desc(bookings.createdAt));
    const invRows = await db.select().from(invoices);

    const map = new Map<string, ClientRow>();
    for (const b of rows) {
      const email = b.clientEmail || b.clientName;
      const existing = map.get(email);
      if (existing) {
        existing.bookings += 1;
        existing.spent += Number(b.deposit);
      } else {
        map.set(email, {
          email,
          name: b.clientName,
          phone: b.clientPhone ?? '',
          bookings: 1,
          spent: Number(b.deposit),
          lastEventDate: b.eventDate ?? '',
        });
      }
    }
    // attach paid invoice counts
    for (const c of map.values()) {
      c.spent = invRows
        .filter((i) => i.clientEmail === c.email && i.status === 'paid')
        .reduce((sum, i) => sum + Number(i.total), 0);
    }

    return Response.json(Array.from(map.values()));
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to load clients' }, { status: 500 });
  }
}
