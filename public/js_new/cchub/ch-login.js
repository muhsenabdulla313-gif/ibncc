/**
 * IBNCC - login / register / forgot-password modals.
 * Login, register and signup OTP go to Laravel. Nothing is saved in localStorage.
 * Header icon is rendered by Blade (@auth), this file only opens/closes its dropdown.
 */

/* ======================================================================
   1. HEADER PROFILE DROPDOWN  (Blade renders .header-profile)
   ====================================================================== */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.header-profile-btn');
  const current = btn ? btn.closest('.header-profile') : null;

  document.querySelectorAll('.header-profile.is-open').forEach(function (p) {
    if (p !== current) p.classList.remove('is-open');
  });

  if (current) {
    e.preventDefault();
    e.stopImmediatePropagation();
    const open = current.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', String(open));
  }
}, true);

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.header-profile.is-open').forEach(function (p) { p.classList.remove('is-open'); });
  }
});

/* ======================================================================
   2. CASCADING SELECTS  (rite > diocese > forane > parish)
   ====================================================================== */
document.addEventListener('change', async function (e) {
  const parent = e.target;
  if (!(parent instanceof HTMLSelectElement) || !parent.dataset.child) return;

  const child = document.getElementById(parent.dataset.child);
  if (!child) return;

  child.dataset.ph = child.dataset.ph || (child.options[0] ? child.options[0].text : 'Select');
  child.innerHTML = '';
  child.add(new Option(child.dataset.ph, ''));

  if (parent.value) {
    try {
      const res = await fetch(parent.dataset.url + '/' + parent.value, { headers: { Accept: 'application/json' } });
      (await res.json()).forEach(function (o) { child.add(new Option(o.name, o.id)); });
    } catch (err) { /* leave the list empty */ }
  }

  child.dispatchEvent(new Event('change', { bubbles: true }));
});

/* ======================================================================
   3. MODALS (open / close / password toggle / forgot-password demo)
   ====================================================================== */
(() => {
  'use strict';

  const OTP_KEY = 'ccHubOtp';
  const RESEND_SECONDS = 30;

  const loginModal = document.getElementById('loginModal');
  const registerModal = document.getElementById('registerModal');
  const otpModal = document.getElementById('otpModal');
  const resetPasswordModal = document.getElementById('resetPasswordModal');
  if (!loginModal && !registerModal && !otpModal && !resetPasswordModal) return;

  let resendTimerId = 0;
  let verifiedResetPhone = '';

  function openDialog(dialog, focusId) {
    if (!dialog) return;
    if (typeof dialog.showModal === 'function') { if (!dialog.open) dialog.showModal(); }
    else dialog.setAttribute('open', '');
    window.requestAnimationFrame(() => { if (focusId) document.getElementById(focusId)?.focus(); });
  }

  function closeDialog(dialog) {
    if (!dialog) return;
    if (dialog.open && typeof dialog.close === 'function') dialog.close();
    else dialog.removeAttribute('open');
  }

  function openLogin(e) {
    e?.preventDefault();
    closeDialog(registerModal); closeDialog(otpModal); closeDialog(resetPasswordModal);
    openDialog(loginModal, 'loginPhone');
  }

  function openRegister(e) {
    e?.preventDefault();
    closeDialog(loginModal); closeDialog(otpModal); closeDialog(resetPasswordModal);
    openDialog(registerModal, 'registerName');
  }

  const normalizePhone = (v) => String(v || '').replace(/\D/g, '');

  function maskPhone(phone) {
    const d = normalizePhone(phone);
    if (d.length < 4) return phone || 'your registered mobile';
    return `${d.slice(0, 2)}${'•'.repeat(Math.max(0, d.length - 6))}${d.slice(-4)}`;
  }

  /* ---------- forgot password (still a front-end demo) ---------- */
  const otpInputs = () => [...(otpModal?.querySelectorAll('.otp-inputs input') || [])];
  const readOtpValue = () => otpInputs().map((i) => i.value.replace(/\D/g, '')).join('');
  const clearOtpInputs = () => otpInputs().forEach((i) => { i.value = ''; });

  function setMessage(errId, okId, type, text) {
    const error = document.getElementById(errId);
    const success = document.getElementById(okId);
    if (error) { error.hidden = type !== 'error'; error.textContent = type === 'error' ? text : ''; }
    if (success) { success.hidden = type !== 'success'; success.textContent = type === 'success' ? text : ''; }
  }
  const setOtpMessage = (t, m) => setMessage('otpError', 'otpSuccess', t, m);
  const setResetMessage = (t, m) => setMessage('resetPasswordError', 'resetPasswordSuccess', t, m);

  const generateOtp = () => String(Math.floor(100000 + Math.random() * 900000));

  function persistOtp(phone, code) {
    sessionStorage.setItem(OTP_KEY, JSON.stringify({ phone: normalizePhone(phone), code, expires: Date.now() + 5 * 60 * 1000 }));
  }
  function readStoredOtp() {
    try { return JSON.parse(sessionStorage.getItem(OTP_KEY) || 'null'); } catch { return null; }
  }

  function stopResendTimer() { window.clearInterval(resendTimerId); resendTimerId = 0; }

  function startResendTimer() {
    const resendBtn = document.getElementById('otpResend');
    const timer = document.getElementById('otpTimer');
    let remaining = RESEND_SECONDS;
    stopResendTimer();
    if (resendBtn) resendBtn.disabled = true;
    if (timer) timer.textContent = `(${remaining}s)`;
    resendTimerId = window.setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        stopResendTimer();
        if (resendBtn) resendBtn.disabled = false;
        if (timer) timer.textContent = '';
        return;
      }
      if (timer) timer.textContent = `(${remaining}s)`;
    }, 1000);
  }

  function sendResetOtp(phone) {
    const code = generateOtp();
    persistOtp(phone, code);
    const masked = document.getElementById('otpMaskedPhone');
    const sms = document.getElementById('otpSms');
    const smsCode = document.getElementById('otpSmsCode');
    if (masked) masked.textContent = maskPhone(phone);
    if (smsCode) smsCode.textContent = code;
    if (sms) sms.hidden = false;
    clearOtpInputs(); setOtpMessage('', ''); startResendTimer();
  }

  function openOtp(e) {
    e?.preventDefault();
    const phoneInput = document.getElementById('loginPhone');
    const phone = phoneInput?.value.trim() || '';
    phoneInput?.setCustomValidity('');
    if (!normalizePhone(phone)) {
      if (phoneInput) {
        phoneInput.setCustomValidity('Enter your registered mobile number.');
        phoneInput.reportValidity();
        phoneInput.focus();
      }
      return;
    }
    closeDialog(loginModal); closeDialog(registerModal); closeDialog(resetPasswordModal);
    sendResetOtp(phone);
    openDialog(otpModal, 'otpDigit1');
  }

  function verifyResetOtp(e) {
    e?.preventDefault();
    const entered = readOtpValue();
    const stored = readStoredOtp();
    if (entered.length !== 6) { setOtpMessage('error', 'Enter the 6-digit OTP sent to your mobile number.'); otpInputs()[0]?.focus(); return; }
    if (!stored || Date.now() > stored.expires) { setOtpMessage('error', 'This OTP has expired. Please resend a new code.'); return; }
    if (entered !== stored.code) { setOtpMessage('error', 'Invalid OTP. Please try again.'); return; }

    verifiedResetPhone = stored.phone;
    sessionStorage.removeItem(OTP_KEY);
    setOtpMessage('success', 'Mobile number verified successfully.');
    window.setTimeout(() => { stopResendTimer(); closeDialog(otpModal); openResetPassword(); }, 500);
  }

  function openResetPassword() {
    document.getElementById('resetPasswordForm')?.reset();
    setResetMessage('', '');
    closeDialog(loginModal); closeDialog(registerModal); closeDialog(otpModal);
    openDialog(resetPasswordModal, 'resetPassword');
  }

  function saveNewPassword(e) {
    e?.preventDefault();
    const password = document.getElementById('resetPassword')?.value || '';
    const confirm = document.getElementById('resetPasswordConfirm')?.value || '';
    if (password.length < 6) { setResetMessage('error', 'Password must be at least 6 characters.'); return; }
    if (password !== confirm) { setResetMessage('error', 'Passwords do not match.'); return; }
    if (!normalizePhone(verifiedResetPhone)) { setResetMessage('error', 'Please verify OTP again before setting a new password.'); return; }

    // TODO: POST to a real Laravel reset-password route here.
    setResetMessage('success', 'Password updated successfully. You can now log in.');
    const loginPhone = document.getElementById('loginPhone');
    if (loginPhone) loginPhone.value = verifiedResetPhone;
    verifiedResetPhone = '';
    window.setTimeout(() => { closeDialog(resetPasswordModal); openDialog(loginModal, 'loginPassword'); }, 800);
  }

  /* ---------- open / close wiring ---------- */
  document.querySelectorAll('.btn-login').forEach((b) => b.addEventListener('click', openLogin));
  document.querySelectorAll('.login-register').forEach((b) => b.addEventListener('click', openRegister));
  document.querySelectorAll('.login-forgot').forEach((b) => b.addEventListener('click', openOtp));

  document.getElementById('otpBackLogin')?.addEventListener('click', openLogin);
  document.getElementById('resetBackLogin')?.addEventListener('click', openLogin);

  document.getElementById('loginModalClose')?.addEventListener('click', () => closeDialog(loginModal));
  document.getElementById('registerModalClose')?.addEventListener('click', () => closeDialog(registerModal));
  document.getElementById('otpModalClose')?.addEventListener('click', () => { stopResendTimer(); closeDialog(otpModal); });
  document.getElementById('resetPasswordModalClose')?.addEventListener('click', () => closeDialog(resetPasswordModal));

  loginModal?.addEventListener('click', (e) => { if (e.target === loginModal) closeDialog(loginModal); });
  registerModal?.addEventListener('click', (e) => { if (e.target === registerModal) closeDialog(registerModal); });
  otpModal?.addEventListener('click', (e) => { if (e.target === otpModal) { stopResendTimer(); closeDialog(otpModal); } });
  resetPasswordModal?.addEventListener('click', (e) => { if (e.target === resetPasswordModal) closeDialog(resetPasswordModal); });

  /* ---------- password show / hide ---------- */
  function bindPasswordToggle(btn) {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.getAttribute('data-password-toggle')) || document.getElementById('loginPassword');
      if (!input) return;
      const hidden = input.type === 'password';
      input.type = hidden ? 'text' : 'password';
      btn.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
      const icon = btn.querySelector('i');
      if (icon) { icon.classList.toggle('fa-eye-slash', !hidden); icon.classList.toggle('fa-eye', hidden); }
    });
  }
  document.querySelectorAll('[data-password-toggle]').forEach(bindPasswordToggle);
  const loginToggle = document.getElementById('loginPasswordToggle');
  if (loginToggle && !loginToggle.hasAttribute('data-password-toggle')) bindPasswordToggle(loginToggle);

  document.getElementById('loginPhone')?.addEventListener('input', (e) => e.target.setCustomValidity(''));

  /* ---------- forgot-password OTP boxes ---------- */
  document.getElementById('otpForm')?.addEventListener('submit', verifyResetOtp);
  document.getElementById('resetPasswordForm')?.addEventListener('submit', saveNewPassword);

  document.getElementById('otpResend')?.addEventListener('click', () => {
    const phone = readStoredOtp()?.phone || document.getElementById('loginPhone')?.value || '';
    if (!normalizePhone(phone)) return;
    sendResetOtp(phone);
    setOtpMessage('success', 'A new OTP has been sent to your registered mobile number.');
    otpInputs()[0]?.focus();
  });

  const otpBoxList = otpInputs();
  otpBoxList.forEach((input, index) => {
    input.addEventListener('input', () => {
      const digit = input.value.replace(/\D/g, '').slice(-1);
      input.value = digit;
      setOtpMessage('', '');
      if (digit && otpBoxList[index + 1]) otpBoxList[index + 1].focus();
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !input.value && otpBoxList[index - 1]) otpBoxList[index - 1].focus();
    });
    input.addEventListener('paste', (e) => {
      const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
      if (!pasted) return;
      e.preventDefault();
      otpBoxList.forEach((box, i) => { box.value = pasted[i] || ''; });
      otpBoxList[Math.min(pasted.length, otpBoxList.length) - 1]?.focus();
    });
  });

  if (window.location.hash === '#login') openLogin();
  if (window.location.hash === '#register') openRegister();
  if (window.location.hash === '#forgot-password') openOtp();
})();

/* ======================================================================
   4. REGISTER + LOGIN  ->  LARAVEL  (this is what saves to the database)
   ====================================================================== */
(function () {
  'use strict';

  var $ = function (id) { return document.getElementById(id); };
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content
          || (document.querySelector('#registerForm [name=_token]') || {}).value
          || (document.querySelector('#loginForm [name=_token]') || {}).value;

  var FIELDS = {
    phone: {
      input: 'registerPhone', verifyBtn: 'registerPhoneVerifyBtn', panel: 'registerPhoneOtpPanel',
      otpInput: 'registerPhoneOtpInput', confirmBtn: 'registerPhoneOtpConfirm',
      demo: 'registerPhoneOtpDemo', msg: 'registerPhoneOtpMsg', badge: 'registerPhoneVerified'
    },
    email: {
      input: 'registerEmail', verifyBtn: 'registerEmailVerifyBtn', panel: 'registerEmailOtpPanel',
      otpInput: 'registerEmailOtpInput', confirmBtn: 'registerEmailOtpConfirm',
      demo: 'registerEmailOtpDemo', msg: 'registerEmailOtpMsg', badge: 'registerEmailVerified'
    }
  };

  function stop(e) { e.preventDefault(); e.stopImmediatePropagation(); }

  function showFormError(text) {
    var el = $('registerError');
    if (!el) { if (text) { alert(text); } return; }
    el.textContent = text || '';
    el.hidden = !text;
  }

  function flash(el, text, ok) {
    if (!el) return;
    el.textContent = text || '';
    el.hidden = !text;
    el.style.color = ok ? '#15803d' : '#b91c1c';
  }

  async function post(url, body) {
    var res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify(body)
    });

    var json = {};
    try { json = await res.json(); } catch (e) {}

    if (!res.ok) {
      var msg = json.message || 'Something went wrong. Please try again.';
      if (json.errors) { msg = Object.values(json.errors)[0][0]; }
      if (res.status === 419) { msg = 'Session expired. Refresh the page and try again.'; }
      if (res.status === 429) { msg = 'Too many requests. Please wait a minute and try again.'; }
      throw new Error(msg);
    }
    return json;
  }

  /* show the Verify button when phone / email is focused */
  document.addEventListener('focusin', function (e) {
    Object.keys(FIELDS).forEach(function (type) {
      var f = FIELDS[type];
      if (e.target.id === f.input && !e.target.readOnly && $(f.verifyBtn)) { $(f.verifyBtn).hidden = false; }
    });
  });

  async function sendOtp(type) {
    var f = FIELDS[type];
    var btn = $(f.verifyBtn);
    showFormError('');
    btn.disabled = true;
    try {
      var json = await post($('registerForm').dataset.otpSend, { type: type, value: $(f.input).value.trim() });
      $(f.panel).hidden = false;
      $(f.otpInput).value = '';
      $(f.otpInput).focus();
      var demo = $(f.demo);
      if (demo) {
        demo.textContent = json.debug_otp ? 'Demo OTP: ' + json.debug_otp : '';
        demo.hidden = !json.debug_otp;
      }
      flash($(f.msg), 'OTP sent. It is valid for 5 minutes.', true);
    } catch (err) {
      showFormError(err.message);
    } finally {
      btn.disabled = false;
    }
  }

  async function verifyOtp(type) {
    var f = FIELDS[type];
    var otp = $(f.otpInput).value.trim();
    if (!/^\d{6}$/.test(otp)) { flash($(f.msg), 'Enter the 6-digit OTP.', false); return; }
    var btn = $(f.confirmBtn);
    btn.disabled = true;
    try {
      await post($('registerForm').dataset.otpVerify, { type: type, value: $(f.input).value.trim(), otp: otp });
      $(f.panel).hidden = true;
      $(f.verifyBtn).hidden = true;
      $(f.badge).hidden = false;
      $(f.input).readOnly = true;
      showFormError('');
    } catch (err) {
      flash($(f.msg), err.message, false);
    } finally {
      btn.disabled = false;
    }
  }

  document.addEventListener('click', function (e) {
    var types = Object.keys(FIELDS);
    for (var i = 0; i < types.length; i++) {
      var f = FIELDS[types[i]];
      if (e.target.closest('#' + f.verifyBtn))  { stop(e); sendOtp(types[i]);   return; }
      if (e.target.closest('#' + f.confirmBtn)) { stop(e); verifyOtp(types[i]); return; }
    }
  }, true);

  /* ---- Sign Up -> POST /register ---- */
  document.addEventListener('submit', async function (e) {
    if (!e.target || e.target.id !== 'registerForm') return;
    stop(e);

    var form = e.target;
    var btn = form.querySelector('.register-submit');
    var data = Object.fromEntries(new FormData(form));
    showFormError('');

    if (!data.parish_id)                { showFormError('Select your rite, diocese, forane and parish.'); return; }
    if (data.password !== data.confirm) { showFormError('Passwords do not match.'); return; }
    if (!$('registerPhone').readOnly)   { showFormError('Please verify your phone number.'); return; }
    if ($('registerEmail').value.trim() && !$('registerEmail').readOnly) {
      showFormError('Please verify your email or leave it empty.'); return;
    }

    btn.disabled = true;
    try {
      var json = await post(form.getAttribute('action'), data);
      window.location.href = json.redirect || '/';
    } catch (err) {
      showFormError(err.message);
      btn.disabled = false;
    }
  }, true);

  /* ---- Login -> POST /login ---- */
  document.addEventListener('submit', async function (e) {
    if (!e.target || e.target.id !== 'loginForm') return;
    stop(e);

    var form = e.target;
    var err = $('loginError');
    var btn = form.querySelector('.login-submit');
    var data = Object.fromEntries(new FormData(form));
    data.remember = form.elements.remember.checked;
    err.hidden = true;
    btn.disabled = true;

    try {
      var json = await post(form.getAttribute('action'), data);
      window.location.href = json.redirect || '/';
    } catch (ex) {
      err.textContent = ex.message;
      err.hidden = false;
      btn.disabled = false;
    }
  }, true);
})();