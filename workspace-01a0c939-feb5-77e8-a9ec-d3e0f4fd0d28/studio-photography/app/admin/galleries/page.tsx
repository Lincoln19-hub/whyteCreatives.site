'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { Images, Plus, Loader2, Trash2, ExternalLink, Lock, CreditCard } from 'lucide-react';

interface Gallery {
  id: number;
  slug: string;
  title: string;
  clientName: string;
  clientEmail: string;
  passwordHash: string;
  balance: string;
  status: string;
  expiryDate: string;
  createdAt: string;
}

export default function GalleriesPage() {
  const [galleries, setGalleries] = useState<Gallery[]>([]);
  const [loading, setLoading] = useState(true);
  const [creating, setCreating] = useState(false);
  const [error, setError] = useState('');
  const [form, setForm] = useState({ title: '', clientName: '', clientEmail: '', password: '', balance: '0', expiryDate: '' });

  async function load() {
    try {
      const res = await fetch('/api/admin/galleries');
      const data = await res.json();
      setGalleries(Array.isArray(data) ? data : []);
    } catch { setGalleries([]); }
    setLoading(false);
  }
  useEffect(() => { load(); }, []);

  async function create(e: React.FormEvent) {
    e.preventDefault();
    setCreating(true);
    setError('');
    try {
      const res = await fetch('/api/admin/galleries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (res.ok) {
        window.location.href = `/admin/galleries/${data.id}`;
        return;
      }
      setError(data?.error || 'Failed to create gallery.');
    } catch { setError('Connection error — please try again.'); }
    setCreating(false);
  }

  async function remove(g: Gallery) {
    if (!confirm(`Delete "${g.title}"?\n\nThe gallery and ALL its photos are removed permanently. Clients with the link will no longer be able to view it.`)) return;
    const res = await fetch(`/api/admin/galleries/${g.id}`, { method: 'DELETE' }).catch(() => null);
    if (res?.ok) setGalleries((prev) => prev.filter((x) => x.id !== g.id));
    else alert('Failed to delete gallery.');
  }

  if (loading) {
    return <div className="flex h-64 items-center justify-center"><Loader2 className="h-8 w-8 animate-spin text-slate-400" /></div>;
  }

  return (
    <div className="space-y-6 p-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-900">Client Galleries</h1>
        <p className="text-slate-500">{galleries.length} delivered galler{galleries.length === 1 ? 'y' : 'ies'}</p>
      </div>

      {/* Create new gallery */}
      <form onSubmit={create} className="card space-y-4 p-6">
        <h2 className="flex items-center gap-2 text-base font-semibold text-slate-900">
          <Plus className="h-4 w-4" /> Deliver a New Gallery
        </h2>
        <div className="grid gap-3 md:grid-cols-3">
          <input className="input" required placeholder="Gallery title * (e.g. Ama & Kojo Wedding)" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
          <input className="input" required placeholder="Client name *" value={form.clientName} onChange={(e) => setForm({ ...form, clientName: e.target.value })} />
          <input className="input" type="email" placeholder="Client email (for balance receipts)" value={form.clientEmail} onChange={(e) => setForm({ ...form, clientEmail: e.target.value })} />
          <input className="input" placeholder="Access password (optional)" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
          <input className="input" type="number" step="0.01" min="0" placeholder="Balance to unlock downloads (GHS)" value={form.balance} onChange={(e) => setForm({ ...form, balance: e.target.value })} />
          <input className="input" type="date" title="Expiry date (optional)" value={form.expiryDate} onChange={(e) => setForm({ ...form, expiryDate: e.target.value })} />
        </div>
        {error && <p className="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600">⚠️ {error}</p>}
        <button type="submit" disabled={creating} className="btn btn-primary disabled:opacity-60">
          {creating ? <Loader2 className="h-4 w-4 animate-spin" /> : <Plus className="h-4 w-4" />}
          {creating ? 'Creating…' : 'Create Gallery & Add Photos'}
        </button>
      </form>

      {/* Gallery list */}
      {galleries.length === 0 ? (
        <div className="card flex flex-col items-center justify-center py-12">
          <Images className="mb-3 h-12 w-12 text-slate-300" />
          <p className="text-slate-500">No galleries yet</p>
          <p className="mt-1 text-sm text-slate-400">Create one above — then upload photos or import from Google Drive.</p>
        </div>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {galleries.map((g) => (
            <div key={g.id} className="card flex flex-col p-5">
              <div className="mb-3 flex items-start justify-between gap-2">
                <div className="min-w-0">
                  <h3 className="truncate font-semibold text-slate-900">{g.title}</h3>
                  <p className="truncate text-xs text-slate-400">{g.clientName} • /gallery/{g.slug}</p>
                </div>
                <button onClick={() => remove(g)} title="Delete gallery" className="shrink-0 rounded-full border border-slate-200 p-2 text-slate-400 transition-all hover:border-red-300 hover:bg-red-50 hover:text-red-600">
                  <Trash2 className="h-3.5 w-3.5" />
                </button>
              </div>
              <div className="mb-3 flex flex-wrap gap-1.5">
                {g.passwordHash && <span className="flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-500"><Lock className="h-2.5 w-2.5" /> Password</span>}
                <span className={`rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ${g.status === 'paid' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
                  {Number(g.balance) > 0 ? (g.status === 'paid' ? 'Paid' : `GHS ${Number(g.balance).toFixed(0)} due`) : 'Free access'}
                </span>
                {g.expiryDate && <span className="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-600">Until {g.expiryDate}</span>}
              </div>
              <div className="mt-auto flex gap-2">
                <Link href={`/admin/galleries/${g.id}`} className="btn btn-primary flex-1 justify-center text-xs">Manage Photos</Link>
                <a href={`/gallery/${g.slug}`} target="_blank" rel="noopener" title="Open live gallery" className="rounded-full border border-slate-200 p-2.5 text-slate-400 hover:text-slate-900">
                  <ExternalLink className="h-3.5 w-3.5" />
                </a>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
