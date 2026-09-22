import { cookies } from "next/headers";
import { createHmac } from "crypto";
import Link from "next/link";
import { notFound } from "next/navigation";
import { db } from "@/db";
import { galleries, galleryPhotos } from "@/lib/schema";
import { eq, asc } from "drizzle-orm";
import { fetchDriveFolder, sizedUrl } from "@/lib/gdrive";
import PasswordGate from "./PasswordGate";
import PayBalance from "./PayBalance";

function appSecret(): string {
  return process.env.APP_SECRET || process.env.PAYSTACK_SECRET_KEY || "studio-dev-secret";
}

export default async function GalleryPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;

  const rows = await db.select().from(galleries).where(eq(galleries.slug, slug)).limit(1);
  const gallery = rows[0];
  if (!gallery) notFound();

  // ── Expiry guard ──
  const expired =
    gallery.expiryDate &&
    new Date(`${gallery.expiryDate}T23:59:59`) < new Date();

  if (expired) {
    return (
      <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6">
        <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
          <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-3xl">⏳</div>
          <h1 className="text-2xl font-extrabold text-slate-900">This Gallery Has Expired</h1>
          <p className="mt-3 text-sm leading-relaxed text-slate-500">
            This private photo gallery was available until <strong>{gallery.expiryDate}</strong> and has now automatically expired.
            Contact the studio and we&apos;ll gladly extend access for you.
          </p>
          <Link href="/" className="mt-6 inline-flex h-11 items-center justify-center rounded-full bg-slate-100 px-6 text-xs font-bold uppercase tracking-widest text-slate-700 hover:bg-slate-200">
            Back to Homepage
          </Link>
        </div>
      </main>
    );
  }

  // ── Password gate ──
  const cookieStore = await cookies();
  const token = cookieStore.get(`gal_${slug}`)?.value ?? "";
  const expected = createHmac("sha256", appSecret()).update(`gallery:${slug}`).digest("hex").slice(0, 40);
  const needsPassword = !!gallery.passwordHash && token !== expected;
  if (needsPassword) return <PasswordGate slug={slug} title={gallery.title} />;

  // ── Photos: visually uploaded (DB) + Google Drive folder, de-duplicated ──
  const dbPhotos = await db
    .select()
    .from(galleryPhotos)
    .where(eq(galleryPhotos.galleryId, gallery.id))
    .orderBy(asc(galleryPhotos.sortOrder), asc(galleryPhotos.id));
  const seen = new Set<string>();
  const photos = [];
  for (const p of dbPhotos) {
    if (seen.has(p.url)) continue;
    seen.add(p.url);
    photos.push({ id: `db-${p.id}`, url: p.url, downloadUrl: p.url, title: p.title || "Photo" });
  }
  if (gallery.gdriveFolder) {
    for (const f of await fetchDriveFolder(gallery.gdriveFolder)) {
      if (seen.has(f.url)) continue;
      seen.add(f.url);
      photos.push(f);
    }
  }
  const paid = gallery.status === "paid" || Number(gallery.balance) <= 0;
  const cover = gallery.coverUrl || (photos[0] ? sizedUrl(photos[0].url, 1600) : "");

  return (
    <main className="min-h-screen bg-white">
      {/* Cinematic hero */}
      <section className="relative flex h-[75vh] w-full items-end justify-center overflow-hidden bg-black">
        {cover && <img src={cover} alt={gallery.title} className="absolute inset-0 h-full w-full object-cover opacity-85" fetchPriority="high" />}
        <div className="absolute inset-0 bg-gradient-to-b from-black/30 via-black/10 to-black/80" />
        {!paid && (
          <div className="watermark absolute inset-0 z-[5]" aria-hidden />
        )}
        <div className="relative z-10 max-w-4xl px-6 pb-16 text-center text-white">
          <span className="text-xs font-light uppercase tracking-[0.3em] text-slate-200">Exclusive Client Preview</span>
          <h1 className="mt-4 font-serif text-4xl font-light tracking-tight text-slate-100 sm:text-6xl">{gallery.title}</h1>
          <div className="mt-4 flex items-center justify-center gap-2 text-sm font-light text-slate-300">
            <span>{photos.length} Images</span>
            {gallery.expiryDate && (
              <>
                <span>•</span>
                <span>Available until {gallery.expiryDate}</span>
              </>
            )}
          </div>
        </div>
      </section>

      {/* Balance banner (preview mode) */}
      {!paid && (
        <div className="mx-auto mt-6 max-w-3xl px-4">
          <div className="flex flex-col items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50/95 p-5 shadow-lg sm:flex-row">
            <div className="flex-1 text-center sm:text-left">
              <p className="text-sm font-bold text-slate-900">🎉 Your photos are ready — previews unlocked!</p>
              <p className="mt-0.5 text-xs leading-relaxed text-slate-600">
                Settle the remaining balance of <strong>GHS {Number(gallery.balance).toFixed(2)}</strong> to unlock full-resolution downloads. Previews are watermarked until then.
              </p>
            </div>
            <PayBalance slug={slug} balance={Number(gallery.balance)} />
          </div>
        </div>
      )}

      {/* Photo grid */}
      <section className="min-h-[50vh] bg-white py-10">
        <div className="mx-auto max-w-7xl px-2 md:px-6">
          {photos.length === 0 ? (
            <div className="mx-auto my-8 max-w-md rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
              <p className="text-lg font-bold text-slate-700">No Photos Yet</p>
              <p className="mt-1 text-sm text-slate-400">This gallery is being prepared — please check back soon.</p>
            </div>
          ) : (
            <div className="columns-2 gap-2 md:columns-3 md:gap-3 lg:columns-4">
              {photos.map((p) => (
                <div key={p.id} className="group relative mb-2 break-inside-avoid overflow-hidden rounded-lg bg-slate-50 shadow-xs transition-all hover:shadow-lg md:mb-3">
                  <img
                    src={sizedUrl(p.url, 800)}
                    alt={p.title}
                    loading="lazy"
                    decoding="async"
                    className="w-full select-none object-cover"
                  />
                  {!paid && <div className="watermark absolute inset-0 z-[5]" aria-hidden />}
                  {paid && (
                    <a
                      href={p.downloadUrl}
                      target="_blank"
                      rel="noopener"
                      className="absolute bottom-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-slate-800 opacity-0 shadow-lg transition-all group-hover:opacity-100"
                      title="Download Photo"
                    >
                      ⬇
                    </a>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Watermark style */}
      <style>{`
        .watermark {
          pointer-events: none;
          background-repeat: repeat;
          background-image: url("data:image/svg+xml;base64,${Buffer.from(
            "<svg xmlns='http://www.w3.org/2000/svg' width='280' height='200'><text x='140' y='100' transform='rotate(-28 140 100)' text-anchor='middle' font-family='Georgia, serif' font-size='17' font-weight='bold' fill='rgba(255,255,255,0.30)'>PREVIEW</text></svg>"
          ).toString("base64")}");
        }
        img { -webkit-user-drag: none; }
      `}</style>
    </main>
  );
}
