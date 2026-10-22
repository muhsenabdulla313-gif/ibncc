{{-- resources/views/admin/users.blade.php : users CRUD, one page, one add/edit modal, all via script --}}
@extends('layouts.admin')
 @section('body')
<style>
  .auc-photo { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
  .auc-role { text-transform: capitalize; }
  .auc-err { color: #dc3545; font-size: 12px; margin-top: 2px; }
  .auc-dl dt { font-weight: 600; color: #6c757d; font-size: 12px; text-transform: uppercase; }
  .auc-dl dd { margin-bottom: 10px; }
  .auc-toast { position: fixed; right: 20px; bottom: 20px; z-index: 99999; padding: 10px 18px; border-radius: 8px; color: #fff; background: #198754; box-shadow: 0 4px 14px rgba(0,0,0,.25); }
  .auc-toast.is-error { background: #dc3545; }
</style>

<div class="container">
  <div class="page-inner">
    <div class="card">
      <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h4 class="card-title mb-0">Users</h4>
        <button type="button" class="btn btn-primary btn-round" id="aucAddBtn">
          <i class="fa fa-plus"></i> Add User
        </button>
      </div>

      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <input type="search" id="aucSearch" class="form-control" placeholder="Search name, phone or email...">
          </div>
          <div class="col-md-3">
            <select id="aucRoleFilter" class="form-select">
              <option value="">All roles</option>
              @foreach ($roles as $r)
                <option value="{{ $r->id }}">{{ ucfirst($r->name) }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>#</th><th>User</th><th>Phone</th><th>Email</th><th>Role</th><th>Joined</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody id="aucBody">
              <tr><td colspan="7" class="text-center text-muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted" id="aucInfo"></small>
          <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="aucPrev">Prev</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="aucNext">Next</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ONE modal for add + edit --}}
<div class="modal fade" id="aucModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="aucForm" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="aucTitle">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="aucId">

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" maxlength="100">
          <div class="auc-err" data-err="name"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <div class="input-group">
            <span class="input-group-text">+91</span>
            <input type="tel" class="form-control" name="phone" maxlength="10" inputmode="numeric" placeholder="10-digit number">
          </div>
          <div class="auc-err" data-err="phone"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" maxlength="255">
          <div class="auc-err" data-err="email"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select class="form-select" name="role_id">
            @foreach ($roles as $r)
              <option value="{{ $r->id }}">{{ ucfirst($r->name) }}</option>
            @endforeach
          </select>
          <div class="auc-err" data-err="role_id"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password <small class="text-muted" id="aucPwHint"></small></label>
          <input type="password" class="form-control" name="password" minlength="6" maxlength="64" autocomplete="new-password">
          <div class="auc-err" data-err="password"></div>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="verified" id="aucVerified" value="1">
          <label class="form-check-label" for="aucVerified">Email verified</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="aucSave">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- View popup (read only) --}}
<div class="modal fade" id="aucView" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="aucViewBody"></div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  const BASE = @json(url('admin/users'));
  const CSRF = @json(csrf_token());
  const NOIMG = @json(asset('assets/images/no-image.avif'));

  const $ = (s, r = document) => r.querySelector(s);
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const modal = new bootstrap.Modal($('#aucModal'));
  const viewModal = new bootstrap.Modal($('#aucView'));
  const form = $('#aucForm');
  const state = { page: 1, last: 1, total: 0, search: '', role: '' };

  function toast(msg, isError) {
    const t = document.createElement('div');
    t.className = 'auc-toast' + (isError ? ' is-error' : '');
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2600);
  }

  async function api(url, method = 'GET', body) {
    const opt = {
      method,
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    };
    if (body) { opt.headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(body); }
    const res = await fetch(url, opt);
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
      const err = new Error(json.message || 'Something went wrong');
      err.errors = json.errors || {};
      throw err;
    }
    return json;
  }

  /* ---------- list ---------- */
  async function load() {
    const q = new URLSearchParams({ page: state.page, search: state.search, role_id: state.role });
    const d = await api(`${BASE}/data?${q}`);
    state.last = d.meta.last;
    state.total = d.meta.total;
    state.page = d.meta.page;

    $('#aucBody').innerHTML = d.data.length ? d.data.map((u) => `
      <tr>
        <td>${u.id}</td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <img class="auc-photo" src="${esc(u.photo)}" onerror="this.onerror=null;this.src='${esc(NOIMG)}'" alt="">
            <div>
              <div class="fw-semibold">${esc(u.name)}</div>
              <small class="text-muted">${esc(u.job || '')}</small>
            </div>
          </div>
        </td>
        <td>${esc(u.phone)}</td>
        <td>${esc(u.email || '-')} ${u.email ? (u.verified ? '<i class="fa fa-check-circle text-success" title="Verified"></i>' : '<i class="fa fa-exclamation-circle text-warning" title="Not verified"></i>') : ''}</td>
        <td><span class="badge bg-${u.role === 'admin' ? 'danger' : (u.role === 'member' ? 'primary' : 'secondary')} auc-role">${esc(u.role)}</span></td>
        <td>${esc(u.joined)}</td>
        <td class="text-end text-nowrap">
          <button class="btn btn-sm btn-icon btn-round btn-info" data-act="view" data-id="${u.id}" title="View"><i class="fa fa-eye"></i></button>
          <button class="btn btn-sm btn-icon btn-round btn-primary" data-act="edit" data-id="${u.id}" title="Edit"><i class="fa fa-pen"></i></button>
          <button class="btn btn-sm btn-icon btn-round btn-danger" data-act="delete" data-id="${u.id}" data-name="${esc(u.name)}" title="Delete"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`).join('') : '<tr><td colspan="7" class="text-center text-muted">No users found.</td></tr>';

    $('#aucInfo').textContent = `Page ${state.page} of ${state.last} · ${state.total} users`;
    $('#aucPrev').disabled = state.page <= 1;
    $('#aucNext').disabled = state.page >= state.last;
  }

  const safeLoad = () => load().catch((e) => toast(e.message, true));

  /* ---------- add / edit modal ---------- */
  function clearErrors() { form.querySelectorAll('[data-err]').forEach((e) => (e.textContent = '')); }

  function openForm(u) {
    clearErrors();
    form.reset();
    $('#aucId').value = u ? u.id : '';
    $('#aucTitle').textContent = u ? 'Edit User' : 'Add User';
    $('#aucPwHint').textContent = u ? '(leave blank to keep current)' : '';
    if (u) {
      form.name.value = u.name || '';
      form.phone.value = u.phone || '';
      form.email.value = u.email || '';
      form.role_id.value = u.role_id;
      form.verified.checked = !!u.verified;
    }
    modal.show();
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    const id = $('#aucId').value;
    const body = {
      name: form.name.value.trim(),
      phone: form.phone.value.trim(),
      email: form.email.value.trim(),
      role_id: form.role_id.value,
      password: form.password.value,
      verified: form.verified.checked ? 1 : 0,
    };
    const btn = $('#aucSave');
    btn.disabled = true;
    try {
      const d = await api(id ? `${BASE}/${id}` : BASE, id ? 'PUT' : 'POST', body);
      modal.hide();
      toast(d.message);
      await load();
    } catch (err) {
      Object.entries(err.errors || {}).forEach(([k, v]) => {
        const el = form.querySelector(`[data-err="${k}"]`);
        if (el) el.textContent = v[0];
      });
      toast(err.message, true);
    } finally {
      btn.disabled = false;
    }
  });

  /* ---------- view popup ---------- */
  function showView(u) {
    const f = (label, val) => `<dt>${label}</dt><dd>${esc(val || '-')}</dd>`;
    $('#aucViewBody').innerHTML = `
      <div class="d-flex align-items-center gap-3 mb-3">
        <img class="auc-photo" style="width:64px;height:64px" src="${esc(u.photo)}" onerror="this.onerror=null;this.src='${esc(NOIMG)}'" alt="">
        <div><h5 class="mb-0">${esc(u.name)}</h5><span class="text-muted auc-role">${esc(u.role)}</span></div>
      </div>
      <div class="row">
        <dl class="col-md-6 auc-dl">
          ${f('Phone', u.phone)}${f('Email', u.email)}${f('WhatsApp', u.whatsapp)}
          ${f('Date of birth', u.dob)}${f('Gender', u.gender)}${f('Blood group', u.blood)}
          ${f('Member since', u.joined)}
        </dl>
        <dl class="col-md-6 auc-dl">
          ${f('Rite', u.rite)}${f('Diocese', u.diocese)}${f('Forane', u.forane)}${f('Parish', u.parish)}
          ${f('Job', u.job)}${f('Category', u.category)}${f('Employment', u.employment)}${f('Organization', u.org)}
        </dl>
        <dl class="col-12 auc-dl">
          ${f('Present address', u.present)}${f('Permanent address', u.permanent)}${f('About', u.about)}
        </dl>
      </div>`;
    viewModal.show();
  }

  /* ---------- events ---------- */
  $('#aucAddBtn').addEventListener('click', () => openForm(null));

  $('#aucBody').addEventListener('click', async (e) => {
    const b = e.target.closest('[data-act]');
    if (!b) return;
    const id = b.dataset.id;
    try {
      if (b.dataset.act === 'view') showView(await api(`${BASE}/${id}`));
      if (b.dataset.act === 'edit') openForm(await api(`${BASE}/${id}`));
      if (b.dataset.act === 'delete') {
        if (!confirm(`Delete "${b.dataset.name}"? This cannot be undone.`)) return;
        const d = await api(`${BASE}/${id}`, 'DELETE');
        toast(d.message);
        if (state.page > 1 && $('#aucBody').children.length === 1) state.page--;
        await load();
      }
    } catch (err) {
      toast(err.message, true);
    }
  });

  let t;
  $('#aucSearch').addEventListener('input', (e) => {
    clearTimeout(t);
    t = setTimeout(() => { state.search = e.target.value.trim(); state.page = 1; safeLoad(); }, 300);
  });
  $('#aucRoleFilter').addEventListener('change', (e) => { state.role = e.target.value; state.page = 1; safeLoad(); });
  $('#aucPrev').addEventListener('click', () => { if (state.page > 1) { state.page--; safeLoad(); } });
  $('#aucNext').addEventListener('click', () => { if (state.page < state.last) { state.page++; safeLoad(); } });

  form.phone.addEventListener('input', (e) => { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10); });

  safeLoad();
});
</script>
@endsection
