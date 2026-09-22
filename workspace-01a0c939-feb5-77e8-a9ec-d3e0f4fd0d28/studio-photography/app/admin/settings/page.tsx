'use client';

import { useEffect, useState } from 'react';
import { Save, Loader2, CheckCircle, XCircle, Copy, Building2, CalendarCheck, CreditCard } from 'lucide-react';

const FIELDS: { key: string; label: string; placeholder: string; section: 'profile' | 'booking' }[] = [
  { key: 'business_name', label: 'Business Name', placeholder: 'Whyte Creatives', section: 'profile' },
  { key: 'business_email', label: 'Business Email', placeholder: 'whytecobby@gmail.com', section: 'profile' },
  { key: 'business_whatsapp', label: 'WhatsApp Phone (international, no +)', placeholder: '233241234567', section: 'profile' },
  { key: 'business_location', label: 'Business Location (shown on invoices)', placeholder: 'Kumasi, Ghana', section: 'profile' },
  { key: 'default_deposit_pct', label: 'Default Deposit % (for custom work)', placeholder: '50', section: 'booking' },
  { key: 'rush_surcharge_pct', label: 'Rush Delivery Surcharge % (0–2 days)', placeholder: '40', section: 'booking' },
  { key: 'priority_surcharge_pct', label: 'Priority Surcharge % (3–5 days)', placeholder: '35', section: 'booking' },
];

export default function SettingsPage() {
  const [values, setValues] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetch('/api/admin/settings')
      .then((r) => r.json())
      .then((data) => {
        setValues(data ?? {});
        setLoading(false);
      })
      .catch(() => {
        setError('Could not load settings — is the database configured?');
        setLoading(false);
      });
  }, []);

  async function save() {
    setSaving(true);
    setSaved(false);
    setError('');
    try {
      const payload: Record<string, string> = {};
      for (const f of FIELDS) payload[f.key] = values[f.key] ?? '';
      const res = await fetch('/api/admin/settings', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error();
      setSaved(true);
      setTimeout(() => setSaved(false), 2500);
    } catch {
      setError('Failed to save — please try again.');
    } finally {
      setSaving(false);
    }
  }

  const paystackOk = values.__paystack_configured === 'yes';
  const appUrl = values.__app_url || 'https://yourdomain.com';

  if (loading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-slate-400" />
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-900">Settings</h1>
        <p className="mt-1 text-sm text-slate-500">Studio profile, booking defaults and integrations.</p>
      </div>

      {/* Studio Profile */}
      <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-slate-900">
          <Building2 className="h-4 w-4 text-slate-400" /> Studio Profile
        </h2>
        <div className="grid gap-4 sm:grid-cols-2">
          {FIELDS.filter((f) => f.section === 'profile').map((f) => (
            <div key={f.key}>
              <label className="label">{f.label}</label>
              <input
                className="input"
                placeholder={f.placeholder}
                value={values[f.key] ?? ''}
                onChange={(e) => setValues((prev) => ({ ...prev, [f.key]: e.target.value }))}
              />
            </div>
          ))}
        </div>
      </section>

      {/* Booking Defaults */}
      <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-slate-900">
          <CalendarCheck className="h-4 w-4 text-slate-400" /> Booking Defaults
        </h2>
        <div className="grid gap-4 sm:grid-cols-3">
          {FIELDS.filter((f) => f.section === 'booking').map((f) => (
            <div key={f.key}>
              <label className="label">{f.label}</label>
              <input
                className="input"
                type="number"
                placeholder={f.placeholder}
                value={values[f.key] ?? ''}
                onChange={(e) => setValues((prev) => ({ ...prev, [f.key]: e.target.value }))}
              />
            </div>
          ))}
        </div>
        <p className="mt-3 text-xs text-slate-400">
          Package deposit percentages override the default. Surcharges apply when delivery is requested within 5 days of the shoot.
        </p>
      </section>

      {/* Payments & Integrations */}
      <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 className="mb-4 flex items-center gap-2 text-base font-semibold text-slate-900">
          <CreditCard className="h-4 w-4 text-slate-400" /> Payments & Integrations
        </h2>

        <div className="space-y-4">
          <div className="flex items-center justify-between rounded-xl bg-slate-50 p-4">
            <div>
              <p className="text-sm font-semibold text-slate-800">Paystack</p>
              <p className="text-xs text-slate-500">Live keys configured via environment variables (Vercel → Settings → Env Vars).</p>
            </div>
            {paystackOk ? (
              <span className="flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-600">
                <CheckCircle className="h-3.5 w-3.5" /> Connected
              </span>
            ) : (
              <span className="flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600">
                <XCircle className="h-3.5 w-3.5" /> Not configured
              </span>
            )}
          </div>

          <div className="rounded-xl bg-slate-50 p-4">
            <p className="text-sm font-semibold text-slate-800">Paystack Webhook URL</p>
            <p className="mt-0.5 text-xs text-slate-500">Paste this into Paystack Dashboard → Settings → API Keys &amp; Webhooks:</p>
            <div className="mt-2 flex items-center gap-2">
              <code className="flex-1 truncate rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700">
                {appUrl}/api/paystack/webhook
              </code>
              <button
                type="button"
                onClick={() => navigator.clipboard?.writeText(`${appUrl}/api/paystack/webhook`)}
                className="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:text-slate-900"
                title="Copy webhook URL"
              >
                <Copy className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Save bar */}
      {error && <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm font-medium text-red-600">⚠️ {error}</p>}
      <div className="flex items-center gap-3">
        <button
          onClick={save}
          disabled={saving}
          className="inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-slate-800 disabled:opacity-60"
        >
          {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
          {saving ? 'Saving…' : 'Save Settings'}
        </button>
        {saved && <span className="flex items-center gap-1.5 text-sm font-semibold text-green-600"><CheckCircle className="h-4 w-4" /> Saved!</span>}
      </div>
    </div>
  );
}
