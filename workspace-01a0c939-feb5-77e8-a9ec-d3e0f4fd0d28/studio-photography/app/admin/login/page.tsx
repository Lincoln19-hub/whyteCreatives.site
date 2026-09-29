'use client';

import { useState } from 'react';
import { Camera, Lock, Loader2, CalendarCheck, Images, Wallet } from 'lucide-react';
import { BRAND } from '@/lib/brand';

const FEATURES = [
  { icon: CalendarCheck, title: 'Booking flow', desc: 'Track sessions, confirmations, and deadlines.' },
  { icon: Images, title: 'Client galleries', desc: 'Share polished collections without the hassle.' },
  { icon: Wallet, title: 'Invoices & payments', desc: 'Keep billing organised from inquiry to delivery.' },
];

export default function AdminLogin() {
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true);
    setError('');
    try {
      const res = await fetch('/api/admin/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ password }),
      });
      if (res.ok) {
        window.location.href = new URLSearchParams(window.location.search).get('next') || '/admin';
        return;
      }
      const data = await res.json().catch(() => ({}));
      setError(data?.error || 'Incorrect password.');
    } catch {
      setError('Connection error — please try again.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <main className="flex min-h-screen bg-slate-50">
      {/* Marketing panel (Fotosut-style) */}
      <div className="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(59,130,246,0.25),transparent_55%)]" />
        <div className="relative flex items-center gap-2.5">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 backdrop-blur">
            <Camera className="h-4.5 w-4.5" />
          </div>
          <span className="text-xl font-bold">{BRAND}</span>
        </div>

        <div className="relative">
          <h1 className="font-serif text-4xl font-bold leading-tight">Studio workspace</h1>
          <p className="mt-3 max-w-md text-slate-300">
            Everything your shoot day, gallery, and client workflow needs — keep projects moving from booking to delivery with a single dashboard designed for creative businesses.
          </p>
          <div className="mt-10 space-y-6">
            {FEATURES.map((f) => (
              <div key={f.title} className="flex items-start gap-4">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 backdrop-blur">
                  <f.icon className="h-5 w-5 text-blue-300" />
                </div>
                <div>
                  <p className="font-semibold">{f.title}</p>
                  <p className="mt-0.5 text-sm text-slate-400">{f.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        <p className="relative text-xs text-slate-500">© {new Date().getFullYear()} {BRAND}. All rights reserved.</p>
      </div>

      {/* Sign-in panel */}
      <div className="flex w-full items-center justify-center px-6 lg:w-1/2">
        <div className="w-full max-w-sm">
          <div className="mb-8 flex flex-col items-center text-center lg:hidden">
            <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-white">
              <Camera className="h-5 w-5" />
            </div>
            <p className="text-lg font-bold text-slate-900">{BRAND}</p>
            <p className="text-xs text-slate-400">Studio workspace</p>
          </div>

          <h2 className="text-2xl font-bold text-slate-900">Welcome back</h2>
          <p className="mt-1 text-sm text-slate-500">Sign in to access your photography dashboard</p>

          <form onSubmit={submit} className="mt-8 space-y-4">
            <div className="relative">
              <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoFocus
                placeholder="Admin password"
                className="w-full rounded-xl border border-slate-300 py-3 pl-10 pr-4 text-slate-800 transition-colors focus:border-slate-900 focus:outline-none"
              />
            </div>
            <button
              type="submit"
              disabled={busy}
              className="w-full rounded-full bg-slate-900 py-3 text-sm font-bold text-white transition-all hover:bg-slate-800 disabled:opacity-60"
            >
              {busy ? <Loader2 className="mx-auto h-4 w-4 animate-spin" /> : 'Sign In'}
            </button>
          </form>
          {error && <p className="mt-3 text-center text-xs font-medium text-red-500">⚠️ {error}</p>}

          <p className="mt-8 text-center text-xs text-slate-400">
            <a href="/" className="hover:text-slate-900">← Back to website</a>
          </p>
        </div>
      </div>
    </main>
  );
}
