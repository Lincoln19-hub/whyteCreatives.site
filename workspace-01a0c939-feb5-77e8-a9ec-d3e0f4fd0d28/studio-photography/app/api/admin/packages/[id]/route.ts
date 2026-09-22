import { db } from '@/lib/db';
import { packages, packageFeatures } from '@/lib/schema';
import { eq, asc, and, isNull } from 'drizzle-orm';
import { NextRequest } from 'next/server';

export const dynamic = 'force-dynamic';

type Ctx = { params: Promise<{ id: string }> };

export async function GET(_req: NextRequest, ctx: Ctx) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  const [row] = await db.select().from(packages).where(eq(packages.id, numId));
  if (!row) return Response.json({ error: 'Not found' }, { status: 404 });

  const features = await db
    .select()
    .from(packageFeatures)
    .where(eq(packageFeatures.packageId, numId))
    .orderBy(asc(packageFeatures.displayOrder));

  return Response.json({ ...row, features });
}

export async function PUT(req: NextRequest, ctx: Ctx) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  try {
    const body = await req.json();

    if (body.name !== undefined && (!body.name || typeof body.name !== 'string' || body.name.trim().length === 0)) {
      return Response.json({ error: 'Package Name is required' }, { status: 400 });
    }

    // Check duplicate name if name is being changed
    if (body.name) {
      const [current] = await db.select().from(packages).where(eq(packages.id, numId));
      if (current) {
        const existing = await db
          .select()
          .from(packages)
          .where(
            and(
              eq(packages.sessionId, body.sessionId ?? current.sessionId),
              eq(packages.name, body.name.trim()),
              isNull(packages.deletedAt)
            )
          );
        if (existing.length > 0 && existing[0].id !== numId) {
          return Response.json(
            { error: 'A package with this name already exists in this session' },
            { status: 409 }
          );
        }
      }
    }

    const updates: Record<string, unknown> = { updatedAt: new Date() };

    const fields = [
      'name', 'description', 'price', 'duration', 'maxPeople',
      'editedPhotos', 'outfitChanges', 'locations', 'deliveryTime',
      'onlineGallery', 'rawImages', 'printing', 'transportation',
      'droneCoverage', 'priorityEditing', 'depositPercentage',
      'rescheduleAllowed', 'rescheduleHours', 'displayOrder', 'active', 'sessionId',
    ] as const;

    for (const key of fields) {
      if (body[key] !== undefined) {
        if (key === 'price') {
          updates[key] = String(body[key]);
        } else if (['onlineGallery', 'rawImages', 'printing', 'transportation', 'droneCoverage', 'priorityEditing', 'rescheduleAllowed', 'active'].includes(key)) {
          updates[key] = !!body[key];
        } else if (key === 'name') {
          updates[key] = body[key].trim();
        } else {
          updates[key] = body[key];
        }
      }
    }

    const [updated] = await db
      .update(packages)
      .set(updates)
      .where(eq(packages.id, numId))
      .returning();

    if (!updated) return Response.json({ error: 'Not found' }, { status: 404 });

    // Replace features if provided
    if (body.features !== undefined && Array.isArray(body.features)) {
      await db.delete(packageFeatures).where(eq(packageFeatures.packageId, numId));
      if (body.features.length > 0) {
        await db.insert(packageFeatures).values(
          body.features.map((f: { feature: string; displayOrder?: number }, i: number) => ({
            packageId: numId,
            feature: f.feature,
            displayOrder: f.displayOrder ?? i,
          }))
        );
      }
    }

    const featureRows = await db
      .select()
      .from(packageFeatures)
      .where(eq(packageFeatures.packageId, numId))
      .orderBy(asc(packageFeatures.displayOrder));

    return Response.json({ ...updated, features: featureRows });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to update package' }, { status: 500 });
  }
}

export async function DELETE(_req: NextRequest, ctx: Ctx) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  // Soft delete
  const [deleted] = await db
    .update(packages)
    .set({ deletedAt: new Date(), active: false, updatedAt: new Date() })
    .where(eq(packages.id, numId))
    .returning();

  if (!deleted) return Response.json({ error: 'Not found' }, { status: 404 });
  return Response.json({ ok: true });
}
