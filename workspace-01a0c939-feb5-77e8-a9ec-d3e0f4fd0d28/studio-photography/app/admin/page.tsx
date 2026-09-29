import { db } from '@/lib/db';
import { bookings, invoices, galleries, sessions } from '@/lib/schema';
import { desc } from 'drizzle-orm';
import Link from 'next/link';
import { CalendarCheck, Images, FileText, Wallet, ArrowUpRight, Plus, Upload, Camera } from 'lucide-react';
import { BRAND } from '@/lib/brand';

export const dynamic = 'force-dynamic';

const MONEY = (n: number) => `GHS ${n.toFixed(2)}`;

export default async function AdminDashboard() {
  // ── Live studio numbers ──
  const [allBookings, allInvoices, allGalleries, allSessions] = await Promise.all([
    db.select().from(bookings).orderBy(desc(bookings.createdAt)),
    db.select().from(invoices).orderBy(desc(invoices.createdAt)),
    db.select().from(galleries).orderBy(desc(galleries.createdAt)),
    db.select().from(sessions),
  ]);

  const paid = allInvoices.filter((i) => i.status === 'paid');
  const unpaid = allInvoices.filter((i) => i.status !== 'paid');
  const revenue = paid.reduce((s, i) => s + Number(i.total), 0);
  const outstanding = unpaid.reduce((s, i) => s + Number(i.total), 0);
  const upcoming = allBookings
    .filter((b) => b.eventDate && b.eventDate >= new Date().toISOString().slice(0, 10) && b.status !== 'cancelled')
    .slice(0, 5);

  const stats = [
    { label: 'Revenue Collected', value: MONEY(revenue), sub: `${paid.length} paid invoice${paid.length === 1 ? '' : 's'}`, icon: Wallet, tone: 'text-green-600 bg-green-50' },
    { label: 'Outstanding', value: MONEY(outstanding), sub: `${unpaid.length} unpaid invoice${unpaid.length === 1 ? '' : 's'}`, icon: FileText, tone: 'text-red-600 bg-red-50' },
    { label: 'Bookings', value: String(allBookings.length), sub: `${upcoming.length} upcoming`, icon: CalendarCheck, tone: 'text-blue-600 bg-blue-50' },
    { label: 'Client Galleries', value: String(allGalleries.length), sub: `${allSessions.filter((s) => s.active).length} active session types`, icon: Images, tone: 'text-purple-600 bg-purple-50' },
  ];

  const quickActions = [
    { href: '/admin/galleries', label: 'Deliver a Gallery', desc: 'Upload photos or import from Drive', icon: Upload },
    { href: '/admin/invoices', label: 'Issue an Invoice', desc: 'Manual or from bookings', icon: FileText },
    { href: '/admin/sessions', label: 'Add a Session Type', desc: 'What clients can book', icon: Camera },
    { href: '/admin/images', label: 'Change Site Images', desc: 'Hero, about & slideshow', icon: Images },
  ];

  return (
    <div className="space-y-8 p-6">
      {/* Workspace hero */}
      <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-8 text-white">
        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(59,130,246,0.25),transparent_55%)]" />
        <div className="relative">
          <p className="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Studio workspace</p>
          <h1 className="mt-2 font-serif text-3xl font-bold">Welcome back to {BRAND}</h1>
          <p className="mt-1.5 max-w-xl text-sm text-slate-300">
            Everything your shoot day, gallery, and client workflow needs — keep projects moving from booking to delivery.
          </p>
        </div>
      </div>

      {/* Live stats */}
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {stats.map((s) => (
          <div key={s.label} className="card p-5">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-xs font-bold uppercase tracking-wider text-slate-400">{s.label}</p>
                <p className="mt-1.5 text-2xl font-extrabold text-slate-900">{s.value}</p>
                <p className="mt-0.5 text-xs text-slate-400">{s.sub}</p>
              </div>
              <div className={`flex h-11 w-11 items-center justify-center rounded-xl ${s.tone}`}>
                <s.icon className="h-5 w-5" />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Quick actions */}
      <div>
        <h2 className="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Quick actions</h2>
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          {quickActions.map((a) => (
            <Link key={a.href} href={a.href} className="card group flex items-center gap-3 p-4 transition-all hover:-translate-y-0.5 hover:shadow-md">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                <a.icon className="h-4 w-4" />
              </div>
              <div className="min-w-0">
                <p className="truncate text-sm font-bold text-slate-900">{a.label}</p>
                <p className="truncate text-xs text-slate-400">{a.desc}</p>
              </div>
              <ArrowUpRight className="ml-auto h-4 w-4 shrink-0 text-slate-300 transition-colors group-hover:text-slate-900" />
            </Link>
          ))}
        </div>
      </div>

      {/* Recent activity */}
      <div className="grid gap-4 lg:grid-cols-2">
        {/* Upcoming shoots */}
        <div className="card p-6">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-base font-semibold text-slate-900">Upcoming Shoots</h2>
            <Link href="/admin/bookings" className="text-xs font-bold uppercase tracking-wider text-blue-600 hover:underline">All bookings</Link>
          </div>
          {upcoming.length === 0 ? (
            <p className="py-6 text-center text-sm text-slate-400">No upcoming shoots — share your booking page!</p>
          ) : (
            <div className="divide-y divide-slate-100">
              {upcoming.map((b) => (
                <div key={b.id} className="flex items-center justify-between py-3">
                  <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-slate-900">{b.clientName}</p>
                    <p className="truncate text-xs text-slate-400">{b.sessionName} • {b.packageName}</p>
                  </div>
                  <div className="shrink-0 text-right">
                    <p className="text-sm font-bold text-slate-900">{b.eventDate}</p>
                    <p className={`text-[10px] font-bold uppercase ${b.status === 'confirmed' ? 'text-blue-600' : 'text-amber-600'}`}>{b.status}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Recent invoices */}
        <div className="card p-6">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-base font-semibold text-slate-900">Recent Invoices</h2>
            <Link href="/admin/invoices" className="text-xs font-bold uppercase tracking-wider text-blue-600 hover:underline">All invoices</Link>
          </div>
          {allInvoices.length === 0 ? (
            <p className="py-6 text-center text-sm text-slate-400">No invoices yet.</p>
          ) : (
            <div className="divide-y divide-slate-100">
              {allInvoices.slice(0, 5).map((i) => (
                <div key={i.id} className="flex items-center justify-between py-3">
                  <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-slate-900">{i.clientName}</p>
                    <p className="truncate font-mono text-xs text-slate-400">{i.number}</p>
                  </div>
                  <div className="shrink-0 text-right">
                    <p className="text-sm font-bold text-slate-900">{MONEY(Number(i.total))}</p>
                    <p className={`text-[10px] font-bold uppercase ${i.status === 'paid' ? 'text-green-600' : 'text-red-600'}`}>{i.status}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
