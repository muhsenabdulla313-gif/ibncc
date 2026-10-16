/* public/js/cchub/profile-verify.js  (replace the WHOLE old file with this)
   Email OTP: Verify button -> send code -> popup -> verify.
   No badge. Verify button shows only when the email is new/changed/unverified. */
(() => {
  const csrf = () =>
    (window.CCHUB && window.CCHUB.csrf) ||
    (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  const emailField = (wrap) => wrap.querySelector('input:not([type="hidden"])');
  const validEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

  const post = async (url, data) => {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
      const first = json.errors ? Object.values(json.errors)[0]?.[0] : null;
      throw new Error(first || json.message || `Request failed (${res.status})`);
    }
    return json;
  };

  /* ---------- Verify button visibility ---------- */
  const refreshButton = (wrap) => {
    const btn = wrap.querySelector('.verify-btn');
    const inp = emailField(wrap);
    if (!btn || !inp) return;
    const val = inp.value.trim().toLowerCase();
    const same = val === (wrap.dataset.original || '').trim().toLowerCase();
    const verified = wrap.dataset.verified === '1' && same;
    btn.hidden = verified || val === '';
  };

  document.addEventListener('input', (e) => {
    const wrap = e.target.closest && e.target.closest('[data-email-verify]');
    if (wrap && e.target === emailField(wrap)) refreshButton(wrap);
  });

  const setVerified = (email) => {
    document.querySelectorAll('[data-email-verify]').forEach((w) => {
      w.dataset.original = email;
      w.dataset.verified = '1';
      const i = emailField(w);
      if (i) i.value = email;
      refreshButton(w);
    });
  };

  /* ---------- OTP popup ---------- */
  let pending = { email: '', sendUrl: '', verifyUrl: '' };
  let timer = null;

  const $ = (id) => document.getElementById(id);

  const say = (text, isError) => {
    const msg = $('emailOtpMsg');
    if (!msg) return;
    msg.hidden = !text;
    msg.textContent = text || '';
    msg.style.color = isError ? '#c0392b' : '';
  };

  const cooldown = (sec = 30) => {
    const resend = $('emailOtpResend');
    clearInterval(timer);
    let left = sec;
    resend.disabled = true;
    resend.textContent = `Resend in ${left}s`;
    timer = setInterval(() => {
      left--;
      if (left <= 0) {
        clearInterval(timer);
        resend.disabled = false;
        resend.textContent = 'Resend code';
      } else {
        resend.textContent = `Resend in ${left}s`;
      }
    }, 1000);
  };

  const sendCode = async () => {
    await post(pending.sendUrl, { email: pending.email });
    cooldown();
  };

  // Click on Verify (capture so no other script can swallow it)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest && e.target.closest('.verify-btn[data-verify="email"]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    const dlg = $('emailOtpDialog');
    if (!dlg) {
      alert('Verification popup is missing on this page. Please refresh and try again.');
      return;
    }

    const wrap = btn.closest('[data-email-verify]');
    const email = emailField(wrap).value.trim().toLowerCase();
    if (!validEmail(email)) {
      alert('Enter a valid email address first.');
      return;
    }

    pending = { email, sendUrl: btn.dataset.sendUrl, verifyUrl: btn.dataset.verifyUrl };

    const label = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Sending...';
    try {
      await sendCode();
      $('emailOtpSubtitle').textContent = `Enter the 6-digit code sent to ${email}`;
      $('emailOtpInput').value = '';
      say('');
      if (!dlg.open) dlg.showModal();
      $('emailOtpInput').focus();
    } catch (err) {
      console.error('Send OTP failed:', err);
      alert(err.message);
    } finally {
      btn.disabled = false;
      btn.textContent = label;
    }
  }, true);

  document.addEventListener('click', async (e) => {
    if (e.target.closest && e.target.closest('#emailOtpResend')) {
      try { await sendCode(); say('A new code has been sent.'); }
      catch (err) { say(err.message, true); }
    }
    if (e.target.closest && e.target.closest('#closeEmailOtp')) {
      $('emailOtpDialog').close();
    }
  });

  document.addEventListener('submit', async (e) => {
    if (e.target.id !== 'emailOtpForm') return;
    e.preventDefault();
    try {
      const r = await post(pending.verifyUrl, {
        email: pending.email,
        otp: $('emailOtpInput').value.trim(),
      });
      setVerified(r.email);
      $('emailOtpDialog').close();
    } catch (err) {
      say(err.message, true);
    }
  });

  document.addEventListener('input', (e) => {
    if (e.target.id === 'emailOtpInput') {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
    }
  });
})();