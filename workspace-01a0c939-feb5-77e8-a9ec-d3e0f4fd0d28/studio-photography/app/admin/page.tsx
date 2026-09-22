import { getDashboardData } from '@/lib/data';
import { formatCurrency, formatDate, getStatusColor } from '@/lib/utils';
import { TrendingUp, CalendarCheck, FileText, Users, ArrowUpRight, ArrowDownRight, Camera, Package } from 'lucide-react';
import Link from 'next/link';

export const dynamic = 'force-dynamic';

const iconMap: Record<string, typeof TrendingUp> = {
  TrendingUp,
  CalendarCheck,
  FileText,
  Users,
};

interface Stat {
  label: string;
  value: string;
  icon: string;
  color: string;
  trend: string;
  trendUp: boolean;
}

export default async function AdminDashboard() {
  const { stats, recentBookings, recentInvoices } = await getDashboardData();

  return (
    <div className="space-y-8 p-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p className="text-slate-500">Welcome back! Here&apos;s an overview of your studio.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat: Stat) => {
          const Icon = iconMap[stat.icon] || TrendingUp;
          return (
            <div key={stat.label} className="card">
              <div className="flex items-center justify-between">
                <div className={`rounded-full p-2 ${stat.color}`}>
                  <Icon className="h-5 w-5" />
                </div>
                <span className={`flex items-center text-sm ${stat.trendUp ? 'text-green-600' : 'text-red-600'}`}>
                  {stat.trend}
                  {stat.trendUp ? <ArrowUpRight className="ml-1 h-4 w-4" /> : <ArrowDownRight className="ml-1 h-4 w-4" />}
                </span>
              </div>
              <div className="mt-3">
                <p className="text-2xl font-bold text-slate-900">{stat.value}</p>
                <p className="text-sm text-slate-500">{stat.label}</p>
              </div>
            </div>
          );
        })}
      </div>

      {/* Quick Actions */}
      <div className="card">
        <h2 className="mb-4 text-lg font-semibold text-slate-900">Session & Package Management</h2>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Link
            href="/admin/sessions"
            className="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition-colors hover:bg-slate-50"
          >
            <div className="rounded-full bg-blue-50 p-2 text-blue-600">
              <Camera className="h-5 w-5" />
            </div>
            <div>
              <p className="font-medium text-slate-900">Sessions</p>
              <p className="text-sm text-slate-500">Manage session types</p>
            </div>
          </Link>
          <Link
            href="/admin/packages"
            className="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition-colors hover:bg-slate-50"
          >
            <div className="rounded-full bg-purple-50 p-2 text-purple-600">
              <Package className="h-5 w-5" />
            </div>
            <div>
              <p className="font-medium text-slate-900">Packages</p>
              <p className="text-sm text-slate-500">Manage pricing packages</p>
            </div>
          </Link>
          <Link
            href="/book"
            className="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition-colors hover:bg-slate-50"
          >
            <div className="rounded-full bg-green-50 p-2 text-green-600">
              <CalendarCheck className="h-5 w-5" />
            </div>
            <div>
              <p className="font-medium text-slate-900">Booking Page</p>
              <p className="text-sm text-slate-500">View live booking</p>
            </div>
          </Link>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* Recent Bookings */}
        <div className="card">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-slate-900">Recent Bookings</h2>
            <Link href="/admin/bookings" className="text-sm text-blue-600 hover:underline">View all</Link>
          </div>
          <div className="space-y-3">
            {recentBookings.slice(0, 3).map((booking: { id: string; client: { name: string }; service: string; eventDate: Date; status: string }) => (
              <div key={booking.id} className="flex items-center justify-between rounded-lg border border-slate-100 p-3">
                <div>
                  <p className="font-medium text-slate-900">{booking.client.name}</p>
                  <p className="text-sm text-slate-500">{booking.service} • {formatDate(booking.eventDate)}</p>
                </div>
                <span className={`badge ${getStatusColor(booking.status)}`}>{booking.status}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Recent Invoices */}
        <div className="card">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-slate-900">Recent Invoices</h2>
          </div>
          <div className="space-y-3">
            {recentInvoices.slice(0, 3).map((invoice: { id: string; invoiceNumber: string; client: { name: string }; total: number; status: string }) => (
              <div key={invoice.id} className="flex items-center justify-between rounded-lg border border-slate-100 p-3">
                <div>
                  <p className="font-medium text-slate-900">{invoice.invoiceNumber}</p>
                  <p className="text-sm text-slate-500">{invoice.client.name}</p>
                </div>
                <div className="text-right">
                  <p className="font-medium text-slate-900">GHS {invoice.total.toFixed(2)}</p>
                  <span className={`badge ${getStatusColor(invoice.status)}`}>{invoice.status}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
