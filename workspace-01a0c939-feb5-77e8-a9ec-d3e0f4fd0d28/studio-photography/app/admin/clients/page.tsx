import { getAllClients } from '@/lib/data';
import { formatDate } from '@/lib/utils';
import { Users } from 'lucide-react';

export const dynamic = 'force-dynamic';

interface Client {
  id: string;
  name: string;
  email: string;
  phone?: string;
  address?: string;
  createdAt: Date;
  _count?: {
    bookings: number;
    invoices: number;
  };
}

export default async function ClientsPage() {
  const clients = await getAllClients() as Client[];

  return (
    <div className="p-6">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-slate-900">Clients</h1>
        <p className="mt-1 text-sm text-slate-500">{clients.length} total clients</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {clients.length === 0 ? (
          <div className="col-span-full card text-center py-12">
            <Users className="mx-auto mb-2 h-8 w-8 text-slate-300" />
            <div className="text-sm text-slate-500">No clients yet</div>
          </div>
        ) : (
          clients.map((client) => (
            <div key={client.id} className="card">
              <div className="mb-4 flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-600">
                  {client.name.split(' ').map((n: string) => n[0]).join('').slice(0, 2)}
                </div>
                <div>
                  <div className="text-sm font-semibold text-slate-900">{client.name}</div>
                  <div className="text-xs text-slate-500">{client.email}</div>
                </div>
              </div>
              {client.phone && <div className="mb-2 text-xs text-slate-600">📞 {client.phone}</div>}
              {client.address && <div className="mb-3 text-xs text-slate-600">📍 {client.address}</div>}
              <div className="flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                <span className="text-slate-500">
                  {client._count?.bookings || 0} bookings · {client._count?.invoices || 0} invoices
                </span>
              </div>
              <div className="mt-2 text-xs text-slate-400">Client since {formatDate(client.createdAt)}</div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
