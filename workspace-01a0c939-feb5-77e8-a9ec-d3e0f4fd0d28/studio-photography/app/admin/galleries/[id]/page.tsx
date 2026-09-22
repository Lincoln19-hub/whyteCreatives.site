'use client';

import { useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import { ArrowLeft, Loader2, Trash2, Star, Save, Upload, FolderDown, CheckCircle } from 'lucide-react';

interface Gallery {
  id: number;
  slug: string;
  title: string;
  clientName: string;
  clientEmail: string;
  passwordHash: string;
  gdriveFolder: string;
  coverUrl: string;
  expiryDate: string;
  balance: string;
  status: string;
}

interface Photo {
  id: number;
  url: string;
  title: string;
}

export default function GalleryEditor({ params }: { params: Promise<{ id: string }> }) {
  const [id, setId] = useState<number | null>(null);
  const [gal, setGal] = useState<Gallery | null>(null);
  const [photos, setPhotos] = useState<Photo[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [importing, setImporting] = useState(false);
  const [busyPhoto, setBusyPhoto] = useState<number | null>(null);
  const [driveUrl, setDriveUrl] = useState('');
  const [notice, setNotice] = useState('');
  const [form, setForm] = useState({ title: '', clientName: '', clientEmail: '', password: '', balance: '0', expiryDate: '', coverUrl: '' });
  const fileRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    params.then(({ id: pid }) => setId(parseInt(pid, 10)));
  }, [params]);

  useEffect(() => {
    if (id === null || isNaN(id)) return;
    fetch(`/api/admin/galleries/${id}`).then((r) => r.json()).then(async (g) => {
      if (g?.error) { setNotice(g.error); setLoading(false); return; }
      setGal(g);
      setForm({
        title: g.title, clientName: g.clientName, clientEmail: g.clientEmail, password: '',
        balance: String(Number(g.balance ?? 0)), expiryDate: g.expiryDate ?? '', coverUrl: g.coverUrl ?? '',
      });
      setDriveUrl(g.gdriveFolder ?? '');
      const ph = await fetch(`/api/admin/galleries/${id}/photos`).then((r) => r.json());
      setPhotos(Array.isArray(ph) ? ph : []);
      setLoading(false);
    }).catch(() => { setLoading(false); setNotice('Failed to load gallery.'); });
  }, [id]);

  // ── client-side image compression (serverless-safe: no disk, no storage service) ──
  async function compressToDataUri(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => {
        const img = new Image();
        img.onload = () => {
          const MAX_W = 1600; // delivery quality for clients
          const scale = Math.min(1, MAX_W / img.width);
          const canvas = document.createElement('canvas');
          canvas.width = Math.round(img.width * scale);
          canvas.height = Math.round(img.height * scale);
          const ctx = canvas.getContext('2d');
          if (!ctx) return reject(new Error('canvas'));
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          resolve(canvas.toDataURL('image/jpeg', 0.85));
        };
        img.onerror = () => reject(new Error('image'));
        img.src = String(reader.result);
      };
      reader.onerror = () => reject(new Error('file'));
      reader.readAsDataURL(file);
    });
  }

  async function uploadFiles(files: FileList | null) {
    if (!files || files.length === 0 || id === null) return;
    setUploading(true);
    setNotice(`Processing ${files.length} photo${files.length === 1 ? '' : 's'}…`);
    try {
      const list: { url: string; title: string }[] = [];
      for (const f of Array.from(files)) {
        if (!f.type.startsWith('image/')) continue;
        list.push({ url: await compressToDataUri(f), title: f.name.replace(/\.[^.]+$/, '') });
      }
      if (list.length === 0) throw new Error('no images');
      const res = await fetch(`/api/admin/galleries/${id}/photos`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ photos: list }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || 'upload failed');
      const ph = await fetch(`/api/admin/galleries/${id}/photos`).then((r) => r.json());
      setPhotos(Array.isArray(ph) ? ph : []);
      setNotice(`✅ ${data.added} photo${data.added === 1 ? '' : 's'} added.`);
    } catch {
      setNotice('⚠️ Upload failed — try fewer/smaller files, or import from Google Drive instead.');
    }
    setUploading(false);
    if (fileRef.current) fileRef.current.value = '';
  }

  async function importFromDrive() {
    if (!driveUrl.trim() || id === null) return;
    setImporting(true);
    setNotice('Importing from Google Drive…');
    try {
      const res = await fetch(`/api/admin/galleries/${id}/photos`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ driveImport: driveUrl.trim() }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.error);
      const ph = await fetch(`/api/admin/galleries/${id}/photos`).then((r) => r.json());
      setPhotos(Array.isArray(ph) ? ph : []);
      setNotice(`✅ Imported ${data.added} of ${data.total} photo(s) found in the Drive folder.`);
      // remember the folder on the gallery for live-sync later
      await fetch(`/api/admin/galleries/${id}`, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ gdriveFolder: driveUrl.trim() }),
      });
    } catch {
      setNotice('⚠️ Drive import failed — check the folder is shared as "Anyone with the link".');
    }
    setImporting(false);
  }

  async function removePhoto(p: Photo) {
    if (id === null) return;
    if (!confirm(`Remove this photo?\n${p.title || ''}`)) return;
    setBusyPhoto(p.id);
    const res = await fetch(`/api/admin/galleries/${id}/photos/${p.id}`, { method: 'DELETE' }).catch(() => null);
    if (res?.ok) setPhotos((prev) => prev.filter((x) => x.id !== p.id));
    else alert('Failed to remove photo.');
    setBusyPhoto(null);
  }

  async function makeCover(p: Photo) {
    if (id === null) return;
    setBusyPhoto(p.id);
    const res = await fetch(`/api/admin/galleries/${id}`, {
      method: 'PUT', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ coverUrl: p.url }),
    }).catch(() => null);
    if (res?.ok) { setGal((g) => (g ? { ...g, coverUrl: p.url } : g)); setForm((f) => ({ ...f, coverUrl: p.url })); setNotice('✅ Cover photo updated.'); }
    setBusyPhoto(null);
  }

  async function save() {
    if (id === null) return;
    setSaving(true);
    setSaved(false);
    try {
      const payload: Record<string, unknown> = {
        title: form.title, clientName: form.clientName, clientEmail: form.clientEmail,
        balance: form.balance, expiryDate: form.expiryDate, coverUrl: form.coverUrl,
      };
      if (form.password) payload.password = form.password;
      const res = await fetch(`/api/admin/galleries/${id}`, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error();
      setSaved(true); setForm((f) => ({ ...f, password: '' }));
      setTimeout(() => setSaved(false), 2500);
    } catch { setNotice('⚠️ Failed to save settings.'); }
    setSaving(false);
  }

  if (loading) return <div className="flex h-64 items-center justify-center"><Loader2 className="h-8 w-8 animate-spin text-slate-400" /></div>;
  if (!gal) return <div className="p-6 text-slate-500">{notice || 'Gallery not found.'}</div>;

  const isCover = (p: Photo) => gal.coverUrl && p.url === gal.coverUrl;

  return (
    <div className="space-y-6 p-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <Link href="/admin/galleries" className="mb-1 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900">
            <ArrowLeft className="h-3.5 w-3.5" /> All Galleries
          </Link>
          <h1 className="text-2xl font-bold text-slate-900">{gal.title}</h1>
          <p className="text-sm text-slate-400">
            <a href={`/gallery/${gal.slug}`} target="_blank" rel="noopener" className="text-blue-600 hover:underline">/gallery/{gal.slug} ↗</a>
            {' • '}{photos.length} photo{photos.length === 1 ? '' : 's'}
            {gal.passwordHash ? ' • 🔒 password-protected' : ''}
          </p>
        </div>
      </div>

      {notice && <p className="rounded-xl bg-slate-100 px-4 py-2.5 text-sm text-slate-600">{notice}</p>}

      {/* Photo manager */}
      <section className="card p-6">
        <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-slate-900">
          <Upload className="h-4 w-4 text-slate-400" /> Photos
        </h2>

        {/* Upload tile */}
        <div className="grid gap-3 sm:grid-cols-2">
          <button
            type="button"
            onClick={() => fileRef.current?.click()}
            disabled={uploading}
            className="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 p-8 text-center transition-all hover:border-slate-900 disabled:opacity-60"
          >
            {uploading ? <Loader2 className="h-6 w-6 animate-spin text-slate-400" /> : <Upload className="h-6 w-6 text-slate-400" />}
            <span className="text-sm font-semibold text-slate-700">{uploading ? 'Processing…' : 'Click to upload photos'}</span>
            <span className="text-xs text-slate-400">Multi-select supported — auto-optimized in your browser</span>
          </button>
          <input ref={fileRef} type="file" accept="image/*" multiple className="hidden" onChange={(e) => uploadFiles(e.target.files)} />

          <div className="flex flex-col gap-2 rounded-2xl border-2 border-dashed border-slate-300 p-6">
            <span className="flex items-center gap-2 text-sm font-semibold text-slate-700"><FolderDown className="h-4 w-4 text-slate-400" /> …or import a Google Drive folder</span>
            <div className="flex gap-2">
              <input className="input flex-1" placeholder="https://drive.google.com/drive/folders/…" value={driveUrl} onChange={(e) => setDriveUrl(e.target.value)} />
              <button type="button" onClick={importFromDrive} disabled={importing} className="btn btn-primary shrink-0 text-xs disabled:opacity-60">
                {importing ? <Loader2 className="h-4 w-4 animate-spin" /> : 'Import'}
              </button>
            </div>
            <p className="text-xs text-slate-400">Every photo in the folder is imported at once (sharing must be "Anyone with the link").</p>
          </div>
        </div>

        {/* Photo grid */}
        {photos.length > 0 && (
          <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            {photos.map((p) => (
              <div key={p.id} className={`group relative overflow-hidden rounded-xl border-2 ${isCover(p) ? 'border-slate-900' : 'border-transparent'}`}>
                <img src={p.url} alt={p.title} className="aspect-square w-full object-cover" />
                {isCover(p) && <span className="absolute left-2 top-2 rounded-full bg-slate-900 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white">Cover</span>}
                <div className="absolute inset-x-0 bottom-0 flex justify-center gap-1.5 bg-gradient-to-t from-black/70 to-transparent p-2 opacity-0 transition-opacity group-hover:opacity-100">
                  <button type="button" onClick={() => makeCover(p)} disabled={busyPhoto === p.id} title="Make cover photo" className="rounded-full bg-white/90 p-2 text-slate-700 hover:text-slate-950">
                    {busyPhoto === p.id ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Star className={`h-3.5 w-3.5 ${isCover(p) ? 'fill-slate-900' : ''}`} />}
                  </button>
                  <button type="button" onClick={() => removePhoto(p)} disabled={busyPhoto === p.id} title="Remove photo" className="rounded-full bg-white/90 p-2 text-red-500 hover:text-red-700">
                    <Trash2 className="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </section>

      {/* Gallery settings */}
      <section className="card p-6">
        <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-slate-900">
          <Save className="h-4 w-4 text-slate-400" /> Gallery Settings
        </h2>
        <div className="grid gap-3 md:grid-cols-3">
          <div><label className="label">Title</label><input className="input" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} /></div>
          <div><label className="label">Client Name</label><input className="input" value={form.clientName} onChange={(e) => setForm({ ...form, clientName: e.target.value })} /></div>
          <div><label className="label">Client Email</label><input className="input" type="email" value={form.clientEmail} onChange={(e) => setForm({ ...form, clientEmail: e.target.value })} /></div>
          <div>
            <label className="label">{gal.passwordHash ? 'New Password (leave blank to keep current)' : 'Access Password (optional)'}</label>
            <input className="input" type="text" placeholder={gal.passwordHash ? '••••••••' : 'No password set'} value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
          </div>
          <div><label className="label">Balance to Unlock Downloads (GHS)</label><input className="input" type="number" step="0.01" min="0" value={form.balance} onChange={(e) => setForm({ ...form, balance: e.target.value })} /></div>
          <div><label className="label">Expiry Date (empty = never)</label><input className="input" type="date" value={form.expiryDate} onChange={(e) => setForm({ ...form, expiryDate: e.target.value })} /></div>
        </div>
        <div className="mt-4 flex items-center gap-3">
          <button onClick={save} disabled={saving} className="btn btn-primary disabled:opacity-60">
            {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
            {saving ? 'Saving…' : 'Save Settings'}
          </button>
          {saved && <span className="flex items-center gap-1.5 text-sm font-semibold text-green-600"><CheckCircle className="h-4 w-4" /> Saved!</span>}
        </div>
      </section>
    </div>
  );
}
