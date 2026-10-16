/* public/js/cchub/admin-church.js
   Admin CRUD for Rite -> Diocese -> Forane -> Parish on ONE page.
   Tabs, search, cascading filters, add / edit dialog, delete confirm, pagination.
*/
(() => {
  'use strict';

  const root = document.getElementById('churchAdmin');
  if (!root) return;

  const BASE = root.dataset.base;
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const $ = (id) => document.getElementById(id);

  /* ---------------- config ---------------- */
  const LABEL = { rite: 'Rite', diocese: 'Diocese', forane: 'Forane', parish: 'Parish' };
  const PLURAL = { rite: 'Rites', diocese: 'Dioceses', forane: 'Foranes', parish: 'Parishes' };
  // parents that must be picked (top -> down) before the name
  const CHAIN = {
    rite: [],
    diocese: ['rite'],
    forane: ['rite', 'diocese'],
    parish: ['rite', 'diocese', 'forane'],
  };
  const COLS = {
    rite: [['name', 'Rite'], ['count', 'Dioceses']],
    diocese: [['name', 'Diocese'], ['rite', 'Rite'], ['count', 'Foranes']],
    forane: [['name', 'Forane'], ['diocese', 'Diocese'], ['rite', 'Rite'], ['count', 'Parishes']],
    parish: [['name', 'Parish'], ['forane', 'Forane'], ['diocese', 'Diocese'], ['rite', 'Rite'], ['count', 'Members']],
  };
  const NAME_BAD = /[^\p{L}\p{M}\p{N} .,'’&()\/+\-]/gu;
  const NAME_OK = /^[\p{L}\p{M}\p{N} .,'’&()\/+\-]+$/u;

  const state = { type: 'rite', page: 1, q: '', filters: {}, rows: new Map(), editId: null, deleteRow: null, token: 0 };

  const head = $('caHead');
  const body = $('caBody');
  const filtersBox = $('caFilters');
  const parentsBox = $('caParents');
  const formDlg = $('caFormDialog');
  const delDlg = $('caDeleteDialog');
  const nameInput = $('caName');

  /* ---------------- helpers ---------------- */
  async function api(url, opts = {}) {
    const res = await fetch(url, {
      ...opts,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const err = new Error(data.message || `Request failed (${res.status})`);
      err.status = res.status;
      err.errors = data.errors || {};
      throw err;
    }
    return data;
  }

  let toastTimer;
  function toast(msg, isError) {
    const t = $('caToast');
    t.textContent = msg;
    t.classList.toggle('is-error', !!isError);
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.hidden = true; }, 3500);
  }

  const cleanName = (v) => v.replace(NAME_BAD, '').replace(/^\s+/, '').replace(/ {2,}/g, ' ').slice(0, 100);

  function showErr(key, msg) {
    const p = formDlg.querySelector(`[data-err="${key}"]`);
    if (!p) return;
    p.textContent = msg || '';
    p.hidden = !msg;
  }

  /* ---------------- cascading selects ---------------- */
  async function loadLevel(box, chain, i, selected) {
    const sel = box.querySelector(`[data-chain="${chain[i]}"]`);
    const prev = i ? box.querySelector(`[data-chain="${chain[i - 1]}"]`).value : '';
    sel.innerHTML = '';
    sel.add(new Option(sel.dataset.ph, ''));
    if (i && !prev) { sel.disabled = true; return; }
    sel.disabled = false;
    const items = await api(`${BASE}/options?type=${chain[i]}${i ? `&parent_id=${prev}` : ''}`);
    items.forEach((o) => sel.add(new Option(o.name, o.id)));
    if (selected) sel.value = String(selected);
  }

  async function buildChain(box, type, all, values = {}) {
    const chain = CHAIN[type];
    box.dataset.type = type;
    box.innerHTML = '';
    chain.forEach((t) => {
      const wrap = document.createElement('label');
      wrap.className = 'ca-field';
      if (!all) {
        const span = document.createElement('span');
        span.textContent = LABEL[t];
        wrap.appendChild(span);
      }
      const sel = document.createElement('select');
      sel.dataset.chain = t;
      sel.dataset.ph = all ? `All ${PLURAL[t].toLowerCase()}` : `Select ${LABEL[t].toLowerCase()}`;
      sel.setAttribute('aria-label', LABEL[t]);
      wrap.appendChild(sel);
      box.appendChild(wrap);
    });
    box.hidden = chain.length === 0;
    for (let i = 0; i < chain.length; i++) await loadLevel(box, chain, i, values[chain[i]]);
  }

  const readChain = (box) => {
    const out = {};
    box.querySelectorAll('[data-chain]').forEach((s) => { if (s.value) out[`${s.dataset.chain}_id`] = s.value; });
    return out;
  };

  async function onChainChange(e, box, isFilter) {
    const sel = e.target.closest('[data-chain]');
    if (!sel) return;
    const chain = CHAIN[box.dataset.type];
    const i = chain.indexOf(sel.dataset.chain);
    for (let j = i + 1; j < chain.length; j++) await loadLevel(box, chain, j);
    if (isFilter) {
      state.filters = readChain(box);
      state.page = 1;
      load();
    } else {
      showErr('parent', '');
    }
  }
  filtersBox.addEventListener('change', (e) => onChainChange(e, filtersBox, true));
  parentsBox.addEventListener('change', (e) => onChainChange(e, parentsBox, false));

  /* ---------------- table ---------------- */
  function renderHead() {
    const tr = document.createElement('tr');
    COLS[state.type].forEach(([, label]) => {
      const th = document.createElement('th');
      th.textContent = label;
      tr.appendChild(th);
    });
    const a = document.createElement('th');
    a.className = 'ca-actions-col';
    a.textContent = 'Actions';
    tr.appendChild(a);
    head.replaceChildren(tr);
  }

  function rowEl(r) {
    const tr = document.createElement('tr');
    tr.dataset.id = r.id;
    COLS[state.type].forEach(([key]) => {
      const td = document.createElement('td');
      const v = r[key];
      td.textContent = v === null || v === undefined || v === '' ? '—' : v;
      if (key === 'name') td.className = 'ca-name';
      if (key === 'count') td.className = 'ca-num';
      tr.appendChild(td);
    });
    const td = document.createElement('td');
    td.className = 'ca-actions';
    td.innerHTML =
      '<button type="button" class="ca-icon-btn" data-act="edit" aria-label="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>' +
      '<button type="button" class="ca-icon-btn is-danger" data-act="delete" aria-label="Delete"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>';
    tr.appendChild(td);
    return tr;
  }

  function message(text) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = COLS[state.type].length + 1;
    td.className = 'ca-empty';
    td.textContent = text;
    tr.appendChild(td);
    body.replaceChildren(tr);
  }

  async function load() {
    const mine = ++state.token;
    const params = new URLSearchParams({ page: state.page });
    if (state.q) params.set('q', state.q);
    Object.entries(state.filters).forEach(([k, v]) => params.set(k, v));

    message('Loading...');
    let data;
    try {
      data = await api(`${BASE}/${state.type}?${params}`);
    } catch (err) {
      if (mine === state.token) message(err.message);
      return;
    }
    if (mine !== state.token) return; // a newer request replaced this one

    // deleted the last item on a page -> go back one page
    if (data.meta.current_page > data.meta.last_page) {
      state.page = data.meta.last_page;
      return load();
    }

    Object.entries(data.counts).forEach(([k, v]) => {
      const b = root.querySelector(`[data-count="${k}"]`);
      if (b) b.textContent = v;
    });

    state.rows = new Map(data.items.map((r) => [r.id, r]));
    if (!data.items.length) {
      message(state.q || Object.keys(state.filters).length ? 'No matches found.' : `No ${PLURAL[state.type].toLowerCase()} added yet.`);
    } else {
      body.replaceChildren(...data.items.map(rowEl));
    }

    const m = data.meta;
    const from = m.total ? (m.current_page - 1) * m.per_page + 1 : 0;
    const to = Math.min(m.current_page * m.per_page, m.total);
    $('caInfo').textContent = m.total ? `Showing ${from}-${to} of ${m.total}` : '0 results';
    $('caPrev').disabled = m.current_page <= 1;
    $('caNext').disabled = m.current_page >= m.last_page;
  }

  body.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const row = state.rows.get(Number(btn.closest('tr').dataset.id));
    if (!row) return;
    if (btn.dataset.act === 'edit') openForm(row);
    else openDelete(row);
  });

  $('caPrev').addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
  $('caNext').addEventListener('click', () => { state.page++; load(); });

  let searchTimer;
  $('caSearch').addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.q = e.target.value.trim();
      state.page = 1;
      load();
    }, 300);
  });

  /* ---------------- tabs ---------------- */
  async function switchTab(type) {
    state.type = type;
    state.page = 1;
    state.q = '';
    state.filters = {};
    $('caSearch').value = '';
    root.querySelectorAll('.ca-tab').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.type === type)));
    $('caAddLabel').textContent = `Add ${LABEL[type]}`;
    renderHead();
    buildChain(filtersBox, type, true);
    load();
  }
  root.querySelector('.ca-tabs').addEventListener('click', (e) => {
    const tab = e.target.closest('.ca-tab');
    if (tab && tab.dataset.type !== state.type) switchTab(tab.dataset.type);
  });

  /* ---------------- add / edit dialog ---------------- */
  async function openForm(row) {
    const type = state.type;
    state.editId = row ? row.id : null;
    $('caFormTitle').textContent = `${row ? 'Edit' : 'Add'} ${LABEL[type]}`;
    $('caNameLabel').textContent = `${LABEL[type]} name`;
    showErr('name', '');
    showErr('parent', '');
    nameInput.value = row ? row.name : '';
    $('caSaveBtn').disabled = false;
    formDlg.showModal();
    await buildChain(parentsBox, type, false, row ? row.ids : {});
    nameInput.focus();
  }
  $('caAddBtn').addEventListener('click', () => openForm(null));

  nameInput.addEventListener('input', () => {
    const v = cleanName(nameInput.value);
    if (v !== nameInput.value) nameInput.value = v;
    showErr('name', '');
  });

  $('caForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const type = state.type;
    const name = nameInput.value.trim().replace(/ {2,}/g, ' ');
    const chain = CHAIN[type];
    let bad = false;

    if (chain.length) {
      const last = parentsBox.querySelector(`[data-chain="${chain[chain.length - 1]}"]`);
      if (!last || !last.value) {
        showErr('parent', `Please select the ${chain.map((t) => LABEL[t].toLowerCase()).join(' and ')}.`);
        bad = true;
      }
    }
    if (name.length < 2) { showErr('name', 'Name must be at least 2 characters.'); bad = true; }
    else if (!NAME_OK.test(name)) { showErr('name', "Use letters, numbers and . , ' & ( ) / + - only."); bad = true; }
    if (bad) return;

    const payload = { name };
    if (chain.length) {
      const t = chain[chain.length - 1];
      payload[`${t}_id`] = parentsBox.querySelector(`[data-chain="${t}"]`).value;
    }

    const saveBtn = $('caSaveBtn');
    saveBtn.disabled = true;
    try {
      const editing = state.editId !== null;
      const res = await api(editing ? `${BASE}/${type}/${state.editId}` : `${BASE}/${type}`, {
        method: editing ? 'PUT' : 'POST',
        body: JSON.stringify(payload),
      });
      formDlg.close();
      toast(res.message || 'Saved.');
      load();
    } catch (err) {
      const errs = err.errors || {};
      if (errs.name) showErr('name', errs.name[0]);
      const fk = chain.length ? `${chain[chain.length - 1]}_id` : null;
      if (fk && errs[fk]) showErr('parent', errs[fk][0]);
      if (!Object.keys(errs).length) toast(err.message, true);
      saveBtn.disabled = false;
    }
  });

  /* ---------------- delete dialog ---------------- */
  function openDelete(row) {
    state.deleteRow = row;
    $('caDeleteTitle').textContent = `Delete ${LABEL[state.type]}`;
    $('caDeleteText').textContent = `Delete ${LABEL[state.type].toLowerCase()} "${row.name}"? This cannot be undone.`;
    $('caDeleteConfirm').disabled = false;
    delDlg.showModal();
  }

  $('caDeleteConfirm').addEventListener('click', async () => {
    const row = state.deleteRow;
    if (!row) return;
    $('caDeleteConfirm').disabled = true;
    try {
      const res = await api(`${BASE}/${state.type}/${row.id}`, { method: 'DELETE' });
      toast(res.message || 'Deleted.');
    } catch (err) {
      toast(err.message, true);
    }
    delDlg.close();
    load();
  });

  /* ---------------- dialog close handling ---------------- */
  [formDlg, delDlg].forEach((dlg) => {
    dlg.addEventListener('click', (e) => {
      if (e.target === dlg || e.target.closest('[data-close]')) dlg.close(); // backdrop or close buttons
    });
  });

  switchTab('rite');
})();
