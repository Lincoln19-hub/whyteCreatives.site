'use client';

import { useState } from 'react';
import { Download, Loader2 } from 'lucide-react';

/** Single-photo download: fetches the file as a blob so it SAVES (no new-tab roulette) */
export default function PhotoDownloadButton({ url, downloadUrl, title }: { url: string; downloadUrl: string; title?: string }) {
  const [busy, setBusy] = useState(false);

  async function download() {
    setBusy(true);
    const name = (title || 'studio-photo').replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.jpg';
    for (const src of [downloadUrl, url]) {
      try {
        const res = await fetch(src, { mode: 'cors' });
        if (!res.ok) continue;
        const blob = await res.blob();
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = name;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 4000);
        setBusy(false);
        return;
      } catch { /* try next source */ }
    }
    // Fallback: open the image in a new tab (user can save manually)
    window.open(url, '_blank');
    setBusy(false);
  }

  return (
    <button
      type="button"
      onClick={download}
      disabled={busy}
      title="Download Photo"
      className="flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-slate-800 shadow-lg transition-all hover:scale-105 disabled:opacity-60"
    >
      {busy ? <Loader2 className="h-4 w-4 animate-spin" /> : <Download className="h-4 w-4" />}
    </button>
  );
}
