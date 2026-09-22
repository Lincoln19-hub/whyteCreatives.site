import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export const dynamic = 'force-dynamic';

export async function GET() {
  const checks = {
    hasDatabaseUrl: !!process.env.DATABASE_URL,
    databaseUrlFormat: process.env.DATABASE_URL?.startsWith('postgresql://') || 
                       process.env.DATABASE_URL?.startsWith('file:') || false,
    hasPaystackKeys: !!process.env.PAYSTACK_PUBLIC_KEY && 
                     !!process.env.PAYSTACK_SECRET_KEY && 
                     !!process.env.NEXT_PUBLIC_PAYSTACK_PUBLIC_KEY,
    nodeEnv: process.env.NODE_ENV,
  };

  return NextResponse.json({
    environment: checks,
    status: checks.hasDatabaseUrl && checks.databaseUrlFormat ? 'ready' : 'setup-required',
  });
}
