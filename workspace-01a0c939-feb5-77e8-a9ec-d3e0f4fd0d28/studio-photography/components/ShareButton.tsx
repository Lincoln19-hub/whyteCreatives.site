'use client';

import { Share2 } from 'lucide-react';
import { BRAND } from '@/lib/brand';

/**
 * One-tap social sharing — sends the actual image file to Instagram / TikTok /
 * WhatsApp via the phone's native share sheet (Web Share API).
 * Desktop fallback: downloads the photo for manual posting.
 */
export default function ShareButton({ url, title, compact = false }: { url: string; title?: string; compact?: boolean }) {
  async function share() {
    const name = (title || 'studio-photo').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
    try {
      const res = await fetch(url, { mode: 'cors' });
      if (!res.ok) throw new Error('fetch');
      const blob = await res.blob();
      const file = new File([blob], `${name}.jpg`, { type: blob.type || 'image/jpeg' });
      if (typeof navigator !== 'undefined' && navigator.canShare && navigator.canShare({ files: [file] })) {
        await navigator.share({ files: [file], title: title || BRAND });
        return;
      }
      throw new Error('no-native-share');
    } catch {
      // Fallback: open the image for download (then post in the app of choice)
      const a = document.createElement('a');
      a.href = url;
      a.download = name;
      a.target = '_blank';
      a.rel = 'noopener';
      a.click();
    }
  }

  return (
    <button
      type="button"
      onClick={share}
      title="Share to Instagram / TikTok / WhatsApp"
      className={`inline-flex items-center justify-center rounded-full bg-white/95 text-slate-800 shadow-lg transition-all hover:scale-105 ${compact ? 'h-9 w-9' : 'h-9 px-3 text-xs font-bold uppercase tracking-widest'}`}
    >
      <Share2 className="h-4 w-4" />
      {!compact && <span className="ml-1.5">Share</span>}
    </button>
  );
}
