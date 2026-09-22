'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { AlertCircle, RefreshCw, Home } from 'lucide-react';

export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error('Application error:', error);
  }, [error]);

  return (
    <html>
      <body className="bg-gray-50">
        <div className="min-h-screen flex items-center justify-center px-6">
          <div className="max-w-md rounded-2xl border border-red-200 bg-white p-10 text-center shadow-sm">
            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
              <AlertCircle className="h-7 w-7" />
            </div>
            <h2 className="mb-2 text-xl font-bold text-gray-900">Application Error</h2>
            <p className="mb-6 text-sm text-gray-500">
              A server-side exception occurred. This is usually due to a missing database connection.
            </p>

            <div className="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-left text-sm">
              <div className="font-semibold text-yellow-800 mb-2">For Admin Users:</div>
              <ol className="space-y-1.5 text-yellow-700 list-decimal list-inside">
                <li>Check Vercel environment variables</li>
                <li>Verify <code className="bg-yellow-100 px-1 rounded">DATABASE_URL</code> is set</li>
                <li>Ensure database service (Neon/Supabase) is running</li>
                <li>Redeploy the application</li>
              </ol>
            </div>

            <div className="flex gap-2 justify-center">
              <button onClick={reset} className="btn btn-primary">
                <RefreshCw className="h-4 w-4" /> Try Again
              </button>
              <Link href="/" className="btn btn-outline">
                <Home className="h-4 w-4" /> Go Home
              </Link>
            </div>
          </div>
        </div>
      </body>
    </html>
  );
}
