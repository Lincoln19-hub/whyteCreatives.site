'use client';

import { useEffect, useState } from 'react';
import { Loader2, Save, CheckCircle, ImageIcon } from 'lucide-react';
import ImageSlotEditor from '@/components/ImageSlotEditor';

const SLOTS = [
  { key: 'image_hero', label: 'Homepage Hero', hint: 'The big first-impression photo at the top of the site', aspect: 'aspect-[16/9]' },
  { key: 'image_about', label: 'About Section Photo', hint: 'Shown next to “About whyteCreatives”', aspect: 'aspect-[4/3]' },
  { key: 'image_gallery_1', label: 'Gallery Card 1', hint: 'Fallback card (used when no Drive folder or TikTok is set)', aspect: 'aspect-[3/4]' },
  { key: 'image_gallery_2', label: 'Gallery Card 2', hint: 'Fallback card (used when no Drive folder or TikTok is set)', aspect: 'aspect-[3/4]' },
  { key: 'image_gallery_3', label: 'Gallery Card 3', hint: 'Fallback card (used when no Drive folder or TikTok is set)', aspect: 'aspect-[3/4]' },
  { key: 'image_gallery_4', label: 'Gallery Card 4', hint: 'Fallback card (used when no Drive folder or TikTok is set)', aspect: 'aspect-[3/4]' },
];

const LABEL_KEYS = ['image_gallery_1_label', 'image_gallery_2_label', 'image_gallery_3_label', 'image_gallery_4_label'];

export default function SiteImagesPage() {
  const [values, setValues] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetch('/api/admin/settings')
      .then((r) => r.json())
      .then((data) => { setValues(data ?? {}); setLoading(false); })
      .catch(() => { setError('Could not load settings.'); setLoading(false); });
  }, []);

  async function save() {
    setSaving(true);
    setSaved(false);
    setError('');
    try {
      const payload: Record<string, string> = {};
      for (const s of SLOTS) payload[s.key] = values[s.key] ?? '';
      payload.gallery_drive_folder = values.gallery_drive_folder ?? '';
      for (const k of LABEL_KEYS) payload[k] = values[k] ?? '';
      const res = await fetch('/api/admin/settings', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error();
      setSaved(true);
      setTimeout(() => setSaved(false), 2500);
    } catch { setError('Failed to save — please try again.'); }
    setSaving(false);
  }

  if (loading) return <div className="flex h-64 items-center justify-center"><Loader2 className="h-8 w-8 animate-spin text-slate-400" /></div>;

  return (
    <div className="space-y-6 p-6">
      <div>
        <h1 className="flex items-center gap-2 text-2xl font-bold text-slate-900"><ImageIcon className="h-6 w-6 text-slate-400" /> Site Images</h1>
        <p className="mt-1 text-sm text-slate-500">Change every photo on the site — upload from your computer or paste a link. No code, ever.</p>
      </div>

      {/* Homepage gallery slideshow — Google Drive folder */}
      <div className="card p-5">
        <h3 className="text-sm font-bold text-slate-900">🎞️ Homepage Gallery Slideshow (Google Drive)</h3>
        <p className="mt-0.5 text-xs text-slate-400">Paste a public folder link — every photo inside becomes a slide in the homepage “Our Gallery” slideshow. Sharing must be “Anyone with the link”.</p>
        <div className="mt-3 flex flex-col gap-2 sm:flex-row">
          <input
            className="input flex-1"
            placeholder="https://drive.google.com/drive/folders/…"
            value={values.gallery_drive_folder ?? ''}
            onChange={(e) => setValues((prev) => ({ ...prev, gallery_drive_folder: e.target.value }))}
          />
          {values.gallery_drive_folder && (
            <button
              type="button"
              onClick={() => setValues((prev) => ({ ...prev, gallery_drive_folder: '' }))}
              className="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-500 hover:bg-red-50 hover:text-red-600"
            >
              Clear
            </button>
          )}
        </div>
        <p className="mt-2 text-[11px] text-slate-400">Leave empty to fall back to the TikTok feed or the showcase cards below.</p>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        {SLOTS.map((s) => (
          <div key={s.key}>
            <ImageSlotEditor
              label={s.label}
              hint={s.hint}
              aspect={s.aspect}
              value={values[s.key] ?? ''}
              onChange={(url) => setValues((prev) => ({ ...prev, [s.key]: url }))}
            />
            {s.key.startsWith('image_gallery_') && (
              <input
                className="input mt-2 text-xs"
                placeholder="Card label (e.g. Outdoor Portrait)"
                value={values[s.key + '_label'] ?? ''}
                onChange={(e) => setValues((prev) => ({ ...prev, [s.key + '_label']: e.target.value }))}
              />
            )}
          </div>
        ))}
      </div>

      {error && <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm font-medium text-red-600">⚠️ {error}</p>}
      <div className="flex items-center gap-3">
        <button onClick={save} disabled={saving} className="btn btn-primary disabled:opacity-60">
          {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
          {saving ? 'Saving…' : 'Save All Images'}
        </button>
        {saved && <span className="flex items-center gap-1.5 text-sm font-semibold text-green-600"><CheckCircle className="h-4 w-4" /> Saved! Live on the site.</span>}
      </div>

      <p className="text-xs text-slate-400">
        💡 Session covers and client gallery photos are managed in their own sections (Sessions / Galleries). Reset any slot here to return to the site default.
      </p>
    </div>
  );
}
