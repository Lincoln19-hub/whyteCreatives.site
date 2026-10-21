import Link from 'next/link';
import { db } from '@/lib/db';
import { sessions, settings } from '@/lib/schema';
import { and, eq, isNull, asc } from 'drizzle-orm';
import { Camera, ArrowRight } from 'lucide-react';
import { BRAND } from '@/lib/brand';
import { fetchDriveFolderCached, sizedUrl } from '@/lib/gdrive';

export const dynamic = 'force-dynamic';

// Default images pulled from the live Pixieset site (swap anytime in Admin → Site Images)
const DEFAULTS = {
  hero: 'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/banner_5_lg.jpg?v=1763645177000',
  about: 'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/portrait_1_lg.jpg?v=1763645188000',
  wall: [
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/vertical_1_lg.jpg?v=1760600122000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/vertical_2_lg.jpg?v=1760600123000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/portrait_2_lg.jpg?v=1763645228000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/vertical_5_lg.jpg?v=1760600135000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/portrait_5_lg.jpg?v=1760600033000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/horizontal_7_lg.jpg?v=1760600096000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/horizontal_4_lg.jpg?v=1760600084000',
    'https://assets-pw.pixieset.com/flex/flex-themes/theme-images/mason/horizontal_3_lg.jpg?v=1760600057000',
  ],
};

export default async function Home() {
  // ── Settings + data ──
  const cfgRows = await db.select().from(settings);
  const cfg: Record<string, string> = {};
  for (const r of cfgRows) cfg[r.key] = r.value ?? '';

  const sessionRows = await db
    .select()
    .from(sessions)
    .where(and(eq(sessions.active, true), isNull(sessions.deletedAt)))
    .orderBy(asc(sessions.displayOrder), asc(sessions.createdAt));

  // Masonry wall: Drive folder photos (if set) → Pixieset-style defaults
  const driveFolder = (cfg.gallery_drive_folder || '').trim();
  const drivePhotos = driveFolder ? await fetchDriveFolderCached(driveFolder) : [];
  const wallImages = drivePhotos.length > 0
    ? drivePhotos.slice(0, 8).map((d) => sizedUrl(d.url, 900))
    : DEFAULTS.wall;

  const heroImg = cfg.image_hero || DEFAULTS.hero;
  const aboutImg = cfg.image_about || DEFAULTS.about;
  const contactEmail = cfg.business_email || 'whytecobby@gmail.com';

  // Gallery cards: sessions (Brand shots / People / Products style) → up to 6, first 3 featured
  const cards = sessionRows.slice(0, 6);

  return (
    <div className="min-h-screen bg-white font-sans text-slate-900">
      {/* ── Nav (minimal, Pixieset-style) ── */}
      <nav className="fixed top-0 left-0 right-0 z-50 border-b border-black/5 bg-white/85 backdrop-blur-md">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
          <Link href="/" className="flex items-center gap-2 text-sm font-bold uppercase tracking-[0.25em]">
            <Camera className="h-4 w-4" /> {BRAND}
          </Link>
          <div className="flex items-center gap-6">
            <a href="#galleries" className="hidden text-xs font-medium uppercase tracking-widest text-slate-500 hover:text-slate-900 sm:inline">Galleries</a>
            <a href="#about" className="hidden text-xs font-medium uppercase tracking-widest text-slate-500 hover:text-slate-900 sm:inline">About</a>
            <Link href="/portfolio" className="hidden text-xs font-medium uppercase tracking-widest text-slate-500 hover:text-slate-900 sm:inline">Portfolio</Link>
            <Link href="/book" className="rounded-full bg-slate-900 px-5 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-slate-800">Book a Session</Link>
          </div>
        </div>
      </nav>

      {/* ── Hero: full-bleed image + brand statement ── */}
      <header className="relative flex h-[85vh] min-h-[520px] items-center justify-center overflow-hidden bg-slate-900">
        <img src={heroImg} alt={BRAND} className="absolute inset-0 h-full w-full object-cover opacity-90" fetchPriority="high" />
        <div className="absolute inset-0 bg-gradient-to-b from-black/50 via-black/25 to-black/60" />
        <div className="relative z-10 px-6 text-center text-white">
          <p className="text-xs font-semibold uppercase tracking-[0.45em] text-white/80">{BRAND}</p>
          <h1 className="mt-5 font-serif text-4xl font-light leading-tight tracking-tight sm:text-6xl">
            Creative Visuals.<br />Timeless Stories.
          </h1>
          <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
            <Link href="#galleries" className="inline-flex h-11 items-center rounded-full border border-white/40 bg-white/10 px-7 text-xs font-bold uppercase tracking-widest backdrop-blur transition-all hover:bg-white hover:text-black">
              View Galleries
            </Link>
            <Link href="/book" className="inline-flex h-11 items-center rounded-full bg-white px-7 text-xs font-bold uppercase tracking-widest text-slate-900 transition-all hover:bg-slate-100">
              Book a Session
            </Link>
          </div>
        </div>
      </header>

      {/* ── Gallery links grid (Brand shots / People / Products …) ── */}
      <section id="galleries" className="mx-auto max-w-6xl px-6 py-20">
        <div className="columns-1 gap-5 sm:columns-2 lg:columns-3">
          {cards.map((s, i) => (
            <Link
              key={s.id}
              href={`/portfolio#session-${s.slug}`}
              className={`group relative mb-5 block break-inside-avoid overflow-hidden rounded-lg bg-slate-100 ${i === 0 ? 'sm:col-span-2' : ''}`}
            >
              {s.image ? (
                <img
                  src={s.image}
                  alt={s.name}
                  loading="lazy"
                  className={`w-full object-cover transition-transform duration-700 group-hover:scale-105 ${i === 0 ? 'aspect-[16/10]' : i % 3 === 1 ? 'aspect-[4/5]' : 'aspect-[4/3]'}`}
                />
              ) : (
                <div className={`flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 ${i === 0 ? 'aspect-[16/10]' : 'aspect-[4/5]'}`}>
                  <Camera className="h-10 w-10 text-slate-300" />
                </div>
              )}
              <div className="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-transparent opacity-80 transition-opacity group-hover:opacity-95" />
              <div className="absolute inset-x-0 bottom-0 flex items-center justify-between p-5">
                <h3 className="font-serif text-xl font-medium text-white">{s.name}</h3>
                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition-all group-hover:bg-white group-hover:text-black">
                  <ArrowRight className="h-4 w-4" />
                </span>
              </div>
            </Link>
          ))}
        </div>
      </section>

      {/* ── "Your story deserves to be seen." ── */}
      <section className="bg-slate-50 py-24">
        <div className="mx-auto max-w-3xl px-6 text-center">
          <h2 className="font-serif text-3xl font-light tracking-tight text-slate-900 sm:text-5xl">Your story deserves to be seen.</h2>
          <p className="mt-6 text-sm leading-relaxed text-slate-500 sm:text-base">
            Welcome to the creative world of {BRAND}, where meaningful moments become lasting memories and ideas are transformed into compelling visual stories.
          </p>
          <p className="mt-4 text-sm leading-relaxed text-slate-500 sm:text-base">
            Our portfolio showcases a collection of photography and videography projects, from timeless wedding moments and striking portraits to graduation celebrations, special events, corporate visuals, and authentic lifestyle imagery.
          </p>
          <Link href="/portfolio" className="mt-8 inline-flex h-11 items-center rounded-full bg-slate-900 px-7 text-xs font-bold uppercase tracking-widest text-white transition-all hover:bg-slate-800">
            View full portfolio
          </Link>
        </div>

        {/* Masonry image wall */}
        <div className="mx-auto mt-16 max-w-6xl px-6">
          <div className="columns-2 gap-4 sm:columns-3 lg:columns-4">
            {wallImages.map((url, i) => (
              <div key={url + i} className="group mb-4 break-inside-avoid overflow-hidden rounded-lg">
                <img src={url} alt="Studio work" loading="lazy" className={`w-full object-cover transition-transform duration-700 group-hover:scale-105 ${i % 3 === 0 ? 'aspect-[3/4]' : i % 3 === 1 ? 'aspect-square' : 'aspect-[4/5]'}`} />
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── About: "I'm lincoln whyte — Get to know me" ── */}
      <section id="about" className="mx-auto max-w-6xl px-6 py-24">
        <div className="grid items-center gap-12 lg:grid-cols-2">
          <div className="order-2 lg:order-1">
            <p className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">I&apos;m lincoln whyte</p>
            <h2 className="mt-4 font-serif text-4xl font-light leading-tight tracking-tight text-slate-900 sm:text-5xl">
              Get to<br />know me
            </h2>
            <p className="mt-6 max-w-md text-sm leading-relaxed text-slate-500 sm:text-base">
              {BRAND} is a professional photography brand based in Akosombo, Ghana, dedicated to capturing timeless moments with creativity and precision.
            </p>
            <div className="mt-8 flex flex-wrap gap-4">
              <a href={`mailto:${contactEmail}`} className="inline-flex h-11 items-center rounded-full bg-slate-900 px-7 text-xs font-bold uppercase tracking-widest text-white hover:bg-slate-800">Read more</a>
              <Link href="/portfolio" className="inline-flex h-11 items-center rounded-full border border-slate-300 px-7 text-xs font-bold uppercase tracking-widest text-slate-700 hover:border-slate-900 hover:text-slate-900">See my work</Link>
            </div>
          </div>
          <div className="order-1 lg:order-2">
            <div className="overflow-hidden rounded-2xl">
              <img src={aboutImg} alt="Lincoln Whyte" className="aspect-[4/5] w-full object-cover" loading="lazy" />
            </div>
          </div>
        </div>
      </section>

      {/* ── "Let's connect" ── */}
      <section className="border-t border-slate-100 bg-slate-50 py-24">
        <div className="mx-auto max-w-3xl px-6 text-center">
          <h2 className="font-serif text-4xl font-light tracking-tight text-slate-900 sm:text-5xl">Let&apos;s connect</h2>
          <p className="mx-auto mt-5 max-w-md text-sm leading-relaxed text-slate-500">
            Have a story worth telling? Let&apos;s create something beautiful together.
          </p>
          <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
            <a href={`mailto:${contactEmail}`} className="inline-flex h-12 items-center rounded-full bg-slate-900 px-8 text-xs font-bold uppercase tracking-widest text-white hover:bg-slate-800">Contact me</a>
            <Link href="/book" className="inline-flex h-12 items-center rounded-full border border-slate-300 px-8 text-xs font-bold uppercase tracking-widest text-slate-700 hover:border-slate-900 hover:text-slate-900">Book a Session</Link>
          </div>
        </div>
      </section>

      {/* ── Footer ── */}
      <footer className="border-t border-slate-100 py-10">
        <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
          <p className="text-xs font-bold uppercase tracking-[0.25em] text-slate-900">{BRAND}</p>
          <p className="text-xs text-slate-400">© {new Date().getFullYear()} {BRAND} — Akosombo, Ghana. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
}
