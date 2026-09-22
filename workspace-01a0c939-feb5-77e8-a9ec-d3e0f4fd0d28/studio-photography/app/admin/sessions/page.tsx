'use client';

import { useState, useEffect, useCallback } from 'react';
import {
  Plus, Search, Eye, Pencil, Trash2, ToggleLeft, ToggleRight,
  Camera, Filter, Loader2, RotateCcw, Copy,
} from 'lucide-react';
import { cn, slugify, formatDate } from '@/lib/utils';
import { Modal } from '@/components/ui/Modal';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';

interface Session {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  category: string | null;
  image: string | null;
  featured: boolean;
  displayOrder: number;
  active: boolean;
  deletedAt: string | null;
  createdAt: string;
  updatedAt: string;
  packageCount: number;
}

export default function SessionsPage() {
  const [sessions, setSessions] = useState<Session[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState<'all' | 'active' | 'inactive'>('all');
  const [showDeleted, setShowDeleted] = useState(false);

  // Modals
  const [formOpen, setFormOpen] = useState(false);
  const [editSession, setEditSession] = useState<Session | null>(null);
  const [viewSession, setViewSession] = useState<Session | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<{ session: Session; force: boolean } | null>(null);
  const [actionLoading, setActionLoading] = useState(false);
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  const showToast = (type: 'success' | 'error', message: string) => {
    setToast({ type, message });
    setTimeout(() => setToast(null), 3000);
  };

  const fetchSessions = useCallback(async () => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/sessions?includeDeleted=${showDeleted}`);
      const data = await res.json();
      setSessions(Array.isArray(data) ? data : []);
    } catch {
      showToast('error', 'Failed to load sessions');
    }
    setLoading(false);
  }, [showDeleted]);

  useEffect(() => {
    fetchSessions();
  }, [fetchSessions]);

  const filtered = sessions.filter((s) => {
    const matchSearch =
      s.name.toLowerCase().includes(search.toLowerCase()) ||
      (s.category || '').toLowerCase().includes(search.toLowerCase());
    const matchFilter =
      filter === 'all' ||
      (filter === 'active' && s.active) ||
      (filter === 'inactive' && !s.active);
    return matchSearch && matchFilter;
  });

  async function handleToggle(session: Session) {
    try {
      const res = await fetch(`/api/admin/sessions/${session.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ active: !session.active }),
      });
      if (!res.ok) throw new Error();
      showToast('success', `Session ${session.active ? 'deactivated' : 'activated'}`);
      fetchSessions();
    } catch {
      showToast('error', 'Failed to toggle session status');
    }
  }

  async function handleDelete() {
    if (!deleteConfirm) return;
    setActionLoading(true);
    try {
      const url = `/api/admin/sessions/${deleteConfirm.session.id}${deleteConfirm.force ? '?force=true' : ''}`;
      const res = await fetch(url, { method: 'DELETE' });
      const data = await res.json();

      if (!res.ok) {
        if (data.hasPackages) {
          setDeleteConfirm({ ...deleteConfirm, force: true });
          setActionLoading(false);
          return;
        }
        throw new Error(data.error);
      }

      showToast('success', 'Session deleted');
      setDeleteConfirm(null);
      fetchSessions();
    } catch (e) {
      showToast('error', e instanceof Error ? e.message : 'Failed to delete session');
    }
    setActionLoading(false);
  }

  async function handleRestore(session: Session) {
    try {
      const res = await fetch(`/api/admin/sessions/${session.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ restore: true }),
      });
      if (!res.ok) throw new Error();
      showToast('success', 'Session restored');
      fetchSessions();
    } catch {
      showToast('error', 'Failed to restore session');
    }
  }

  async function handleDuplicate(session: Session) {
    try {
      const res = await fetch('/api/admin/sessions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: `${session.name} (Copy)`,
          slug: slugify(`${session.name} copy`),
          description: session.description,
          category: session.category,
          image: session.image,
          featured: session.featured,
          displayOrder: session.displayOrder + 1,
          active: false,
        }),
      });
      if (!res.ok) throw new Error();
      showToast('success', 'Session duplicated');
      fetchSessions();
    } catch {
      showToast('error', 'Failed to duplicate session');
    }
  }

  return (
    <div className="p-6">
      {/* Toast */}
      {toast && (
        <div className={`fixed bottom-4 right-4 z-50 rounded-lg px-4 py-3 shadow-lg ${
          toast.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'
        }`}>
          {toast.message}
        </div>
      )}

      {/* Header */}
      <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Sessions</h1>
          <p className="mt-1 text-sm text-slate-500">Manage your photography sessions</p>
        </div>
        <button
          onClick={() => {
            setEditSession(null);
            setFormOpen(true);
          }}
          className="btn btn-primary"
        >
          <Plus className="h-4 w-4" />
          New Session
        </button>
      </div>

      {/* Filters */}
      <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search sessions..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="input pl-10"
          />
        </div>
        <div className="flex items-center gap-2">
          <Filter className="h-4 w-4 text-slate-400" />
          {(['all', 'active', 'inactive'] as const).map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f)}
              className={cn(
                'rounded-lg px-3 py-1.5 text-xs font-medium capitalize',
                filter === f
                  ? 'bg-slate-900 text-white'
                  : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50'
              )}
            >
              {f}
            </button>
          ))}
          <label className="ml-2 flex items-center gap-1.5 text-xs text-slate-500">
            <input
              type="checkbox"
              checked={showDeleted}
              onChange={(e) => setShowDeleted(e.target.checked)}
              className="rounded"
            />
            Show deleted
          </label>
        </div>
      </div>

      {/* Table */}
      <div className="overflow-x-auto rounded-xl bg-white shadow-sm">
        {loading ? (
          <div className="flex items-center justify-center py-20">
            <Loader2 className="h-6 w-6 animate-spin text-slate-400" />
          </div>
        ) : filtered.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 text-slate-400">
            <Camera className="mb-3 h-10 w-10" />
            <p className="text-sm">No sessions found</p>
          </div>
        ) : (
          <table className="w-full text-left text-sm">
            <thead>
              <tr className="border-b bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <th className="px-6 py-3">Session</th>
                <th className="px-6 py-3">Category</th>
                <th className="hidden px-6 py-3 md:table-cell">Order</th>
                <th className="px-6 py-3">Status</th>
                <th className="hidden px-6 py-3 lg:table-cell">Packages</th>
                <th className="hidden px-6 py-3 xl:table-cell">Created</th>
                <th className="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filtered.map((s) => (
                <tr
                  key={s.id}
                  className={cn(
                    'transition-colors hover:bg-slate-50',
                    s.deletedAt && 'opacity-50'
                  )}
                >
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      {s.image ? (
                        <img src={s.image} alt={s.name} className="h-10 w-10 rounded-lg object-cover" />
                      ) : (
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                          <Camera className="h-4 w-4" />
                        </div>
                      )}
                      <div>
                        <p className="font-medium text-slate-900">{s.name}</p>
                        <p className="text-xs text-slate-400">{s.slug}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-slate-600">{s.category || '—'}</td>
                  <td className="hidden px-6 py-4 text-slate-600 md:table-cell">{s.displayOrder}</td>
                  <td className="px-6 py-4">
                    {s.deletedAt ? (
                      <span className="badge bg-red-50 text-red-600">Deleted</span>
                    ) : s.active ? (
                      <span className="badge bg-green-50 text-green-600">Active</span>
                    ) : (
                      <span className="badge bg-yellow-50 text-yellow-600">Inactive</span>
                    )}
                  </td>
                  <td className="hidden px-6 py-4 text-slate-600 lg:table-cell">{s.packageCount}</td>
                  <td className="hidden px-6 py-4 text-slate-500 xl:table-cell">{formatDate(s.createdAt)}</td>
                  <td className="px-6 py-4">
                    <div className="flex items-center justify-end gap-1">
                      {s.deletedAt ? (
                        <button
                          onClick={() => handleRestore(s)}
                          className="rounded-lg p-1.5 text-slate-400 hover:bg-green-50 hover:text-green-600"
                          title="Restore"
                        >
                          <RotateCcw className="h-4 w-4" />
                        </button>
                      ) : (
                        <>
                          <button
                            onClick={() => setViewSession(s)}
                            className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            title="View"
                          >
                            <Eye className="h-4 w-4" />
                          </button>
                          <button
                            onClick={() => {
                              setEditSession(s);
                              setFormOpen(true);
                            }}
                            className="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-blue-600"
                            title="Edit"
                          >
                            <Pencil className="h-4 w-4" />
                          </button>
                          <button
                            onClick={() => handleDuplicate(s)}
                            className="rounded-lg p-1.5 text-slate-400 hover:bg-purple-50 hover:text-purple-600"
                            title="Duplicate"
                          >
                            <Copy className="h-4 w-4" />
                          </button>
                          <button
                            onClick={() => handleToggle(s)}
                            className={cn(
                              'rounded-lg p-1.5',
                              s.active ? 'text-green-500 hover:bg-green-50' : 'text-slate-400 hover:bg-slate-100'
                            )}
                            title={s.active ? 'Deactivate' : 'Activate'}
                          >
                            {s.active ? <ToggleRight className="h-4 w-4" /> : <ToggleLeft className="h-4 w-4" />}
                          </button>
                          <button
                            onClick={() => setDeleteConfirm({ session: s, force: false })}
                            className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600"
                            title="Delete"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Form Modal */}
      <SessionFormModal
        open={formOpen}
        session={editSession}
        onClose={() => {
          setFormOpen(false);
          setEditSession(null);
        }}
        onSaved={() => {
          setFormOpen(false);
          setEditSession(null);
          fetchSessions();
          showToast('success', editSession ? 'Session updated' : 'Session created');
        }}
      />

      {/* View Modal */}
      {viewSession && (
        <Modal open={!!viewSession} onClose={() => setViewSession(null)} title={viewSession.name}>
          <div className="space-y-4">
            {viewSession.image && (
              <img src={viewSession.image} alt={viewSession.name} className="h-48 w-full rounded-xl object-cover" />
            )}
            <div className="grid gap-3 sm:grid-cols-2">
              <div><p className="text-xs text-slate-400 uppercase">Name</p><p className="text-sm font-medium">{viewSession.name}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Slug</p><p className="text-sm font-medium">{viewSession.slug}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Category</p><p className="text-sm font-medium">{viewSession.category || '—'}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Status</p><p className="text-sm font-medium">{viewSession.active ? 'Active' : 'Inactive'}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Featured</p><p className="text-sm font-medium">{viewSession.featured ? 'Yes' : 'No'}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Packages</p><p className="text-sm font-medium">{viewSession.packageCount}</p></div>
            </div>
            {viewSession.description && (
              <div>
                <p className="text-xs text-slate-400 uppercase">Description</p>
                <p className="mt-1 text-sm text-slate-600">{viewSession.description}</p>
              </div>
            )}
          </div>
        </Modal>
      )}

      {/* Delete Confirm */}
      <ConfirmDialog
        open={!!deleteConfirm}
        onClose={() => setDeleteConfirm(null)}
        onConfirm={handleDelete}
        title="Delete Session"
        message={
          deleteConfirm?.force
            ? `"${deleteConfirm.session.name}" has ${deleteConfirm.session.packageCount} package(s). All packages will also be deleted. Continue?`
            : `Are you sure you want to delete "${deleteConfirm?.session.name}"?`
        }
        confirmText={deleteConfirm?.force ? 'Delete with Packages' : 'Delete'}
        loading={actionLoading}
      />
    </div>
  );
}

// Session Form Modal Component
function SessionFormModal({
  open,
  session,
  onClose,
  onSaved,
}: {
  open: boolean;
  session: Session | null;
  onClose: () => void;
  onSaved: () => void;
}) {
  const isEdit = !!session;
  const [form, setForm] = useState({
    name: '',
    slug: '',
    description: '',
    category: '',
    image: '',
    featured: false,
    displayOrder: 0,
    active: true,
  });
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [slugManual, setSlugManual] = useState(false);

  useEffect(() => {
    if (open) {
      if (session) {
        setForm({
          name: session.name,
          slug: session.slug,
          description: session.description || '',
          category: session.category || '',
          image: session.image || '',
          featured: session.featured,
          displayOrder: session.displayOrder,
          active: session.active,
        });
        setSlugManual(true);
      } else {
        setForm({
          name: '',
          slug: '',
          description: '',
          category: '',
          image: '',
          featured: false,
          displayOrder: 0,
          active: true,
        });
        setSlugManual(false);
      }
    }
  }, [open, session]);

  function handleNameChange(name: string) {
    setForm((prev) => ({
      ...prev,
      name,
      slug: slugManual ? prev.slug : slugify(name),
    }));
  }

  // Convert Google Drive share links to direct CDN image URLs (same engine as the galleries)
  function toDirectImageUrl(url: string): string {
    const m = url.match(/drive\.google\.com[^\s]*?(?:\/file\/d\/|[?&]id=)([a-zA-Z0-9_-]+)/);
    return m ? `https://lh3.googleusercontent.com/d/${m[1]}` : url;
  }

  // Client-side image pipeline: downscale + compress in the browser → compact data-URI.
  // (Serverless-safe: no disk writes, no storage service — the image lives in the database.)
  async function handleImageUpload(file: File) {
    setUploading(true);
    try {
      const dataUri = await new Promise<string>((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
          const img = new Image();
          img.onload = () => {
            const MAX_W = 1200;
            const scale = Math.min(1, MAX_W / img.width);
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            const ctx = canvas.getContext('2d');
            if (!ctx) return reject(new Error('Canvas unavailable'));
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL('image/jpeg', 0.85));
          };
          img.onerror = () => reject(new Error('Could not read image'));
          img.src = String(reader.result);
        };
        reader.onerror = () => reject(new Error('Could not read file'));
        reader.readAsDataURL(file);
      });
      setForm((prev) => ({ ...prev, image: dataUri }));
    } catch {
      alert('Failed to process image — try pasting an image URL instead.');
    }
    setUploading(false);
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!form.name.trim()) {
      alert('Session Name is required');
      return;
    }

    setSaving(true);
    try {
      const url = isEdit ? `/api/admin/sessions/${session.id}` : '/api/admin/sessions';
      const method = isEdit ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      if (!res.ok) {
        const err = await res.json();
        throw new Error(err.error || 'Failed to save');
      }

      onSaved();
    } catch (e) {
      alert(e instanceof Error ? e.message : 'Failed to save session');
    }
    setSaving(false);
  }

  const categories = ['Portrait', 'Wedding', 'Event', 'Commercial', 'Family', 'Graduation', 'Corporate', 'Other'];

  return (
    <Modal open={open} onClose={onClose} title={isEdit ? 'Edit Session' : 'New Session'}>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="label">Session Name *</label>
          <input
            type="text"
            value={form.name}
            onChange={(e) => handleNameChange(e.target.value)}
            placeholder="e.g. Outdoor Portrait"
            className="input"
          />
        </div>

        <div>
          <label className="label">SEO Slug</label>
          <input
            type="text"
            value={form.slug}
            onChange={(e) => {
              setSlugManual(true);
              setForm((prev) => ({ ...prev, slug: e.target.value }));
            }}
            className="input"
          />
          <p className="mt-1 text-xs text-slate-400">Auto-generated from name</p>
        </div>

        <div>
          <label className="label">Category</label>
          <select
            value={form.category}
            onChange={(e) => setForm((prev) => ({ ...prev, category: e.target.value }))}
            className="input"
          >
            <option value="">Select category</option>
            {categories.map((c) => (
              <option key={c} value={c}>{c}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="label">Description</label>
          <textarea
            value={form.description}
            onChange={(e) => setForm((prev) => ({ ...prev, description: e.target.value }))}
            rows={3}
            className="input"
            placeholder="Describe this session..."
          />
        </div>

        <div>
          <label className="label">Featured Image (shows on the booking page)</label>
          <input
            type="text"
            className="input"
            placeholder="Paste an image URL or Google Drive file link…"
            value={form.image.startsWith('data:') ? '(uploaded image — replace to change)' : form.image}
            onChange={(e) => setForm((prev) => ({ ...prev, image: e.target.value }))}
            onBlur={(e) => setForm((prev) => ({ ...prev, image: toDirectImageUrl(e.target.value.trim()) }))}
            disabled={form.image.startsWith('data:')}
          />
          <div className="mt-2">
          {form.image ? (
            <div className="relative inline-block">
              <img src={form.image} alt="Preview" className="h-32 w-48 rounded-lg object-cover" />
              <button
                type="button"
                onClick={() => setForm((prev) => ({ ...prev, image: '' }))}
                className="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white"
              >
                <Trash2 className="h-3 w-3" />
              </button>
            </div>
          ) : (
            <label className="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-slate-300 p-6 hover:border-slate-400">
              {uploading ? (
                <Loader2 className="h-5 w-5 animate-spin text-slate-400" />
              ) : (
                <Camera className="h-5 w-5 text-slate-400" />
              )}
              <span className="text-sm text-slate-500">{uploading ? 'Uploading...' : 'Click to upload image'}</span>
              <input
                type="file"
                accept="image/*"
                className="hidden"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file) handleImageUpload(file);
                }}
              />
            </label>
          )}
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="label">Display Order</label>
            <input
              type="number"
              value={form.displayOrder}
              onChange={(e) => setForm((prev) => ({ ...prev, displayOrder: parseInt(e.target.value) || 0 }))}
              className="input"
            />
          </div>
          <div>
            <label className="label">Status</label>
            <select
              value={form.active ? 'active' : 'inactive'}
              onChange={(e) => setForm((prev) => ({ ...prev, active: e.target.value === 'active' }))}
              className="input"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <label className="flex items-center gap-2">
          <input
            type="checkbox"
            checked={form.featured}
            onChange={(e) => setForm((prev) => ({ ...prev, featured: e.target.checked }))}
            className="rounded"
          />
          <span className="text-sm text-slate-700">Featured Session</span>
        </label>

        <div className="flex justify-end gap-3 border-t pt-4">
          <button type="button" onClick={onClose} className="btn btn-outline">Cancel</button>
          <button type="submit" disabled={saving} className="btn btn-primary">
            {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
            {saving ? 'Saving...' : 'Save Session'}
          </button>
        </div>
      </form>
    </Modal>
  );
}
