// Demo data for the studio dashboard
// This uses static demo data since the main focus is Session & Package Management

const DEMO_CLIENTS = [
  { id: 'c1', name: 'Sarah & James Mensah', email: 'sarah.mensah@email.com', phone: '+233 24 123 4567', address: 'East Legon, Accra', createdAt: new Date('2026-06-01'), _count: { bookings: 2, invoices: 2 } },
  { id: 'c2', name: 'Kofi Asante', email: 'kofi.asante@company.com', phone: '+233 20 987 6543', address: 'Airport Residential, Accra', createdAt: new Date('2026-06-15'), _count: { bookings: 1, invoices: 1 } },
  { id: 'c3', name: 'Ama Serwaa', email: 'ama.serwaa@email.com', phone: '+233 27 555 1234', address: 'Osu, Accra', createdAt: new Date('2026-07-01'), _count: { bookings: 0, invoices: 0 } },
];

const DEMO_BOOKINGS = [
  { id: 'b1', clientId: 'c1', service: 'Wedding', eventDate: new Date('2026-08-15'), duration: 8, location: 'La Palm Royal Beach Hotel', budget: 5000, status: 'confirmed', createdAt: new Date('2026-06-01'), client: { name: 'Sarah & James Mensah', email: 'sarah.mensah@email.com' } },
  { id: 'b2', clientId: 'c2', service: 'Event', eventDate: new Date('2026-07-20'), duration: 4, location: 'Kempinski Hotel, Accra', budget: 2000, status: 'pending', createdAt: new Date('2026-06-15'), client: { name: 'Kofi Asante', email: 'kofi.asante@company.com' } },
  { id: 'b3', clientId: 'c3', service: 'Portrait', eventDate: new Date('2026-07-10'), duration: 2, location: 'Studio', budget: 500, status: 'completed', createdAt: new Date('2026-07-01'), client: { name: 'Ama Serwaa', email: 'ama.serwaa@email.com' } },
];

const DEMO_INVOICES = [
  { id: 'inv1', invoiceNumber: 'INV-2026-001', clientId: 'c1', amount: 4500, total: 5000, status: 'paid', dueDate: new Date('2026-08-10'), createdAt: new Date('2026-06-01'), client: { name: 'Sarah & James Mensah', email: 'sarah.mensah@email.com' } },
  { id: 'inv2', invoiceNumber: 'INV-2026-002', clientId: 'c2', amount: 1800, total: 2000, status: 'unpaid', dueDate: new Date('2026-07-15'), createdAt: new Date('2026-06-15'), client: { name: 'Kofi Asante', email: 'kofi.asante@company.com' } },
  { id: 'inv3', invoiceNumber: 'INV-2026-003', clientId: 'c3', amount: 450, total: 500, status: 'paid', dueDate: new Date('2026-07-10'), createdAt: new Date('2026-07-01'), client: { name: 'Ama Serwaa', email: 'ama.serwaa@email.com' } },
];

export async function getDashboardData() {
  return {
    stats: [
      { label: 'Total Revenue', value: 'GHS 5,500.00', icon: 'TrendingUp', color: 'text-green-600 bg-green-50', trend: '+12.5%', trendUp: true },
      { label: 'Pending Bookings', value: '2', icon: 'CalendarCheck', color: 'text-blue-600 bg-blue-50', trend: '2 upcoming', trendUp: true },
      { label: 'Unpaid Invoices', value: '1', icon: 'FileText', color: 'text-red-600 bg-red-50', trend: 'Action needed', trendUp: false },
      { label: 'Total Clients', value: '3', icon: 'Users', color: 'text-purple-600 bg-purple-50', trend: '+1 this month', trendUp: true },
    ],
    recentBookings: DEMO_BOOKINGS,
    recentInvoices: DEMO_INVOICES,
  };
}

export async function getAllBookings() {
  return DEMO_BOOKINGS;
}

export async function getAllInvoices() {
  return DEMO_INVOICES;
}

export async function getInvoice(id: string) {
  return DEMO_INVOICES.find(inv => inv.id === id) || null;
}

export async function getAllClients() {
  return DEMO_CLIENTS;
}
