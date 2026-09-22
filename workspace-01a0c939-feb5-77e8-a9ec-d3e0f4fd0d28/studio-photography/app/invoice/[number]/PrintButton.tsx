'use client';

import { Printer } from 'lucide-react';

export default function PrintButton() {
  return (
    <button
      onClick={() => window.print()}
      className="inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-white hover:bg-slate-800"
    >
      <Printer className="h-4 w-4" /> Download as PDF
    </button>
  );
}
