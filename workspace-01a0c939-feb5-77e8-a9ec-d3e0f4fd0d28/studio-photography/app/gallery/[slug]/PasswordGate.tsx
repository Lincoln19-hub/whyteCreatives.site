'use client';

import { useState } from "react";
import Link from "next/link";

export default function PasswordGate({ slug, title }: { slug: string; title: string }) {
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true);
    setError("");
    try {
      const res = await fetch(`/api/galleries/${slug}/unlock`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password }),
      });
      const data = await res.json();
      if (res.ok) window.location.reload();
      else setError(data?.error || "Incorrect password. Please try again.");
    } catch {
      setError("Connection error — please try again.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6">
      <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
        <div className="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-2xl">🔒</div>
        <h2 className="text-2xl font-bold text-slate-900">Protected Client Gallery</h2>
        <p className="mt-2 text-sm leading-relaxed text-slate-500">
          This is a secure, private gallery{title ? ` for ${title}` : ""}. Enter your access password to unlock your photos.
        </p>
        <form onSubmit={submit} className="mt-6 space-y-4">
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            placeholder="Enter password"
            className="w-full rounded-lg border border-slate-300 px-4 py-3 text-center text-slate-800 focus:border-slate-900 focus:outline-none"
          />
          <button type="submit" disabled={busy} className="w-full rounded-full bg-slate-900 py-3 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-60">
            {busy ? "Unlocking…" : "Unlock Gallery"}
          </button>
        </form>
        {error && <p className="mt-3 text-xs font-medium text-red-500">⚠️ {error}</p>}
        <Link href="/" className="mt-6 inline-block text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900">
          Back to Homepage
        </Link>
      </div>
    </main>
  );
}
