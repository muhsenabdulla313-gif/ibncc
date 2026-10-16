/*
 * profile-save.js
 * Saves the owner's profile edits to Laravel (POST /profile/update).
 * Load it AFTER ch-profile.js. ch-profile.js only changes the screen; this file writes the database.
 */
(function () {
  'use strict';

  var body = document.body;
  if (body.dataset.owner !== '1') { return; }

  var URL_UPDATE = body.dataset.updateUrl || '/profile/update';
  var TOKEN = (document.querySelector('meta[name="csrf-token"]') || {}).content;
  var PLATFORMS = [['instagram', 'Instagram'], ['facebook', 'Facebook'], ['youtube', 'YouTube'], ['x', 'X'], ['linkedin', 'LinkedIn']];

  /* ------------------------------------------------ helpers */
  async function send(payload) {
    var isForm = payload instanceof FormData;
    var res = await fetch(URL_UPDATE, {
      method: 'POST',
      headers: Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                             isForm ? {} : { 'Content-Type': 'application/json' }),
      body: isForm ? payload : JSON.stringify(payload)
    });
    var json = await res.json().catch(function () { return {}; });
    if (!res.ok) {
      var msg = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Something went wrong. Please try again.');
      if (res.status === 419) { msg = 'Session expired. Refresh the page and try again.'; }
      throw new Error(msg);
    }
    return json;
  }

  function errorBox(host, before) {
    var box = host.querySelector('.js-save-error');
    if (!box) {
      box = document.createElement('p');
      box.className = 'js-save-error';
      box.style.cssText = 'color:#b91c1c;font-size:.9rem;margin:8px 0;';
      host.insertBefore(box, before || null);
    }
    return box;
  }

  function showError(form, msg) {
    var box = errorBox(form, form.querySelector('.detail-edit-actions, .profile-brief-form-actions, .password-dialog-actions'));
    box.textContent = msg;
    box.hidden = false;
  }

  function stop(e) { e.preventDefault(); e.stopImmediatePropagation(); }

  /* ------------------------------------------------ 1. detail sections (church, personal, contact, ...) */
  document.addEventListener('submit', async function (e) {
    var form = e.target;
    if (!form.matches || !form.matches('.detail-edit-form')) { return; }
    stop(e);

    var details = form.closest('details');
    var section = form.dataset.section || (details && details.dataset.section);
    var values = Array.prototype.map.call(form.querySelectorAll('.detail-input-wrap input'), function (i) { return i.value.trim(); });
    var btn = form.querySelector('[type=submit]');

    if (btn) { btn.disabled = true; }
    try {
      await send({ action: 'section', section: section, values: values });
      window.location.reload();
    } catch (err) {
      showError(form, err.message);
      if (btn) { btn.disabled = false; }
    }
  }, true);

  /* ------------------------------------------------ 2. profile brief */
  document.addEventListener('submit', async function (e) {
    var form = e.target;
    if (form.id !== 'profileBriefForm') { return; }
    stop(e);

    var btn = form.querySelector('[type=submit]');
    if (btn) { btn.disabled = true; }
    try {
      await send({
        action: 'brief',
        name: form.elements.name.value.trim(),
        bio: form.elements.bio.value.trim(),
        status: form.elements.status.value
      });
      window.location.reload();
    } catch (err) {
      showError(form, err.message);
      if (btn) { btn.disabled = false; }
    }
  }, true);

  /* ------------------------------------------------ 3. eye icon (privacy) */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.detail-action--privacy');
    if (!btn) { return; }

    var details = btn.closest('details');
    var section = btn.dataset.section || (details && details.dataset.section);
    if (!section) { return; }

    var hidden = btn.getAttribute('aria-pressed') !== 'true';       // state we are switching TO

    send({ action: 'privacy', section: section, hidden: hidden }).catch(function (err) { alert(err.message); });

    // make the icon match the saved state after ch-profile.js has finished its own toggle
    setTimeout(function () {
      btn.setAttribute('aria-pressed', String(hidden));
      var icon = btn.querySelector('i');
      if (icon) { icon.className = 'fa-solid ' + (hidden ? 'fa-eye-slash' : 'fa-eye'); }
      btn.title = hidden ? 'Hidden from other viewers - click to show' : 'Visible to other viewers - click to hide';
    }, 0);
  }, true);

  /* ------------------------------------------------ 4. work details */
  document.addEventListener('click', async function (e) {
    var btn = e.target.closest('#workSaveBtn');
    if (!btn) { return; }
    stop(e);

    var area = document.querySelector('#workDetailsView textarea');
    var textEl = document.getElementById('workSummaryText');
    var text = area ? area.value : (textEl ? textEl.innerText : '');

    btn.disabled = true;
    try {
      await send({ action: 'work', work_description: text.trim() });
      window.location.reload();
    } catch (err) {
      alert(err.message);
      btn.disabled = false;
    }
  }, true);

  /* ------------------------------------------------ 5. cover + profile photo (no fixed IDs needed) */
  function kindOf(el) {
    if (el.closest('.profile-photo-wrap')) { return 'photo'; }
    if (el.closest('.profile-cover')) { return 'cover'; }
    var s = ((el.id || '') + ' ' + (el.name || '') + ' ' + (el.className || '')).toLowerCase();
    if (/photo|avatar|portrait/.test(s)) { return 'photo'; }
    if (/cover|banner/.test(s)) { return 'cover'; }
    return null;
  }

  function targetImg(kind) {
    return kind === 'photo'
      ? document.querySelector('.profile-photo-wrap img, img.profile-photo')
      : document.querySelector('.profile-cover img');
  }

  async function uploadImage(kind, file) {
    var fd = new FormData();
    fd.append('action', kind);
    fd.append('file', file);
    try {
      var json = await send(fd);
      var img = targetImg(kind);
      if (img && json.url) { img.src = json.url + '?t=' + Date.now(); }
    } catch (err) {
      alert(err.message);
    }
  }

  // any file input inside the banner (cover or photo) -> upload on choose
  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input.matches || !input.matches('input[type=file]') || input.dataset.gallery) { return; }
    var kind = kindOf(input);
    if (!kind || !input.files[0]) { return; }
    e.stopImmediatePropagation();
    uploadImage(kind, input.files[0]);
    input.value = '';
  }, true);

  // if the edit buttons have no file input behind them, open a picker ourselves
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.profile-cover button, .profile-cover a, .profile-photo-wrap button, .profile-photo-wrap a');
    if (!btn) { return; }
    var box = btn.closest('.profile-photo-wrap') || btn.closest('.profile-cover');
    if (box.querySelector('input[type=file]')) { return; }
    var kind = box.classList.contains('profile-photo-wrap') ? 'photo' : 'cover';
    stop(e);
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp';
    input.onchange = function () { if (input.files[0]) { uploadImage(kind, input.files[0]); } };
    input.click();
  }, true);

  /* ------------------------------------------------ 6. gallery: Add Image (with title) */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.gallery-add-btn');
    if (!btn) { return; }
    stop(e);

    var panel = btn.closest('.gallery-panel') || document.body;
    var old = document.getElementById('galleryAddForm');
    if (old) { old.remove(); return; }

    var form = document.createElement('form');
    form.id = 'galleryAddForm';
    form.className = 'profile-brief-form';
    form.innerHTML =
      '<label><span>Title (optional)</span><input type="text" name="title" maxlength="100" placeholder="e.g. Annual meet 2026"></label>' +
      '<label><span>Images</span><input type="file" name="files" accept="image/jpeg,image/png,image/webp" multiple required data-gallery="1"></label>' +
      '<div class="profile-brief-form-actions">' +
      '<button type="button" class="sidebar-btn-secondary" data-gallery-cancel>Cancel</button>' +
      '<button type="submit" class="sidebar-btn-primary">Upload</button></div>';
    var header = panel.querySelector('.gallery-header');
    if (header && header.nextSibling) { header.parentNode.insertBefore(form, header.nextSibling); } else { panel.appendChild(form); }

    form.querySelector('[data-gallery-cancel]').addEventListener('click', function () { form.remove(); });
    form.addEventListener('submit', async function (ev) {
      ev.preventDefault();
      var files = form.elements.files.files;
      if (!files.length) { return; }
      var fd = new FormData();
      fd.append('action', 'gallery');
      fd.append('title', form.elements.title.value.trim());
      Array.prototype.forEach.call(files, function (f) { fd.append('files[]', f); });
      var sub = form.querySelector('[type=submit]');
      sub.disabled = true;
      try {
        await send(fd);
        window.location.reload();
      } catch (err) {
        showError(form, err.message);
        sub.disabled = false;
      }
    });
  }, true);

  /* ------------------------------------------------ 7. social links: add / edit / delete */
  function openSocialForm(row) {
    var card = document.getElementById('social-links-card');
    if (!card) { return; }
    var old = document.getElementById('socialInlineForm');
    if (old) { old.remove(); }

    var current = row ? row.querySelector('[data-field=platform]').textContent.trim().toLowerCase() : '';
    var url = row ? row.querySelector('[data-field=url]').getAttribute('href') : '';

    var form = document.createElement('form');
    form.id = 'socialInlineForm';
    form.className = 'profile-brief-form';
    form.innerHTML =
      '<label><span>Platform</span><select name="name">' +
      PLATFORMS.map(function (p) { return '<option value="' + p[0] + '"' + (p[0] === current ? ' selected' : '') + '>' + p[1] + '</option>'; }).join('') +
      '</select></label>' +
      '<label><span>Profile URL</span><input type="url" name="url" placeholder="https://" required></label>' +
      '<div class="profile-brief-form-actions">' +
      '<button type="button" class="sidebar-btn-secondary" data-social-cancel>Cancel</button>' +
      '<button type="submit" class="sidebar-btn-primary">Save</button></div>';
    form.elements.url.value = url;
    card.appendChild(form);

    form.querySelector('[data-social-cancel]').addEventListener('click', function () { form.remove(); });
    form.addEventListener('submit', async function (ev) {
      ev.preventDefault();
      var btn = form.querySelector('[type=submit]');
      btn.disabled = true;
      try {
        await send({ action: 'social.save', id: row ? row.dataset.id : null, name: form.elements.name.value, url: form.elements.url.value.trim() });
        window.location.reload();
      } catch (err) {
        showError(form, err.message);
        btn.disabled = false;
      }
    });
  }

  document.addEventListener('click', async function (e) {
    var add = e.target.closest('#addSocialBtn');
    var edit = e.target.closest('.social-edit-btn');
    var del = e.target.closest('.social-delete-btn');
    if (!add && !edit && !del) { return; }
    stop(e);

    if (add) { openSocialForm(null); return; }

    var row = (edit || del).closest('[data-social-row]');
    if (edit) { openSocialForm(row); return; }

    if (del && row && row.dataset.id && confirm('Delete this link?')) {
      try {
        await send({ action: 'social.delete', id: row.dataset.id });
        window.location.reload();
      } catch (err) { alert(err.message); }
    }
  }, true);

  /* ------------------------------------------------ 8. change password */
  document.addEventListener('submit', async function (e) {
    var form = e.target;
    if (form.id !== 'changePasswordForm') { return; }
    stop(e);

    var btn = form.querySelector('.password-btn-submit');
    var data = new FormData(form);
    if (btn) { btn.disabled = true; }
    try {
      await send({ action: 'password', current: data.get('current'), new: data.get('new'), confirm: data.get('confirm') });
      form.reset();
      var dlg = document.getElementById('changePasswordDialog');
      if (dlg && dlg.close) { dlg.close(); }
      alert('Password updated successfully.');
    } catch (err) {
      showError(form, err.message);
    } finally {
      if (btn) { btn.disabled = false; }
    }
  }, true);
})();