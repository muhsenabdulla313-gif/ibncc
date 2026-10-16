(() => {
  'use strict';
  const C = window.CCHUB || {};
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const linkify = (s) => esc(s).replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
  const fill = (tpl, id) => tpl.replace('__ID__', encodeURIComponent(id));
  const TICK = { sent: 'Sent', delivered: 'Delivered', read: 'Seen' };

  async function api(u, { method = 'GET', body } = {}) {
    const o = { method, headers: { 'X-CSRF-TOKEN': C.csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
    if (body instanceof FormData) o.body = body;
    else if (body) { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
    const r = await fetch(u, o);
    const d = await r.json().catch(() => ({}));
    if (!r.ok) throw new Error(d.message || (d.errors && Object.values(d.errors)[0][0]) || 'Something went wrong');
    return d;
  }

  function toast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.setAttribute('role', 'status');
    t.style.cssText = 'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);background:#222;color:#fff;padding:10px 18px;border-radius:8px;z-index:99999;font-size:14px;box-shadow:0 4px 14px rgba(0,0,0,.3)';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2600);
  }

  // Capture-phase handler: runs before (and blocks) older scripts on the same elements.
  const own = (type, sel, fn) => document.addEventListener(type, (e) => {
    const el = e.target.closest && e.target.closest(sel);
    if (!el) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    Promise.resolve(fn(e, el)).catch((err) => toast(err.message));
  }, true);

  function setBadge(btnSel, n, label) {
    const btn = $(btnSel);
    const icon = btn && $('.app-nav-icon', btn);
    if (!icon) return;
    let b = $('.app-nav-badge', icon);
    if (n > 0) {
      if (!b) { b = document.createElement('span'); b.className = 'app-nav-badge'; b.setAttribute('aria-hidden', 'true'); icon.appendChild(b); }
      b.textContent = n;
    } else if (b) b.remove();
    btn.setAttribute('aria-label', `${label}, ${n} unread`);
  }

  /* ================= DROPDOWNS (admin messages / share) ================= */
  const adminDrop = $('#adminMessagesDropdown');
  const shareDrop = $('#shareProfileDropdown');
  const drops = [[adminDrop, '#openAdminMessagesBtn'], [shareDrop, '#openShareProfileBtn']].filter((d) => d[0]);

  function closeDrops(except) {
    drops.forEach(([d, b]) => {
      if (d === except) return;
      d.hidden = true;
      $(b)?.setAttribute('aria-expanded', 'false');
    });
  }
  function toggleDrop(d, btn) {
    const opening = d.hidden;
    closeDrops(d);
    d.hidden = !opening;
    btn.setAttribute('aria-expanded', String(opening));
    return opening;
  }
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.admin-messages-dropdown, .share-profile-dropdown, #openAdminMessagesBtn, #openShareProfileBtn')) closeDrops();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closeDrops();
    closePanel();
  });

  /* ================= ADMIN MESSAGES ================= */
  own('click', '#openAdminMessagesBtn', async (e, btn) => {
    if (!toggleDrop(adminDrop, btn)) return;
    if (!$('#adminMessagesList .is-unread')) return;
    await api(C.adminReadUrl, { method: 'POST' });
    setBadge('#openAdminMessagesBtn', 0, 'Admin Messages');
    $('#adminMessagesCount').textContent = '0 new';
    setTimeout(() => $$('#adminMessagesList .is-unread').forEach((li) => li.classList.remove('is-unread')), 2500);
  });

  /* ================= THREAD RENDERING ================= */
  function bubble(m) {
    const att = (m.attachments || []).map((a) => a.is_image
      ? `<a href="${esc(a.url)}" target="_blank" rel="noopener"><img src="${esc(a.url)}" alt="${esc(a.name)}" style="max-width:200px;border-radius:8px;display:block;margin-bottom:4px"></a>`
      : `<a href="${esc(a.url)}" target="_blank" rel="noopener" style="display:block;margin-bottom:4px"><i class="fa-solid fa-file" aria-hidden="true"></i> ${esc(a.name)}</a>`).join('');
    const tick = m.is_out
      ? `<span class="msg-ticks is-${m.status}" data-status="${m.status}" title="${TICK[m.status]}" aria-label="${TICK[m.status]}"><i class="fa-solid ${m.status === 'sent' ? 'fa-check' : 'fa-check-double'}" aria-hidden="true"></i></span>`
      : '';
    return `<div class="msg-bubble ${m.is_out ? 'is-out' : 'is-in'}" data-id="${m.id}">${att}${m.body ? `<p class="msg-bubble-text">${linkify(m.body)}</p>` : ''}<div class="msg-bubble-meta"><time>${esc(m.time)}</time>${tick}</div></div>`;
  }

  function createThread(box, uuid) {
    let last = 0;
    let day = '';
    return {
      uuid,
      reset() { last = 0; day = ''; box.innerHTML = ''; },
      add(msgs) {
        const nearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 80 || last === 0;
        let added = false;
        msgs.forEach((m) => {
          if (box.querySelector(`[data-id="${m.id}"]`)) return;
          if (m.day !== day) {
            day = m.day;
            box.insertAdjacentHTML('beforeend', `<div class="msg-day-sep" role="separator"><time>${esc(m.day)}</time></div>`);
          }
          box.insertAdjacentHTML('beforeend', bubble(m));
          last = Math.max(last, m.id);
          added = true;
        });
        if (added) $('#otherChatEmpty')?.remove();
        if (added && nearBottom) box.scrollTop = box.scrollHeight;
      },
      ticks(map) {
        Object.entries(map || {}).forEach(([id, st]) => {
          const t = $(`[data-id="${id}"] .msg-ticks`, box);
          if (!t || t.dataset.status === st) return;
          t.dataset.status = st;
          t.className = `msg-ticks is-${st}`;
          t.title = TICK[st];
          t.innerHTML = `<i class="fa-solid ${st === 'sent' ? 'fa-check' : 'fa-check-double'}" aria-hidden="true"></i>`;
        });
      },
      async poll() {
        const d = await api(`${fill(C.threadUrl, uuid)}?after=${last}`);
        this.add(d.messages);
        this.ticks(d.statuses);
      },
      async send(fd) {
        const d = await api(fill(C.sendUrl, uuid), { method: 'POST', body: fd });
        this.add([d.message]);
      },
    };
  }

  /* ================= OWNER MESSAGES PANEL ================= */
  const panel = $('#messagesPanel');
  const listView = $('#msgListView');
  const chatView = $('#msgChatView');
  const log = $('#msgChatLog');
  const listEl = $('#msgConversationList');
  let panelThread = null;
  let attached = [];

  function rowHtml(c) {
    return `<li><button type="button" class="msg-row" data-chat-id="${esc(c.uuid)}" data-name="${esc(c.name)}" data-avatar="${esc(c.photo_url)}" data-status="${esc(c.preview)}"${c.unread ? ' data-unread="true"' : ''}>
      <span class="msg-avatar-wrap ${c.unread ? 'is-story' : ''}"><img src="${esc(c.photo_url)}" onerror="this.onerror=null;this.src='${esc(C.noImg)}'" alt="" width="56" height="56"></span>
      <span class="msg-row-copy"><span class="msg-row-name">${esc(c.name)}</span><span class="msg-row-preview ${c.unread ? 'is-unread' : ''}">${esc(c.preview)}</span></span></button></li>`;
  }

  async function refreshList() {
    if (!C.isOwner || !listEl) return;
    const d = await api(C.listUrl);
    listEl.innerHTML = d.conversations.length ? d.conversations.map(rowHtml).join('') : '<li class="msg-empty"><p>No conversations yet.</p></li>';
    setBadge('#openMessagesBtn', d.unread, 'Messages');
  }

  function openPanel() {
    panel.hidden = false;
    $('#openMessagesBtn')?.setAttribute('aria-expanded', 'true');
    closeDrops();
    showList();
  }
  function closePanel() {
    if (!panel || panel.hidden) return;
    panel.hidden = true;
    panelThread = null;
    $('#openMessagesBtn')?.setAttribute('aria-expanded', 'false');
  }
  function showList() {
    panelThread = null;
    chatView.hidden = true;
    listView.hidden = false;
    refreshList().catch(() => {});
  }
  function clearAttach() { attached = []; renderAttach(); $('#msgFileInput').value = ''; }
  function renderAttach() {
    $('#msgAttachPreview').innerHTML = attached.map((f, i) =>
      `<span class="msg-attach-chip" style="display:inline-flex;gap:6px;align-items:center;padding:4px 8px;margin:4px;background:#eee;border-radius:12px;font-size:12px">${esc(f.name)} <button type="button" data-attach-remove="${i}" aria-label="Remove ${esc(f.name)}" style="border:0;background:none;cursor:pointer">&times;</button></span>`).join('');
  }

  own('click', '#openMessagesBtn', () => (panel.hidden ? openPanel() : closePanel()));
  own('click', '[data-msg-close]', closePanel);
  own('click', '#msgChatBack', showList);

  own('click', '.msg-row', async (e, row) => {
    $('#msgChatAvatar').src = row.dataset.avatar || C.noImg;
    $('#msgChatName').textContent = row.dataset.name;
    $('#msgChatStatus').textContent = '';
    listView.hidden = true;
    chatView.hidden = false;
    clearAttach();
    panelThread = createThread(log, row.dataset.chatId);
    panelThread.reset();
    await panelThread.poll();
    $('#msgChatInput').focus();
  });

  own('click', '#msgAttachBtn', () => $('#msgFileInput').click());
  own('click', '[data-attach-remove]', (e, b) => { attached.splice(+b.dataset.attachRemove, 1); renderAttach(); });
  document.addEventListener('change', (e) => {
    if (e.target.id !== 'msgFileInput') return;
    e.stopImmediatePropagation();
    attached = attached.concat([...e.target.files]).slice(0, 5);
    e.target.value = '';
    renderAttach();
  }, true);

  own('submit', '#msgChatForm', async (e, form) => {
    if (!panelThread) return;
    const input = $('#msgChatInput');
    const body = input.value.trim();
    if (!body && !attached.length) return;
    const fd = new FormData();
    fd.append('body', body);
    attached.forEach((f) => fd.append('files[]', f));
    $('#msgSendBtn').disabled = true;
    try {
      await panelThread.send(fd);
      input.value = '';
      clearAttach();
    } finally {
      $('#msgSendBtn').disabled = false;
    }
  });

  /* ================= VISITOR CHAT BOX ================= */
  const chatBox = $('#otherChatBox');
  let visThread = null;

  own('click', '#sendMessageBtn', async (e, btn) => {
    const opening = chatBox.hidden;
    chatBox.hidden = !opening;
    btn.setAttribute('aria-expanded', String(opening));
    if (opening) {
      visThread = visThread || createThread($('#otherChatMessages'), chatBox.dataset.uuid);
      await visThread.poll();
      $('#otherChatInput').focus();
    }
  });
  own('click', '#closeChatBtn', () => { chatBox.hidden = true; $('#sendMessageBtn')?.setAttribute('aria-expanded', 'false'); });
  own('submit', '#otherChatForm', async () => {
    const input = $('#otherChatInput');
    const body = input.value.trim();
    if (!body) return;
    visThread = visThread || createThread($('#otherChatMessages'), chatBox.dataset.uuid);
    const fd = new FormData();
    fd.append('body', body);
    await visThread.send(fd);
    input.value = '';
  });

  // Enter = send, Shift+Enter = new line
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter' || e.shiftKey || e.isComposing) return;
    if (e.target.id === 'msgChatInput' || e.target.id === 'otherChatInput') {
      e.preventDefault();
      e.stopImmediatePropagation();
      e.target.form.requestSubmit();
    }
  }, true);

  /* ================= SAVE / BLOCK ================= */
  function setSaved(btn, saved) {
    btn.dataset.saved = saved ? '1' : '0';
    btn.setAttribute('aria-pressed', String(saved));
    $('i', btn).className = `${saved ? 'fa-solid' : 'fa-regular'} fa-bookmark`;
    $('.app-nav-label', btn).textContent = saved ? 'Saved' : 'Save Profile';
  }

  own('click', '[data-save-profile]', async (e, btn) => {
    const d = await api(btn.dataset.url, { method: 'POST' });
    setSaved(btn, d.saved);
    toast(d.saved ? 'Profile saved' : 'Removed from saved profiles');
  });

  own('click', '[data-block-profile]', async (e, btn) => {
    const blocked = btn.dataset.blocked === '1';
    if (!blocked && !confirm("Block this member? They won't be able to message you or view your profile.")) return;
    const d = await api(btn.dataset.url, { method: 'POST' });
    btn.dataset.blocked = d.blocked ? '1' : '0';
    $('.app-nav-label', btn).textContent = d.blocked ? 'Unblock' : 'Block Profile';
    if (d.blocked) {
      const s = $('[data-save-profile]');
      if (s) setSaved(s, false);
      if (chatBox) chatBox.hidden = true;
    }
    toast(d.blocked ? 'Member blocked' : 'Member unblocked');
  });

  /* ================= SHARE PROFILE ================= */
  const resultsEl = $('#shareProfileResults');
  const emptyEl = $('#shareProfileEmpty');
  const searchEl = $('#shareProfileSearch');

  function shareRow(m) {
    return `<li class="share-profile-result" role="option" aria-selected="false" data-uuid="${esc(m.uuid)}">
      <img src="${esc(m.photo)}" onerror="this.onerror=null;this.src='${esc(C.noImg)}'" alt="" width="36" height="36">
      <span class="share-profile-result-copy"><strong>${esc(m.name)}</strong><em>${esc(m.role)}${m.place ? ' · ' + esc(m.place) : ''}${m.saved ? ' · <i class="fa-solid fa-bookmark" aria-hidden="true"></i> Saved' : ''}</em></span>
      <button type="button" class="share-profile-send" hidden>Send</button></li>`;
  }

  async function loadShare(q = '') {
    if (!resultsEl) return;
    const d = await api(`${C.shareUrl}?q=${encodeURIComponent(q)}`);
    resultsEl.innerHTML = d.members.map(shareRow).join('');
    emptyEl.hidden = d.members.length > 0;
  }

  own('click', '#openShareProfileBtn', async (e, btn) => {
    if (toggleDrop(shareDrop, btn)) await loadShare(searchEl.value.trim());
  });

  let st;
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'shareProfileSearch') return;
    e.stopImmediatePropagation();
    clearTimeout(st);
    st = setTimeout(() => loadShare(e.target.value.trim()).catch(() => {}), 250);
  }, true);

  const profileHref = () => new URL(shareDrop.dataset.profilePath, location.origin).href;

  own('click', '#shareProfileCopyBtn', async () => {
    const href = profileHref();
    try { await navigator.clipboard.writeText(href); }
    catch {
      const t = document.createElement('textarea');
      t.value = href; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove();
    }
    const s = $('#shareProfileCopyStatus');
    s.hidden = false;
    setTimeout(() => (s.hidden = true), 1800);
  });

  // Send button must be registered BEFORE the row handler (it sits inside the row)
  own('click', '.share-profile-send', async (e, btn) => {
    const li = btn.closest('.share-profile-result');
    btn.disabled = true;
    try {
      const fd = new FormData();
      fd.append('body', `Check out ${shareDrop.dataset.profileName}'s profile on CC Hub: ${profileHref()}`);
      await api(fill(C.sendUrl, li.dataset.uuid), { method: 'POST', body: fd });
      btn.textContent = 'Sent ✓';
    } catch (err) {
      btn.disabled = false;
      throw err;
    }
  });

  own('click', '.share-profile-result', (e, li) => {
    $$('.share-profile-result', resultsEl).forEach((r) => {
      r.setAttribute('aria-selected', 'false');
      $('.share-profile-send', r).hidden = true;
    });
    li.setAttribute('aria-selected', 'true');
    const b = $('.share-profile-send', li);
    b.hidden = false;
    b.disabled = false;
    b.textContent = 'Send';
  });

  /* ================= POLLING ================= */
  let tick = 0;
  setInterval(() => {
    if (document.hidden) return;
    tick++;
    if (panel && !panel.hidden) {
      (panelThread ? panelThread.poll() : refreshList()).catch(() => {});
    } else if (C.isOwner && tick % 4 === 0) {
      refreshList().catch(() => {});
    }
    if (visThread && chatBox && !chatBox.hidden) visThread.poll().catch(() => {});
  }, 5000);
  if (new URLSearchParams(location.search).get('share') === '1') $('#openShareProfileBtn')?.click();
})();
own('click', '.share-profile-send', async (e, btn) => {
  const li = btn.closest('.share-profile-result');
  if (!li?.dataset.uuid) throw new Error('This member cannot be messaged.');
  btn.disabled = true;
  try {
    await api(fill(C.shareSendUrl, shareDrop.dataset.profileUuid), { method: 'POST', body: { to: li.dataset.uuid } });
    btn.textContent = 'Sent ✓';
  } catch (err) {
    btn.disabled = false;
    throw err;
  }
});