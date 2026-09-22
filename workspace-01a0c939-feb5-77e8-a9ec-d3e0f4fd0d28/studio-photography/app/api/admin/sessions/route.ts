import { db } from '@/lib/db';
import { driveDirectUrl } from '@/lib/gdrive';
import { sessions, packages } from '@/lib/schema';
import { eq, isNull, sql, asc, and } from 'drizzle-orm';
import { NextRequest } from 'next/server';
import { slugify } from '@/lib/utils';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const includeDeleted = req.nextUrl.searchParams.get('includeDeleted') === 'true';
    const where = includeDeleted ? undefined : isNull(sessions.deletedAt);

    const sessionRows = await db
      .select()
      .from(sessions)
      .where(where)
      .orderBy(asc(sessions.displayOrder), asc(sessions.createdAt));

    // Get package counts separately
    const result = await Promise.all(
      sessionRows.map(async (session) => {
        const [countResult] = await db
          .select({ count: sql<number>`count(*)::int` })
          .from(packages)
          .where(and(eq(packages.sessionId, session.id), isNull(packages.deletedAt)));
        
        return {
          ...session,
          packageCount: countResult?.count ?? 0,
        };
      })
    );

    return Response.json(result);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to fetch sessions' }, { status: 500 });
  }
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { name, description, category, image, featured, displayOrder, active } = body;

    if (!name || typeof name !== 'string' || name.trim().length === 0) {
      return Response.json({ error: 'Session Name is required' }, { status: 400 });
    }

    const slug = body.slug?.trim() || slugify(name);

    const [created] = await db
      .insert(sessions)
      .values({
        name: name.trim(),
        slug,
        description: description || '',
        category: category || '',
        image: driveDirectUrl(image || ''),
        featured: !!featured,
        displayOrder: displayOrder ?? 0,
        active: active !== false,
      })
      .returning();

    return Response.json(created, { status: 201 });
  } catch (e: unknown) {
    console.error(e);
    const msg = e instanceof Error ? e.message : 'Failed to create session';
    if (msg.includes('unique') || msg.includes('duplicate')) {
      return Response.json({ error: 'A session with this slug already exists' }, { status: 409 });
    }
    return Response.json({ error: msg }, { status: 500 });
  }
}
