'use client';

import { useState } from 'react';
import { Camera, Lock, Loader2 } from 'lucide-react';
import { BRAND } from '@/lib/brand';

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
    <main className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 px-6">
      <div className="w-full max-w-sm rounded-2xl border border-white/10 bg-white p-8 shadow-2xl">
        <div className="mb-6 flex flex-col items-center">
          <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
            <Camera className="h-6 w-6 text-slate-800" />
          </div>
          <h1 className="text-xl font-bold text-slate-900">{BRAND} Admin</h1>
          <p className="mt-1 text-xs text-slate-400">Management area — studio staff only</p>
        </div>
        <form onSubmit={submit} className="space-y-4">
          <div className="relative">
            <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              autoFocus
              placeholder="Admin password"
              className="w-full rounded-xl border border-slate-300 py-3 pl-10 pr-4 text-slate-800 focus:border-slate-900 focus:outline-none"
            />
          </div>
          <button
            type="submit"
            disabled={busy}
            className="w-full rounded-full bg-slate-900 py-3 text-sm font-bold text-white transition-colors hover:bg-slate-800 disabled:opacity-60"
          >
            {busy ? <Loader2 className="mx-auto h-4 w-4 animate-spin" /> : 'Sign In'}
          </button>
        </form>
        {error && <p className="mt-3 text-center text-xs font-medium text-red-500">⚠️ {error}</p>}
      </div>
    </main>
  );
}
