'use client';

import { Share2 } from 'lucide-react';
import { BRAND } from '@/lib/brand';

/** Share a link (video/post URL) via the phone's share sheet; desktop → copies the link */
export default function ShareLinkButton({ url, title }: { url: string; title?: string }) {
  async function share() {
    try {
      if (typeof navigator !== 'undefined' && navigator.share) {
        await navigator.share({ url, title: title || BRAND });
        return;
      }
      throw new Error('no-share');
    } catch {
      try {
        await navigator.clipboard?.writeText(url);
        alert('Link copied — paste it into Instagram, TikTok or WhatsApp!');
      } catch {
        window.open(url, '_blank');
      }
    }
  }

  return (
    <button
      type="button"
      onClick={share}
      title="Share this work"
      className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-slate-800 shadow-lg transition-all hover:scale-105"
    >
      <Share2 className="h-4 w-4" />
    </button>
  );
}
