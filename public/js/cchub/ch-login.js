/**
 * IBNCC - modal open/close, password toggle, forgot-password OTP UI.
 * Login / register / signup OTP are handled by register.js (server side).
 */
(() => {
  "use strict";

  const OTP_KEY = "ccHubOtp";
  const RESEND_SECONDS = 30;

  /* ---------- Header user dropdown (Blade @auth) ---------- */
  const userBtn = document.getElementById("userMenuBtn");
  const userDropdown = document.getElementById("userDropdown");
  if (userBtn && userDropdown) {
    userBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("open");
    });
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".user-menu")) userDropdown.classList.remove("open");
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") userDropdown.classList.remove("open");
    });
  }

  const loginModal = document.getElementById("loginModal");
  const registerModal = document.getElementById("registerModal");
  const otpModal = document.getElementById("otpModal");
  const resetPasswordModal = document.getElementById("resetPasswordModal");
  if (!loginModal && !registerModal && !otpModal && !resetPasswordModal) return;

  let resendTimerId = 0;
  let verifiedResetPhone = "";

  function openDialog(dialog, focusId) {
    if (!dialog) return;
    if (typeof dialog.showModal === "function") {
      if (!dialog.open) dialog.showModal();
    } else {
      dialog.setAttribute("open", "");
    }
    window.requestAnimationFrame(() => {
      if (focusId) document.getElementById(focusId)?.focus();
    });
  }

  function closeDialog(dialog) {
    if (!dialog) return;
    if (dialog.open && typeof dialog.close === "function") {
      dialog.close();
    } else {
      dialog.removeAttribute("open");
    }
  }

  function openLogin(e) {
    e?.preventDefault();
    closeDialog(registerModal);
    closeDialog(otpModal);
    closeDialog(resetPasswordModal);
    openDialog(loginModal, "loginPhone");
  }

  function openRegister(e) {
    e?.preventDefault();
    closeDialog(loginModal);
    closeDialog(otpModal);
    closeDialog(resetPasswordModal);
    openDialog(registerModal, "registerName");
  }

  function normalizePhone(value) {
    return String(value || "").replace(/\D/g, "");
  }

  function maskPhone(phone) {
    const digits = normalizePhone(phone);
    if (digits.length < 4) return phone || "your registered mobile";
    return `${digits.slice(0, 2)}${"•".repeat(Math.max(0, digits.length - 6))}${digits.slice(-4)}`;
  }

  /* ---------- Forgot password (still demo: not connected to server) ---------- */
  function otpInputs() {
    return [...(otpModal?.querySelectorAll(".otp-inputs input") || [])];
  }

  function readOtpValue() {
    return otpInputs().map((i) => i.value.replace(/\D/g, "")).join("");
  }

  function clearOtpInputs() {
    otpInputs().forEach((i) => { i.value = ""; });
  }

  function setMessage(errId, okId, type, text) {
    const error = document.getElementById(errId);
    const success = document.getElementById(okId);
    if (error) {
      error.hidden = type !== "error";
      error.textContent = type === "error" ? text : "";
    }
    if (success) {
      success.hidden = type !== "success";
      success.textContent = type === "success" ? text : "";
    }
  }

  const setOtpMessage = (t, m) => setMessage("otpError", "otpSuccess", t, m);
  const setResetPasswordMessage = (t, m) => setMessage("resetPasswordError", "resetPasswordSuccess", t, m);

  function generateOtp() {
    return String(Math.floor(100000 + Math.random() * 900000));
  }

  function persistOtp(phone, code) {
    sessionStorage.setItem(OTP_KEY, JSON.stringify({
      phone: normalizePhone(phone),
      code,
      expires: Date.now() + 5 * 60 * 1000,
    }));
  }

  function readStoredOtp() {
    try {
      return JSON.parse(sessionStorage.getItem(OTP_KEY) || "null");
    } catch {
      return null;
    }
  }

  function stopResendTimer() {
    window.clearInterval(resendTimerId);
    resendTimerId = 0;
  }

  function startResendTimer() {
    const resendBtn = document.getElementById("otpResend");
    const timer = document.getElementById("otpTimer");
    let remaining = RESEND_SECONDS;
    stopResendTimer();
    if (resendBtn) resendBtn.disabled = true;
    if (timer) timer.textContent = `(${remaining}s)`;

    resendTimerId = window.setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        stopResendTimer();
        if (resendBtn) resendBtn.disabled = false;
        if (timer) timer.textContent = "";
        return;
      }
      if (timer) timer.textContent = `(${remaining}s)`;
    }, 1000);
  }

  function sendOtp(phone) {
    const code = generateOtp();
    persistOtp(phone, code);

    const masked = document.getElementById("otpMaskedPhone");
    const sms = document.getElementById("otpSms");
    const smsCode = document.getElementById("otpSmsCode");
    if (masked) masked.textContent = maskPhone(phone);
    if (smsCode) smsCode.textContent = code;
    if (sms) sms.hidden = false;

    clearOtpInputs();
    setOtpMessage("", "");
    startResendTimer();
  }

  function openOtp(e) {
    e?.preventDefault();
    const phoneInput = document.getElementById("loginPhone");
    const phone = phoneInput?.value.trim() || "";

    phoneInput?.setCustomValidity("");

    if (!normalizePhone(phone)) {
      if (phoneInput) {
        phoneInput.setCustomValidity("Enter your registered mobile number.");
        phoneInput.reportValidity();
        phoneInput.focus();
      }
      return;
    }

    closeDialog(loginModal);
    closeDialog(registerModal);
    closeDialog(resetPasswordModal);
    sendOtp(phone);
    openDialog(otpModal, "otpDigit1");
  }

  function verifyOtp(e) {
    e?.preventDefault();
    const entered = readOtpValue();
    const stored = readStoredOtp();

    if (entered.length !== 6) {
      setOtpMessage("error", "Enter the 6-digit OTP sent to your mobile number.");
      otpInputs()[0]?.focus();
      return;
    }
    if (!stored || Date.now() > stored.expires) {
      setOtpMessage("error", "This OTP has expired. Please resend a new code.");
      return;
    }
    if (entered !== stored.code) {
      setOtpMessage("error", "Invalid OTP. Please try again.");
      otpInputs()[otpInputs().length - 1]?.focus();
      return;
    }

    verifiedResetPhone = stored.phone;
    sessionStorage.removeItem(OTP_KEY);
    setOtpMessage("success", "Mobile number verified successfully.");
    window.setTimeout(() => {
      stopResendTimer();
      closeDialog(otpModal);
      openResetPassword();
    }, 500);
  }

  function openResetPassword() {
    document.getElementById("resetPasswordForm")?.reset();
    setResetPasswordMessage("", "");
    closeDialog(loginModal);
    closeDialog(registerModal);
    closeDialog(otpModal);
    openDialog(resetPasswordModal, "resetPassword");
  }

  function saveNewPassword(e) {
    e?.preventDefault();
    const password = document.getElementById("resetPassword")?.value || "";
    const confirm = document.getElementById("resetPasswordConfirm")?.value || "";

    if (password.length < 6) {
      setResetPasswordMessage("error", "Password must be at least 6 characters.");
      return;
    }
    if (password !== confirm) {
      setResetPasswordMessage("error", "Passwords do not match.");
      return;
    }
    if (!normalizePhone(verifiedResetPhone)) {
      setResetPasswordMessage("error", "Please verify OTP again before setting a new password.");
      return;
    }

    // TODO: POST to a real Laravel reset-password route here.
    setResetPasswordMessage("success", "Password updated successfully. You can now log in.");

    const loginPhone = document.getElementById("loginPhone");
    if (loginPhone) loginPhone.value = verifiedResetPhone;
    verifiedResetPhone = "";

    window.setTimeout(() => {
      closeDialog(resetPasswordModal);
      openDialog(loginModal, "loginPassword");
    }, 800);
  }

  /* ---------- Open / close wiring ---------- */
  document.querySelectorAll(".btn-login").forEach((b) => b.addEventListener("click", openLogin));
  document.querySelectorAll(".login-register").forEach((b) => b.addEventListener("click", openRegister));
  document.querySelectorAll(".login-forgot").forEach((b) => b.addEventListener("click", openOtp));

  document.getElementById("otpBackLogin")?.addEventListener("click", openLogin);
  document.getElementById("resetBackLogin")?.addEventListener("click", openLogin);

  document.getElementById("loginModalClose")?.addEventListener("click", () => closeDialog(loginModal));
  document.getElementById("registerModalClose")?.addEventListener("click", () => closeDialog(registerModal));
  document.getElementById("otpModalClose")?.addEventListener("click", () => {
    stopResendTimer();
    closeDialog(otpModal);
  });
  document.getElementById("resetPasswordModalClose")?.addEventListener("click", () => closeDialog(resetPasswordModal));

  loginModal?.addEventListener("click", (e) => { if (e.target === loginModal) closeDialog(loginModal); });
  registerModal?.addEventListener("click", (e) => { if (e.target === registerModal) closeDialog(registerModal); });
  otpModal?.addEventListener("click", (e) => {
    if (e.target === otpModal) {
      stopResendTimer();
      closeDialog(otpModal);
    }
  });
  resetPasswordModal?.addEventListener("click", (e) => { if (e.target === resetPasswordModal) closeDialog(resetPasswordModal); });

  /* ---------- Password show/hide ---------- */
  function bindPasswordToggle(btn) {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.getAttribute("data-password-toggle")) ||
        document.getElementById("loginPassword");
      if (!input) return;
      const hidden = input.type === "password";
      input.type = hidden ? "text" : "password";
      btn.setAttribute("aria-label", hidden ? "Hide password" : "Show password");
      const icon = btn.querySelector("i");
      if (icon) {
        icon.classList.toggle("fa-eye-slash", !hidden);
        icon.classList.toggle("fa-eye", hidden);
      }
    });
  }

  document.querySelectorAll("[data-password-toggle]").forEach(bindPasswordToggle);
  const loginToggle = document.getElementById("loginPasswordToggle");
  if (loginToggle && !loginToggle.hasAttribute("data-password-toggle")) bindPasswordToggle(loginToggle);

  document.getElementById("loginPhone")?.addEventListener("input", (e) => e.target.setCustomValidity(""));

  /* ---------- Forgot-password OTP boxes ---------- */
  document.getElementById("otpForm")?.addEventListener("submit", verifyOtp);
  document.getElementById("resetPasswordForm")?.addEventListener("submit", saveNewPassword);

  document.getElementById("otpResend")?.addEventListener("click", () => {
    const stored = readStoredOtp();
    const phone = stored?.phone || document.getElementById("loginPhone")?.value || "";
    if (!normalizePhone(phone)) return;
    sendOtp(phone);
    setOtpMessage("success", "A new OTP has been sent to your registered mobile number.");
    otpInputs()[0]?.focus();
  });

  const otpBoxList = otpInputs();
  otpBoxList.forEach((input, index) => {
    input.addEventListener("input", () => {
      const digit = input.value.replace(/\D/g, "").slice(-1);
      input.value = digit;
      setOtpMessage("", "");
      if (digit && otpBoxList[index + 1]) otpBoxList[index + 1].focus();
    });
    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && !input.value && otpBoxList[index - 1]) otpBoxList[index - 1].focus();
    });
    input.addEventListener("paste", (e) => {
      const pasted = (e.clipboardData || window.clipboardData).getData("text").replace(/\D/g, "").slice(0, 6);
      if (!pasted) return;
      e.preventDefault();
      otpBoxList.forEach((box, i) => { box.value = pasted[i] || ""; });
      otpBoxList[Math.min(pasted.length, otpBoxList.length) - 1]?.focus();
    });
  });

  if (window.location.hash === "#login") openLogin();
  if (window.location.hash === "#register") openRegister();
  if (window.location.hash === "#forgot-password") openOtp();
})();
  /* ---------- Header profile dropdown (rendered by Blade @auth) ---------- */
  const profile = document.querySelector(".header-profile");
  if (profile) {
    const btn = profile.querySelector(".header-profile-btn");
    const menu = profile.querySelector(".header-profile-menu");
    const close = () => { menu.hidden = true; btn.setAttribute("aria-expanded", "false"); };

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const opening = menu.hidden;
      menu.hidden = !opening;
      btn.setAttribute("aria-expanded", String(opening));
    });
    document.addEventListener("click", (e) => { if (!e.target.closest(".header-profile")) close(); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") { close(); btn.focus(); } });
  }