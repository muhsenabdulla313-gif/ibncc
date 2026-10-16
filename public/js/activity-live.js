(() => {
  'use strict';
  const C = window.CCHUB;
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const fill = (t, id) => t.replace('__ID__', encodeURIComponent(id));

  const toastEl = $('#activityToast');
  let tt;
  function toast(msg) {
    $('#activityToastText').textContent = msg;
    toastEl.classList.add('is-visible', 'is-active', 'show');
    clearTimeout(tt);
    tt = setTimeout(() => toastEl.classList.remove('is-visible', 'is-active', 'show'), 2500);
  }

  async function post(url) {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': C.csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const d = await r.json().catch(() => ({}));
    if (!r.ok) throw new Error(d.message || 'Something went wrong');
    return d;
  }

  /* ---------- counts / header ---------- */
  const tabs = $$('.activity-tab-btn');
  const labelOf = (t) => ({ label: t.dataset.label, icon: t.dataset.icon });

  function count(tab) {
    return $$(`#${tab}ProfileList > .profile-card`).length;
  }

  function sync(tab) {
    const n = count(tab);
    $(`#${tab}CountBadge`).textContent = n;
    $(`#${tab}ProfileList .activity-empty`).hidden = n > 0;
    if (tabs.find((t) => t.classList.contains('is-active'))?.dataset.tab === tab) header(tab);
  }

  function header(tab) {
    const btn = $(`#tab-${tab}`);
    const { label, icon } = labelOf(btn);
    $('#activityHeaderBadgeText').textContent = `${count(tab)} ${label}`;
    $('#activityHeaderIcon').className = `fa-solid ${icon}`;
  }

  /* ---------- tabs ---------- */
  function activate(tab) {
    tabs.forEach((b) => {
      const on = b.dataset.tab === tab;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-selected', String(on));
      $(`#panel-${b.dataset.tab}`).hidden = !on;
    });
    const b = $(`#tab-${tab}`);
    $('#activitySectionTitle').textContent = b.dataset.title;
    $('#activitySectionSubtitle').textContent = b.dataset.subtitle;
    header(tab);
    history.replaceState(null, '', `#${tab}`);
  }

  tabs.forEach((b) => b.addEventListener('click', () => activate(b.dataset.tab)));
  const initial = location.hash.slice(1);
  if (['saved', 'shared', 'blocked'].includes(initial)) activate(initial);

  /* ---------- unsave / unblock ---------- */
  function removeCard(card, tab) {
    card.style.transition = 'opacity .25s';
    card.style.opacity = '0';
    setTimeout(() => { card.remove(); sync(tab); }, 250);
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const card = btn.closest('.profile-card');
    const uuid = card?.dataset.uuid;
    if (!uuid) return;

    btn.disabled = true;
    try {
      if (btn.dataset.action === 'unsave') {
        const d = await post(fill(C.saveUrl, uuid));
        if (d.saved) { toast('Profile saved'); btn.disabled = false; return; } // toggled back on, shouldn't happen
        removeCard(card, 'saved');
        toast(`${btn.dataset.name} removed from saved`);
      } else if (btn.dataset.action === 'unblock') {
        const d = await post(fill(C.blockUrl, uuid));
        if (d.blocked) { toast('Member blocked'); btn.disabled = false; return; }
        removeCard(card, 'blocked');
        toast(`${btn.dataset.name} unblocked`);
      }
    } catch (err) {
      btn.disabled = false;
      toast(err.message);
    }
  });
})();