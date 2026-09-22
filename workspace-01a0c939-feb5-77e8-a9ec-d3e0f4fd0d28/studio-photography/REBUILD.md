# 🚀 Studio Static Rebuild — Vercel + Serverless

The full studio business engine, rebuilt as a **Next.js static/serverless app** — no WordPress, no PHP hosting, no CPU limits.

## What's implemented in this build

| Feature | Route | How it works |
|---|---|---|
| 🏠 Homepage + sessions/packages | `/` | Static/ISR from Postgres (Neon) |
| 📅 Booking + **Paystack deposit** | `/book` | Form → `POST /api/bookings` → server creates booking + invoice, inits Paystack → redirect to checkout |
| ✅ Payment callback | `/api/paystack/callback` | Verifies with Paystack → confirms booking → `/book/success` receipt |
| 🔐 **Webhook (source of truth)** | `/api/paystack/webhook` | HMAC-SHA512 signature check → marks bookings/invoices/galleries paid |
| 📸 **Client galleries** | `/gallery/[slug]` | Password gate (HMAC cookie) → Drive photos (live scrape) → **watermarked previews until balance paid** → Paystack balance checkout → full downloads |
| 🧾 Invoices | DB table | Auto-created per booking deposit + per gallery balance |
| ⏳ Gallery expiry | `/gallery/[slug]` | `expiry_date` passes → friendly lockout screen |
| 🛠 Admin (sessions & packages) | `/admin/*` | Existing scaffold CRUD (extend next) |

## ✅ Setup status (completed 2026-09-22)

- **Database**: Neon Postgres — connected, **7 tables created**, seeded with **6 sessions / 10 packages / 56 features**
- **Paystack**: LIVE keys verified against the API (in `.env`, gitignored)
- **APP_SECRET**: generated (in `.env`)
- **Migration**: `drizzle/20260922122140_init/` (apply on any fresh DB with the SQL inside)
- ⚠️ `neon_auth` schema in Neon is a system table — never include it in schema pushes

## Remaining steps (user-side)

### 🟢 Easiest path — one script does everything

```bash
bash setup-vercel.sh
```
It installs the Vercel CLI, logs you in (one browser click), creates the project, **pushes every value from `.env` automatically**, and deploys. No manual env-var typing at all.

### 🔵 Alternative — paste these into Vercel → Settings → Environment Variables

| Name | Value |
|---|---|
| `DATABASE_URL` | `postgresql://neondb_owner:npg_pXQYk7Kh9ZLx@ep-odd-bonus-atphxh50-pooler.c-9.us-east-1.aws.neon.tech/neondb?sslmode=require` |
| `PAYSTACK_PUBLIC_KEY` | `pk_live_f6e1445083527af6b579c3b096671cb06d22a22f` |
| `PAYSTACK_SECRET_KEY` | `sk_live_f0db4d7a3400044dda36659705d4d6ee0b7ae94f` |
| `NEXT_PUBLIC_PAYSTACK_PUBLIC_KEY` | `pk_live_f6e1445083527af6b579c3b096671cb06d22a22f` |
| `APP_SECRET` | `eb77cafebf8f0678b1101c0a95aabdf658f1ef7e4d4b83ff55f6a4d255a6c6b0` |
| `ADMIN_PASSWORD` | `WhyteStudio2026!` |
| `ADMIN_TOKEN` | `4bad0d994cac09814794e48038f8d4540bf7c370d313b2ecf09c1b63f62e7d12` |

Then: Paystack Dashboard → Settings → API Keys & Webhooks → `https://YOUR-DOMAIN/api/paystack/webhook`



1. **Push this folder to GitHub** (new repo)
2. **Database**: create a free project at [neon.tech](https://neon.tech) → copy the connection string
3. **Deploy**: [vercel.com/new](https://vercel.com/new) → import the repo (auto-detects Next.js)
4. **Environment variables** (Vercel → Settings → Env Vars) — see `.env.example`:
   - `DATABASE_URL` (from Neon)
   - `PAYSTACK_PUBLIC_KEY`, `PAYSTACK_SECRET_KEY`, `NEXT_PUBLIC_PAYSTACK_PUBLIC_KEY`
   - `APP_SECRET` (any long random string)
   - `ADMIN_PASSWORD` = `WhyteStudio2026!` ← your admin login (change it in `.env` + Vercel anytime)
   - `ADMIN_TOKEN` (the long random string from your `.env`)
5. **Create tables**: from your machine, `npm install` then `npx drizzle-kit push` with `DATABASE_URL` set (uses `drizzle.config.json`)
6. **Seed sessions/packages** (optional demo data): `npm run seed` if present, or add via `/admin`
7. **Paystack webhook**: Paystack Dashboard → Settings → API Keys & Webhooks → `https://yourdomain.vercel.app/api/paystack/webhook`

## Delivering a client gallery (one command)

```bash
node scripts/new-gallery.mjs --slug ama-wedding --title "Ama & Kojo Wedding" \
  --client "Ama Serwaa" --email ama@email.com --password secret123 \
  --folder "https://drive.google.com/drive/folders/XYZ" \
  --balance 500 --expiry 2027-03-15
```
→ share `https://yourdomain.com/gallery/ama-wedding` + the password. Photos stream live from the public Drive folder; downloads unlock after the balance is paid via Paystack.

## Architecture notes

- **Money**: numeric in Postgres; Paystack amounts in pesewas (`GHS × 100`)
- **Payments verified twice**: callback (UX redirect) **and** signed webhook (truth) — safe against tampering
- **Passwords**: never stored raw — `sha256(APP_SECRET + password)`; unlock cookie is an HMAC token
- **Drive photos**: `lib/gdrive.ts` parses Google's stable `embeddedfolderview` (same engine as the WP theme), with fallback scraping; 800px grid tiles, 1600px covers

## Roadmap (next phases)

1. **Portfolio page** (public, category filters) — same Drive engine, `portfolio` table
2. **Admin extensions**: bookings/galleries/invoices CRUD + stats in `/admin`
3. **Download tracking + notifications**: `download_events` table is ready; add Resend for emails
4. **Full-collection ZIP**: Vercel functions cap response size/time — use the Drive folder link for bulk, or stream a ZIP in Phase 2 via chunked functions
5. **Screenshot protections** (cloak/watermark JS) — port from the WP theme

## Local development

```bash
npm install
cp .env.example .env   # fill in values
npx drizzle-kit push   # create tables
npm run dev
```
