#!/usr/bin/env bash
# ═══ One-shot Vercel setup — pushes ALL your environment values & deploys ═══
# Run this from the studio-photography folder on your machine:
#   bash setup-vercel.sh
set -e

echo "🚀 Studio Vercel Setup"
echo "======================"

# 1. Ensure Vercel CLI
if ! command -v vercel &> /dev/null; then
  echo "📦 Installing Vercel CLI…"
  npm install -g vercel
fi

# 2. Login (opens your browser — one click)
echo
echo "🔐 A browser window will open — log in with the 'Continue with GitHub/Email' option:"
vercel login

# 3. Link this folder to a new Vercel project
echo
echo "🔗 Linking project (accept the defaults — it creates the project for you):"
vercel link

# 4. Push EVERY value from .env straight into Vercel (all environments)
echo
echo "🌐 Pushing all environment variables…"
vercel env push

# 5. Deploy!
echo
echo "⛵ Deploying to production…"
vercel --prod

echo
echo "✅ DONE! Your env vars are set and the site is deploying."
echo "   Next: Paystack Dashboard → Settings → API Keys & Webhooks →"
echo "   set the webhook URL to: https://YOUR-DOMAIN/api/paystack/webhook"
