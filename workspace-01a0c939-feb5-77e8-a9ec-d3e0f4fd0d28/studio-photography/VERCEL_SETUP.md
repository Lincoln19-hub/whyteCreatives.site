# Vercel Deployment Checklist

## Required Environment Variables

Add these in Vercel → Settings → Environment Variables:

### 1. Database (REQUIRED)
```
DATABASE_URL
```
Get this from [Neon.tech](https://neon.tech) (free):
1. Create project → copy connection string
2. Looks like: `postgresql://user:password@ep-xxx.neon.tech/dbname?sslmode=require`

### 2. Paystack (REQUIRED for bookings)
```
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxx
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxx
PAYSTACK_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxx
NEXT_PUBLIC_PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxx
DEPOSIT_PERCENTAGE=50
```

Get Paystack keys:
1. Sign up at [paystack.com](https://paystack.com)
2. Go to **Settings → API Keys & Webhooks**
3. Copy **Test Secret Key** and **Test Public Key**
4. For webhook secret, set up webhook URL: `https://your-app.vercel.app/api/webhooks/paystack`

---

## Step-by-Step Deployment

### Step 1: Set Up Neon Database (2 min)
1. Go to [neon.tech](https://neon.tech) → Sign up with GitHub
2. Create new project → name it `studio-photography`
3. Copy the connection string
4. In Vercel → Settings → Environment Variables → add `DATABASE_URL`

### Step 2: Set Up Paystack (3 min)
1. Go to [paystack.com](https://paystack.com) → Sign up
2. Dashboard → Settings → API Keys & Webhooks
3. Copy test keys
4. Add to Vercel environment variables (all 4 Paystack vars)

### Step 3: Push Code & Redeploy
```bash
# In your studio-app folder:
git add -A
git commit -m "add paystack integration"
git push origin main
```
Vercel will auto-redeploy.

### Step 4: Set Up Webhook (1 min)
1. After deployment, get your Vercel URL (e.g., `studio-photography.vercel.app`)
2. In Paystack dashboard → Settings → API Keys & Webhooks
3. Add webhook URL: `https://studio-photography.vercel.app/api/webhooks/paystack`
4. Copy the webhook secret → add to Vercel as `PAYSTACK_WEBHOOK_SECRET`
5. Redeploy on Vercel

---

## Testing

1. Go to your deployed site → `/book`
2. Fill out the form
3. Click "Pay Deposit & Book Now"
4. Use Paystack test card: `4084 0808 0808 0808`, any future date, any CVV
5. Check admin dashboard → booking should appear as "confirmed"

---

## Troubleshooting

**"Payment system loading" error:**
- Check that `NEXT_PUBLIC_PAYSTACK_PUBLIC_KEY` is set in Vercel (must start with `NEXT_PUBLIC_`)

**Booking not created after payment:**
- Check Vercel function logs for errors
- Verify `PAYSTACK_SECRET_KEY` is correct

**Webhook not firing:**
- Ensure webhook URL is correct in Paystack dashboard
- Check webhook secret matches `PAYSTACK_WEBHOOK_SECRET`
