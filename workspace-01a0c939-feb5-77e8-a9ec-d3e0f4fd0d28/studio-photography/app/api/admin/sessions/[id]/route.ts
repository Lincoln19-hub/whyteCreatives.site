import { db } from '@/lib/db';
import { driveDirectUrl } from '@/lib/gdrive';
import { sessions, packages } from '@/lib/schema';
import { eq, and, isNull, sql } from 'drizzle-orm';
import { NextRequest } from 'next/server';
import { slugify } from '@/lib/utils';

export const dynamic = 'force-dynamic';

type Ctx = { params: Promise<{ id: string }> };

export async function GET(_req: NextRequest, ctx: Ctx) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  const [row] = await db.select().from(sessions).where(eq(sessions.id, numId));
  if (!row) return Response.json({ error: 'Not found' }, { status: 404 });
  return Response.json(row);
}

export async function PUT(req: NextRequest, ctx: Ctx) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  try {
    const body = await req.json();

    const updates: Record<string, unknown> = { updatedAt: new Date() };

    if (body.name !== undefined) {
      if (typeof body.name !== 'string' || body.name.trim().length === 0) {
        return Response.json({ error: 'Session Name is required' }, { status: 400 });
      }
      updates.name = body.name.trim();
      if (!body.slug) updates.slug = slugify(body.name);
    }
    if (body.slug !== undefined) updates.slug = body.slug.trim();
    if (body.description !== undefined) updates.description = body.description;
    if (body.category !== undefined) updates.category = body.category;
    if (body.image !== undefined) updates.image = driveDirectUrl(body.image);
    if (body.featured !== undefined) updates.featured = !!body.featured;
    if (body.displayOrder !== undefined) updates.displayOrder = body.displayOrder;
    if (body.active !== undefined) updates.active = !!body.active;
    if (body.restore === true) updates.deletedAt = null;

    const [updated] = await db
      .update(sessions)
      .set(updates)
      .where(eq(sessions.id, numId))
      .returning();

    if (!updated) return Response.json({ error: 'Not found' }, { status: 404 });
    return Response.json(updated);
  } catch (e: unknown) {
    console.error(e);
    const msg = e instanceof Error ? e.message : 'Failed to update session';
    if (msg.includes('unique') || msg.includes('duplicate')) {
      return Response.json({ error: 'A session with this slug already exists' }, { status: 409 });
    }
    return Response.json({ error: msg }, { status: 500 });
  }
}

export async function DELETE(req: NextRequest, ctx: Ctx) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  const force = req.nextUrl.searchParams.get('force') === 'true';

  // Check for packages
  const pkgCount = await db
    .select({ count: sql<number>`count(*)::int` })
    .from(packages)
    .where(and(eq(packages.sessionId, numId), isNull(packages.deletedAt)));

  if (pkgCount[0].count > 0 && !force) {
    return Response.json(
      { error: 'Session has packages. Use ?force=true to confirm deletion.', hasPackages: true },
      { status: 409 }
    );
  }

  // Soft delete
  const [deleted] = await db
    .update(sessions)
    .set({ deletedAt: new Date(), active: false, updatedAt: new Date() })
    .where(eq(sessions.id, numId))
    .returning();

  if (!deleted) return Response.json({ error: 'Not found' }, { status: 404 });

  // Also soft-delete child packages
  if (force) {
    await db
      .update(packages)
      .set({ deletedAt: new Date(), active: false, updatedAt: new Date() })
      .where(eq(packages.sessionId, numId));
  }

  return Response.json({ ok: true });
}
