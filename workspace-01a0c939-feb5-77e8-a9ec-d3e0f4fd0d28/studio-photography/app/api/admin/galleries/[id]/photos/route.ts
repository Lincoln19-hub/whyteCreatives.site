import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { galleries, galleryPhotos } from '@/lib/schema';
import { eq, asc } from 'drizzle-orm';
import { fetchDriveFolder } from '@/lib/gdrive';

export const dynamic = 'force-dynamic';

/** GET → all photos of a gallery */
export async function GET(_req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });
  const rows = await db.select().from(galleryPhotos).where(eq(galleryPhotos.galleryId, numId)).orderBy(asc(galleryPhotos.sortOrder), asc(galleryPhotos.id));
  return Response.json(rows);
}

/**
 * POST → add photos.
 * Body: { photos: [{ url, title }] }  — visual uploads (compressed data-URIs) or pasted URLs
 *    or { driveImport: "<folder url>" } — imports every photo from a public Google Drive folder
 */
export async function POST(req: NextRequest, ctx: { params: Promise<{ id: string }> }) {
  const { id } = await ctx.params;
  const numId = parseInt(id, 10);
  if (isNaN(numId)) return Response.json({ error: 'Invalid id' }, { status: 400 });

  const gal = (await db.select().from(galleries).where(eq(galleries.id, numId)).limit(1))[0];
  if (!gal) return Response.json({ error: 'Gallery not found' }, { status: 404 });

  try {
    const body = await req.json();
    const existing = await db.select().from(galleryPhotos).where(eq(galleryPhotos.galleryId, numId));
    const existingUrls = new Set(existing.map((p) => p.url));
    let nextOrder = existing.length;

    // ── Drive folder import ──
    if (body?.driveImport) {
      const files = await fetchDriveFolder(String(body.driveImport));
      let added = 0;
      for (const f of files) {
        if (existingUrls.has(f.url)) continue;
        await db.insert(galleryPhotos).values({ galleryId: numId, url: f.url, title: f.title, sortOrder: nextOrder++ });
        added++;
      }
      return Response.json({ ok: true, added, total: files.length });
    }

    // ── Direct uploads / pasted URLs ──
    const photos: { url: string; title?: string }[] = Array.isArray(body?.photos) ? body.photos : [];
    let added = 0;
    for (const p of photos) {
      const url = String(p?.url ?? '').trim();
      if (!url || existingUrls.has(url)) continue;
      await db.insert(galleryPhotos).values({ galleryId: numId, url, title: String(p?.title ?? ''), sortOrder: nextOrder++ });
      added++;
    }
    return Response.json({ ok: true, added });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to add photos' }, { status: 500 });
  }
}
