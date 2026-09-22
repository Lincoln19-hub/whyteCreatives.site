import Link from 'next/link';
import { db } from '@/lib/db';
import { sessions, packages, settings } from '@/lib/schema';
import { eq, and, isNull, asc } from 'drizzle-orm';
import { Camera, Play } from 'lucide-react';
import ShareLinkButton from '@/components/ShareLinkButton';
import ShareButton from '@/components/ShareButton';
import { getLatestTikTokVideos, cleanUsername } from '@/lib/tiktok';
import { BRAND } from '@/lib/brand';

export const dynamic = 'force-dynamic';
export const metadata = { title: `Portfolio — ${BRAND}` };

export default async function PortfolioPage() {
  // ── TikTok username: admin Settings → env fallback ──
  const cfgRows = await db.select().from(settings);
  const cfg: Record<string, string> = {};
  for (const r of cfgRows) cfg[r.key] = r.value ?? '';
  const username = cleanUsername(cfg.tiktok_username || process.env.TIKTOK_USERNAME || '');

  // ── Latest works from TikTok (auto-updates with every post) ──
  const tiktoks = username ? await getLatestTikTokVideos(username, 12) : [];

  // ── Fallback: session cards (when TikTok isn't configured/readable) ──
  const sessionRows = await db
    .select()
    .from(sessions)
    .where(and(eq(sessions.active, true), isNull(sessions.deletedAt)))
    .orderBy(asc(sessions.displayOrder), asc(sessions.createdAt));
  const pkgRows = await db.select().from(packages).where(and(eq(packages.active, true), isNull(packages.deletedAt)));

  return (
    <main className="min-h-screen bg-slate-50">
      <header className="border-b bg-white">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6">
          <Link href="/" className="flex items-center gap-2 text-lg font-bold text-slate-900">
            <Camera className="h-6 w-6" /> {BRAND}
          </Link>
          <Link href="/book" className="btn btn-primary">Book a Session</Link>
        </div>
      </header>

      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div className="mb-10 text-center">
          <span className="text-xs uppercase tracking-[0.3em] text-slate-400">Our Work</span>
          <h1 className="mt-3 font-serif text-4xl font-bold text-slate-900 sm:text-5xl">Portfolio</h1>
          <p className="mx-auto mt-3 max-w-xl text-sm text-slate-500">
            {tiktoks.length > 0 ? (
              <>Fresh from our TikTok — <strong>@{username}</strong> — updates automatically every time we post. Tap any work to watch, or share it.</>
            ) : (
              <>Browse the sessions we shoot — tap <strong>Share</strong> on any photo to post it straight to Instagram or TikTok.</>
            )}
          </p>
        </div>

        {tiktoks.length > 0 ? (
          /* ═══ TIKTOK LATEST WORKS ═══ */
          <div className="columns-1 gap-4 sm:columns-2 lg:columns-3 xl:columns-4">
            {tiktoks.map((t) => (
              <div key={t.id} className="group relative mb-4 break-inside-avoid overflow-hidden rounded-2xl bg-slate-900 shadow-sm transition-shadow hover:shadow-xl">
                <a href={t.url} target="_blank" rel="noopener" className="block">
                  {t.thumbnail ? (
                    <img src={t.thumbnail} alt={t.title} loading="lazy" referrerPolicy="no-referrer" className="w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                  ) : (
                    <div className="flex aspect-[4/5] items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 text-slate-600">
                      <Play className="h-10 w-10" />
                    </div>
                  )}
                  <div className="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-black/20" />
                  <div className="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                    <span className="flex h-14 w-14 items-center justify-center rounded-full border border-white/40 bg-white/15 backdrop-blur">
                      <Play className="ml-0.5 h-6 w-6 fill-white text-white" />
                    </span>
                  </div>
                  <span className="absolute left-3 top-3 rounded-full border border-white/20 bg-black/40 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-white backdrop-blur">TikTok</span>
                </a>
                <div className="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-4">
                  <p className="line-clamp-2 min-w-0 text-sm font-semibold text-white" style={{ display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>{t.title}</p>
                  <ShareLinkButton url={t.url} title={t.title} />
                </div>
              </div>
            ))}
          </div>
        ) : (
          <>
          {/* Fallback: session showcase (when TikTok isn't configured/readable) */}
          {username ? (
            <div className="mx-auto mb-8 max-w-lg rounded-xl border border-amber-200 bg-amber-50 p-4 text-center text-sm text-amber-700">
              Couldn&apos;t read the TikTok profile <strong>@{username}</strong> right now — showing session showcase instead. Double-check the username in Admin → Settings.
            </div>
          ) : (
            <div className="mx-auto mb-8 max-w-lg rounded-xl border border-blue-200 bg-blue-50 p-4 text-center text-sm text-blue-700">
              💡 Want this page to auto-fill with your newest TikTok posts? Set your <strong>TikTok username</strong> in Admin → Settings (or the <code>TIKTOK_USERNAME</code> env var).
            </div>
          )}
          {sessionRows.length === 0 ? (
            <div className="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
              <p className="text-slate-500">No showcase items yet — add session images in the admin dashboard.</p>
            </div>
          ) : (
            <div className="columns-1 gap-4 sm:columns-2 lg:columns-3">
              {sessionRows.map((s) => {
                const count = pkgRows.filter((p) => p.sessionId === s.id).length;
                const img = s.image || '';
                return (
                  <div key={s.id} className="group relative mb-4 break-inside-avoid overflow-hidden rounded-2xl bg-white shadow-sm transition-shadow hover:shadow-xl">
                    {img ? (
                      <img src={img} alt={s.name} loading="lazy" className="w-full object-cover" />
                    ) : (
                      <div className="flex aspect-[4/3] items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                        <Camera className="h-10 w-10 text-slate-300" />
                      </div>
                    )}
                    <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent p-4">
                      <div className="flex items-end justify-between gap-3">
                        <div className="min-w-0">
                          <h2 className="truncate font-serif text-lg font-semibold text-white">{s.name}</h2>
                          <p className="text-xs text-white/70">{s.category || 'Photography'}{count > 0 ? ` • ${count} package${count === 1 ? '' : 's'}` : ''}</p>
                        </div>
                        <div className="flex shrink-0 gap-2">
                          {img && <ShareButton url={img} title={s.name} compact />}
                          <Link href={`/book#session-${s.slug}`} className="flex h-9 items-center rounded-full bg-white/95 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-900 shadow-lg hover:scale-105">Book</Link>
                        </div>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
          </>
        )}

        <div className="mt-12 flex flex-col items-center gap-3 rounded-2xl bg-white p-8 text-center shadow-sm">
          <p className="text-sm text-slate-500">Like what you see? Your session could be next.</p>
          <Link href="/book" className="btn btn-primary">Book a Session</Link>
        </div>
      </div>
    </main>
  );
}
