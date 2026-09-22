'use client';

import { useEffect, useState } from 'react';
import { CalendarCheck, Loader2, Trash2 } from 'lucide-react';

interface Booking {
  id: number;
  sessionName: string;
  packageName: string;
  clientName: string;
  clientEmail: string;
  clientPhone: string;
  eventDate: string;
  eventLocation: string;
  total: string;
  deposit: string;
  status: string;
  paystackRef: string;
}

const STATUS_STYLES: Record<string, string> = {
  pending: 'bg-amber-50 text-amber-600',
  confirmed: 'bg-blue-50 text-blue-600',
  completed: 'bg-green-50 text-green-600',
  cancelled: 'bg-red-50 text-red-600',
};

export default function BookingsPage() {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleting, setDeleting] = useState<number | null>(null);

  async function load() {
    try {
      const res = await fetch('/api/admin/bookings');
      const data = await res.json();
      setBookings(Array.isArray(data) ? data : []);
    } catch {
      setBookings([]);
    }
    setLoading(false);
  }

  useEffect(() => { load(); }, []);

  async function remove(id: number, name: string) {
    if (!confirm(`Delete the booking for "${name}"?\n\nIts linked invoices are removed too. This cannot be undone.`)) return;
    setDeleting(id);
    try {
      const res = await fetch(`/api/admin/bookings/${id}`, { method: 'DELETE' });
      if (res.ok) setBookings((prev) => prev.filter((b) => b.id !== id));
      else alert('Failed to delete booking.');
    } catch {
      alert('Connection error — please try again.');
    }
    setDeleting(null);
  }

  if (loading) {
    return <div className="flex h-64 items-center justify-center"><Loader2 className="h-8 w-8 animate-spin text-slate-400" /></div>;
  }

  return (
    <div className="space-y-6 p-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-900">Bookings</h1>
        <p className="text-slate-500">{bookings.length} booking{bookings.length === 1 ? '' : 's'} — live from your booking page</p>
      </div>

      <div className="card overflow-hidden p-0">
        {bookings.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-12">
            <CalendarCheck className="mb-4 h-12 w-12 text-slate-300" />
            <p className="text-slate-500">No bookings yet</p>
            <p className="mt-1 text-sm text-slate-400">Bookings appear here the moment a client books on your site.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                  <th className="px-6 py-3">Client</th>
                  <th className="px-6 py-3">Session / Package</th>
                  <th className="px-6 py-3">Shoot Date</th>
                  <th className="px-6 py-3">Total / Deposit</th>
                  <th className="px-6 py-3">Status</th>
                  <th className="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {bookings.map((b) => (
                  <tr key={b.id} className="hover:bg-slate-50/60">
                    <td className="px-6 py-4">
                      <p className="font-semibold text-slate-900">{b.clientName}</p>
                      <p className="text-xs text-slate-400">{b.clientEmail}{b.clientPhone ? ` • ${b.clientPhone}` : ''}</p>
                    </td>
                    <td className="px-6 py-4">
                      <p className="font-medium text-slate-800">{b.sessionName}</p>
                      <p className="text-xs text-slate-400">{b.packageName}{b.eventLocation ? ` • ${b.eventLocation}` : ''}</p>
                    </td>
                    <td className="px-6 py-4 text-slate-600">{b.eventDate || '—'}</td>
                    <td className="px-6 py-4">
                      <p className="font-bold text-slate-900">GHS {Number(b.total).toFixed(2)}</p>
                      <p className="text-xs text-green-600">GHS {Number(b.deposit).toFixed(2)} deposit</p>
                    </td>
                    <td className="px-6 py-4">
                      <span className={`rounded-full px-2.5 py-1 text-xs font-bold uppercase ${STATUS_STYLES[b.status] ?? 'bg-slate-100 text-slate-500'}`}>
                        {b.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => remove(b.id, b.clientName)}
                        disabled={deleting === b.id}
                        title="Delete booking"
                        className="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 transition-all hover:border-red-300 hover:bg-red-50 hover:text-red-600 disabled:opacity-50"
                      >
                        {deleting === b.id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
                      </button>
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
