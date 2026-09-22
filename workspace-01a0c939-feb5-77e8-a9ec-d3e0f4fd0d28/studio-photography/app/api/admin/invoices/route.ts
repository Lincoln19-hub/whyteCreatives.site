import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { invoices } from '@/lib/schema';
import { desc } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

/** GET → all invoices, newest first */
export async function GET() {
  try {
    const rows = await db.select().from(invoices).orderBy(desc(invoices.createdAt));
    return Response.json(rows);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to load invoices' }, { status: 500 });
  }
}

/** POST → create a manual invoice */
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { clientName, clientEmail, total, dueDate, notes } = body ?? {};
    if (!clientName || !total || Number(total) <= 0) {
      return Response.json({ error: 'Client name and a positive amount are required.' }, { status: 400 });
    }
    const number = `INV-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Date.now().toString().slice(-4)}`;
    const [created] = await db.insert(invoices).values({
      number,
      purpose: 'manual',
      clientName: String(clientName),
      clientEmail: String(clientEmail ?? ''),
      total: String(Number(total).toFixed(2)),
      status: 'unpaid',
      dueDate: String(dueDate ?? ''),
      notes: String(notes ?? ''),
    }).returning();
    return Response.json(created, { status: 201 });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to create invoice' }, { status: 500 });
  }
}
