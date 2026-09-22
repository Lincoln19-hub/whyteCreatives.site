import { NextRequest } from 'next/server';
import { createHash } from 'crypto';
import { db } from '@/lib/db';
import { galleries } from '@/lib/schema';
import { desc } from 'drizzle-orm';

export const dynamic = 'force-dynamic';

function appSecret() {
  return process.env.APP_SECRET || process.env.PAYSTACK_SECRET_KEY || 'studio-dev-secret';
}

/** GET → all galleries (newest first) */
export async function GET() {
  try {
    const rows = await db.select().from(galleries).orderBy(desc(galleries.createdAt));
    return Response.json(rows.map((g) => ({ ...g, passwordHash: g.passwordHash ? '••••' : '' })));
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to load galleries' }, { status: 500 });
  }
}

/** POST → create a gallery */
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { title, slug, clientName, clientEmail, password, gdriveFolder, coverUrl, expiryDate, balance } = body ?? {};
    if (!title || !clientName) {
      return Response.json({ error: 'Title and client name are required.' }, { status: 400 });
    }

    const finalSlug = (slug || title).toLowerCase().trim()
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || `gallery-${Date.now()}`;

    const passwordHash = password
      ? createHash('sha256').update(appSecret() + String(password)).digest('hex')
      : '';

    const [created] = await db.insert(galleries).values({
      slug: finalSlug,
      title: String(title),
      clientName: String(clientName),
      clientEmail: String(clientEmail ?? ''),
      passwordHash,
      gdriveFolder: String(gdriveFolder ?? ''),
      coverUrl: String(coverUrl ?? ''),
      expiryDate: String(expiryDate ?? ''),
      balance: String(Number(balance ?? 0).toFixed(2)),
      status: Number(balance ?? 0) > 0 ? 'unpaid' : 'paid',
    }).returning();

    return Response.json(created, { status: 201 });
  } catch (e: unknown) {
    const msg = e instanceof Error ? e.message : 'Failed to create gallery';
    if (msg.includes('unique') || msg.includes('duplicate')) {
      return Response.json({ error: 'A gallery with this link slug already exists — pick another.' }, { status: 409 });
    }
    console.error(e);
    return Response.json({ error: msg }, { status: 500 });
  }
}
