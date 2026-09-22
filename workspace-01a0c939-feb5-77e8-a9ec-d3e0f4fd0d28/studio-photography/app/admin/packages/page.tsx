'use client';

import { useState, useEffect, useCallback } from 'react';
import {
  Plus, Search, Eye, Pencil, Trash2, ToggleLeft, ToggleRight,
  Package as PackageIcon, Filter, Loader2, Copy, GripVertical,
} from 'lucide-react';
import { cn, formatCurrency } from '@/lib/utils';
import { Modal } from '@/components/ui/Modal';
import { ConfirmDialog } from '@/components/ui/ConfirmDialog';

interface PackageFeature {
  id: number;
  feature: string;
  displayOrder: number;
}

interface PackageData {
  id: number;
  sessionId: number;
  name: string;
  description: string | null;
  price: string;
  duration: string;
  maxPeople: number | null;
  editedPhotos: number | null;
  outfitChanges: number | null;
  locations: number | null;
  deliveryTime: string | null;
  onlineGallery: boolean;
  rawImages: boolean;
  printing: boolean;
  transportation: boolean;
  droneCoverage: boolean;
  priorityEditing: boolean;
  depositPercentage: number;
  rescheduleAllowed: boolean;
  rescheduleHours: number | null;
  displayOrder: number;
  active: boolean;
  deletedAt: string | null;
  createdAt: string;
  features: PackageFeature[];
}

interface Session {
  id: number;
  name: string;
}

export default function PackagesPage() {
  const [packages, setPackages] = useState<PackageData[]>([]);
  const [sessions, setSessions] = useState<Session[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState<'all' | 'active' | 'inactive'>('all');
  const [sessionFilter, setSessionFilter] = useState<string>('');

  const [formOpen, setFormOpen] = useState(false);
  const [editPkg, setEditPkg] = useState<PackageData | null>(null);
  const [viewPkg, setViewPkg] = useState<PackageData | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<PackageData | null>(null);
  const [actionLoading, setActionLoading] = useState(false);
  const [toast, setToast] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  const showToast = (type: 'success' | 'error', message: string) => {
    setToast({ type, message });
    setTimeout(() => setToast(null), 3000);
  };

  const fetchPackages = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (sessionFilter) params.set('sessionId', sessionFilter);
      const res = await fetch(`/api/admin/packages?${params.toString()}`);
      const data = await res.json();
      setPackages(Array.isArray(data) ? data : []);
    } catch {
      showToast('error', 'Failed to load packages');
    }
    setLoading(false);
  }, [sessionFilter]);

  const fetchSessions = useCallback(async () => {
    try {
      const res = await fetch('/api/admin/sessions');
      const data = await res.json();
      setSessions(Array.isArray(data) ? data : []);
    } catch {
      /* ignore */
    }
  }, []);

  useEffect(() => {
    fetchSessions();
  }, [fetchSessions]);

  useEffect(() => {
    fetchPackages();
  }, [fetchPackages]);

  const filtered = packages.filter((p) => {
    const matchSearch = p.name.toLowerCase().includes(search.toLowerCase());
    const matchFilter =
      filter === 'all' ||
      (filter === 'active' && p.active) ||
      (filter === 'inactive' && !p.active);
    return matchSearch && matchFilter;
  });

  function getSessionName(sessionId: number) {
    return sessions.find((s) => s.id === sessionId)?.name || 'Unknown';
  }

  async function handleToggle(pkg: PackageData) {
    try {
      const res = await fetch(`/api/admin/packages/${pkg.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ active: !pkg.active }),
      });
      if (!res.ok) throw new Error();
      showToast('success', `Package ${pkg.active ? 'deactivated' : 'activated'}`);
      fetchPackages();
    } catch {
      showToast('error', 'Failed to toggle status');
    }
  }

  async function handleDelete() {
    if (!deleteConfirm) return;
    setActionLoading(true);
    try {
      const res = await fetch(`/api/admin/packages/${deleteConfirm.id}`, { method: 'DELETE' });
      if (!res.ok) throw new Error();
      showToast('success', 'Package deleted');
      setDeleteConfirm(null);
      fetchPackages();
    } catch {
      showToast('error', 'Failed to delete package');
    }
    setActionLoading(false);
  }

  async function handleDuplicate(pkg: PackageData) {
    try {
      const body = {
        sessionId: pkg.sessionId,
        name: `${pkg.name} (Copy)`,
        description: pkg.description,
        price: pkg.price,
        duration: pkg.duration,
        maxPeople: pkg.maxPeople,
        editedPhotos: pkg.editedPhotos,
        outfitChanges: pkg.outfitChanges,
        locations: pkg.locations,
        deliveryTime: pkg.deliveryTime,
        onlineGallery: pkg.onlineGallery,
        rawImages: pkg.rawImages,
        printing: pkg.printing,
        transportation: pkg.transportation,
        droneCoverage: pkg.droneCoverage,
        priorityEditing: pkg.priorityEditing,
        depositPercentage: pkg.depositPercentage,
        rescheduleAllowed: pkg.rescheduleAllowed,
        rescheduleHours: pkg.rescheduleHours,
        displayOrder: pkg.displayOrder + 1,
        active: false,
        features: pkg.features.map((f) => ({ feature: f.feature, displayOrder: f.displayOrder })),
      };
      const res = await fetch('/api/admin/packages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });
      if (!res.ok) throw new Error();
      showToast('success', 'Package duplicated');
      fetchPackages();
    } catch {
      showToast('error', 'Failed to duplicate package');
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
          <h1 className="text-2xl font-bold text-slate-900">Packages</h1>
          <p className="mt-1 text-sm text-slate-500">Manage packages across all sessions</p>
        </div>
        <button
          onClick={() => {
            setEditPkg(null);
            setFormOpen(true);
          }}
          className="btn btn-primary"
        >
          <Plus className="h-4 w-4" />
          New Package
        </button>
      </div>

      {/* Filters */}
      <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search packages..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="input pl-10"
          />
        </div>
        <select
          value={sessionFilter}
          onChange={(e) => setSessionFilter(e.target.value)}
          className="input w-auto"
        >
          <option value="">All Sessions</option>
          {sessions.map((s) => (
            <option key={s.id} value={s.id}>{s.name}</option>
          ))}
        </select>
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
            <PackageIcon className="mb-3 h-10 w-10" />
            <p className="text-sm">No packages found</p>
          </div>
        ) : (
          <table className="w-full text-left text-sm">
            <thead>
              <tr className="border-b bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <th className="px-6 py-3">Package</th>
                <th className="px-6 py-3">Session</th>
                <th className="px-6 py-3">Price</th>
                <th className="hidden px-6 py-3 md:table-cell">Duration</th>
                <th className="hidden px-6 py-3 lg:table-cell">Deposit</th>
                <th className="px-6 py-3">Status</th>
                <th className="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filtered.map((p) => (
                <tr key={p.id} className="transition-colors hover:bg-slate-50">
                  <td className="px-6 py-4">
                    <p className="font-medium text-slate-900">{p.name}</p>
                    <p className="text-xs text-slate-400">{p.features.length} features</p>
                  </td>
                  <td className="px-6 py-4 text-slate-600">{getSessionName(p.sessionId)}</td>
                  <td className="px-6 py-4 font-medium text-slate-900">{formatCurrency(Number(p.price))}</td>
                  <td className="hidden px-6 py-4 text-slate-600 md:table-cell">{p.duration}</td>
                  <td className="hidden px-6 py-4 text-slate-600 lg:table-cell">{p.depositPercentage}%</td>
                  <td className="px-6 py-4">
                    {p.active ? (
                      <span className="badge bg-green-50 text-green-600">Active</span>
                    ) : (
                      <span className="badge bg-yellow-50 text-yellow-600">Inactive</span>
                    )}
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center justify-end gap-1">
                      <button
                        onClick={() => setViewPkg(p)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        title="View"
                      >
                        <Eye className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => {
                          setEditPkg(p);
                          setFormOpen(true);
                        }}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-blue-600"
                        title="Edit"
                      >
                        <Pencil className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => handleDuplicate(p)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-purple-50 hover:text-purple-600"
                        title="Duplicate"
                      >
                        <Copy className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => handleToggle(p)}
                        className={cn(
                          'rounded-lg p-1.5',
                          p.active ? 'text-green-500 hover:bg-green-50' : 'text-slate-400 hover:bg-slate-100'
                        )}
                        title={p.active ? 'Deactivate' : 'Activate'}
                      >
                        {p.active ? <ToggleRight className="h-4 w-4" /> : <ToggleLeft className="h-4 w-4" />}
                      </button>
                      <button
                        onClick={() => setDeleteConfirm(p)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600"
                        title="Delete"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Form Modal */}
      <PackageFormModal
        open={formOpen}
        pkg={editPkg}
        sessions={sessions}
        onClose={() => {
          setFormOpen(false);
          setEditPkg(null);
        }}
        onSaved={() => {
          setFormOpen(false);
          setEditPkg(null);
          fetchPackages();
          showToast('success', editPkg ? 'Package updated' : 'Package created');
        }}
      />

      {/* View Modal */}
      {viewPkg && (
        <Modal open={!!viewPkg} onClose={() => setViewPkg(null)} title={viewPkg.name} size="lg">
          <div className="space-y-4">
            <div className="rounded-xl bg-slate-50 p-4 text-center">
              <p className="text-3xl font-bold text-slate-900">{formatCurrency(Number(viewPkg.price))}</p>
              <p className="mt-1 text-sm text-slate-500">{viewPkg.duration}</p>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
              <div><p className="text-xs text-slate-400 uppercase">Session</p><p className="text-sm font-medium">{getSessionName(viewPkg.sessionId)}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Status</p><p className="text-sm font-medium">{viewPkg.active ? 'Active' : 'Inactive'}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Max People</p><p className="text-sm font-medium">{viewPkg.maxPeople ?? 1}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Edited Photos</p><p className="text-sm font-medium">{viewPkg.editedPhotos ?? 0}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Outfit Changes</p><p className="text-sm font-medium">{viewPkg.outfitChanges ?? 1}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Locations</p><p className="text-sm font-medium">{viewPkg.locations ?? 1}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Delivery Time</p><p className="text-sm font-medium">{viewPkg.deliveryTime || '—'}</p></div>
              <div><p className="text-xs text-slate-400 uppercase">Deposit</p><p className="text-sm font-medium">{viewPkg.depositPercentage}%</p></div>
            </div>

            <div>
              <p className="text-xs text-slate-400 uppercase mb-2">Included Services</p>
              <div className="grid gap-1 sm:grid-cols-2 text-sm">
                {[
                  ['Online Gallery', viewPkg.onlineGallery],
                  ['Raw Images', viewPkg.rawImages],
                  ['Printing', viewPkg.printing],
                  ['Transportation', viewPkg.transportation],
                  ['Drone Coverage', viewPkg.droneCoverage],
                  ['Priority Editing', viewPkg.priorityEditing],
                  ['Rescheduling', viewPkg.rescheduleAllowed],
                ].map(([label, included]) => (
                  <p key={label as string} className={included ? 'text-green-600' : 'text-slate-400'}>
                    {included ? '✓' : '✗'} {label as string}
                  </p>
                ))}
              </div>
            </div>

            {viewPkg.features.length > 0 && (
              <div>
                <p className="text-xs text-slate-400 uppercase mb-2">Features</p>
                <div className="space-y-1.5">
                  {viewPkg.features.map((f) => (
                    <div key={f.id} className="flex items-center gap-2 text-sm text-slate-600">
                      ✓ {f.feature}
                    </div>
                  ))}
                </div>
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
        title="Delete Package"
        message={`Are you sure you want to delete "${deleteConfirm?.name}"?`}
        confirmText="Delete"
        loading={actionLoading}
      />
    </div>
  );
}

// Package Form Modal
function PackageFormModal({
  open,
  pkg,
  sessions,
  onClose,
  onSaved,
}: {
  open: boolean;
  pkg: PackageData | null;
  sessions: Session[];
  onClose: () => void;
  onSaved: () => void;
}) {
  const isEdit = !!pkg;

  interface FeatureRow {
    id: string;
    feature: string;
  }

  const [form, setForm] = useState({
    sessionId: 0,
    name: '',
    description: '',
    price: '',
    duration: '1 hour',
    maxPeople: 1,
    editedPhotos: 10,
    outfitChanges: 1,
    locations: 1,
    deliveryTime: '3 Days',
    onlineGallery: false,
    rawImages: false,
    printing: false,
    transportation: false,
    droneCoverage: false,
    priorityEditing: false,
    depositPercentage: 50,
    rescheduleAllowed: true,
    rescheduleHours: 48,
    displayOrder: 0,
    active: true,
  });

  const [features, setFeatures] = useState<FeatureRow[]>([]);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (open) {
      if (pkg) {
        setForm({
          sessionId: pkg.sessionId,
          name: pkg.name,
          description: pkg.description || '',
          price: pkg.price,
          duration: pkg.duration,
          maxPeople: pkg.maxPeople ?? 1,
          editedPhotos: pkg.editedPhotos ?? 0,
          outfitChanges: pkg.outfitChanges ?? 1,
          locations: pkg.locations ?? 1,
          deliveryTime: pkg.deliveryTime || '3 Days',
          onlineGallery: pkg.onlineGallery,
          rawImages: pkg.rawImages,
          printing: pkg.printing,
          transportation: pkg.transportation,
          droneCoverage: pkg.droneCoverage,
          priorityEditing: pkg.priorityEditing,
          depositPercentage: pkg.depositPercentage,
          rescheduleAllowed: pkg.rescheduleAllowed,
          rescheduleHours: pkg.rescheduleHours ?? 48,
          displayOrder: pkg.displayOrder,
          active: pkg.active,
        });
        setFeatures(pkg.features.map((f) => ({ id: String(f.id), feature: f.feature })));
      } else {
        setForm({
          sessionId: sessions[0]?.id ?? 0,
          name: '',
          description: '',
          price: '',
          duration: '1 hour',
          maxPeople: 1,
          editedPhotos: 10,
          outfitChanges: 1,
          locations: 1,
          deliveryTime: '3 Days',
          onlineGallery: false,
          rawImages: false,
          printing: false,
          transportation: false,
          droneCoverage: false,
          priorityEditing: false,
          depositPercentage: 50,
          rescheduleAllowed: true,
          rescheduleHours: 48,
          displayOrder: 0,
          active: true,
        });
        setFeatures([]);
      }
    }
  }, [open, pkg, sessions]);

  function addFeature() {
    setFeatures((prev) => [...prev, { id: `new-${Date.now()}`, feature: '' }]);
  }

  function removeFeature(id: string) {
    setFeatures((prev) => prev.filter((f) => f.id !== id));
  }

  function updateFeature(id: string, value: string) {
    setFeatures((prev) => prev.map((f) => (f.id === id ? { ...f, feature: value } : f)));
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    if (!form.name.trim()) {
      alert('Package Name is required');
      return;
    }
    if (!form.price || isNaN(Number(form.price)) || Number(form.price) <= 0) {
      alert('Price must be greater than 0');
      return;
    }
    if (!form.sessionId) {
      alert('Please select a session');
      return;
    }

    const validFeatures = features
      .filter((f) => f.feature.trim())
      .map((f, i) => ({ feature: f.feature.trim(), displayOrder: i }));

    setSaving(true);
    try {
      const url = isEdit ? `/api/admin/packages/${pkg.id}` : '/api/admin/packages';
      const method = isEdit ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...form, price: Number(form.price), features: validFeatures }),
      });

      if (!res.ok) {
        const err = await res.json();
        throw new Error(err.error || 'Failed to save');
      }

      onSaved();
    } catch (e) {
      alert(e instanceof Error ? e.message : 'Failed to save package');
    }
    setSaving(false);
  }

  const durations = ['1 hour', '2 hours', '3 hours', 'Half Day', 'Full Day'];
  const deliveryTimes = ['1 Day', '2 Days', '3 Days', '5 Days', '7 Days', '14 Days'];

  return (
    <Modal open={open} onClose={onClose} title={isEdit ? 'Edit Package' : 'New Package'} size="xl">
      <form onSubmit={handleSubmit} className="max-h-[70vh] space-y-4 overflow-y-auto pr-2">
        {/* Session */}
        <div>
          <label className="label">Session *</label>
          <select
            value={form.sessionId}
            onChange={(e) => setForm((prev) => ({ ...prev, sessionId: parseInt(e.target.value) }))}
            className="input"
          >
            <option value={0}>Select session</option>
            {sessions.map((s) => (
              <option key={s.id} value={s.id}>{s.name}</option>
            ))}
          </select>
        </div>

        {/* Name & Price */}
        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label className="label">Package Name *</label>
            <input
              type="text"
              value={form.name}
              onChange={(e) => setForm((prev) => ({ ...prev, name: e.target.value }))}
              placeholder="e.g. Basic, Standard, Premium"
              className="input"
            />
          </div>
          <div>
            <label className="label">Price (GH₵) *</label>
            <input
              type="number"
              value={form.price}
              onChange={(e) => setForm((prev) => ({ ...prev, price: e.target.value }))}
              placeholder="400"
              min="0"
              step="0.01"
              className="input"
            />
          </div>
        </div>

        {/* Duration & Delivery */}
        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label className="label">Duration *</label>
            <select
              value={form.duration}
              onChange={(e) => setForm((prev) => ({ ...prev, duration: e.target.value }))}
              className="input"
            >
              {durations.map((d) => (
                <option key={d} value={d}>{d}</option>
              ))}
            </select>
          </div>
          <div>
            <label className="label">Delivery Time</label>
            <select
              value={form.deliveryTime}
              onChange={(e) => setForm((prev) => ({ ...prev, deliveryTime: e.target.value }))}
              className="input"
            >
              {deliveryTimes.map((d) => (
                <option key={d} value={d}>{d}</option>
              ))}
            </select>
          </div>
        </div>

        {/* Number fields */}
        <div className="grid gap-4 sm:grid-cols-4">
          <div>
            <label className="label">Max People</label>
            <input
              type="number"
              value={form.maxPeople}
              onChange={(e) => setForm((prev) => ({ ...prev, maxPeople: parseInt(e.target.value) || 1 }))}
              min="1"
              className="input"
            />
          </div>
          <div>
            <label className="label">Edited Photos</label>
            <input
              type="number"
              value={form.editedPhotos}
              onChange={(e) => setForm((prev) => ({ ...prev, editedPhotos: parseInt(e.target.value) || 0 }))}
              min="0"
              className="input"
            />
          </div>
          <div>
            <label className="label">Outfit Changes</label>
            <input
              type="number"
              value={form.outfitChanges}
              onChange={(e) => setForm((prev) => ({ ...prev, outfitChanges: parseInt(e.target.value) || 1 }))}
              min="0"
              className="input"
            />
          </div>
          <div>
            <label className="label">Locations</label>
            <input
              type="number"
              value={form.locations}
              onChange={(e) => setForm((prev) => ({ ...prev, locations: parseInt(e.target.value) || 1 }))}
              min="1"
              className="input"
            />
          </div>
        </div>

        {/* Checkboxes */}
        <div>
          <label className="label">Included Services</label>
          <div className="grid gap-2 sm:grid-cols-3">
            {([
              ['onlineGallery', 'Online Gallery'],
              ['rawImages', 'Raw Images'],
              ['printing', 'Printing'],
              ['transportation', 'Transportation'],
              ['droneCoverage', 'Drone Coverage'],
              ['priorityEditing', 'Priority Editing'],
            ] as const).map(([key, label]) => (
              <label key={key} className="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                <input
                  type="checkbox"
                  checked={form[key] as boolean}
                  onChange={(e) => setForm((prev) => ({ ...prev, [key]: e.target.checked }))}
                  className="rounded"
                />
                <span className="text-sm text-slate-700">{label}</span>
              </label>
            ))}
          </div>
        </div>

        {/* Deposit & Reschedule */}
        <div className="grid gap-4 sm:grid-cols-3">
          <div>
            <label className="label">Deposit % *</label>
            <input
              type="number"
              value={form.depositPercentage}
              onChange={(e) => setForm((prev) => ({ ...prev, depositPercentage: parseInt(e.target.value) || 0 }))}
              min="0"
              max="100"
              className="input"
            />
          </div>
          <div className="flex items-center">
            <label className="flex items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                checked={form.rescheduleAllowed}
                onChange={(e) => setForm((prev) => ({ ...prev, rescheduleAllowed: e.target.checked }))}
                className="rounded"
              />
              Rescheduling Allowed
            </label>
          </div>
          <div>
            <label className="label">Notice (Hours)</label>
            <input
              type="number"
              value={form.rescheduleHours}
              onChange={(e) => setForm((prev) => ({ ...prev, rescheduleHours: parseInt(e.target.value) || 0 }))}
              min="0"
              className="input"
            />
          </div>
        </div>

        {/* Description */}
        <div>
          <label className="label">Package Description</label>
          <textarea
            value={form.description}
            onChange={(e) => setForm((prev) => ({ ...prev, description: e.target.value }))}
            rows={2}
            className="input"
            placeholder="Describe this package..."
          />
        </div>

        {/* Display Order & Status */}
        <div className="grid gap-4 sm:grid-cols-2">
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

        {/* Features */}
        <div>
          <div className="mb-2 flex items-center justify-between">
            <label className="label mb-0">Package Features</label>
            <button
              type="button"
              onClick={addFeature}
              className="btn btn-sm btn-secondary"
            >
              <Plus className="h-3 w-3" />
              Add Feature
            </button>
          </div>
          <div className="space-y-2">
            {features.map((f) => (
              <div key={f.id} className="flex items-center gap-2">
                <GripVertical className="h-4 w-4 shrink-0 cursor-grab text-slate-400" />
                <span className="shrink-0 text-green-500">✓</span>
                <input
                  type="text"
                  value={f.feature}
                  onChange={(e) => updateFeature(f.id, e.target.value)}
                  placeholder="e.g. 10 Edited Images"
                  className="input"
                />
                <button
                  type="button"
                  onClick={() => removeFeature(f.id)}
                  className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600"
                >
                  <Trash2 className="h-4 w-4" />
                </button>
              </div>
            ))}
            {features.length === 0 && (
              <p className="text-xs text-slate-400 italic">No features added yet.</p>
            )}
          </div>
        </div>

        {/* Actions */}
        <div className="flex justify-end gap-3 border-t pt-4">
          <button type="button" onClick={onClose} className="btn btn-outline">Cancel</button>
          <button type="submit" disabled={saving} className="btn btn-primary">
            {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
            {saving ? 'Saving...' : 'Save Package'}
          </button>
        </div>
      </form>
    </Modal>
  );
}
