/* public/js/cchub/activity-live.js  -- use ONLY this one script (delete the old stub) */
(() => {
  'use strict';
  const C = window.CCHUB || {};
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const fill = (t, id) => t.replace('__ID__', encodeURIComponent(id));

  /* ---------- toast ---------- */
  const toastEl = $('#activityToast');
  let tt;
  function toast(msg, icon = 'fa-circle-check') {
    if (!toastEl) return;
    $('#activityToastText').textContent = msg;
    const i = $('i', toastEl);
    if (i) i.className = `fa-solid ${icon}`;
    toastEl.classList.add('is-visible', 'is-active', 'show');
    clearTimeout(tt);
    tt = setTimeout(() => toastEl.classList.remove('is-visible', 'is-active', 'show'), 2500);
  }

  /* ---------- server call (save / block are toggles) ---------- */
  async function post(url, data = {}) {
    const r = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': C.csrf,
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    });
    const d = await r.json().catch(() => ({}));
    if (!r.ok) throw new Error(d.message || `Something went wrong (${r.status})`);
    return d;
  }

  /* ---------- member id: data-uuid on card/button, or from the card's /members/{uuid} link ---------- */
  function memberId(el) {
    const card = el.closest('.profile-card');
    const direct = el.dataset.uuid || el.dataset.id || card?.dataset.uuid || card?.dataset.id;
    if (direct) return direct;
    const a = card && $('a[href*="/members/"]', card);
    if (a) {
      const parts = new URL(a.href, location.origin).pathname.split('/').filter(Boolean);
      const i = parts.lastIndexOf('members');
      if (i !== -1 && parts[i + 1]) return parts[i + 1];
    }
    return null;
  }

  function profileLink(el) {
    const a = $('a[href*="/members/"]', el.closest('.profile-card') || document);
    return a ? new URL(a.href, location.origin).href : null;
  }

  /* ---------- counts / header ---------- */
  const tabs = $$('.activity-tab-btn');
  const count = (tab) => $$(`#${tab}ProfileList > .profile-card`).length;

  function header(tab) {
    const btn = $(`#tab-${tab}`);
    if (!btn) return;
    const n = count(tab);
    const text = $('#activityHeaderBadgeText');
    const icon = $('#activityHeaderIcon');
    if (text) text.textContent = `${n} ${btn.dataset.label}`;
    if (icon) icon.className = `fa-solid ${btn.dataset.icon}`;
  }

  function sync(tab) {
    const n = count(tab);
    const badge = $(`#${tab}CountBadge`);
    const empty = $(`#${tab}ProfileList .activity-empty`);
    if (badge) badge.textContent = n;
    if (empty) empty.hidden = n > 0;
    if (tabs.find((t) => t.classList.contains('is-active'))?.dataset.tab === tab) header(tab);
  }

  /* ---------- tabs ---------- */
  function activate(tab) {
    tabs.forEach((b) => {
      const on = b.dataset.tab === tab;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-selected', String(on));
      const panel = $(`#panel-${b.dataset.tab}`);
      if (panel) panel.hidden = !on;
    });
    const b = $(`#tab-${tab}`);
    if (!b) return;
    $('#activitySectionTitle').textContent = b.dataset.title;
    $('#activitySectionSubtitle').textContent = b.dataset.subtitle;
    header(tab);
    history.replaceState(null, '', `#${tab}`);
  }

  tabs.forEach((b) => {
    b.addEventListener('click', () => activate(b.dataset.tab));
    b.addEventListener('keydown', (e) => {
      const i = tabs.indexOf(b);
      let n = -1;
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') n = (i + 1) % tabs.length;
      if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') n = (i - 1 + tabs.length) % tabs.length;
      if (n !== -1) { e.preventDefault(); tabs[n].focus(); activate(tabs[n].dataset.tab); }
    });
  });

  const initial = location.hash.slice(1);
  if (['saved', 'shared', 'blocked'].includes(initial)) activate(initial);

  /* ---------- actions ---------- */
  function removeCard(card, tab) {
    card.style.transition = 'opacity .25s';
    card.style.opacity = '0';
    setTimeout(() => { card.remove(); sync(tab); }, 250);
  }

  // Capture phase on window: runs before ch-profile.js's handlers on document/elements,
  // and stopImmediatePropagation keeps them from also handling the same click.
  window.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    if (!['unsave', 'unblock', 'reshare'].includes(action)) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    const name = btn.dataset.name || 'Member';

    // Re-share: copy the profile link
    if (action === 'reshare') {
      const link = profileLink(btn);
      if (!link) return toast('Could not find the profile link.', 'fa-triangle-exclamation');
      try {
        await navigator.clipboard.writeText(link);
        toast(`Profile link for ${name} copied!`, 'fa-share-nodes');
      } catch {
        window.prompt('Copy this link:', link);
      }
      return;
    }

    const card = btn.closest('.profile-card');
    const uuid = memberId(btn);
    if (!card || !uuid) {
      console.error('[activity] no member id found for', btn);
      return toast('Could not find this member. Please refresh.', 'fa-triangle-exclamation');
    }
    if (btn.disabled) return;

    btn.disabled = true;
    try {
      if (action === 'unsave') {
        const d = await post(fill(C.saveUrl, uuid), { state: 'off' });
        console.log('[activity] unsave response', d);
        if (d.saved === true) { toast('Profile saved'); btn.disabled = false; return; } // toggled back on
        removeCard(card, 'saved');
        toast(`${name} removed from saved`);
      } else {
        const d = await post(fill(C.blockUrl, uuid), { state: 'off' });
        console.log('[activity] unblock response', d);
        if (d.blocked === true) { toast('Member blocked'); btn.disabled = false; return; }
        removeCard(card, 'blocked');
        toast(`${name} unblocked`);
      }
    } catch (err) {
      console.error('[activity]', err);
      btn.disabled = false;
      toast(err.message, 'fa-triangle-exclamation');
    }
  }, true); // true = capture phase

  ['saved', 'shared', 'blocked'].forEach(sync);
  console.log('[activity] ready');
})();