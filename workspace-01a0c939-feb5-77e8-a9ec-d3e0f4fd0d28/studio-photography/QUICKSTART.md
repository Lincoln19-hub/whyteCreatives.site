# Studio Photography App — Quick Start

## ✅ Setup Complete

Your full-stack photography studio management app is ready!

## 🚀 Start the Development Server

```bash
cd studio-app
npm run dev
```

Then open **[http://localhost:3000](http://localhost:3000)**

## 📱 Pages Overview

### Public Pages
| Page | URL | Description |
|------|-----|-------------|
| Home | `/` | Landing page with services, gallery, about |
| Book | `/book` | Public booking form for clients |

### Admin Dashboard
| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `/admin` | Overview with stats, recent bookings & invoices |
| Bookings | `/admin/bookings` | List and manage all bookings |
| Invoices | `/admin/invoices` | Invoice list with totals |
| New Invoice | `/admin/invoices/new` | Create a new invoice |
| Invoice Detail | `/admin/invoices/[id]` | View invoice details & payments |
| Receipt | `/admin/invoices/[id]/receipt` | Printable receipt view |
| Clients | `/admin/clients` | Client list with contact info |

### API Endpoints
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/bookings` | GET/POST | Manage bookings |
| `/api/invoices` | GET/POST | Manage invoices |
| `/api/payments` | POST | Record a payment |
| `/api/payments/[id]` | GET/POST | Invoice-specific payments |
| `/api/clients` | GET | List all clients |
| `/api/bookings-list` | GET | Bookings for dropdowns |

## 🌱 Sample Data

The database comes pre-seeded with:
- **3 clients** (Sarah & James Mensah, Kofi Asante, Ama Serwaa)
- **3 bookings** (wedding, event, portrait)
- **3 invoices** (1 paid, 1 unpaid, 1 paid)
- **Sample payments** with different methods

## 💡 How to Use

### Create a Booking (Public)
1. Go to `/book`
2. Fill in client details and booking info
3. Submit — creates a new booking with status "pending"

### Create an Invoice (Admin)
1. Go to `/admin/invoices/new`
2. Select a client and optional linked booking
3. Add line items with descriptions, quantities, and rates
4. Set tax rate and due date
5. Submit — generates invoice with auto-generated number

### Record a Payment
1. Go to an invoice detail page (`/admin/invoices/[id]`)
2. Click "Record Payment"
3. Enter amount, method, reference
4. Submit — automatically updates invoice status to "paid" when fully paid

### View/Print Receipt
1. From an invoice detail page, click "Print Receipt"
2. Opens a clean, printable receipt layout
3. Use browser's print function (Ctrl+P / Cmd+P)

## 🗄️ Database Management

```bash
# View/edit database visually
npx prisma studio

# Reset database
npx prisma db push --force-reset
npm run db:seed

# View raw SQL queries (already enabled in dev)
# Check lib/db.ts for Prisma log settings
```

## 🎨 Customization

### Colors
Edit `tailwind.config.ts` — primary color is `#1d4ed8` (blue-700)

### Brand
- Logo text: "Studio" — change in `app/page.tsx` and admin layout
- Contact email: update in footer and receipt pages

### Currency
Default is Ghana Cedis (GHS). Change in `lib/utils.ts`:
```typescript
currency: 'GHS'  // Change to 'USD', 'EUR', etc.
```

## 📦 Production Deployment

### Vercel (Recommended)
```bash
npm install -g vercel
vercel
```

### For Production Database
Migrate from SQLite to PostgreSQL:
1. Update `.env`: `DATABASE_URL="postgresql://..."`
2. Run `npx prisma db push`
3. Deploy

## 🛠️ Common Commands

```bash
npm run dev          # Start dev server
npm run build        # Build for production
npm run start        # Start production server
npm run db:push      # Sync database schema
npm run db:seed      # Seed with sample data
npm run db:studio    # Open Prisma Studio (visual DB editor)
```
