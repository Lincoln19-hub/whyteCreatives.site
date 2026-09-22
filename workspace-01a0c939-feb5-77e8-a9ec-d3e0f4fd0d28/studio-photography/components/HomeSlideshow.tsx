'use client';

import { useEffect, useRef, useState, useCallback } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

/** Cinematic auto-playing slideshow — swipe on phones, arrows + dots, pauses on hover */
export default function HomeSlideshow({ images }: { images: { url: string; title: string }[] }) {
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);
  const touchX = useRef(0);
  const count = images.length;

  const next = useCallback(() => setIndex((i) => (i + 1) % count), [count]);
  const prev = useCallback(() => setIndex((i) => (i - 1 + count) % count), [count]);

  useEffect(() => {
    if (paused || count < 2) return;
    const t = setInterval(next, 4000);
    return () => clearInterval(t);
  }, [paused, next, count]);

  if (count === 0) return null;

  return (
    <div
      className="relative h-[380px] w-full overflow-hidden rounded-2xl bg-gray-900 shadow-lg md:h-[520px]"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onTouchStart={(e) => { touchX.current = e.changedTouches[0].clientX; }}
      onTouchEnd={(e) => {
        const dx = e.changedTouches[0].clientX - touchX.current;
        if (Math.abs(dx) > 50) (dx < 0 ? next : prev)();
      }}
    >
      {/* Slides (stacked, crossfade) */}
      {images.map((img, i) => (
        <div
          key={img.url + i}
          className={`absolute inset-0 transition-opacity duration-700 ${i === index ? 'opacity-100' : 'opacity-0'}`}
          aria-hidden={i !== index}
        >
          <img src={img.url} alt={img.title} loading={i === 0 ? 'eager' : 'lazy'} className="h-full w-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-black/10" />
        </div>
      ))}

      {/* Caption */}
      <div className="pointer-events-none absolute inset-x-0 bottom-14 flex justify-center px-6 md:bottom-16">
        <span className="max-w-xl truncate rounded-full bg-black/40 px-4 py-1.5 text-sm font-medium text-white backdrop-blur">
          {images[index].title}
        </span>
      </div>

      {/* Arrows */}
      {count > 1 && (
        <>
          <button type="button" onClick={prev} aria-label="Previous photo" className="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-slate-900 shadow-md transition-all hover:bg-white">
            <ChevronLeft className="h-5 w-5" />
          </button>
          <button type="button" onClick={next} aria-label="Next photo" className="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-slate-900 shadow-md transition-all hover:bg-white">
            <ChevronRight className="h-5 w-5" />
          </button>
        </>
      )}

      {/* Dots */}
      {count > 1 && (
        <div className="absolute inset-x-0 bottom-4 flex justify-center gap-1.5">
          {images.map((_, i) => (
            <button
              key={i}
              type="button"
              onClick={() => setIndex(i)}
              aria-label={`Go to photo ${i + 1}`}
              className={`h-2 rounded-full transition-all ${i === index ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'}`}
            />
          ))}
        </div>
      )}
    </div>
  );
}
