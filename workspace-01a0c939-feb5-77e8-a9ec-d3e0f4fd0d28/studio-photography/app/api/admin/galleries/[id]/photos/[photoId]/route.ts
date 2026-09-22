import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { galleryPhotos } from '@/lib/schema';
import { eq, and } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

/** DELETE → remove one photo from a gallery */
export async function DELETE(_req: NextRequest, ctx: { params: Promise<{ id: string; photoId: string }> }) {
  const { id, photoId } = await ctx.params;
  const galId = parseInt(id, 10);
  const phId = parseInt(photoId, 10);
  if (isNaN(galId) || isNaN(phId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  const deleted = await db.delete(galleryPhotos)
    .where(and(eq(galleryPhotos.id, phId), eq(galleryPhotos.galleryId, galId)))
    .returning();
  if (deleted.length === 0) return Response.json({ error: 'Photo not found' }, { status: 404 });
  return Response.json({ ok: true });
}
