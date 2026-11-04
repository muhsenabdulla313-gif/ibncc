/* Seller Hub — dynamic page (replaces the old static seller_page.js).
   Talks to /seller/* JSON endpoints. Class names match seller_page.css. */
(() => {
  const C = window.SELLER, R = C.routes;
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const csrf = $('meta[name="csrf-token"]').content;
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const money = (n) => '₹' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 2 });
  const numOrNull = (v) => (v === '' || v == null ? null : Number(v));
  const STATUS = { draft: 'Draft', pending: 'In review', active: 'Live', rejected: 'Rejected', inactive: 'Hidden' };

  const st = { mode: C.mode, product: null, images: [], removed: [], gallery: 0 };

  /* ---------- helpers ---------- */
  function toast(msg, type = 'ok') {
    const t = document.createElement('div');
    t.className = 'seller-toast is-' + type;
    t.textContent = msg;
    $('#sellerToasts').appendChild(t);
    setTimeout(() => t.classList.add('is-out'), 4200);
    setTimeout(() => t.remove(), 4700);
  }
  async function api(method, url, body) {
    const o = { method, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
    if (body instanceof FormData) o.body = body;
    else if (body !== undefined) { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
    const res = await fetch(url, o);
    let j = null; try { j = await res.json(); } catch (e) {}
    if (!res.ok) {
      const msgs = j && j.errors ? Object.values(j.errors).flat() : [(j && j.message) || 'Something went wrong'];
      const err = new Error(msgs[0]); err.messages = msgs; throw err;
    }
    return j;
  }
  async function run(btn, fn) {
    if (btn) btn.disabled = true;
    try { await fn(); } catch (e) { toast((e.messages || [e.message]).join(' · '), 'error'); }
    finally { if (btn) btn.disabled = false; }
  }
  const pUrl = (sub = '') => R.product + '/' + st.product.uuid + sub;

  /* ---------- combo box ---------- */
  function Combo(root) {
    const txt = $('input[type=text]', root), hid = $('[data-combo-value]', root), ul = $('.seller-combo-list', root), tg = $('.seller-combo-toggle', root);
    let items = [], cb = () => {};
    const nameOf = (id) => (items.find((i) => String(i.id) === String(id)) || {}).name || '';
    const render = (f = '') => {
      const l = items.filter((i) => i.name.toLowerCase().includes(f.toLowerCase()));
      ul.innerHTML = l.length ? l.map((i) => `<li role="option" data-id="${i.id}" ${String(i.id) === hid.value ? 'aria-selected="true"' : ''}>${esc(i.name)}</li>`).join('') : '<li class="is-empty">No matches</li>';
    };
    const open = (f = '') => { if (txt.disabled) return; render(f); ul.hidden = false; txt.setAttribute('aria-expanded', 'true'); };
    const close = () => { ul.hidden = true; txt.setAttribute('aria-expanded', 'false'); txt.value = nameOf(hid.value); };
    const pick = (id) => { const ch = String(id) !== hid.value; hid.value = id; txt.value = nameOf(id); ul.hidden = true; if (ch) cb(id); };
    txt.addEventListener('focus', () => { txt.select(); open(); });
    txt.addEventListener('input', () => { if (!txt.value && hid.value) { hid.value = ''; cb(''); } open(txt.value); });
    txt.addEventListener('blur', () => setTimeout(close, 120));
    tg.addEventListener('click', () => (ul.hidden ? (txt.focus(), open()) : close()));
    ul.addEventListener('mousedown', (e) => { const li = e.target.closest('li[data-id]'); if (li) { e.preventDefault(); pick(li.dataset.id); } });
    return {
      setItems(a) { items = a; if (!items.some((i) => String(i.id) === hid.value)) hid.value = ''; txt.value = nameOf(hid.value); },
      set(id) { hid.value = id ?? ''; txt.value = nameOf(id); },
      get: () => hid.value,
      onChange(f) { cb = f; },
      disable(b) { txt.disabled = b; tg.disabled = b; },
    };
  }

  /* ---------- category cascade (category > subcategory > sub-subcategory) ---------- */
  const catCombos = $$('#panel-add-edit [data-combo][data-level]').map(Combo);
  const catItems = [[], [], []];
  function clearFrom(l) { for (let k = l; k < 3; k++) { catItems[k] = []; catCombos[k].setItems([]); catCombos[k].disable(true); } }
  async function loadLevel(l, parent) {
    if (l > 2) return;
    const items = l === 0 ? C.roots : await api('GET', R.children + '?parent_id=' + parent);
    catItems[l] = items; catCombos[l].setItems(items); catCombos[l].disable(!items.length);
    clearFrom(l + 1);
  }
  catCombos.forEach((cb, l) => cb.onChange((id) => {
    clearFrom(l + 1);
    const hit = catItems[l].find((i) => String(i.id) === String(id));
    if (id && hit && hit.has_children) run(null, () => loadLevel(l + 1, id));
  }));
  async function setCategoryPath(path) {
    await loadLevel(0); clearFrom(1);
    for (let i = 0; i < path.length && i < 3; i++) {
      if (i > 0) await loadLevel(i, path[i - 1].id);
      catCombos[i].set(path[i].id);
    }
  }
  const leafCategory = () => [...catCombos].reverse().map((c) => c.get()).find(Boolean) || '';

  /* ---------- unit combos (Pricing + Price tiers share one value) ---------- */
  const unitItems = C.units.map((u) => ({ id: u.id, name: u.name }));
  const unitCombos = $$('[data-combo][data-kind="unit"]').map(Combo);
  unitCombos.forEach((c, i) => {
    c.setItems(unitItems);
    c.onChange((id) => { unitCombos.forEach((o, j) => j !== i && o.set(id)); $('#moqUnit').value = (unitItems.find((u) => String(u.id) === String(id)) || {}).name || ''; });
  });
  const setUnit = (id) => unitCombos.forEach((c) => c.set(id));

  /* ---------- mode + navigation ---------- */
  function setMode(m, push = true) {
    st.mode = m; document.body.dataset.sellerMode = m;
    $$('.seller-mode-tab').forEach((t) => { const on = t.dataset.mode === m; t.classList.toggle('is-active', on); t.setAttribute('aria-selected', on); });
    $$('[data-mode-hint]').forEach((h) => (h.hidden = h.dataset.modeHint !== m));
    $$('.seller-nav-item').forEach((b) => (b.hidden = !b.dataset.sellerModes.split(' ').includes(m)));
    $$('[data-mode-badge]').forEach((b) => (b.textContent = m === 'trading' ? 'Trading' : 'Marketplace'));
    $('#thPrice').textContent = m === 'trading' ? 'Price (from)' : 'Price';
    if (push) history.replaceState(null, '', '?mode=' + m);
    const cur = $('.seller-nav-item.is-active');
    if (cur && cur.hidden) go('products');
    if (!st.product) $('#productSaleType').value = m === 'trading' ? 'wholesale' : 'retail';
    loadList();
  }
  function go(name) {
    const item = $(`.seller-nav-item[data-panel="${name}"]`);
    if (item && item.classList.contains('needs-product') && !st.product) { toast('Save or open a product first.', 'error'); return; }
    $$('.seller-nav-item').forEach((b) => { const on = b.dataset.panel === name; b.classList.toggle('is-active', on); b.setAttribute('aria-selected', on); });
    $$('.seller-panel').forEach((p) => { const on = p.dataset.panel === name; p.classList.toggle('is-active', on); p.hidden = !on; });
    $('#currentBar').hidden = !(st.product && !['products', 'retail-orders', 'bulk-orders'].includes(name));
  }
  $$('.seller-nav-item').forEach((b) => b.addEventListener('click', () => go(b.dataset.panel)));
  $$('.seller-mode-tab').forEach((t) => t.addEventListener('click', () => setMode(t.dataset.mode)));

  /* ---------- my products list ---------- */
  async function loadList() {
    const body = $('#myProductsBody');
    try {
      const rows = await api('GET', R.products + '?mode=' + st.mode);
      if (!rows.length) { body.innerHTML = '<tr><td colspan="8" class="seller-empty">No products yet — click “New product” to add your first listing.</td></tr>'; return; }
      body.innerHTML = rows.map((r) => {
        const acts = [`<button type="button" class="seller-link-btn" data-act="edit">Edit</button>`];
        if (['draft', 'rejected'].includes(r.status)) acts.push(`<button type="button" class="seller-link-btn" data-act="submit">Submit</button>`);
        if (r.status === 'pending') acts.push(`<button type="button" class="seller-link-btn" data-act="draft">Withdraw</button>`);
        if (r.status === 'active') acts.push(`<button type="button" class="seller-link-btn" data-act="deactivate">Hide</button>`);
        if (r.status === 'inactive') acts.push(`<button type="button" class="seller-link-btn" data-act="activate">Show</button>`);
        acts.push(`<button type="button" class="seller-link-btn is-danger" data-act="delete">Delete</button>`);
        const ch = { retail: 'Marketplace', wholesale: 'Trading', both: 'Both' }[r.sale_type];
        return `<tr data-uuid="${r.uuid}">
          <td>${r.image ? `<img class="seller-prod-thumb" src="${esc(r.image)}" alt="">` : '<span class="seller-prod-thumb is-empty"><i class="fa-regular fa-image"></i></span>'}</td>
          <td><strong>${esc(r.title)}</strong><br><small>${esc(r.sku || '')}${r.variants > 1 ? ' · ' + r.variants + ' variants' : ''}</small></td>
          <td>${esc(r.category || '—')}</td><td>${ch}</td>
          <td>${r.price !== null ? (st.mode === 'trading' ? 'from ' : '') + money(r.price) + (r.unit ? ' / ' + esc(r.unit) : '') : '—'}</td>
          <td>${r.stock}</td>
          <td><span class="seller-pill is-${r.status}">${STATUS[r.status]}</span></td>
          <td class="seller-actions-cell">${acts.join('')}</td></tr>`;
      }).join('');
    } catch (e) { body.innerHTML = `<tr><td colspan="8" class="seller-empty">${esc(e.message)}</td></tr>`; }
  }
  $('#myProductsBody').addEventListener('click', (e) => {
    const b = e.target.closest('[data-act]'); if (!b) return;
    const uuid = b.closest('tr').dataset.uuid, act = b.dataset.act;
    run(b, async () => {
      if (act === 'edit') return openProduct(uuid, 'add-edit');
      if (act === 'delete') {
        if (!confirm('Delete this product? Past orders keep their details.')) return;
        await api('DELETE', R.product + '/' + uuid);
        if (st.product && st.product.uuid === uuid) newProduct(false);
        toast('Product deleted.'); return loadList();
      }
      const res = await api('POST', R.product + '/' + uuid + '/status', { action: act });
      if (st.product && st.product.uuid === uuid) setProduct(res.product);
      toast(res.message); loadList();
    });
  });
  $('#newProductBtn').addEventListener('click', () => newProduct(true));

  /* ---------- load / set current product ---------- */
  async function openProduct(uuid, panel) { const p = await api('GET', R.product + '/' + uuid); await setProduct(p); go(panel || 'add-edit'); }

  async function setProduct(p) {
    st.product = p;
    $$('.needs-product').forEach((b) => b.classList.remove('is-locked'));
    fillBar(p); fillAddEdit(p); fillDetails(p); fillVariants(p); fillPricing(p); fillBulk(p); fillTiers(p); fillStock(p);
    await setCategoryPath(p.category_path);
  }

  function newProduct(navigate) {
    st.product = null; st.images = []; st.removed = [];
    $$('.needs-product').forEach((b) => b.classList.add('is-locked'));
    $('#formAddEdit').reset(); $('#addEditTitle').textContent = 'Add product';
    $('#productSaleType').value = st.mode === 'trading' ? 'wholesale' : 'retail';
    renderImages(); setCategoryPath([]); $('#currentBar').hidden = true;
    if (navigate) go('add-edit');
  }

  function fillBar(p) {
    $('#currentTitle').textContent = p.title;
    const s = $('#currentStatus'); s.textContent = STATUS[p.status]; s.className = 'seller-pill is-' + p.status;
    const m = $('#currentMissing'); const notes = [];
    if (p.status === 'rejected' && p.rejection_reason) notes.push('Rejected: ' + p.rejection_reason);
    if (['draft', 'rejected'].includes(p.status) && p.missing.length) notes.push('Before you can submit: ' + p.missing.join(' · '));
    m.hidden = !notes.length; m.textContent = notes.join('  —  ');
  }

  /* ---------- Add / edit ---------- */
  function fillAddEdit(p) {
    $('#addEditTitle').textContent = 'Edit product';
    $('#productName').value = p.title; $('#productSaleType').value = p.sale_type;
    $('#productBrand').value = p.brand_name || ''; $('#productSku').value = p.sku || '';
    $('#productModel').value = p.model_no || ''; $('#productShort').value = p.short_description || '';
    $('#productDescription').value = p.description || '';
    st.images = p.images.map((i) => ({ id: i.id, url: i.url, primary: i.is_primary })); st.removed = [];
    renderImages();
  }
  function renderImages() {
    if (st.images.length && !st.images.some((i) => i.primary)) st.images[0].primary = true;
    $('#productImages').innerHTML = st.images.map((im, i) =>
      `<div class="seller-thumb ${im.primary ? 'is-active' : ''}" data-i="${i}" title="${im.primary ? 'Cover image' : 'Click to make cover'}"><img src="${esc(im.url)}" alt=""><button type="button" class="seller-thumb-remove" data-rm="${i}" aria-label="Remove image"><i class="fa-solid fa-xmark"></i></button></div>`
    ).join('') + (st.images.length < 8 ? '<button type="button" class="seller-upload-slot" data-add><i class="fa-solid fa-plus"></i> Add image</button>' : '');
  }
  $('#productImages').addEventListener('click', (e) => {
    if (e.target.closest('[data-add]')) return $('#productImageInput').click();
    const rm = e.target.closest('[data-rm]');
    if (rm) { const [im] = st.images.splice(+rm.dataset.rm, 1); if (im.id) st.removed.push(im.id); return renderImages(); }
    const th = e.target.closest('.seller-thumb');
    if (th) { st.images.forEach((im, i) => (im.primary = i === +th.dataset.i)); renderImages(); }
  });
  $('#productImageInput').addEventListener('change', (e) => {
    for (const f of e.target.files) {
      if (st.images.length >= 8) { toast('Maximum 8 images.', 'error'); break; }
      if (f.size > 4 * 1024 * 1024) { toast(f.name + ' is larger than 4 MB.', 'error'); continue; }
      st.images.push({ file: f, url: URL.createObjectURL(f), primary: false });
    }
    e.target.value = ''; renderImages();
  });
  $('#resetAddEdit').addEventListener('click', () => (st.product ? setProduct(st.product) : newProduct(false)));

  $('#formAddEdit').addEventListener('submit', (e) => {
    e.preventDefault();
    run(e.submitter, async () => {
      const title = $('#productName').value.trim();
      if (!title) throw new Error('Enter a product name.');
      if (!leafCategory()) throw new Error('Select a category (down to the most specific level).');
      const fd = new FormData();
      fd.append('title', title); fd.append('sale_type', $('#productSaleType').value); fd.append('category_id', leafCategory());
      fd.append('brand_name', $('#productBrand').value); fd.append('sku', $('#productSku').value); fd.append('model_no', $('#productModel').value);
      fd.append('short_description', $('#productShort').value); fd.append('description', $('#productDescription').value);
      fd.append('action', (e.submitter && e.submitter.dataset.save) || 'submit');
      const fresh = st.images.filter((i) => i.file);
      fresh.forEach((i) => fd.append('images[]', i.file));
      st.removed.forEach((id) => fd.append('remove_images[]', id));
      const prim = st.images.find((i) => i.primary);
      if (prim && prim.id) fd.append('primary_id', prim.id);
      else if (prim) fd.append('primary_new', fresh.indexOf(prim));
      let res;
      if (st.product) { fd.append('_method', 'PUT'); res = await api('POST', pUrl(), fd); }
      else res = await api('POST', R.product, fd);
      const created = !st.product;
      await setProduct(res.product); toast(res.message); loadList();
      if (created) go(st.mode === 'trading' ? 'price-tiers' : 'pricing');
    });
  });

  /* ---------- Product details ---------- */
  function valueControl(r) {
    const opts = (r.options || []).map((o) => `<option value="${o.id}" ${[].concat(r.value).map(String).includes(String(o.id)) ? 'selected' : ''}>${esc(o.label)}</option>`).join('');
    const v = esc(Array.isArray(r.value) ? '' : r.value);
    switch (r.input_type) {
      case 'select': case 'color': return `<select class="prop-value"><option value="">—</option>${opts}</select>`;
      case 'multiselect': return `<select class="prop-value" multiple size="3">${opts}</select>`;
      case 'boolean': return `<select class="prop-value"><option value="">—</option><option value="1" ${r.value === '1' ? 'selected' : ''}>Yes</option><option value="0" ${r.value === '0' ? 'selected' : ''}>No</option></select>`;
      case 'number': return `<input class="prop-value" type="number" step="any" value="${v}" placeholder="${esc(r.unit || '')}">`;
      case 'date': return `<input class="prop-value" type="date" value="${v}">`;
      case 'textarea': return `<textarea class="prop-value" rows="2">${v}</textarea>`;
      default: return `<input class="prop-value" type="text" value="${v}" placeholder="${r.is_required ? 'Required' : ''}">`;
    }
  }
  const propRow = (r) => `<div class="seller-prop-row" data-attr="${r.id || ''}">
      <input class="prop-name" type="text" value="${esc(r.name)}" ${r.custom ? 'placeholder="Property"' : 'readonly'} aria-label="Property">
      ${valueControl(r)}
      ${r.custom ? '<button type="button" class="seller-row-remove" aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button>' : '<span></span>'}</div>`;
  function fillDetails(p) {
    $('#propertyRows').innerHTML = p.details.map((r) => propRow({ ...r, name: r.name + (r.is_required ? ' *' : '') })).join('');
    st.gallery = 0; renderGallery();
  }
  $('#addPropertyRow').addEventListener('click', () => $('#propertyRows').insertAdjacentHTML('beforeend', propRow({ id: null, name: '', input_type: 'text', value: '', custom: true })));
  $('#propertyRows').addEventListener('click', (e) => { const b = e.target.closest('.seller-row-remove'); if (b) b.closest('.seller-prop-row').remove(); });
  $('#saveDetails').addEventListener('click', (e) => run(e.currentTarget, async () => {
    const rows = $$('#propertyRows .seller-prop-row').map((r) => {
      const c = r.querySelector('.prop-value');
      return { attribute_id: r.dataset.attr ? +r.dataset.attr : null, name: r.querySelector('.prop-name').value.replace(/ \*$/, ''), value: c.multiple ? [...c.selectedOptions].map((o) => o.value) : c.value };
    });
    const res = await api('PUT', pUrl('/details'), { rows });
    setProduct(res.product); toast(res.message);
  }));
  function renderGallery() {
    const imgs = st.product ? st.product.images : [];
    const main = $('#detailsMainImage');
    main.hidden = !imgs.length; if (imgs.length) main.src = imgs[st.gallery].url;
    $('#detailsThumbs').innerHTML = imgs.map((im, i) => `<button type="button" class="seller-thumb ${i === st.gallery ? 'is-active' : ''}" data-i="${i}"><img src="${esc(im.url)}" alt=""></button>`).join('');
  }
  $('#detailsThumbs').addEventListener('click', (e) => { const t = e.target.closest('.seller-thumb'); if (t) { st.gallery = +t.dataset.i; renderGallery(); } });
  $('#thumbPrev').addEventListener('click', () => { const n = st.product ? st.product.images.length : 0; if (n) { st.gallery = (st.gallery + n - 1) % n; renderGallery(); } });
  $('#thumbNext').addEventListener('click', () => { const n = st.product ? st.product.images.length : 0; if (n) { st.gallery = (st.gallery + 1) % n; renderGallery(); } });

  /* ---------- Variants ---------- */
  function variantRow(v, axes) {
    const sels = axes.map((ax) => `<div class="seller-field"><label>${esc(ax.name)}</label><select class="v-opt" data-attr="${ax.id}"><option value="">Select</option>${ax.options.map((o) => `<option value="${o.id}" ${String(v.options[ax.id]) === String(o.id) ? 'selected' : ''}>${esc(o.label)}</option>`).join('')}</select></div>`).join('');
    return `<div class="seller-variant-row" data-id="${v.id || ''}">
      <div class="seller-field"><label>Code (SKU)</label><input class="v-sku" type="text" maxlength="100" value="${esc(v.sku)}"></div>${sels}
      <label class="seller-check"><input type="checkbox" class="v-active" ${v.is_active !== false ? 'checked' : ''}> Active</label>
      ${v.is_default ? '<span class="seller-pill is-active">Default</span>' : ''}
      <button type="button" class="seller-row-remove" aria-label="Remove variant"><i class="fa-solid fa-xmark"></i></button></div>`;
  }
  function fillVariants(p) {
    const axes = p.axes;
    $('#variantHint').textContent = axes.length ? 'Choose every option for each variant, e.g. Colour + Size. Each combination can exist once.' : 'This category has no variant options (such as size or colour), so the product is sold as a single item.';
    $('#addVariantRow').hidden = !axes.length;
    $('#variantRows').innerHTML = p.variants.map((v) => variantRow(v, axes)).join('');
  }
  $('#addVariantRow').addEventListener('click', () => {
    const p = st.product, n = $$('#variantRows .seller-variant-row').length + 1;
    $('#variantRows').insertAdjacentHTML('beforeend', variantRow({ id: null, sku: (p.sku || 'SKU') + '-' + n, options: {}, is_active: true }, p.axes));
  });
  $('#variantRows').addEventListener('click', (e) => {
    const b = e.target.closest('.seller-row-remove'); if (!b) return;
    if ($$('#variantRows .seller-variant-row').length <= 1) return toast('A product needs at least one variant.', 'error');
    b.closest('.seller-variant-row').remove();
  });
  $('#saveVariants').addEventListener('click', (e) => run(e.currentTarget, async () => {
    const variants = $$('#variantRows .seller-variant-row').map((r) => {
      const options = {}; $$('.v-opt', r).forEach((s) => { if (s.value) options[s.dataset.attr] = +s.value; });
      return { id: r.dataset.id ? +r.dataset.id : null, sku: $('.v-sku', r).value, is_active: $('.v-active', r).checked, options };
    });
    const res = await api('PUT', pUrl('/variants'), { variants });
    setProduct(res.product); toast(res.message);
  }));

  /* ---------- Pricing ---------- */
  function fillPricing(p) {
    $('#pricingRows').innerHTML = p.variants.map((v) => `<div class="seller-variant-row" data-id="${v.id}">
      <div class="seller-variant-name"><strong>${esc(v.label)}</strong><small>${esc(v.sku)}</small></div>
      <div class="seller-field"><label>Actual price / MRP (₹)</label><input class="pr-mrp" type="number" min="0" step="0.01" value="${v.mrp ?? ''}" required></div>
      <div class="seller-field"><label>Current price (₹)</label><input class="pr-price" type="number" min="0" step="0.01" value="${v.price !== null && v.price !== v.mrp ? v.price : ''}" placeholder="Optional"></div></div>`).join('');
    setUnit(p.unit_id); $('#moqUnit').value = p.unit_name || '';
    $('#hsnCode').value = p.hsn_code || ''; $('#taxRate').value = p.tax_rate; $('#priceIncludesTax').checked = p.price_includes_tax;
  }
  $('#formPricing').addEventListener('submit', (e) => {
    e.preventDefault();
    run(e.submitter, async () => {
      const variants = $$('#pricingRows .seller-variant-row').map((r) => ({ id: +r.dataset.id, mrp: numOrNull($('.pr-mrp', r).value), price: numOrNull($('.pr-price', r).value) }));
      if (variants.some((v) => v.mrp === null)) throw new Error('Enter the MRP for every variant.');
      const res = await api('PUT', pUrl('/pricing'), { unit_id: unitCombos[0].get() || st.product.unit_id, hsn_code: $('#hsnCode').value, tax_rate: numOrNull($('#taxRate').value) ?? 0, price_includes_tax: $('#priceIncludesTax').checked, variants });
      setProduct(res.product); toast(res.message);
    });
  });

  /* ---------- Bulk rule ---------- */
  function fillBulk(p) {
    const wholesale = p.sale_type !== 'retail';
    const first = p.tiers[0];
    $('#moqQty').value = wholesale ? p.min_order_qty : '';
    $('#bulkPricePerUnit').value = first && first.min_qty === p.min_order_qty ? first.price : '';
    $('#bulkNote').value = p.offer_text || '';
    $('#disableBulk').hidden = p.sale_type !== 'both';
  }
  $('#formBulk').addEventListener('submit', (e) => {
    e.preventDefault();
    run(e.submitter, async () => {
      const res = await api('PUT', pUrl('/bulk-rule'), { min_order_qty: numOrNull($('#moqQty').value), bulk_price: numOrNull($('#bulkPricePerUnit').value), offer_text: $('#bulkNote').value });
      setProduct(res.product); toast(res.message);
    });
  });
  $('#disableBulk').addEventListener('click', (e) => run(e.currentTarget, async () => {
    if (!confirm('Turn off bulk selling? Wholesale price tiers will be removed.')) return;
    const res = await api('DELETE', pUrl('/bulk-rule')); setProduct(res.product); toast(res.message);
  }));

  /* ---------- Price tiers ---------- */
  const tierRow = (t) => `<div class="seller-tier-row">
    <label>From <input type="number" name="from" min="0.001" step="any" value="${t.min_qty ?? ''}"></label>
    <label>To <input type="number" name="to" min="0.001" step="any" value="${t.max_qty ?? ''}" placeholder="∞"></label>
    <label>Price / unit (₹) <input type="number" name="price" min="0" step="0.01" value="${t.price ?? ''}"></label>
    <button type="button" class="seller-row-remove" aria-label="Remove tier"><i class="fa-solid fa-xmark"></i></button></div>`;
  function fillTiers(p) { $('#tierList').innerHTML = (p.tiers.length ? p.tiers : [{}]).map(tierRow).join(''); }
  $('#addTierRow').addEventListener('click', () => {
    const last = $$('#tierList .seller-tier-row').pop(), to = last && numOrNull($('[name=to]', last).value);
    $('#tierList').insertAdjacentHTML('beforeend', tierRow({ min_qty: to ? to + 1 : '' }));
  });
  $('#tierList').addEventListener('click', (e) => {
    const b = e.target.closest('.seller-row-remove'); if (!b) return;
    if ($$('#tierList .seller-tier-row').length <= 1) return toast('Keep at least one tier.', 'error');
    b.closest('.seller-tier-row').remove();
  });
  $('#saveTiers').addEventListener('click', (e) => run(e.currentTarget, async () => {
    const tiers = $$('#tierList .seller-tier-row').map((r) => ({ min_qty: numOrNull($('[name=from]', r).value), max_qty: numOrNull($('[name=to]', r).value), price: numOrNull($('[name=price]', r).value) }));
    if (tiers.some((t) => t.min_qty === null || t.price === null)) throw new Error('Fill “From” and “Price” for every tier.');
    const res = await api('PUT', pUrl('/tiers'), { unit_id: unitCombos[1].get() || st.product.unit_id, tiers });
    setProduct(res.product); toast(res.message);
  }));

  /* ---------- Stock ---------- */
  function fillStock(p) {
    $('#trackStock').checked = p.track_stock;
    $('#stockRows').innerHTML = p.variants.map((v) => `<div class="seller-variant-row" data-id="${v.id}" data-reserved="${v.reserved}">
      <div class="seller-variant-name"><strong>${esc(v.label)}</strong><small>${esc(v.sku)}</small></div>
      <div class="seller-field"><label>Available quantity</label><input class="st-qty" type="number" min="0" step="${p.unit_allow_decimal ? 'any' : '1'}" value="${v.stock}"></div>
      <div class="seller-field"><label>Low-stock threshold</label><input class="st-low" type="number" min="0" step="any" value="${v.low_stock_alert ?? ''}"></div>
      <div class="seller-field"><label>Reserved</label><input type="text" value="${v.reserved}" readonly></div></div>`).join('');
    $('#stockMovements').innerHTML = p.movements.length ? p.movements.map((m) => `<tr><td>${esc(m.at)}</td><td>${esc(m.sku)}</td><td>${esc(m.type)}</td><td>${m.change > 0 ? '+' : ''}${m.change}</td><td>${m.after}</td></tr>`).join('') : '<tr><td colspan="5" class="seller-empty">No stock activity yet.</td></tr>';
    meter();
  }
  function meter() {
    let avail = 0, low = 0;
    $$('#stockRows .seller-variant-row').forEach((r) => { avail += Number($('.st-qty', r).value || 0) - Number(r.dataset.reserved || 0); low += Number($('.st-low', r).value || 0); });
    const m = $('#stockMeter'), isLow = avail <= 0 || (low > 0 && avail <= low);
    m.classList.toggle('is-low', isLow);
    $('#stockMeterLabel').textContent = `${avail} units available — ` + (low > 0 ? (avail <= low ? 'at or below threshold' : 'above threshold') : 'no low-stock alert set');
    $('.bar span', m).style.width = Math.max(0, Math.min(100, low > 0 ? (avail / (low * 2)) * 100 : avail > 0 ? 100 : 0)) + '%';
  }
  $('#stockRows').addEventListener('input', meter);
  $('#formStock').addEventListener('submit', (e) => {
    e.preventDefault();
    run(e.submitter, async () => {
      const variants = $$('#stockRows .seller-variant-row').map((r) => ({ id: +r.dataset.id, stock: numOrNull($('.st-qty', r).value) ?? 0, low_stock_alert: numOrNull($('.st-low', r).value) }));
      const res = await api('PUT', pUrl('/stock'), { track_stock: $('#trackStock').checked, variants });
      setProduct(res.product); toast(res.message);
    });
  });

  /* ---------- Orders: details modal ---------- */
  const modal = $('#orderModal');
  const closeModal = () => (modal.hidden = true);
  modal.addEventListener('click', (e) => { if (e.target.closest('[data-close]')) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
  document.addEventListener('click', (e) => {
    const a = e.target.closest('.seller-order-link'); if (!a) return;
    e.preventDefault();
    run(null, async () => renderOrder(await api('GET', R.orders + '/' + a.dataset.item)));
  });
  const addr = (a) => !a ? '—' : typeof a === 'string' ? esc(a) : Object.entries(a).filter(([, v]) => v).map(([k, v]) => `<div><small>${esc(k.replace(/_/g, ' '))}:</small> ${esc(v)}</div>`).join('');
  function renderOrder(o) {
    const opts = [o.status, ...o.next].map((s) => `<option value="${s}" ${s === o.status ? 'selected' : ''}>${s[0].toUpperCase() + s.slice(1)}</option>`).join('');
    $('#orderModalBody').innerHTML = `
      <h3 id="orderModalTitle">${o.price_type === 'wholesale' ? 'Bulk request' : 'Order'} #${esc(o.order_no)} <span class="seller-pill is-${o.status}">${o.status}</span></h3>
      <p class="hint">Placed ${esc(o.date)}${o.contacted_at ? ' · contacted ' + esc(o.contacted_at) : ''}</p>
      <div class="seller-modal-grid">
        <div><h4>Buyer</h4><p><strong>${esc(o.buyer.name)}</strong><br><a href="tel:${esc(o.buyer.phone)}">${esc(o.buyer.phone)}</a>${o.buyer.email ? `<br><a href="mailto:${esc(o.buyer.email)}">${esc(o.buyer.email)}</a>` : ''}</p></div>
        <div><h4>Shipping address</h4><p>${addr(o.shipping_address)}</p></div>
      </div>
      <h4>Item</h4>
      <table class="seller-table"><tbody>
        <tr><td>${esc(o.title)}${o.options ? '<br><small>' + esc(Object.values(o.options).join(' / ')) + '</small>' : ''}<br><small>${esc(o.sku || '')}</small></td>
        <td>${o.qty} ${esc(o.unit_name)} × ${money(o.unit_price)}</td><td><strong>${money(o.line_total)}</strong><br><small>incl. tax ${money(o.tax_amount)} (${o.tax_rate}%)</small></td></tr></tbody></table>
      ${o.buyer_note ? `<h4>Buyer note</h4><p>${esc(o.buyer_note)}</p>` : ''}
      <div class="seller-form-grid" style="margin-top:12px">
        <div class="seller-field"><label>Status</label><select id="ordStatus" class="seller-select">${opts}</select></div>
        <div class="seller-field full"><label>Your note (private)</label><textarea id="ordNote" rows="2" maxlength="1000">${esc(o.seller_note || '')}</textarea></div>
      </div>
      <div class="seller-actions"><button type="button" class="seller-btn seller-btn-primary" id="ordSave">Save</button><button type="button" class="seller-btn seller-btn-ghost" data-close>Close</button></div>`;
    modal.hidden = false;
    $('#ordSave').onclick = (ev) => run(ev.currentTarget, async () => {
      const res = await api('PUT', R.orders + '/' + o.id, { status: $('#ordStatus').value, seller_note: $('#ordNote').value });
      toast(res.message); renderOrder(res.order);
      $$(`[data-order-status="${o.id}"]`).forEach((b) => { b.textContent = res.order.status[0].toUpperCase() + res.order.status.slice(1); b.className = 'seller-pill is-' + res.order.status; });
    });
  }

  /* ---------- init ---------- */
  loadLevel(0);
  newProduct(false);
  setMode(st.mode, false);
  go('products');
})();
