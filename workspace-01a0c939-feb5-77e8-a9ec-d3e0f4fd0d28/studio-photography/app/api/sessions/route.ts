import { db } from '@/lib/db';
import { sessions, packages, packageFeatures } from '@/lib/schema';
import { eq, and, isNull, asc } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    // Fetch only active, non-deleted sessions
    const sessionRows = await db
      .select()
      .from(sessions)
      .where(and(eq(sessions.active, true), isNull(sessions.deletedAt)))
      .orderBy(asc(sessions.displayOrder), asc(sessions.createdAt));

    // Fetch active packages for each session
    const result = await Promise.all(
      sessionRows.map(async (session) => {
        const pkgRows = await db
          .select()
          .from(packages)
          .where(
            and(
              eq(packages.sessionId, session.id),
              eq(packages.active, true),
              isNull(packages.deletedAt)
            )
          )
          .orderBy(asc(packages.displayOrder), asc(packages.createdAt));

        const pkgsWithFeatures = await Promise.all(
          pkgRows.map(async (pkg) => {
            const features = await db
              .select()
              .from(packageFeatures)
              .where(eq(packageFeatures.packageId, pkg.id))
              .orderBy(asc(packageFeatures.displayOrder));
            return { ...pkg, features };
          })
        );

        return { ...session, packages: pkgsWithFeatures };
      })
    );

    return Response.json(result);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to fetch sessions' }, { status: 500 });
  }
}
