'use client';

import Link from 'next/link';
import { BRAND } from '@/lib/brand';
import { usePathname } from 'next/navigation';
import { ReactNode, useState } from 'react';
import {
  LayoutDashboard,
  CalendarCheck,
  Camera,
  FileText,
  Images,
  Image,
  Package,
  Users,
  Settings,
  Menu,
  X,
  ExternalLink,
  LogOut,
} from 'lucide-react';

const navItems = [
  { href: '/admin', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/admin/sessions', label: 'Sessions', icon: Camera },
  { href: '/admin/packages', label: 'Packages', icon: Package },
  { href: '/admin/bookings', label: 'Bookings', icon: CalendarCheck },
  { href: '/admin/galleries', label: 'Galleries', icon: Images },
  { href: '/admin/invoices', label: 'Invoices', icon: FileText },
  { href: '/admin/clients', label: 'Clients', icon: Users },
  { href: '/admin/settings', label: 'Settings', icon: Settings },
  { href: '/admin/images', label: 'Site Images', icon: Image },
];

export default function AdminLayout({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="flex min-h-screen bg-slate-50">
      {/* Mobile sidebar toggle */}
      <button
        onClick={() => setSidebarOpen(true)}
        className="fixed left-4 top-4 z-50 rounded-lg bg-white p-2 shadow-md lg:hidden"
      >
        <Menu className="h-5 w-5" />
      </button>

      {/* Mobile overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-40 bg-black/50 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed inset-y-0 left-0 z-50 w-64 transform bg-white shadow-xl transition-transform lg:static lg:translate-x-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-full flex-col">
          {/* Logo */}
          <div className="flex items-center justify-between border-b p-4">
            <Link href="/admin" className="flex items-center gap-2 text-lg font-bold text-slate-900">
              <Camera className="h-6 w-6" />
              {BRAND}
            </Link>
            <button onClick={() => setSidebarOpen(false)} className="lg:hidden">
              <X className="h-5 w-5" />
            </button>
          </div>

          {/* Navigation */}
          <nav className="flex-1 space-y-1 p-4">
            {navItems.map((item) => {
              const isActive = item.href === '/admin' 
                ? pathname === '/admin' 
                : pathname.startsWith(item.href);
              
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={() => setSidebarOpen(false)}
                  className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                    isActive
                      ? 'bg-slate-900 text-white'
                      : 'text-slate-600 hover:bg-slate-100'
                  }`}
                >
                  <item.icon className="h-4 w-4" />
                  {item.label}
                </Link>
              );
            })}
          </nav>

          {/* Footer Links */}
          <div className="border-t p-4">
            <Link
              href="/book"
              className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
            >
              <ExternalLink className="h-4 w-4" />
              View Booking Page
            </Link>
            <Link
              href="/"
              className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
            >
              <ExternalLink className="h-4 w-4" />
              View Website
            </Link>
            <button
              type="button"
              onClick={async () => {
                await fetch('/api/admin/logout', { method: 'POST' }).catch(() => {});
                window.location.href = '/admin/login';
              }}
              className="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50"
            >
              <LogOut className="h-4 w-4" />
              Sign Out
            </button>
          </div>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex-1 overflow-x-hidden">{children}</main>
    </div>
  );
}
