# 📷 Studio Photography

> Professional photography studio management system — bookings, invoicing, receipts & admin dashboard.

[![Deploy to Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/YOUR_USERNAME/studio-photography&project-name=studio-photography&repository-name=studio-photography)

---

## ✨ Features

### 🌐 Public Site
- Beautiful landing page with services, gallery & about sections
- **Online booking form** — clients can book sessions directly
- Mobile-responsive design

### 🔐 Admin Dashboard
- **Dashboard** — revenue stats, pending bookings, unpaid invoices
- **Bookings** — view, manage & track all session bookings
- **Invoicing** — create invoices with line items, tax & auto-numbering
- **Payments** — record payments (cash, card, bank transfer, mobile money)
- **Receipts** — printable receipt view for paid invoices
- **Clients** — full client directory with contact info & history

---

## 🚀 Quick Start

### 1. Install Dependencies

```bash
npm install
```

### 2. Set Up Database

```bash
npx prisma generate
npx prisma db push
npm run db:seed
```

### 3. Run Dev Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000)

---

## ️ Project Structure

```
studio-app/
├── app/
│   ├── page.tsx              ← Public landing page
│   ├── book/                 ← Public booking form
│   ├── admin/                ← Admin dashboard
│   │   ├── page.tsx          ← Dashboard overview
│   │   ├── bookings/         ← Booking management
│   │   ├── invoices/         ← Invoice management
│   │   │   ├── new/          ← Create invoice
│   │   │   └── [id]/         ← Invoice detail + receipt
│   │   └── clients/          ← Client management
│   └── api/                  ← API routes (bookings, invoices, payments, clients)
├── prisma/
│   ├── schema.prisma         ← Database schema (Client, Booking, Invoice, Payment)
│   └── seed.ts              ← Sample data (3 clients, 3 bookings, 3 invoices)
├── lib/
│   ├── db.ts                ← Prisma client singleton
│   └── utils.ts             ← Formatting & utility functions
└── public/images/            ← Static images
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Next.js 14 (App Router) |
| Language | TypeScript |
| Database | SQLite (dev) / PostgreSQL (production) |
| ORM | Prisma |
| Styling | Tailwind CSS |
| Icons | Lucide React |

---

## 📊 Database Schema

- **Client** — name, email, phone, address, notes
- **Booking** — service, event date, duration, location, budget, status (pending/confirmed/completed/cancelled)
- **Invoice** — auto-numbered, line items, tax rate, due date, status (unpaid/paid/overdue)
- **InvoiceItem** — description, quantity, rate, amount
- **Payment** — amount, method (cash/card/bank_transfer/mobile_money), reference, date

---

## 🌍 Deploy to Vercel

Click the button above, or:

```bash
npx vercel
```

### Production Database

For production, switch from SQLite to PostgreSQL:

1. Add a Postgres database (Vercel Postgres, Supabase, etc.)
2. Update the `DATABASE_URL` environment variable
3. Run `npx prisma db push` in the Vercel deployment

---

## 📝 Common Commands

```bash
npm run dev          # Start development server
npm run build        # Build for production
npm run start        # Start production server
npm run db:push      # Sync database schema
npm run db:seed      # Seed with sample data
npm run db:studio    # Open Prisma Studio (visual DB editor)
```

---

## 🔐 Environment Variables

See `.env.example` for required variables:

```env
DATABASE_URL="file:./dev.db"

# Paystack (get keys from https://dashboard.paystack.com/#/settings/developers)
PAYSTACK_PUBLIC_KEY="pk_test_xxx"
PAYSTACK_SECRET_KEY="sk_test_xxx"
PAYSTACK_WEBHOOK_SECRET="whsec_xxx"
NEXT_PUBLIC_PAYSTACK_PUBLIC_KEY="pk_test_xxx"

# Deposit percentage (default 50%)
DEPOSIT_PERCENTAGE=50
```

### Setting Up Paystack

1. Sign up at [paystack.com](https://paystack.com) (free)
2. Go to **Settings → API Keys & Webhooks**
3. Copy your **Test Keys** (start with `pk_test_` and `sk_test_`)
4. Add them to your `.env` file (local) or Vercel environment variables (production)
5. For production, switch to **Live Keys** when ready

### Webhook Setup (for payment confirmations)

In your Paystack dashboard:
1. Go to **Settings → API Keys & Webhooks**
2. Add webhook URL: `https://your-domain.com/api/webhooks/paystack`
3. Copy the webhook secret and add as `PAYSTACK_WEBHOOK_SECRET`

For production with PostgreSQL:
```env
DATABASE_URL="postgresql://user:password@host:5432/studio_db?schema=public"
```

---

## 📄 License

MIT
