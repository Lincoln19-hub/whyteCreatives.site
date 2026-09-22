import { db } from '@/lib/db';
import { packages, packageFeatures } from '@/lib/schema';
import { eq, isNull, asc, and } from 'drizzle-orm';
import { NextRequest } from 'next/server';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const sessionId = req.nextUrl.searchParams.get('sessionId');
    const includeDeleted = req.nextUrl.searchParams.get('includeDeleted') === 'true';

    const conditions = [];
    if (!includeDeleted) conditions.push(isNull(packages.deletedAt));
    if (sessionId) conditions.push(eq(packages.sessionId, parseInt(sessionId, 10)));

    const where = conditions.length > 0 ? and(...conditions) : undefined;

    const rows = await db
      .select()
      .from(packages)
      .where(where)
      .orderBy(asc(packages.displayOrder), asc(packages.createdAt));

    // Fetch features for each package
    const result = await Promise.all(
      rows.map(async (pkg) => {
        const features = await db
          .select()
          .from(packageFeatures)
          .where(eq(packageFeatures.packageId, pkg.id))
          .orderBy(asc(packageFeatures.displayOrder));
        return { ...pkg, features };
      })
    );

    return Response.json(result);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to fetch packages' }, { status: 500 });
  }
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const {
      sessionId, name, description, price, duration, maxPeople,
      editedPhotos, outfitChanges, locations, deliveryTime,
      onlineGallery, rawImages, printing, transportation,
      droneCoverage, priorityEditing, depositPercentage,
      rescheduleAllowed, rescheduleHours, displayOrder, active,
      features,
    } = body;

    if (!name || typeof name !== 'string' || name.trim().length === 0) {
      return Response.json({ error: 'Package Name is required' }, { status: 400 });
    }
    if (!sessionId) {
      return Response.json({ error: 'Session ID is required' }, { status: 400 });
    }
    if (price === undefined || price === null || isNaN(Number(price))) {
      return Response.json({ error: 'Price is required and must be a number' }, { status: 400 });
    }
    if (!duration) {
      return Response.json({ error: 'Duration is required' }, { status: 400 });
    }

    // Check for duplicate name in session
    const existing = await db
      .select()
      .from(packages)
      .where(
        and(
          eq(packages.sessionId, sessionId),
          eq(packages.name, name.trim()),
          isNull(packages.deletedAt)
        )
      );

    if (existing.length > 0) {
      return Response.json(
        { error: 'A package with this name already exists in this session' },
        { status: 409 }
      );
    }

    const [created] = await db
      .insert(packages)
      .values({
        sessionId,
        name: name.trim(),
        description: description || '',
        price: String(price),
        duration,
        maxPeople: maxPeople ?? 1,
        editedPhotos: editedPhotos ?? 0,
        outfitChanges: outfitChanges ?? 1,
        locations: locations ?? 1,
        deliveryTime: deliveryTime || '3 Days',
        onlineGallery: !!onlineGallery,
        rawImages: !!rawImages,
        printing: !!printing,
        transportation: !!transportation,
        droneCoverage: !!droneCoverage,
        priorityEditing: !!priorityEditing,
        depositPercentage: depositPercentage ?? 50,
        rescheduleAllowed: rescheduleAllowed !== false,
        rescheduleHours: rescheduleHours ?? 48,
        displayOrder: displayOrder ?? 0,
        active: active !== false,
      })
      .returning();

    // Insert features
    if (Array.isArray(features) && features.length > 0) {
      await db.insert(packageFeatures).values(
        features.map((f: { feature: string; displayOrder?: number }, i: number) => ({
          packageId: created.id,
          feature: f.feature,
          displayOrder: f.displayOrder ?? i,
        }))
      );
    }

    // Return with features
    const featureRows = await db
      .select()
      .from(packageFeatures)
      .where(eq(packageFeatures.packageId, created.id))
      .orderBy(asc(packageFeatures.displayOrder));

    return Response.json({ ...created, features: featureRows }, { status: 201 });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to create package' }, { status: 500 });
  }
}
