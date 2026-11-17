
/* IBNCC modal validation – login, register, reset password, OTP.
   Include once, after the modals' HTML (before </body>). No HTML changes needed. */
(() => {
  const PHONE_RE = /^\+?[1-9]\d{7,14}$/;               // E.164: optional +, 8–15 digits
  const NAME_RE  = /^[\p{L}\p{M}][\p{L}\p{M} .'’-]*$/u; // any language letters
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const PASS_RE  = /^(?=.*[A-Za-z])(?=.*\d).+$/;        // at least 1 letter + 1 number

  const $ = (id) => document.getElementById(id);

  /* ---------- per-field config ---------- */
  const cfg = {
    loginPhone:            { type: 'phone' },
    loginPassword:         { type: 'loginpass', max: 64 },
    registerName:          { type: 'name',  max: 60 },
    registerRite:          { type: 'select', label: 'rite' },
    registerDiocese:       { type: 'select', label: 'diocese' },
    registerForane:        { type: 'select', label: 'forane' },
    registerParish:        { type: 'select', label: 'parish' },
    registerPassword:      { type: 'newpass', max: 64 },
    registerConfirm:       { type: 'confirm', of: 'registerPassword', max: 64 },
    registerPhone:         { type: 'phone' },
    registerEmail:         { type: 'email', max: 254 },
    registerPhoneOtpInput: { type: 'otp' },
    registerEmailOtpInput: { type: 'otp' },
    resetPassword:         { type: 'newpass', max: 64 },
    resetPasswordConfirm:  { type: 'confirm', of: 'resetPassword', max: 64 },
  };

  /* ---------- validators (return error text or '') ---------- */
  const check = {
    phone: (v) => !v ? 'Phone number is required'
      : !PHONE_RE.test(v) ? 'Enter 8–15 digits (with country code, e.g. +91…)' : '',
    loginpass: (v) => !v ? 'Password is required' : '',
    name: (v) => {
      v = v.trim();
      return !v ? 'Name is required'
        : v.length < 2 ? 'Name must be at least 2 characters'
        : !NAME_RE.test(v) ? 'Use letters, spaces, . - \' only' : '';
    },
    email: (v) => !v ? 'Email is required'
      : !EMAIL_RE.test(v) ? 'Enter a valid email address' : '',
    select: (v, c) => !v ? `Please select your ${c.label}` : '',
    newpass: (v) => !v ? 'Password is required'
      : v.length < 8 ? 'Minimum 8 characters'
      : !PASS_RE.test(v) ? 'Include at least one letter and one number' : '',
    confirm: (v, c) => !v ? 'Please confirm your password'
      : v !== $(c.of).value ? 'Passwords do not match' : '',
    otp: (v) => v && v.length !== 6 ? 'OTP must be 6 digits' : '',
  };

  /* ---------- error UI ---------- */
  const holder = (el) => el.closest('.login-field, .register-field') || el.parentElement;

  function showError(el, msg) {
    const box = holder(el);
    let note = box.nextElementSibling;
    if (!note || !note.classList.contains('field-error')) {
      note = document.createElement('small');
      note.className = 'field-error';
      note.setAttribute('role', 'alert');
      box.after(note);
    }
    note.textContent = msg;
    note.hidden = !msg;
    el.setAttribute('aria-invalid', msg ? 'true' : 'false');
    box.classList.toggle('has-error', !!msg);
  }

  function validate(el) {
    const c = cfg[el.id];
    const msg = check[c.type](el.value, c);
    showError(el, msg);
    return !msg;
  }

  /* ---------- input sanitizers ---------- */
  function sanitize(el, c) {
    let v = el.value;
    if (c.type === 'phone') {
      v = v.replace(/[^\d+]/g, '');
      v = (v[0] === '+' ? '+' : '') + v.replace(/\+/g, '');
      v = v.slice(0, v[0] === '+' ? 16 : 15);
    } else if (c.type === 'otp') {
      v = v.replace(/\D/g, '').slice(0, 6);
    } else if (c.type === 'email') {
      v = v.replace(/\s/g, '');
    }
    if (c.max) v = v.slice(0, c.max);
    if (v !== el.value) el.value = v;
  }

  /* ---------- wire up fields ---------- */
  Object.entries(cfg).forEach(([id, c]) => {
    const el = $(id);
    if (!el) return;
    el.form && el.form.setAttribute('novalidate', '');

    if (c.type === 'phone')  { el.type = 'tel'; el.inputMode = 'tel'; el.maxLength = 16; el.autocomplete = 'tel'; }
    if (c.type === 'otp')    { el.inputMode = 'numeric'; el.maxLength = 6; }
    if (c.type === 'name')   { el.maxLength = c.max; }
    if (c.type === 'email')  { el.maxLength = c.max; el.autocapitalize = 'off'; }
    if (c.max && /pass|confirm/.test(c.type)) el.maxLength = c.max;
    if (c.type === 'newpass') el.minLength = 8;

    let touched = false;
    el.addEventListener('input', () => {
      sanitize(el, c);
      if (touched) validate(el);
      // keep confirm in sync when the main password changes
      Object.entries(cfg).forEach(([cid, cc]) => {
        if (cc.of === id && $(cid) && $(cid).value) validate($(cid));
      });
    });
    el.addEventListener('blur', () => {
      if (c.type === 'email' || c.type === 'name') el.value = el.value.trim();
      if (c.type !== 'otp' || el.value) { touched = true; validate(el); }
    });
    el.addEventListener('change', () => { touched = true; validate(el); }); // selects
  });

  /* ---------- 6-box OTP: digits only + paste ---------- */
  const otpBoxes = [...document.querySelectorAll('#otpForm .otp-inputs input')];
  otpBoxes.forEach((box, i) => {
    box.addEventListener('input', () => { box.value = box.value.replace(/\D/g, '').slice(0, 1); });
    box.addEventListener('paste', (e) => {
      e.preventDefault();
      const d = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
      d.split('').forEach((ch, k) => { if (otpBoxes[i + k]) otpBoxes[i + k].value = ch; });
      (otpBoxes[Math.min(i + d.length, 5)] || box).focus();
    });
  });

  /* ---------- submit guards (capture: block other handlers if invalid) ---------- */
  function guard(formId, extra) {
    const form = $(formId);
    if (!form) return;
    form.setAttribute('novalidate', '');
    form.addEventListener('submit', (e) => {
      let ok = true;
      Object.keys(cfg).forEach((id) => {
        const el = $(id);
        if (!el || !form.contains(el) || cfg[id].type === 'otp') return;
        if (!validate(el) && ok) { el.focus(); }
        ok = ok && !el.getAttribute('aria-invalid').includes('true');
      });
      if (extra && !extra()) ok = false;
      if (!ok) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);
  }

  guard('loginForm');
  guard('registerForm');
  guard('resetPasswordForm');
  guard('otpForm', () => {
    const code = otpBoxes.map((b) => b.value).join('');
    const err = $('otpError');
    if (!/^\d{6}$/.test(code)) {
      err.textContent = 'Enter the complete 6-digit OTP';
      err.hidden = false;
      (otpBoxes.find((b) => !b.value) || otpBoxes[0]).focus();
      return false;
    }
    err.hidden = true;
    return true;
  });

  /* ---------- clear errors when a modal closes ---------- */
  document.querySelectorAll('dialog.login-modal').forEach((d) =>
    d.addEventListener('close', () => {
      d.querySelectorAll('.field-error').forEach((n) => (n.hidden = true));
      d.querySelectorAll('[aria-invalid]').forEach((n) => n.removeAttribute('aria-invalid'));
      d.querySelectorAll('.has-error').forEach((n) => n.classList.remove('has-error'));
    })
  );
})();
