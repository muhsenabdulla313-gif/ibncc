@extends('layouts.admin')

@section('body')
<div class="container-fluid cat-page">
  <div class="page-inner">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
      <div>
        <h3 class="fw-bold mb-1">Product Catalog</h3>
        <p class="text-muted mb-0">Categories, attributes, units and brands — everything sellers pick from.</p>
      </div>
      <button type="button" class="btn btn-primary" id="catAddBtn"><i class="fa fa-plus"></i> Add <span id="catAddLabel">category</span></button>
    </div>

    <div id="catAlert"></div>

    <div class="card">
      <div class="card-header">
        <ul class="nav nav-pills" id="catTabs">
          <li class="nav-item"><a class="nav-link active" href="#" data-entity="categories">Categories</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-entity="attributes">Attributes</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-entity="attribute-groups">Attribute groups</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-entity="units">Units</a></li>
          <li class="nav-item"><a class="nav-link" href="#" data-entity="brands">Brands</a></li>
        </ul>
      </div>
      <div class="card-body">
        <input type="search" id="catSearch" class="form-control mb-3" style="max-width:320px" placeholder="Search…">
        <div class="table-responsive">
          <table class="table table-hover align-middle cat-table">
            <thead><tr id="catHead"></tr></thead>
            <tbody id="catBody"><tr><td class="text-center text-muted py-4">Loading…</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ONE modal used for every entity (add + edit) --}}
<div class="modal fade" id="catModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="catForm" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="catModalTitle">Add</h5>
        <button type="button" class="btn-close" data-cat-close aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="catErrors"></div>
        <div class="row g-3" id="catFields"></div>
        <div id="catExtra"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-cat-close>Cancel</button>
        <button type="submit" class="btn btn-primary" id="catSaveBtn">Save</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
  .cat-table td .cat-indent{display:inline-block}
  .cat-extra-box{border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-top:16px}
  .cat-extra-row{display:grid;gap:8px;align-items:center;margin-bottom:8px}
  .cat-extra-row.cat-attr{grid-template-columns:2fr 70px 1fr 1fr 1fr 70px 34px}
  .cat-extra-row.cat-opt{grid-template-columns:2fr 1.5fr 110px 70px 34px}
  .cat-extra-head{font-size:.75rem;text-transform:uppercase;color:#6b7280}
  .cat-thumb{width:38px;height:38px;border-radius:8px;object-fit:cover}
  @media(max-width:768px){.cat-extra-row.cat-attr,.cat-extra-row.cat-opt{grid-template-columns:1fr 1fr}}
</style>
@endpush

@push('scripts')
<script>
(() => {
  const BASE = @json(url('admin/catalog'));
  const CSRF = @json(csrf_token());
  const $ = (s, r = document) => r.querySelector(s);
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const badge = (on, yes = 'Active', no = 'Inactive') => `<span class="badge ${on ? 'bg-success' : 'bg-secondary'}">${on ? yes : no}</span>`;
  const yn = (v) => (v ? '✓' : '—');

  let L = { categories: [], attributes: [], groups: [], units: [] };   // dropdown lookups
  let entity = 'categories', rows = [], editId = null;

  const TYPES = ['text', 'textarea', 'number', 'select', 'multiselect', 'boolean', 'color', 'date'].map((v) => [v, v]);
  const optsOf = (arr, label = (x) => x.name) => [['', '— none —'], ...arr.map((x) => [x.id, label(x)])];

  /* ---- one config per entity: label, table columns, modal fields, optional extra editor ---- */
  const CFG = {
    categories: {
      label: 'category', cols: [
        ['Name', (r) => `<span class="cat-indent" style="padding-left:${r.depth * 22}px">${r.depth ? '↳ ' : ''}<strong>${esc(r.name)}</strong></span>`],
        ['Slug', (r) => esc(r.slug)], ['Parent', (r) => esc(r.parent || '—')], ['Products', (r) => r.products_count],
        ['Attributes', (r) => r.attrs_count], ['Order', (r) => r.sort_order], ['Status', (r) => badge(r.is_active)]],
      fields: () => [
        { n: 'name', l: 'Name', t: 'text', req: 1, w: 6 },
        { n: 'parent_id', l: 'Parent category', t: 'select', w: 6, o: () => [['', '— top level —'], ...L.categories.filter((c) => c.depth < 2 && c.id !== editId).map((c) => [c.id, '— '.repeat(c.depth) + c.name])] },
        { n: 'slug', l: 'Slug', t: 'text', w: 6, help: 'Leave blank to generate from the name' },
        { n: 'sort_order', l: 'Sort order', t: 'number', w: 3, d: 0 },
        { n: 'is_active', l: 'Active', t: 'switch', w: 3, d: 1 },
        { n: 'description', l: 'Description', t: 'textarea', w: 12 },
        { n: 'image', l: 'Image', t: 'file', w: 12, url: 'image_url' },
        { n: 'meta_title', l: 'Meta title', t: 'text', w: 6 },
        { n: 'meta_description', l: 'Meta description', t: 'text', w: 6 }],
      extra: 'attrs',
    },
    attributes: {
      label: 'attribute', cols: [
        ['Name', (r) => `<strong>${esc(r.name)}</strong>`], ['Group', (r) => esc(r.group_name || '—')], ['Type', (r) => esc(r.input_type)],
        ['Unit', (r) => esc(r.unit_name || '—')], ['Options', (r) => r.options_count], ['Used in categories', (r) => r.categories_count],
        ['Filter', (r) => yn(r.is_filterable)], ['Variant', (r) => yn(r.is_variant_axis)], ['Visible', (r) => yn(r.is_visible)]],
      fields: () => [
        { n: 'name', l: 'Name', t: 'text', req: 1, w: 6 },
        { n: 'input_type', l: 'Input type', t: 'select', w: 6, o: () => TYPES, d: 'text' },
        { n: 'attribute_group_id', l: 'Group', t: 'select', w: 4, o: () => optsOf(L.groups) },
        { n: 'unit_id', l: 'Unit (for numbers)', t: 'select', w: 4, o: () => optsOf(L.units) },
        { n: 'slug', l: 'Slug', t: 'text', w: 4 },
        { n: 'is_filterable', l: 'Filterable', t: 'switch', w: 4 }, { n: 'is_searchable', l: 'Searchable', t: 'switch', w: 4 },
        { n: 'is_comparable', l: 'Comparable', t: 'switch', w: 4 }, { n: 'is_variant_axis', l: 'Variant option (Size / Colour)', t: 'switch', w: 4 },
        { n: 'is_visible', l: 'Visible on product page', t: 'switch', w: 4, d: 1 }, { n: 'sort_order', l: 'Sort order', t: 'number', w: 4, d: 0 }],
      extra: 'options',
    },
    'attribute-groups': {
      label: 'attribute group', cols: [['Name', (r) => `<strong>${esc(r.name)}</strong>`], ['Slug', (r) => esc(r.slug)], ['Attributes', (r) => r.attributes_count], ['Order', (r) => r.sort_order]],
      fields: () => [{ n: 'name', l: 'Name', t: 'text', req: 1, w: 6 }, { n: 'slug', l: 'Slug', t: 'text', w: 4 }, { n: 'sort_order', l: 'Sort order', t: 'number', w: 2, d: 0 }],
    },
    units: {
      label: 'unit', cols: [['Name', (r) => `<strong>${esc(r.name)}</strong>`], ['Short', (r) => esc(r.short_name)], ['Type', (r) => esc(r.type)],
        ['Base unit', (r) => esc(r.base_name || '—')], ['Factor', (r) => (r.base_name ? Number(r.conversion_factor) : '—')], ['Decimals', (r) => yn(r.allow_decimal)]],
      fields: () => [
        { n: 'name', l: 'Name', t: 'text', req: 1, w: 6 }, { n: 'short_name', l: 'Short name', t: 'text', req: 1, w: 3 },
        { n: 'type', l: 'Type', t: 'select', w: 3, o: () => ['count', 'weight', 'length', 'area', 'volume', 'pack'].map((v) => [v, v]), d: 'count' },
        { n: 'base_unit_id', l: 'Base unit', t: 'select', w: 5, o: () => [['', '— none (this is a base unit) —'], ...L.units.filter((u) => u.id !== editId).map((u) => [u.id, u.name])] },
        { n: 'conversion_factor', l: 'Base units in 1 of this', t: 'number', w: 4, d: 1, step: 'any' },
        { n: 'allow_decimal', l: 'Allow decimal qty', t: 'switch', w: 3 }],
    },
    brands: {
      label: 'brand', cols: [['', (r) => (r.logo_url ? `<img class="cat-thumb" src="${esc(r.logo_url)}" alt="">` : '')], ['Name', (r) => `<strong>${esc(r.name)}</strong>`],
        ['Slug', (r) => esc(r.slug)], ['Products', (r) => r.products_count], ['Status', (r) => badge(r.is_active)]],
      fields: () => [{ n: 'name', l: 'Name', t: 'text', req: 1, w: 6 }, { n: 'slug', l: 'Slug', t: 'text', w: 4 }, { n: 'is_active', l: 'Active', t: 'switch', w: 2, d: 1 },
        { n: 'logo', l: 'Logo', t: 'file', w: 12, url: 'logo_url' }],
    },
  };

  /* ---- http ---- */
  async function api(method, url, body) {
    const o = { method, headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
    if (body) o.body = body;
    const res = await fetch(url, o); let j = null; try { j = await res.json(); } catch (e) {}
    if (!res.ok) { const m = j && j.errors ? Object.values(j.errors).flat() : [(j && j.message) || 'Request failed']; const e = new Error(m[0]); e.messages = m; throw e; }
    return j;
  }
  function flash(msg, type = 'success') {
    const a = $('#catAlert'); a.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${esc(msg)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    setTimeout(() => (a.innerHTML = ''), 4500);
  }

  /* ---- modal (bootstrap 5 with a tiny fallback) ---- */
  const modalEl = $('#catModal');
  let backdrop = null;
  const modal = {
    show() { if (window.bootstrap) return bootstrap.Modal.getOrCreateInstance(modalEl).show(); modalEl.style.display = 'block'; modalEl.classList.add('show'); backdrop = document.createElement('div'); backdrop.className = 'modal-backdrop fade show'; document.body.appendChild(backdrop); },
    hide() { if (window.bootstrap) return bootstrap.Modal.getOrCreateInstance(modalEl).hide(); modalEl.style.display = 'none'; modalEl.classList.remove('show'); backdrop && backdrop.remove(); },
  };
  document.querySelectorAll('[data-cat-close]').forEach((b) => b.addEventListener('click', () => modal.hide()));

  /* ---- table ---- */
  async function loadLookups() { L = await api('GET', BASE + '/lookups'); }
  async function load() {
    const c = CFG[entity];
    $('#catHead').innerHTML = c.cols.map(([h]) => `<th>${h}</th>`).join('') + '<th class="text-end">Actions</th>';
    $('#catBody').innerHTML = `<tr><td colspan="${c.cols.length + 1}" class="text-center text-muted py-4">Loading…</td></tr>`;
    rows = await api('GET', `${BASE}/${entity}`);
    draw();
  }
  function draw() {
    const c = CFG[entity], q = $('#catSearch').value.trim().toLowerCase();
    const list = rows.filter((r) => !q || JSON.stringify(r).toLowerCase().includes(q));
    $('#catBody').innerHTML = list.length ? list.map((r) => `<tr data-id="${r.id}">${c.cols.map(([, f]) => `<td>${f(r)}</td>`).join('')}
      <td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-act="edit"><i class="fa fa-pen"></i></button>
      <button class="btn btn-sm btn-outline-danger" data-act="del"><i class="fa fa-trash"></i></button></td></tr>`).join('')
      : `<tr><td colspan="${c.cols.length + 1}" class="text-center text-muted py-4">Nothing here yet.</td></tr>`;
  }
  $('#catSearch').addEventListener('input', draw);
  $('#catTabs').addEventListener('click', (e) => {
    const a = e.target.closest('[data-entity]'); if (!a) return; e.preventDefault();
    document.querySelectorAll('#catTabs .nav-link').forEach((x) => x.classList.toggle('active', x === a));
    entity = a.dataset.entity; $('#catAddLabel').textContent = CFG[entity].label; $('#catSearch').value = '';
    load().catch((er) => flash(er.message, 'danger'));
  });
  $('#catBody').addEventListener('click', async (e) => {
    const b = e.target.closest('[data-act]'); if (!b) return;
    const id = +b.closest('tr').dataset.id;
    try {
      if (b.dataset.act === 'edit') return openForm(await api('GET', `${BASE}/${entity}/${id}`));
      if (!confirm('Delete this ' + CFG[entity].label + '?')) return;
      await api('DELETE', `${BASE}/${entity}/${id}`);
      flash('Deleted.'); await loadLookups(); load();
    } catch (er) { flash(er.messages.join(' '), 'danger'); }
  });
  $('#catAddBtn').addEventListener('click', () => openForm(null));

  /* ---- form builder ---- */
  function openForm(data) {
    const c = CFG[entity]; editId = data ? data.id : null;
    $('#catModalTitle').textContent = (data ? 'Edit ' : 'Add ') + c.label;
    $('#catErrors').innerHTML = '';
    $('#catFields').innerHTML = c.fields().map((f) => {
      const v = data ? data[f.n] : f.d;
      const id = 'cf_' + f.n, col = `col-md-${f.w || 6}`;
      let input;
      if (f.t === 'select') input = `<select class="form-select" id="${id}" name="${f.n}">${f.o().map(([val, lab]) => `<option value="${esc(val)}" ${String(v ?? '') === String(val) ? 'selected' : ''}>${esc(lab)}</option>`).join('')}</select>`;
      else if (f.t === 'textarea') input = `<textarea class="form-control" id="${id}" name="${f.n}" rows="3">${esc(v)}</textarea>`;
      else if (f.t === 'switch') return `<div class="${col} d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="${id}" name="${f.n}" ${+v ? 'checked' : ''}><label class="form-check-label" for="${id}">${f.l}</label></div></div>`;
      else if (f.t === 'file') input = `${data && data[f.url] ? `<div class="mb-2"><img class="cat-thumb" src="${esc(data[f.url])}" alt=""> <label class="ms-2 small"><input type="checkbox" name="remove_image"> remove</label></div>` : ''}<input class="form-control" type="file" id="${id}" name="${f.n}" accept="image/*">`;
      else input = `<input class="form-control" type="${f.t}" id="${id}" name="${f.n}" value="${esc(v)}" ${f.step ? `step="${f.step}"` : ''} ${f.t === 'number' ? 'min="0"' : ''}>`;
      return `<div class="${col}"><label class="form-label" for="${id}">${f.l}${f.req ? ' <span class="text-danger">*</span>' : ''}</label>${input}${f.help ? `<small class="text-muted">${f.help}</small>` : ''}</div>`;
    }).join('');

    const x = $('#catExtra'); x.innerHTML = ''; x.hidden = false;
    if (c.extra === 'attrs') renderAttrs(x, data ? data.attrs : []);
    if (c.extra === 'options') { renderOptions(x, data ? data.options : []); const t = $('#cf_input_type'), tog = () => (x.hidden = !['select', 'multiselect', 'color'].includes(t.value)); t.addEventListener('change', tog); tog(); }
    modal.show();
  }

  /* category -> attribute assignment rows */
  function attrRow(r = {}) {
    const tri = (v) => `<option value="" ${v === '' || v == null ? 'selected' : ''}>Default</option><option value="1" ${v === 1 ? 'selected' : ''}>Yes</option><option value="0" ${v === 0 ? 'selected' : ''}>No</option>`;
    return `<div class="cat-extra-row cat-attr">
      <select class="form-select form-select-sm a-attr"><option value="">Select attribute…</option>${L.attributes.map((a) => `<option value="${a.id}" ${a.id == r.attribute_id ? 'selected' : ''}>${esc(a.name)} (${a.input_type})</option>`).join('')}</select>
      <label class="small"><input type="checkbox" class="a-req" ${r.is_required ? 'checked' : ''}> Required</label>
      <select class="form-select form-select-sm a-filter">${tri(r.is_filterable)}</select>
      <select class="form-select form-select-sm a-axis">${tri(r.is_variant_axis)}</select>
      <select class="form-select form-select-sm a-applies">${['both', 'retail', 'wholesale'].map((v) => `<option ${v === (r.applies_to || 'both') ? 'selected' : ''}>${v}</option>`).join('')}</select>
      <input type="number" class="form-control form-control-sm a-sort" min="0" value="${r.sort_order ?? 0}">
      <button type="button" class="btn btn-sm btn-outline-danger a-del">&times;</button></div>`;
  }
  function renderAttrs(x, list) {
    x.innerHTML = `<div class="cat-extra-box"><h6 class="mb-1">Attributes for this category</h6>
      <p class="small text-muted">Sub-categories inherit these automatically. “Default” follows the attribute’s own setting.</p>
      <div class="cat-extra-row cat-attr cat-extra-head"><span>Attribute</span><span></span><span>Filterable</span><span>Variant option</span><span>Applies to</span><span>Order</span><span></span></div>
      <div id="attrRows">${list.map(attrRow).join('')}</div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="attrAdd"><i class="fa fa-plus"></i> Add attribute</button></div>`;
    $('#attrAdd').onclick = () => $('#attrRows').insertAdjacentHTML('beforeend', attrRow({ sort_order: $('#attrRows').children.length }));
    $('#attrRows').onclick = (e) => e.target.closest('.a-del') && e.target.closest('.cat-extra-row').remove();
  }
  const collectAttrs = () => [...document.querySelectorAll('#attrRows .cat-extra-row')].filter((r) => $('.a-attr', r).value).map((r) => ({
    attribute_id: +$('.a-attr', r).value, is_required: $('.a-req', r).checked, is_filterable: $('.a-filter', r).value,
    is_variant_axis: $('.a-axis', r).value, applies_to: $('.a-applies', r).value, sort_order: +$('.a-sort', r).value || 0 }));

  /* attribute -> options rows */
  const optRow = (o = {}) => `<div class="cat-extra-row cat-opt" data-id="${o.id || ''}">
      <input class="form-control form-control-sm o-label" placeholder="Label (e.g. Large)" value="${esc(o.label)}">
      <input class="form-control form-control-sm o-value" placeholder="Value (auto)" value="${esc(o.value)}">
      <input class="form-control form-control-sm o-hex" placeholder="#colour" value="${esc(o.color_hex)}">
      <input type="number" class="form-control form-control-sm o-sort" min="0" value="${o.sort_order ?? 0}">
      <button type="button" class="btn btn-sm btn-outline-danger o-del">&times;</button></div>`;
  function renderOptions(x, list) {
    x.innerHTML = `<div class="cat-extra-box"><h6 class="mb-1">Options</h6>
      <p class="small text-muted">The choices sellers can pick (sizes, colours, materials…). Colour hex is optional.</p>
      <div class="cat-extra-row cat-opt cat-extra-head"><span>Label</span><span>Value</span><span>Colour</span><span>Order</span><span></span></div>
      <div id="optRows">${list.map(optRow).join('')}</div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="optAdd"><i class="fa fa-plus"></i> Add option</button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="optBulk">Paste many</button></div>`;
    $('#optAdd').onclick = () => $('#optRows').insertAdjacentHTML('beforeend', optRow({ sort_order: $('#optRows').children.length }));
    $('#optBulk').onclick = () => {
      const t = prompt('One option per line:'); if (!t) return;
      t.split('\n').map((s) => s.trim()).filter(Boolean).forEach((s) => $('#optRows').insertAdjacentHTML('beforeend', optRow({ label: s, sort_order: $('#optRows').children.length })));
    };
    $('#optRows').onclick = (e) => e.target.closest('.o-del') && e.target.closest('.cat-extra-row').remove();
  }
  const collectOpts = () => [...document.querySelectorAll('#optRows .cat-extra-row')].map((r) => ({ id: r.dataset.id ? +r.dataset.id : null,
    label: $('.o-label', r).value, value: $('.o-value', r).value, color_hex: $('.o-hex', r).value, sort_order: +$('.o-sort', r).value || 0 }));

  /* ---- submit ---- */
  $('#catForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const c = CFG[entity], fd = new FormData();
    c.fields().forEach((f) => {
      const el = $(`[name="${f.n}"]`, e.target); if (!el) return;
      if (f.t === 'switch') fd.append(f.n, el.checked ? 1 : 0);
      else if (f.t === 'file') { if (el.files[0]) fd.append(f.n, el.files[0]); }
      else fd.append(f.n, el.value);
    });
    const rm = $('[name="remove_image"]', e.target); if (rm && rm.checked) fd.append('remove_image', 1);
    if (c.extra === 'attrs') fd.append('attrs_json', JSON.stringify(collectAttrs()));
    if (c.extra === 'options') fd.append('options_json', JSON.stringify(collectOpts()));
    if (editId) fd.append('_method', 'PUT');

    const btn = $('#catSaveBtn'); btn.disabled = true;
    try {
      const res = await api('POST', editId ? `${BASE}/${entity}/${editId}` : `${BASE}/${entity}`, fd);
      modal.hide(); flash(res.message); await loadLookups(); await load();
    } catch (er) {
      $('#catErrors').innerHTML = `<div class="alert alert-danger"><ul class="mb-0">${er.messages.map((m) => `<li>${esc(m)}</li>`).join('')}</ul></div>`;
      $('.modal-body', modalEl).scrollTop = 0;
    } finally { btn.disabled = false; }
  });

  loadLookups().then(load).catch((er) => flash(er.message, 'danger'));
})();
</script>
@endpush
