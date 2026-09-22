'use client';

import { useState } from "react";

export default function PayBalance({ slug, balance }: { slug: string; balance: number }) {
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  async function pay() {
    setBusy(true);
    setError("");
    try {
      const res = await fetch(`/api/galleries/${slug}/pay`, { method: "POST" });
      const data = await res.json();
      if (res.ok && data.authorizationUrl) {
        window.location.href = data.authorizationUrl;
        return;
      }
      setError(data?.error || "Could not start checkout — try again.");
    } catch {
      setError("Connection error — please try again.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="shrink-0 text-center">
      <button
        onClick={pay}
        disabled={busy}
        className="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-slate-900 px-6 text-xs font-bold uppercase tracking-widest text-white shadow-md hover:bg-slate-800 disabled:opacity-60"
      >
        {busy ? "Opening Paystack…" : `💳 Pay GHS ${balance.toFixed(2)}`}
      </button>
      {error && <p className="mt-2 text-xs font-medium text-red-500">{error}</p>}
    </div>
  );
}
