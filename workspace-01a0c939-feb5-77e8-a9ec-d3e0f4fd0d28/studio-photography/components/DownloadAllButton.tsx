'use client';

import { useState } from 'react';
import { Loader2, Package } from 'lucide-react';

/**
 * "Download Full Collection (ZIP)" — builds the archive entirely in the visitor's
 * browser (JSZip), so there are no server size/time limits on Vercel.
 */
export default function DownloadAllButton({ photos }: { photos: { url: string; downloadUrl: string; title: string }[] }) {
  const [busy, setBusy] = useState(false);
  const [progress, setProgress] = useState(0);

  async function downloadAll() {
    if (busy || photos.length === 0) return;
    setBusy(true);
    setProgress(0);

    try {
      const JSZip = (await import('jszip')).default;
      const zip = new JSZip();
      let added = 0;

      for (let i = 0; i < photos.length; i++) {
        setProgress(i + 1);
        const p = photos[i];
        let blob: Blob | null = null;
        for (const src of [p.downloadUrl, p.url]) {
          try {
            const res = await fetch(src, { mode: 'cors' });
            if (res.ok) { blob = await res.blob(); break; }
          } catch { /* next source */ }
        }
        if (!blob || blob.size < 1024) continue; // skip failures (likely CORS-blocked)
        const name = (p.title || `photo-${i + 1}`).replace(/[^a-z0-9]+/gi, '-').toLowerCase().slice(0, 60);
        zip.file(`${String(added + 1).padStart(3, '0')}_${name}.jpg`, blob);
        added++;
      }

      if (added === 0) throw new Error('none-fetched');

      const archive = await zip.generateAsync({ type: 'blob' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(archive);
      a.download = 'full-collection.zip';
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(a.href), 8000);
    } catch {
      alert('Some photos could not be packaged (their host blocked direct fetching). Individual photo downloads still work — or use the Google Drive folder button.');
    }
    setBusy(false);
  }

  return (
    <button
      type="button"
      onClick={downloadAll}
      disabled={busy}
      className="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-slate-900 px-6 text-xs font-bold uppercase tracking-widest text-white shadow-md transition-all hover:bg-slate-800 disabled:opacity-70"
    >
      {busy ? <Loader2 className="h-4 w-4 animate-spin" /> : <Package className="h-4 w-4" />}
      {busy ? `Packaging ${progress}/${photos.length}…` : `Download Full Collection (${photos.length}) — ZIP`}
    </button>
  );
}
