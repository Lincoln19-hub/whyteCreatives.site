'use client';

import Link from 'next/link';
import { AlertCircle, RefreshCw, Home } from 'lucide-react';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center px-6">
      <div className="max-w-md rounded-2xl border border-red-200 bg-white p-10 text-center shadow-sm">
        <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
          <AlertCircle className="h-7 w-7" />
        </div>
        <h2 className="mb-2 text-xl font-bold text-gray-900">Something went wrong</h2>
        <p className="mb-6 text-sm text-gray-500">
          The admin dashboard couldn't connect to the database. Please check your environment variables.
        </p>

        <div className="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-left text-sm">
          <div className="font-semibold text-yellow-800 mb-2">Quick Fix Checklist:</div>
          <ol className="space-y-1.5 text-yellow-700 list-decimal list-inside">
            <li>Go to Vercel → Settings → Environment Variables</li>
            <li>Make sure <code className="bg-yellow-100 px-1 rounded">DATABASE_URL</code> is set</li>
            <li>For Neon database, format: <code className="bg-yellow-100 px-1 rounded text-xs">postgresql://user:pass@host/db</code></li>
            <li>Redeploy after adding the variable</li>
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

        {process.env.NODE_ENV === 'development' && error.message && (
          <div className="mt-6 rounded-lg bg-gray-100 p-4 text-left text-xs font-mono text-gray-600">
            {error.message}
          </div>
        )}
      </div>
    </div>
  );
}
