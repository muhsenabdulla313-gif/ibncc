/*
 * Register modal  →  server (send OTP, verify OTP, sign up).
 * Handlers are attached on `document` in the capture phase, so they run BEFORE the demo
 * handlers in ch-login.js and stop them (stopImmediatePropagation) — no need to edit that file.
 */
(function () {
  'use strict';

  var $ = function (id) { return document.getElementById(id); };
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content;

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
    if (!el) { return; }
    el.textContent = text || '';
    el.hidden = !text;
    el.style.color = ok ? '#15803d' : '#b91c1c';
  }

  async function post(url, body) {
    var res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify(body)
    });

    var json = {};
    try { json = await res.json(); } catch (e) { /* non-JSON response */ }

    if (!res.ok) {
      var msg = json.message || 'Something went wrong. Please try again.';
      if (json.errors) { msg = Object.values(json.errors)[0][0]; }
      if (res.status === 429) { msg = 'Too many requests. Please wait a minute and try again.'; }
      throw new Error(msg);
    }
    return json;
  }

  /* ---- send OTP ---- */
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

  /* ---- verify OTP ---- */
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
      $(f.input).readOnly = true;   // verified value can't be edited any more
      showFormError('');
    } catch (err) {
      flash($(f.msg), err.message, false);
    } finally {
      btn.disabled = false;
    }
  }

  /* ---- clicks: Verify / Confirm buttons ---- */
  document.addEventListener('click', function (e) {
    var types = Object.keys(FIELDS);
    for (var i = 0; i < types.length; i++) {
      var f = FIELDS[types[i]];
      if (e.target.closest('#' + f.verifyBtn))  { stop(e); sendOtp(types[i]);   return; }
      if (e.target.closest('#' + f.confirmBtn)) { stop(e); verifyOtp(types[i]); return; }
    }
  }, true);

  /* ---- submit: Sign Up ---- */
  document.addEventListener('submit', async function (e) {
    if (!e.target || e.target.id !== 'registerForm') { return; }
    stop(e);

    var form = e.target;
    var btn = form.querySelector('.register-submit');
    var data = Object.fromEntries(new FormData(form));
    showFormError('');

    // quick checks (the server checks everything again)
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
})();