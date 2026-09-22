// ── Paystack server helpers (Vercel-safe) ────────────────────────────────────

const PAYSTACK_BASE = "https://api.paystack.co";

function secretKey(): string {
  const k = process.env.PAYSTACK_SECRET_KEY;
  if (!k) throw new Error("PAYSTACK_SECRET_KEY is not configured");
  return k;
}

export interface InitTxn {
  email: string;
  amountGhs: number;
  reference: string;
  metadata?: Record<string, unknown>;
  callbackUrl?: string;
}

/** Initialize a transaction → { authorization_url, reference } */
export async function initializeTransaction(input: InitTxn): Promise<{ authorization_url: string; reference: string }> {
  const res = await fetch(`${PAYSTACK_BASE}/transaction/initialize`, {
    method: "POST",
    headers: { Authorization: `Bearer ${secretKey()}`, "Content-Type": "application/json" },
    body: JSON.stringify({
      email: input.email,
      amount: Math.round(input.amountGhs * 100), // GHS pesewas
      currency: "GHS",
      reference: input.reference,
      metadata: input.metadata ?? {},
      callback_url: input.callbackUrl,
    }),
  });
  const json = await res.json();
  if (!res.ok || !json?.status || !json?.data?.authorization_url) {
    throw new Error(json?.message || "Paystack initialization failed");
  }
  return json.data;
}

/** Verify a transaction by reference */
export async function verifyTransaction(reference: string): Promise<{ status: string; amount: number; metadata?: Record<string, unknown> }> {
  const res = await fetch(`${PAYSTACK_BASE}/transaction/verify/${encodeURIComponent(reference)}`, {
    headers: { Authorization: `Bearer ${secretKey()}` },
    cache: "no-store",
  });
  const json = await res.json();
  if (!res.ok || !json?.status) throw new Error(json?.message || "Paystack verification failed");
  return { status: json.data.status, amount: json.data.amount, metadata: json.data.metadata };
}
