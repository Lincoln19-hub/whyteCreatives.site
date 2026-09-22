'use client';

import { useRef, useState } from 'react';
import { Upload, Loader2, X } from 'lucide-react';

/** Convert Google Drive share links to direct CDN image URLs */
export function toDirectImageUrl(url: string): string {
  const m = url.match(/drive\.google\.com[^\s]*?(?:\/file\/d\/|[?&]id=)([a-zA-Z0-9_-]+)/);
  return m ? `https://lh3.googleusercontent.com/d/${m[1]}` : url;
}

/** Downscale + compress an image file entirely in the browser (serverless-safe) */
export function compressImageFile(file: File, maxWidth = 1600, quality = 0.85): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => {
      const img = new Image();
      img.onload = () => {
        const scale = Math.min(1, maxWidth / img.width);
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(img.width * scale);
        canvas.height = Math.round(img.height * scale);
        const ctx = canvas.getContext('2d');
        if (!ctx) return reject(new Error('canvas'));
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        resolve(canvas.toDataURL('image/jpeg', quality));
      };
      img.onerror = () => reject(new Error('image'));
      img.src = String(reader.result);
    };
    reader.onerror = () => reject(new Error('file'));
    reader.readAsDataURL(file);
  });
}

/**
 * One visual image slot: preview + upload tile + URL/Drive paste.
 * onChange('') = cleared (falls back to the site default).
 */
export default function ImageSlotEditor({
  label,
  hint,
  value,
  fallbackPreview,
  aspect = 'aspect-video',
  onChange,
}: {
  label: string;
  hint?: string;
  value: string;
  fallbackPreview?: string;
  aspect?: string;
  onChange: (url: string) => void;
}) {
  const [busy, setBusy] = useState(false);
  const [pasteMode, setPasteMode] = useState(false);
  const [pasteValue, setPasteValue] = useState('');
  const fileRef = useRef<HTMLInputElement>(null);
  const isData = value.startsWith('data:');
  const preview = value || fallbackPreview || '';

  async function handleFile(file: File) {
    setBusy(true);
    try {
      onChange(await compressImageFile(file));
    } catch { alert('Could not process that image — try pasting a URL instead.'); }
    setBusy(false);
  }

  return (
    <div className="card p-5">
      <div className="mb-3 flex items-center justify-between">
        <div>
          <h3 className="text-sm font-bold text-slate-900">{label}</h3>
          {hint && <p className="text-xs text-slate-400">{hint}</p>}
        </div>
        {value && (
          <button
            type="button"
            onClick={() => onChange('')}
            title="Reset to site default"
            className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-500 hover:bg-red-50 hover:text-red-600"
          >
            <X className="h-3 w-3" /> Reset
          </button>
        )}
      </div>

      <div className={`relative ${aspect} w-full overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50`}>
        {preview ? (
          <>
            <img src={preview} alt={label} className="h-full w-full object-cover" />
            {!value && (
              <span className="absolute left-2 top-2 rounded-full bg-black/50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white">Default</span>
            )}
            {isData && (
              <span className="absolute left-2 top-2 rounded-full bg-green-600 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white">Uploaded</span>
            )}
          </>
        ) : (
          <div className="flex h-full items-center justify-center text-xs text-slate-400">No image set — site default is used</div>
        )}
      </div>

      <div className="mt-3 flex flex-wrap gap-2">
        <button type="button" onClick={() => fileRef.current?.click()} disabled={busy} className="inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 disabled:opacity-60">
          {busy ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Upload className="h-3.5 w-3.5" />}
          {busy ? 'Processing…' : 'Upload'}
        </button>
        <input ref={fileRef} type="file" accept="image/*" className="hidden" onChange={(e) => { const f = e.target.files?.[0]; if (f) handleFile(f); e.target.value = ''; }} />
        <button type="button" onClick={() => setPasteMode((s) => !s)} className="rounded-full border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:border-slate-900 hover:text-slate-900">
          Paste URL / Drive link
        </button>
      </div>

      {pasteMode && (
        <div className="mt-2 flex gap-2">
          <input
            className="input flex-1 text-xs"
            placeholder="https://… or https://drive.google.com/file/d/…/view"
            value={pasteValue}
            onChange={(e) => setPasteValue(e.target.value)}
          />
          <button
            type="button"
            onClick={() => { const v = pasteValue.trim(); if (v) { onChange(toDirectImageUrl(v)); setPasteValue(''); setPasteMode(false); } }}
            className="rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800"
          >
            Use
          </button>
        </div>
      )}
    </div>
  );
}
