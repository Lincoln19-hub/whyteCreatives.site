import { NextRequest } from 'next/server';
import { createHash } from 'crypto';
import { db } from '@/lib/db';
import { galleries } from '@/lib/schema';
import { eq } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

function appSecret() {
  return process.env.APP_SECRET || process.env.PAYSTACK_SECRET_KEY || 'studio-dev-secret';
}

/** GET → one gallery (for the editor) */
export async function GET(_req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });
  const rows = await db.select().from(galleries).where(eq(galleries.id, numId)).limit(1);
  if (!rows[0]) return Response.json({ error: 'Gallery not found' }, { status: 404 });
  return Response.json(rows[0]);
}

/** PUT → update gallery fields */
export async function PUT(req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  try {
    const body = await req.json();
    const updates: Record<string, unknown> = {};

    if (body.title !== undefined) updates.title = String(body.title);
    if (body.clientName !== undefined) updates.clientName = String(body.clientName);
    if (body.clientEmail !== undefined) updates.clientEmail = String(body.clientEmail);
    if (body.gdriveFolder !== undefined) updates.gdriveFolder = String(body.gdriveFolder);
    if (body.coverUrl !== undefined) updates.coverUrl = String(body.coverUrl);
    if (body.expiryDate !== undefined) updates.expiryDate = String(body.expiryDate);
    if (body.balance !== undefined) {
      updates.balance = String(Number(body.balance ?? 0).toFixed(2));
      if (body.status === undefined) updates.status = Number(body.balance ?? 0) > 0 ? 'unpaid' : 'paid';
    }
    if (body.status !== undefined) updates.status = String(body.status);
    // password: provided → rehash; empty string + clearPassword flag → remove
    if (body.password) {
      updates.passwordHash = createHash('sha256').update(appSecret() + String(body.password)).digest('hex');
    } else if (body.clearPassword === true) {
      updates.passwordHash = '';
    }

    const [updated] = await db.update(galleries).set(updates).where(eq(galleries.id, numId)).returning();
    if (!updated) return Response.json({ error: 'Gallery not found' }, { status: 404 });
    return Response.json(updated);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to update gallery' }, { status: 500 });
  }
}

/** DELETE → remove gallery (photos cascade away with it) */
export async function DELETE(_req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });
  const deleted = await db.delete(galleries).where(eq(galleries.id, numId)).returning();
  if (deleted.length === 0) return Response.json({ error: 'Gallery not found' }, { status: 404 });
  return Response.json({ ok: true });
}
