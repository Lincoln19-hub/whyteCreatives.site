#!/usr/bin/env node
/**
 * Create a client gallery — usage:
 *   node scripts/new-gallery.mjs --slug ama-wedding --title "Ama & Kojo Wedding" \
 *     --client "Ama Serwaa" --email ama@email.com --password secret123 \
 *     --folder "https://drive.google.com/drive/folders/XYZ" --balance 500 --expiry 2027-03-15
 */
import { createHash } from "crypto";
import { readFileSync } from "fs";
import pg from "pg";

// crude .env loader (no extra deps)
try {
  for (const line of readFileSync(".env", "utf8").split("\n")) {
    const m = line.match(/^\s*([A-Z_]+)\s*=\s*(.*)\s*$/);
    if (m && !process.env[m[1]]) process.env[m[1]] = m[2].replace(/^["']|["']$/g, "");
  }
} catch {}

const args = {};
process.argv.slice(2).forEach((a, i, arr) => { if (a.startsWith("--")) args[a.slice(2)] = arr[i + 1]; });

const required = ["slug", "title", "client", "folder"];
for (const k of required) {
  if (!args[k]) { console.error(`Missing --${k}. See usage at the top of this file.`); process.exit(1); }
}

const secret = process.env.APP_SECRET || process.env.PAYSTACK_SECRET_KEY || "studio-dev-secret";
const passwordHash = args.password ? createHash("sha256").update(secret + args.password).digest("hex") : "";

const pool = new pg.Pool({ connectionString: process.env.DATABASE_URL });
const { rows } = await pool.query(
  `INSERT INTO galleries (slug, title, client_name, client_email, password_hash, gdrive_folder, cover_url, expiry_date, balance, status)
   VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,'unpaid')
   ON CONFLICT (slug) DO UPDATE SET title=$2, client_name=$3, client_email=$4, password_hash=$5, gdrive_folder=$6, cover_url=$7, expiry_date=$8, balance=$9
   RETURNING id, slug`,
  [args.slug, args.title, args.client, args.email || "", passwordHash, args.folder, args.cover || "", args.expiry || "", args.balance || "0"]
);
await pool.end();

console.log(`✅ Gallery ready: /gallery/${rows[0].slug}`);
if (args.password) console.log(`🔒 Password protected: "${args.password}"`);
if (Number(args.balance) > 0) console.log(`💳 Balance to unlock downloads: GHS ${Number(args.balance).toFixed(2)}`);
