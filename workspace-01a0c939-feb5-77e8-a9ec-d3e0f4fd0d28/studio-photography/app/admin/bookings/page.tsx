import { getAllBookings } from '@/lib/data';
import { formatDate, getStatusColor } from '@/lib/utils';
import Link from 'next/link';
import { CalendarCheck, Plus } from 'lucide-react';

export const dynamic = 'force-dynamic';

interface Booking {
  id: string;
  service: string;
  eventDate: Date;
  location?: string;
  status: string;
  budget?: number;
  client: {
    name: string;
    email: string;
  };
}

export default async function BookingsPage() {
  const bookings = await getAllBookings() as Booking[];

  return (
    <div className="space-y-6 p-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Bookings</h1>
          <p className="text-slate-500">Manage your photography bookings</p>
        </div>
        <button className="btn btn-primary">
          <Plus className="h-4 w-4" />
          New Booking
        </button>
      </div>

      <div className="card overflow-hidden p-0">
        {bookings.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-12">
            <CalendarCheck className="mb-4 h-12 w-12 text-slate-300" />
            <p className="text-slate-500">No bookings yet</p>
          </div>
        ) : (
          <table className="w-full text-left text-sm">
            <thead className="border-b bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
              <tr>
                <th className="px-6 py-3">Client</th>
                <th className="px-6 py-3">Service</th>
                <th className="px-6 py-3">Date</th>
                <th className="px-6 py-3">Location</th>
                <th className="px-6 py-3">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {bookings.map((booking) => (
                <tr key={booking.id} className="hover:bg-slate-50">
                  <td className="px-6 py-4">
                    <p className="font-medium text-slate-900">{booking.client.name}</p>
                    <p className="text-xs text-slate-500">{booking.client.email}</p>
                  </td>
                  <td className="px-6 py-4 text-slate-600">{booking.service}</td>
                  <td className="px-6 py-4 text-slate-600">{formatDate(booking.eventDate)}</td>
                  <td className="px-6 py-4 text-slate-600">{booking.location || '—'}</td>
                  <td className="px-6 py-4">
                    <span className={`badge ${getStatusColor(booking.status)}`}>{booking.status}</span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}
