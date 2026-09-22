import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { invoices } from '@/lib/schema';
import { eq } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

/** PUT → update invoice (status, fields) */
export async function PUT(req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  try {
    const body = await req.json();
    const updates: Record<string, unknown> = {};
    if (body.status !== undefined) updates.status = body.status === 'paid' ? 'paid' : 'unpaid';
    if (body.clientName !== undefined) updates.clientName = String(body.clientName);
    if (body.clientEmail !== undefined) updates.clientEmail = String(body.clientEmail);
    if (body.total !== undefined) updates.total = String(Number(body.total ?? 0).toFixed(2));
    if (body.dueDate !== undefined) updates.dueDate = String(body.dueDate);
    if (body.notes !== undefined) updates.notes = String(body.notes);

    const [updated] = await db.update(invoices).set(updates).where(eq(invoices.id, numId)).returning();
    if (!updated) return Response.json({ error: 'Invoice not found' }, { status: 404 });
    return Response.json(updated);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to update invoice' }, { status: 500 });
  }
}

/** DELETE → remove an invoice */
export async function DELETE(_req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });
  const deleted = await db.delete(invoices).where(eq(invoices.id, numId)).returning();
  if (deleted.length === 0) return Response.json({ error: 'Invoice not found' }, { status: 404 });
  return Response.json({ ok: true });
}
