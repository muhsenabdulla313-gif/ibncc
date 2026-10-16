(() => {
  const C = window.CCHUB || {};
  const dlg = document.getElementById('emailOtpDialog');
  if (!dlg) { console.warn('emailOtpDialog missing from page'); return; }

  const form = document.getElementById('emailOtpForm');
  const otpInput = document.getElementById('emailOtpInput');
  const msg = document.getElementById('emailOtpMsg');
  const resend = document.getElementById('emailOtpResend');
  const subtitle = document.getElementById('emailOtpSubtitle');
  let pendingEmail = '';
  let timer = null;

  const emailField = (wrap) => wrap.querySelector('input:not([type="hidden"])');
  const validEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

  const post = async (url, data) => {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': C.csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok) {
      const first = json.errors ? Object.values(json.errors)[0]?.[0] : null;
      throw new Error(first || json.message || `Error ${res.status}`);
    }
    return json;
  };

  const say = (text, isError = false) => {
    msg.hidden = !text;
    msg.textContent = text || '';
    msg.style.color = isError ? '#c0392b' : '';
  };

  const startCooldown = (sec = 30) => {
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

  const setState = (wrap, email, verified) => {
    wrap.dataset.original = email;
    wrap.dataset.verified = verified ? '1' : '0';
    const inp = emailField(wrap);
    if (inp) inp.value = email;
    wrap.querySelector('.verified-badge').hidden = !verified;
    wrap.querySelector('.verify-btn').hidden = verified || email === '';
  };

  // Typing in the email field: hide badge, show Verify when changed
  document.addEventListener('input', (e) => {
    const wrap = e.target.closest('[data-email-verify]');
    if (!wrap || e.target !== emailField(wrap)) return;

    const val = e.target.value.trim().toLowerCase();
    const changed = val !== (wrap.dataset.original || '').trim().toLowerCase();
    const showBadge = wrap.dataset.verified === '1' && !changed;

    wrap.querySelector('.verified-badge').hidden = !showBadge;
    wrap.querySelector('.verify-btn').hidden = showBadge || val === '';
  });

  const sendOtp = async () => {
    await post(C.emailOtpSendUrl, { email: pendingEmail });
    startCooldown();
  };

  // Verify click: send OTP, open popup
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.verify-btn[data-verify="email"]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    const wrap = btn.closest('[data-email-verify]');
    const email = emailField(wrap).value.trim().toLowerCase();
    if (!validEmail(email)) { alert('Enter a valid email address.'); return; }

    pendingEmail = email;
    btn.disabled = true;
    try {
      await sendOtp();
      subtitle.textContent = `Enter the 6-digit code sent to ${email}`;
      otpInput.value = '';
      say('');
      dlg.showModal();
      otpInput.focus();
    } catch (err) {
      alert(err.message);
    } finally {
      btn.disabled = false;
    }
  }, true);

  resend.addEventListener('click', async () => {
    try { await sendOtp(); say('A new code has been sent.'); }
    catch (err) { say(err.message, true); }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const r = await post(C.emailOtpVerifyUrl, { email: pendingEmail, otp: otpInput.value.trim() });
      document.querySelectorAll('[data-email-verify]').forEach((w) => setState(w, r.email, true));
      dlg.close();
    } catch (err) {
      say(err.message, true);
    }
  });

  otpInput.addEventListener('input', () => {
    otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
  });

  document.getElementById('closeEmailOtp').addEventListener('click', () => dlg.close());
})();