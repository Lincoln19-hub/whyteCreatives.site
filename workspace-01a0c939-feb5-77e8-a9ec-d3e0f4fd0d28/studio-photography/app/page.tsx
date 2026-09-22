import Link from 'next/link';
import Image from 'next/image';
import { Camera, Sparkles, Clock, Heart, Calendar, MapPin, Play } from 'lucide-react';
import { db } from '@/lib/db';
import { settings } from '@/lib/schema';
import { getLatestTikTokVideos, cleanUsername } from '@/lib/tiktok';
import { BRAND } from '@/lib/brand';

export default async function Home() {
  // Brand + TikTok username from studio settings (Admin → Settings)
  const cfgRows = await db.select().from(settings);
  const cfg: Record<string, string> = {};
  for (const r of cfgRows) cfg[r.key] = r.value ?? '';
  const brandName = (cfg.business_name || '').trim() || BRAND;
  const tiktokUser = cleanUsername(cfg.tiktok_username || '');
  const tiktoks = tiktokUser ? await getLatestTikTokVideos(tiktokUser, 8) : [];

  return (
    <div className="min-h-screen">
      {/* Navigation */}
      <nav className="fixed top-0 left-0 right-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-md">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
          <Link href="/" className="flex items-center gap-2.5">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-primary">
              <Camera className="h-4 w-4" />
            </div>
            <span className="text-lg font-semibold">{brandName}</span>
          </Link>
          <div className="hidden items-center gap-8 md:flex">
            <a href="#about" className="text-sm text-gray-600 hover:text-gray-900">About</a>
            <a href="#services" className="text-sm text-gray-600 hover:text-gray-900">Services</a>
            <a href="#gallery" className="text-sm text-gray-600 hover:text-gray-900">Gallery</a>
          </div>
          <div className="flex items-center gap-3">
            <Link href="/book" className="btn btn-primary">Book a Session</Link>
          </div>
        </div>
      </nav>

      {/* Hero */}
      <section className="relative overflow-hidden pt-28 pb-16 md:pt-36 md:pb-24">
        <div className="mx-auto max-w-7xl px-6">
          <div className="grid items-center gap-12 md:grid-cols-[1fr_0.9fr] md:gap-16">
            <div>
              <div className="mb-4 inline-flex items-center gap-2 text-sm font-medium text-primary">
                <Sparkles className="h-4 w-4" />
                Capturing Moments, Creating Memories
              </div>
              <h1 className="text-5xl font-bold leading-[1.1] tracking-tight text-gray-900 md:text-6xl lg:text-[4.5rem]">
                Timeless Photography
                <br />
                for Your Most
                <br />
                <span className="text-gray-400">Important Days</span>
              </h1>
              <p className="mt-5 max-w-lg text-lg leading-relaxed text-gray-500">
                We specialize in capturing the essence of life's most precious moments with elegance, artistry, and attention to detail. From intimate portraits to grand celebrations, every frame tells your story.
              </p>
              <div className="mt-8 flex flex-wrap gap-3">
                <Link href="/book" className="btn btn-primary btn-lg">Book a Session</Link>
                <a href="#gallery" className="btn btn-outline btn-lg">View My Work</a>
              </div>
            </div>
            <div className="relative">
              <div className="overflow-hidden rounded-2xl shadow-2xl">
                <img
                  src="/images/hero-portrait.webp"
                  alt="Professional portrait photography"
                  className="aspect-[3/4] w-full object-cover"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Services */}
      <section id="services" className="py-24">
        <div className="mx-auto max-w-7xl px-6">
          <div className="mx-auto mb-14 max-w-xl text-center">
            <div className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-primary">
              <Calendar className="h-4 w-4" />
              Our Services
            </div>
            <h2 className="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
              Tailored Photography Experiences
            </h2>
          </div>
          <div className="grid gap-6 md:grid-cols-3">
            {[
              {
                icon: Heart,
                title: 'Weddings',
                desc: 'Celebrate your love story with timeless wedding photography that captures every precious moment.',
              },
              {
                icon: Camera,
                title: 'Portraits',
                desc: 'Professional portraits that showcase your personality and elegance with artistic direction.',
              },
              {
                icon: Calendar,
                title: 'Events',
                desc: 'Corporate events, celebrations, and special occasions documented with style and professionalism.',
              },
            ].map((service) => (
              <div
                key={service.title}
                className="group rounded-xl border border-gray-100 bg-white p-8 transition-all hover:border-gray-200 hover:shadow-lg"
              >
                <div className="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-primary">
                  <service.icon className="h-5 w-5" />
                </div>
                <h3 className="mb-2 text-lg font-semibold text-gray-900">{service.title}</h3>
                <p className="mb-4 text-sm leading-relaxed text-gray-500">{service.desc}</p>
                <Link href="/book" className="text-sm font-medium text-primary hover:text-primary-800">
                  Book Now →
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* About */}
      <section id="about" className="border-y border-gray-100 bg-gray-50 py-24">
        <div className="mx-auto grid max-w-7xl items-center gap-12 px-6 md:grid-cols-2 md:gap-20">
          <div className="overflow-hidden rounded-2xl shadow-xl">
            <img
              src="/images/about-photo.webp"
              alt="Photography in action"
              className="aspect-[4/5] w-full object-cover"
            />
          </div>
          <div>
            <div className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-primary">
              <Clock className="h-4 w-4" />
              About {brandName}
            </div>
            <h2 className="mb-5 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
              Where Art Meets Authenticity
            </h2>
            <p className="mb-4 text-base leading-relaxed text-gray-500">
              With over 15 years of experience in professional photography, we've dedicated ourselves to capturing the authentic moments that matter most. Our approach combines technical expertise with artistic direction to create images that transcend time.
            </p>
            <p className="mb-6 text-sm leading-relaxed text-gray-500">
              Every project is treated as a unique collaboration. We listen to your vision, understand your story, and deliver photographs that exceed expectations.
            </p>
            <ul className="mb-8 space-y-3">
              {[
                'Award-winning photography team',
                'State-of-the-art equipment & editing',
                'Fast turnaround with online gallery',
                'Personalized consultation for every client',
              ].map((item) => (
                <li key={item} className="flex items-start gap-2.5 text-sm text-gray-600">
                  <span className="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-blue-50 text-primary">
                    <svg className="h-2.5 w-2.5" fill="none" stroke="currentColor" strokeWidth="3" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/></svg>
                  </span>
                  {item}
                </li>
              ))}
            </ul>
            <Link href="/book" className="btn btn-primary">Start Your Project</Link>
          </div>
        </div>
      </section>

      {/* Gallery */}
      <section id="gallery" className="py-24">
        <div className="mx-auto max-w-7xl px-6">
          <div className="mx-auto mb-10 max-w-xl text-center">
            <div className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-primary">
              <Camera className="h-4 w-4" />
              Our Gallery
            </div>
            <h2 className="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">Explore Our Latest Work</h2>
          </div>
          {tiktoks.length > 0 ? (
            /* 🎬 Latest works pulled live from TikTok — auto-updates with every post */
            <>
              <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                {tiktoks.map((t) => (
                  <a key={t.id} href={t.url} target="_blank" rel="noopener" className="group relative aspect-[3/4] overflow-hidden rounded-xl bg-gray-900">
                    {t.thumbnail && (
                      <img
                        src={t.thumbnail}
                        alt={t.title}
                        referrerPolicy="no-referrer"
                        loading="lazy"
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                      />
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/10" />
                    <div className="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                      <span className="flex h-12 w-12 items-center justify-center rounded-full border border-white/40 bg-white/15 backdrop-blur">
                        <Play className="ml-0.5 h-5 w-5 fill-white text-white" />
                      </span>
                    </div>
                    <span className="absolute left-3 top-3 rounded-full bg-black/40 px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest text-white backdrop-blur">TikTok</span>
                    <div className="absolute inset-x-0 bottom-0 flex items-end bg-gradient-to-t from-black/60 via-transparent p-4 opacity-0 transition-opacity group-hover:opacity-100">
                      <span className="line-clamp-2 text-sm font-medium text-white">{t.title}</span>
                    </div>
                  </a>
                ))}
              </div>
              <div className="mt-8 text-center">
                <Link href="/portfolio" className="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline">
                  View Full Portfolio →
                </Link>
              </div>
            </>
          ) : (
            /* Fallback showcase until TikTok is connected in Admin → Settings */
            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
              {[
                { label: 'Outdoor Portrait', pos: 'center', image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=1000' },
                { label: 'Studio Portrait', pos: 'top', image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=1000' },
                { label: 'Wedding Celebration', pos: 'bottom', image: 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&q=80&w=1000' },
                { label: 'Timeless Session', pos: 'center', image: 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=1000' },
              ].map((item, i) => (
                <div key={i} className="group relative aspect-[3/4] overflow-hidden rounded-xl bg-gray-100">
                  <img
                    src={item.image}
                    alt={item.label}
                    className={`h-full w-full object-cover transition-transform duration-500 group-hover:scale-105`}
                    style={{ objectPosition: item.pos }}
                  />
                  <div className="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 via-transparent opacity-0 transition-opacity group-hover:opacity-100">
                    <span className="p-5 text-sm font-medium text-white">{item.label}</span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* CTA */}
      <section className="border-y border-gray-100 bg-gray-50 py-24">
        <div className="mx-auto max-w-2xl px-6 text-center">
          <div className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-primary">
            <Sparkles className="h-4 w-4" />
            Ready to Capture Your Story?
          </div>
          <h2 className="mb-4 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
            Let's Create Something Beautiful
          </h2>
          <p className="mb-8 text-lg text-gray-500">
            Book a consultation with our team today and let us bring your vision to life.
          </p>
          <div className="flex justify-center gap-3">
            <Link href="/book" className="btn btn-primary btn-lg">Book a Session</Link>
            <a href="#gallery" className="btn btn-outline btn-lg">View Portfolio</a>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12">
        <div className="mx-auto max-w-7xl px-6">
          <div className="grid gap-10 md:grid-cols-4">
            <div className="md:col-span-1">
              <Link href="/" className="mb-3 flex items-center gap-2.5">
                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-primary">
                  <Camera className="h-4 w-4" />
                </div>
                <span className="text-lg font-semibold">{brandName}</span>
              </Link>
              <p className="max-w-xs text-sm text-gray-500">
                Capturing life's most precious moments with elegance, artistry, and attention to detail.
              </p>
            </div>
            {[
              {
                title: 'Navigate',
                links: [
                  { label: 'About', href: '#about' },
                  { label: 'Services', href: '#services' },
                  { label: 'Gallery', href: '#gallery' },
                  { label: 'Contact', href: '/book' },
                ],
              },
              {
                title: 'Services',
                links: [
                  { label: 'Weddings', href: '/book' },
                  { label: 'Portraits', href: '/book' },
                  { label: 'Events', href: '/book' },
                  { label: 'Commercial', href: '/book' },
                ],
              },
            ].map((section) => (
              <div key={section.title}>
                <h4 className="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-900">{section.title}</h4>
                <ul className="space-y-2.5">
                  {section.links.map((link) => (
                    <li key={link.label}>
                      <Link href={link.href} className="text-sm text-gray-500 hover:text-primary">{link.label}</Link>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
          <div className="mt-10 border-t border-gray-100 pt-6 text-center text-sm text-gray-400">
            © {new Date().getFullYear()} {brandName}. All rights reserved.
          </div>
        </div>
      </footer>
    </div>
  );
}
