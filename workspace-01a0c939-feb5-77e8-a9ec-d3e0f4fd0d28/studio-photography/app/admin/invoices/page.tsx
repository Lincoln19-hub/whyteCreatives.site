'use client';

import { useEffect, useState } from 'react';
import { FileText, Loader2, Trash2, Plus, CheckCircle, XCircle, Search } from 'lucide-react';

interface Invoice {
  id: number;
  number: string;
  bookingId: number | null;
  galleryId: number | null;
  purpose: string;
  clientName: string;
  clientEmail: string;
  total: string;
  status: string;
  paystackRef: string;
  dueDate: string;
  notes: string;
  createdAt: string;
}

const PURPOSE_LABELS: Record<string, string> = {
  booking_deposit: 'Booking Deposit',
  gallery_balance: 'Gallery Balance',
  manual: 'Manual',
};

export default function InvoicesPage() {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState<number | null>(null);
  const [search, setSearch] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [creating, setCreating] = useState(false);
  const [error, setError] = useState('');
  const [form, setForm] = useState({ clientName: '', clientEmail: '', total: '', dueDate: '', notes: '' });

  async function load() {
    try {
      const res = await fetch('/api/admin/invoices');
      const data = await res.json();
      setInvoices(Array.isArray(data) ? data : []);
    } catch { setInvoices([]); }
    setLoading(false);
  }
  useEffect(() => { load(); }, []);

  async function create(e: React.FormEvent) {
    e.preventDefault();
    setCreating(true);
    setError('');
    try {
      const res = await fetch('/api/admin/invoices', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || 'Failed');
      setShowForm(false);
      setForm({ clientName: '', clientEmail: '', total: '', dueDate: '', notes: '' });
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create invoice.');
    }
    setCreating(false);
  }

  async function toggleStatus(inv: Invoice) {
    setBusy(inv.id);
    const res = await fetch(`/api/admin/invoices/${inv.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: inv.status === 'paid' ? 'unpaid' : 'paid' }),
    }).catch(() => null);
    if (res?.ok) {
      setInvoices((prev) => prev.map((i) => (i.id === inv.id ? { ...i, status: i.status === 'paid' ? 'unpaid' : 'paid' } : i)));
    } else alert('Failed to update invoice.');
    setBusy(null);
  }

  async function remove(inv: Invoice) {
    if (!confirm(`Delete invoice ${inv.number} (${inv.clientName})?\n\nThis cannot be undone.`)) return;
    setBusy(inv.id);
    const res = await fetch(`/api/admin/invoices/${inv.id}`, { method: 'DELETE' }).catch(() => null);
    if (res?.ok) setInvoices((prev) => prev.filter((i) => i.id !== inv.id));
    else alert('Failed to delete invoice.');
    setBusy(null);
  }

  const outstanding = invoices.filter((i) => i.status !== 'paid').reduce((s, i) => s + Number(i.total), 0);
  const earned = invoices.filter((i) => i.status === 'paid').reduce((s, i) => s + Number(i.total), 0);
  const q = search.toLowerCase();
  const filtered = invoices.filter((i) =>
    !q || i.clientName.toLowerCase().includes(q) || i.number.toLowerCase().includes(q) || (i.clientEmail ?? '').toLowerCase().includes(q)
  );

  if (loading) return <div className="flex h-64 items-center justify-center"><Loader2 className="h-8 w-8 animate-spin text-slate-400" /></div>;

  return (
    <div className="space-y-6 p-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Invoices</h1>
          <p className="text-slate-500">{invoices.length} invoice{invoices.length === 1 ? '' : 's'} — deposits, balances & manual</p>
        </div>
        <button onClick={() => setShowForm((s) => !s)} className="btn btn-primary">
          <Plus className="h-4 w-4" /> New Invoice
        </button>
      </div>

      {/* Stats */}
      <div className="grid gap-4 sm:grid-cols-3">
        <div className="card p-5">
          <p className="text-xs font-bold uppercase tracking-wider text-slate-400">Collected</p>
          <p className="mt-1 text-2xl font-extrabold text-green-600">GHS {earned.toFixed(2)}</p>
          <p className="text-xs text-slate-400">{invoices.filter((i) => i.status === 'paid').length} paid</p>
        </div>
        <div className="card p-5">
          <p className="text-xs font-bold uppercase tracking-wider text-slate-400">Outstanding</p>
          <p className="mt-1 text-2xl font-extrabold text-red-600">GHS {outstanding.toFixed(2)}</p>
          <p className="text-xs text-slate-400">{invoices.filter((i) => i.status !== 'paid').length} unpaid</p>
        </div>
        <div className="card p-5">
          <p className="text-xs font-bold uppercase tracking-wider text-slate-400">Total Invoiced</p>
          <p className="mt-1 text-2xl font-extrabold text-slate-900">GHS {(earned + outstanding).toFixed(2)}</p>
          <p className="text-xs text-slate-400">all time</p>
        </div>
      </div>

      {/* Manual invoice form */}
      {showForm && (
        <form onSubmit={create} className="card space-y-3 p-6">
          <h2 className="text-base font-semibold text-slate-900">New Manual Invoice</h2>
          <div className="grid gap-3 md:grid-cols-4">
            <input className="input" required placeholder="Client name *" value={form.clientName} onChange={(e) => setForm({ ...form, clientName: e.target.value })} />
            <input className="input" type="email" placeholder="Client email" value={form.clientEmail} onChange={(e) => setForm({ ...form, clientEmail: e.target.value })} />
            <input className="input" required type="number" step="0.01" min="0.01" placeholder="Amount (GHS) *" value={form.total} onChange={(e) => setForm({ ...form, total: e.target.value })} />
            <input className="input" type="date" title="Due date" value={form.dueDate} onChange={(e) => setForm({ ...form, dueDate: e.target.value })} />
          </div>
          <textarea className="input" rows={2} placeholder="Line items / description (e.g. 8×10 print — GHS 50; extra hour coverage — GHS 200)" value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} />
          {error && <p className="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600">⚠️ {error}</p>}
          <div className="flex gap-2">
            <button type="submit" disabled={creating} className="btn btn-primary disabled:opacity-60">
              {creating ? <Loader2 className="h-4 w-4 animate-spin" /> : <FileText className="h-4 w-4" />}
              {creating ? 'Creating…' : 'Create Invoice'}
            </button>
            <button type="button" onClick={() => setShowForm(false)} className="rounded-full border border-slate-200 px-5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
          </div>
        </form>
      )}

      {/* Search */}
      <div className="relative max-w-sm">
        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input className="input pl-9" placeholder="Search client, email or invoice #…" value={search} onChange={(e) => setSearch(e.target.value)} />
      </div>

      {/* Table */}
      <div className="card overflow-hidden p-0">
        {filtered.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-12">
            <FileText className="mb-3 h-12 w-12 text-slate-300" />
            <p className="text-slate-500">{invoices.length === 0 ? 'No invoices yet' : 'No matches'}</p>
            <p className="mt-1 text-sm text-slate-400">Invoices are created automatically on booking deposits & gallery balances — or manually above.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                  <th className="px-6 py-3">Invoice</th>
                  <th className="px-6 py-3">Client</th>
                  <th className="px-6 py-3">Purpose</th>
                  <th className="px-6 py-3">Amount</th>
                  <th className="px-6 py-3">Due</th>
                  <th className="px-6 py-3">Status</th>
                  <th className="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {filtered.map((inv) => (
                  <tr key={inv.id} className="hover:bg-slate-50/60">
                    <td className="px-6 py-4">
                      <p className="font-mono text-xs font-bold text-slate-900">{inv.number}</p>
                      <p className="text-[11px] text-slate-400">{new Date(inv.createdAt).toLocaleDateString()}</p>
                      {inv.paystackRef && <p className="text-[10px] text-green-600">✓ {inv.paystackRef.slice(0, 24)}</p>}
                    </td>
                    <td className="px-6 py-4">
                      <p className="font-semibold text-slate-900">{inv.clientName}</p>
                      {inv.clientEmail && <p className="text-xs text-slate-400">{inv.clientEmail}</p>}
                      {inv.notes && <p className="mt-0.5 max-w-[220px] truncate text-[11px] text-slate-400" title={inv.notes}>{inv.notes}</p>}
                    </td>
                    <td className="px-6 py-4">
                      <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        {PURPOSE_LABELS[inv.purpose] ?? inv.purpose}
                      </span>
                    </td>
                    <td className="px-6 py-4 font-bold text-slate-900">GHS {Number(inv.total).toFixed(2)}</td>
                    <td className="px-6 py-4 text-slate-600">{inv.dueDate || '—'}</td>
                    <td className="px-6 py-4">
                      <span className={`rounded-full px-2.5 py-1 text-xs font-bold uppercase ${inv.status === 'paid' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
                        {inv.status}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex justify-end gap-2">
                        <a
                          href={`/invoice/${encodeURIComponent(inv.number)}`}
                          target="_blank"
                          rel="noopener"
                          title="View / Download PDF"
                          className="inline-flex h-8 items-center gap-1 rounded-full border border-blue-100 bg-blue-50 px-3 text-xs font-bold text-blue-600 hover:text-blue-800"
                        >
                          📄 PDF
                        </a>
                        <button
                          onClick={() => toggleStatus(inv)}
                          disabled={busy === inv.id}
                          title={inv.status === 'paid' ? 'Mark as unpaid' : 'Mark as paid'}
                          className={`inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-bold transition-all disabled:opacity-50 ${inv.status === 'paid' ? 'bg-slate-100 text-slate-500 hover:bg-slate-200' : 'bg-green-600 text-white hover:bg-green-500'}`}
                        >
                          {busy === inv.id ? <Loader2 className="h-3 w-3 animate-spin" /> : inv.status === 'paid' ? <XCircle className="h-3 w-3" /> : <CheckCircle className="h-3 w-3" />}
                          {inv.status === 'paid' ? 'Unpay' : 'Mark Paid'}
                        </button>
                        <button
                          onClick={() => remove(inv)}
                          disabled={busy === inv.id}
                          title="Delete invoice"
                          className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-400 transition-all hover:border-red-300 hover:bg-red-50 hover:text-red-600 disabled:opacity-50"
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
