import { NextRequest } from 'next/server';
import { db } from '@/lib/db';
import { settings } from '@/lib/schema';

export const dynamic = 'force-dynamic';

/** GET → all settings as { key: value } + integration status */
export async function GET(req: NextRequest) {
  try {
    const rows = await db.select().from(settings);
    const out: Record<string, string> = {};
    for (const r of rows) out[r.key] = r.value ?? '';

    out.__paystack_configured = process.env.PAYSTACK_SECRET_KEY ? 'yes' : 'no';
    out.__app_url = `${req.nextUrl.protocol}//${req.nextUrl.host}`;

    return Response.json(out);
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to load settings' }, { status: 500 });
  }
}

/** PUT { key: value, ... } → upsert each */
export async function PUT(req: NextRequest) {
  try {
    const body = (await req.json()) as Record<string, unknown>;
    const entries = Object.entries(body).filter(([k]) => !k.startsWith('__'));
    if (entries.length === 0) return Response.json({ error: 'Nothing to save' }, { status: 400 });

    for (const [key, value] of entries) {
      await db
        .insert(settings)
        .values({ key, value: String(value ?? '') })
        .onConflictDoUpdate({ target: settings.key, set: { value: String(value ?? ''), updatedAt: new Date() } });
    }
    return Response.json({ ok: true });
  } catch (e) {
    console.error(e);
    return Response.json({ error: 'Failed to save settings' }, { status: 500 });
  }
}
