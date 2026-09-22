'use client';

import { useEffect, useState } from 'react';
import { Users, Loader2, Trash2 } from 'lucide-react';

interface Client {
  email: string;
  name: string;
  phone: string;
  bookings: number;
  spent: number;
  lastEventDate: string;
}

export default function ClientsPage() {
  const [clients, setClients] = useState<Client[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleting, setDeleting] = useState<string | null>(null);

  async function load() {
    try {
      const res = await fetch('/api/admin/clients');
      const data = await res.json();
      setClients(Array.isArray(data) ? data : []);
    } catch {
      setClients([]);
    }
    setLoading(false);
  }

  useEffect(() => { load(); }, []);

  async function remove(client: Client) {
    if (!confirm(`Delete "${client.name}" and their ${client.bookings} booking${client.bookings === 1 ? '' : 's'}?\n\nLinked invoices are removed too. Client galleries are kept. This cannot be undone.`)) return;
    setDeleting(client.email);
    try {
      const res = await fetch(`/api/admin/clients/${encodeURIComponent(client.email)}`, { method: 'DELETE' });
      if (res.ok) setClients((prev) => prev.filter((c) => c.email !== client.email));
      else alert('Failed to delete client.');
    } catch {
      alert('Connection error — please try again.');
    }
    setDeleting(null);
  }

  if (loading) {
    return <div className="flex h-64 items-center justify-center"><Loader2 className="h-8 w-8 animate-spin text-slate-400" /></div>;
  }

  return (
    <div className="p-6">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-slate-900">Clients</h1>
        <p className="mt-1 text-sm text-slate-500">{clients.length} client{clients.length === 1 ? '' : 's'} — built from your live bookings</p>
      </div>

      {clients.length === 0 ? (
        <div className="card col-span-full py-12 text-center">
          <Users className="mx-auto mb-2 h-8 w-8 text-slate-300" />
          <div className="text-sm text-slate-500">No clients yet</div>
          <p className="mt-1 text-xs text-slate-400">Clients appear here automatically after their first booking.</p>
        </div>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {clients.map((client) => (
            <div key={client.email} className="card flex flex-col">
              <div className="mb-4 flex items-start justify-between">
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-600">
                    {client.name.charAt(0).toUpperCase()}
                  </div>
                  <div>
                    <p className="font-semibold text-slate-900">{client.name}</p>
                    <p className="text-xs text-slate-400">{client.email}</p>
                  </div>
                </div>
                <button
                  onClick={() => remove(client)}
                  disabled={deleting === client.email}
                  title="Delete client & their bookings"
                  className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-400 transition-all hover:border-red-300 hover:bg-red-50 hover:text-red-600 disabled:opacity-50"
                >
                  {deleting === client.email ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Trash2 className="h-3.5 w-3.5" />}
                </button>
              </div>

              <div className="mt-auto grid grid-cols-3 gap-2 border-t pt-4 text-center">
                <div>
                  <p className="text-lg font-bold text-slate-900">{client.bookings}</p>
                  <p className="text-[10px] uppercase tracking-wider text-slate-400">Bookings</p>
                </div>
                <div>
                  <p className="text-lg font-bold text-green-600">GHS {client.spent.toFixed(0)}</p>
                  <p className="text-[10px] uppercase tracking-wider text-slate-400">Paid</p>
                </div>
                <div>
                  <p className="text-lg font-bold text-slate-900">{client.lastEventDate || '—'}</p>
                  <p className="text-[10px] uppercase tracking-wider text-slate-400">Last Shoot</p>
                </div>
              </div>

              {client.phone && <p className="mt-3 text-xs text-slate-400">📞 {client.phone}</p>}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
