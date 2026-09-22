import { cookies } from "next/headers";
import { createHmac } from "crypto";
import { db } from "@/lib/db";
import { invoices, settings } from "@/lib/schema";
import { eq } from "drizzle-orm";
import { notFound } from "next/navigation";
import PrintButton from "./PrintButton";

export const dynamic = "force-dynamic";

function secret() {
  return process.env.APP_SECRET || "studio-dev-secret";
}

const PURPOSE_LABELS: Record<string, string> = {
  booking_deposit: "Booking Deposit",
  gallery_balance: "Gallery Balance Settlement",
  manual: "Photography Services",
};

export default async function InvoicePage({
  params,
  searchParams,
}: {
  params: Promise<{ number: string }>;
  searchParams: Promise<{ t?: string }>;
}) {
  const { number } = await params;
  const { t } = await searchParams;
  const invoiceNumber = decodeURIComponent(number);

  const rows = await db.select().from(invoices).where(eq(invoices.number, invoiceNumber)).limit(1);
  const inv = rows[0];
  if (!inv) notFound();

  // Access: studio admin (cookie) OR a signed share token (?t=…)
  const token = createHmac("sha256", secret()).update(`invoice:${inv.number}`).digest("hex").slice(0, 40);
  const cookieStore = await cookies();
  const adminCookie = cookieStore.get("studio_admin")?.value ?? "";
  const authed = (!!process.env.ADMIN_TOKEN && adminCookie === process.env.ADMIN_TOKEN) || (!!t && t === token);

  if (!authed) {
    return (
      <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6">
        <div className="max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
          <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-2xl">🔒</div>
          <h1 className="text-xl font-bold text-slate-900">Private Invoice</h1>
          <p className="mt-2 text-sm text-slate-500">This invoice link is protected. Please request a signed link from the studio.</p>
        </div>
      </main>
    );
  }

  // Studio profile from settings (with graceful defaults)
  const sRows = await db.select().from(settings);
  const cfg: Record<string, string> = {};
  for (const r of sRows) cfg[r.key] = r.value ?? "";
  const biz = {
    name: cfg.business_name || "Whyte Creatives",
    email: cfg.business_email || "whytecobby@gmail.com",
    location: cfg.business_location || "Ghana",
  };

  const lineItems = (inv.notes ?? "").split("\n").map((l) => l.trim()).filter(Boolean);
  const paid = inv.status === "paid";

  return (
    <main className="min-h-screen bg-slate-100 py-8 print:bg-white print:py-0">
      <div className="mx-auto max-w-2xl bg-white p-10 shadow-lg print:shadow-none">
        {/* Header */}
        <div className="flex items-start justify-between border-b border-slate-100 pb-6">
          <div>
            <h1 className="font-serif text-2xl font-bold text-slate-900">{biz.name}</h1>
            <p className="mt-1 text-xs text-slate-500">{biz.email}</p>
            <p className="text-xs text-slate-500">{biz.location}</p>
          </div>
          <div className="text-right">
            <span className={`rounded-full px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest ${paid ? "bg-green-50 text-green-600" : "bg-red-50 text-red-600"}`}>
              {paid ? "Paid" : "Unpaid"}
            </span>
            <p className="mt-2 font-mono text-sm font-bold text-slate-900">{inv.number}</p>
            <p className="text-xs text-slate-400">{new Date(inv.createdAt).toLocaleDateString()}</p>
          </div>
        </div>

        {/* Bill to */}
        <div className="mt-6 grid grid-cols-2 gap-4 text-sm">
          <div>
            <p className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Billed To</p>
            <p className="mt-1 font-semibold text-slate-900">{inv.clientName}</p>
            {inv.clientEmail && <p className="text-xs text-slate-500">{inv.clientEmail}</p>}
          </div>
          <div className="text-right">
            <p className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Details</p>
            <p className="mt-1 text-xs text-slate-600">{PURPOSE_LABELS[inv.purpose] ?? inv.purpose}</p>
            {inv.dueDate && <p className="text-xs text-slate-600">Due: {inv.dueDate}</p>}
            {inv.paystackRef && <p className="text-xs text-green-600">Ref: {inv.paystackRef}</p>}
          </div>
        </div>

        {/* Line items */}
        <table className="mt-6 w-full text-left text-sm">
          <thead>
            <tr className="border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400">
              <th className="py-2">Description</th>
              <th className="py-2 text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            {lineItems.length > 0 ? (
              lineItems.map((line, i) => {
                const m = line.match(/^(.*?)[\s—-]+GHS?\s*([\d,.]+)$/i);
                return (
                  <tr key={i} className="border-b border-slate-50 text-slate-700">
                    <td className="py-2.5">{m ? m[1].trim() : line}</td>
                    <td className="py-2.5 text-right font-medium">{m ? `GHS ${m[2]}` : ""}</td>
                  </tr>
                );
              })
            ) : (
              <tr className="border-b border-slate-50 text-slate-700">
                <td className="py-2.5">{PURPOSE_LABELS[inv.purpose] ?? "Photography Services"}</td>
                <td className="py-2.5 text-right font-medium">GHS {Number(inv.total).toFixed(2)}</td>
              </tr>
            )}
          </tbody>
        </table>

        {/* Total */}
        <div className="mt-4 flex justify-end">
          <div className="w-56 space-y-1.5 text-sm">
            <div className={`flex justify-between rounded-lg p-2.5 font-bold ${paid ? "bg-green-50 text-green-700" : "bg-red-50 text-red-700"}`}>
              <span>{paid ? "Total Paid" : "Total Due"}</span>
              <span>GHS {Number(inv.total).toFixed(2)}</span>
            </div>
          </div>
        </div>

        <p className="mt-8 border-t border-slate-100 pt-4 text-center text-[10px] text-slate-400">
          Thank you for choosing {biz.name} — generated {new Date().toLocaleDateString()}
        </p>
      </div>

      <div className="no-print mx-auto mt-4 max-w-2xl text-center">
        <PrintButton />
      </div>

      <style>{`@media print { .no-print { display: none !important; } }`}</style>
    </main>
  );
}
