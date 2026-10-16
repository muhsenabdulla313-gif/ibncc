/**
 * CC Hub: Login, register (email OTP), searchable dropdowns
 */

(() => {
  "use strict";

  const loginModal = document.getElementById("loginModal");
  const registerModal = document.getElementById("registerModal");
  const otpModal = document.getElementById("otpModal");
  const resetPasswordModal = document.getElementById("resetPasswordModal");
  if (!loginModal && !registerModal && !otpModal && !resetPasswordModal) return;

  /* ---------- Dialog helpers ---------- */
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

  function showLoginError(text) {
    const el = document.getElementById("loginError");
    if (el) {
      el.textContent = text;
      el.hidden = !text;
    } else if (text) {
      alert(text);
    }
  }

  /* ---------- Open / close wiring ---------- */
  document.querySelectorAll(".btn-login").forEach((btn) => btn.addEventListener("click", openLogin));
  document.querySelectorAll(".login-register").forEach((btn) => btn.addEventListener("click", openRegister));

  // Forgot password is not wired to the server yet
  document.querySelectorAll(".login-forgot").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      showLoginError("Password reset is not available yet. Please contact the admin.");
    });
  });

  document.getElementById("otpBackLogin")?.addEventListener("click", openLogin);
  document.getElementById("resetBackLogin")?.addEventListener("click", openLogin);

  document.getElementById("loginModalClose")?.addEventListener("click", () => closeDialog(loginModal));
  document.getElementById("registerModalClose")?.addEventListener("click", () => closeDialog(registerModal));
  document.getElementById("otpModalClose")?.addEventListener("click", () => closeDialog(otpModal));
  document.getElementById("resetPasswordModalClose")?.addEventListener("click", () => closeDialog(resetPasswordModal));

  [loginModal, registerModal, otpModal, resetPasswordModal].forEach((dlg) => {
    dlg?.addEventListener("click", (e) => {
      if (e.target === dlg) closeDialog(dlg);
    });
  });

  /* ---------- Password show / hide ---------- */
  function bindPasswordToggle(btn) {
    btn.addEventListener("click", () => {
      const input =
        document.getElementById(btn.getAttribute("data-password-toggle")) ||
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
  if (loginToggle && !loginToggle.hasAttribute("data-password-toggle")) {
    bindPasswordToggle(loginToggle);
  }

  /* ---------- Login (server) ---------- */
  document.getElementById("loginForm")?.addEventListener("submit", async function (e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector(".login-submit");
    showLoginError("");
    if (btn) btn.disabled = true;

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        credentials: "same-origin",
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : "";
        showLoginError(firstError || data.message || "Login failed.");
        return;
      }

      window.location.href = data.redirect || "/";
    } catch (error) {
      console.error(error);
      showLoginError("Unable to connect to the server.");
    } finally {
      if (btn) btn.disabled = false;
    }
  });

  /* ---------- Register email OTP verification ---------- */
  function setupRegisterEmailVerification(o) {
    if (!o.form || !o.input || !o.verifyBtn || !o.otpPanel || !o.otpInput || !o.confirmBtn) return;

    const sendUrl = o.form.dataset.otpSend;
    const verifyUrl = o.form.dataset.otpVerify;
    const csrf = o.form.querySelector('input[name="_token"]')?.value || "";
    let verifiedEmail = null;

    const post = async (url, body) => {
      const r = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": csrf,
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(body),
      });
      const j = await r.json().catch(() => ({}));
      if (!r.ok) {
        throw new Error(j.message || (j.errors && Object.values(j.errors)[0][0]) || "Something went wrong");
      }
      return j;
    };

    const say = (text, ok) => {
      if (!o.msgEl) return;
      o.msgEl.textContent = text;
      o.msgEl.hidden = !text;
      o.msgEl.style.color = ok ? "#16a34a" : "#dc2626";
    };

    const current = () => o.input.value.trim().toLowerCase();
    const isValid = () => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(current());

    // show the Verify button as soon as the email looks valid
    o.input.addEventListener("input", () => {
      if (current() !== verifiedEmail) {
        verifiedEmail = null;
        if (o.verifiedBadge) o.verifiedBadge.hidden = true;
      }
      o.verifyBtn.hidden = !isValid() || current() === verifiedEmail;
      if (!isValid()) o.otpPanel.hidden = true;
    });

    o.verifyBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      if (!isValid()) {
        say("Enter a valid email address.", false);
        o.otpPanel.hidden = false;
        return;
      }
      o.verifyBtn.disabled = true;
      o.verifyBtn.textContent = "Sending…";
      try {
        await post(sendUrl, { email: current() });
        o.otpPanel.hidden = false;
        o.otpInput.value = "";
        o.otpInput.focus();
        say("OTP sent. Check your inbox (and spam folder).", true);
        o.verifyBtn.textContent = "Resend";
      } catch (err) {
        o.otpPanel.hidden = false;
        say(err.message, false);
        o.verifyBtn.textContent = "Verify";
      }
      o.verifyBtn.disabled = false;
    });

    o.otpInput.addEventListener("input", () => {
      o.otpInput.value = o.otpInput.value.replace(/\D/g, "").slice(0, 6);
      say("", true);
    });

    o.otpInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        o.confirmBtn.click();
      }
    });

    o.confirmBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      const code = o.otpInput.value.trim();
      if (code.length !== 6) return say("Enter the 6-digit OTP.", false);
      o.confirmBtn.disabled = true;
      try {
        await post(verifyUrl, { email: current(), code });
        verifiedEmail = current();
        o.otpPanel.hidden = true;
        o.verifyBtn.hidden = true;
        if (o.verifiedBadge) o.verifiedBadge.hidden = false;
        say("", true);
      } catch (err) {
        say(err.message, false);
      }
      o.confirmBtn.disabled = false;
    });

    return { isVerified: () => verifiedEmail !== null && verifiedEmail === current() };
  }

  const emailVerifier = setupRegisterEmailVerification({
    form: document.getElementById("registerForm"),
    input: document.getElementById("registerEmail"),
    verifyBtn: document.getElementById("registerEmailVerifyBtn"),
    verifiedBadge: document.getElementById("registerEmailVerified"),
    otpPanel: document.getElementById("registerEmailOtpPanel"),
    otpInput: document.getElementById("registerEmailOtpInput"),
    confirmBtn: document.getElementById("registerEmailOtpConfirm"),
    msgEl: document.getElementById("registerEmailOtpMsg"),
  });

  /* ---------- Register submit ---------- */
  document.getElementById("registerForm")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const form = e.currentTarget;

    const password = document.getElementById("registerPassword")?.value || "";
    const confirm = document.getElementById("registerConfirm")?.value || "";

    if (password !== confirm) {
      alert("Passwords do not match.");
      document.getElementById("registerConfirm")?.focus();
      return;
    }

    if (!emailVerifier || !emailVerifier.isVerified()) {
      alert("Please verify your email address before signing up.");
      document.getElementById("registerEmail")?.focus();
      return;
    }

    const parish = document.getElementById("registerParish");
    if (!parish?.value) {
      alert("Please select your parish.");
      parish?.focus();
      return;
    }

    const submitButton = form.querySelector(".register-submit");
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account...';
    }

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        if (data.errors) {
          alert(Object.values(data.errors).flat().join("\n") || "Registration failed.");
        } else {
          alert(data.message || "Registration failed.");
        }
        return;
      }

      alert(data.message || "Account created successfully.");
      form.reset();
      window.location.href = data.redirect || "/";
    } catch (error) {
      console.error("Registration error:", error);
      alert("Something went wrong while creating your account.");
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fa-solid fa-user-plus"></i> Sign Up';
      }
    }
  });

  /* ---------- Searchable dependent dropdowns (Rite > Diocese > Forane > Parish) ---------- */
  function setupRegisterCombos(form) {
    const combos = [];
    let escapeHandled = false;

    function closeAll(except) {
      combos.forEach((combo) => {
        if (combo !== except) combo.close(false);
      });
    }

    form.querySelectorAll(".register-combo select").forEach((select, index) => {
      const field = select.closest(".register-combo");
      const label = field.querySelector("label");
      const uid = select.id || `registerCombo${index}`;
      const placeholder =
        select.querySelector('option[value=""]')?.textContent.trim() || "Select an option";
      const labelText = label?.textContent.trim() || "";

      if (label && !label.id) label.id = `${uid}Label`;

      const trigger = document.createElement("button");
      trigger.type = "button";
      trigger.className = "register-combo-trigger";
      trigger.id = `${uid}Trigger`;
      trigger.setAttribute("aria-haspopup", "listbox");
      trigger.setAttribute("aria-expanded", "false");
      trigger.setAttribute("aria-controls", `${uid}Panel`);
      if (label) trigger.setAttribute("aria-labelledby", `${label.id} ${trigger.id}`);

      const panel = document.createElement("div");
      panel.className = "register-combo-panel";
      panel.id = `${uid}Panel`;
      panel.hidden = true;

      const searchWrap = document.createElement("div");
      searchWrap.className = "register-combo-search";
      searchWrap.innerHTML = '<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>';

      const search = document.createElement("input");
      search.type = "text";
      search.autocomplete = "off";
      search.spellcheck = false;
      search.placeholder = labelText ? `Search ${labelText.toLowerCase()}` : "Search";
      search.setAttribute("role", "combobox");
      search.setAttribute("aria-label", labelText ? `Search ${labelText}` : "Search");
      search.setAttribute("aria-autocomplete", "list");
      search.setAttribute("aria-expanded", "true");
      search.setAttribute("aria-controls", `${uid}List`);
      searchWrap.appendChild(search);

      const list = document.createElement("ul");
      list.className = "register-combo-list";
      list.id = `${uid}List`;
      list.setAttribute("role", "listbox");
      if (label) list.setAttribute("aria-labelledby", label.id);

      const empty = document.createElement("p");
      empty.className = "register-combo-empty";
      empty.textContent = "No matches found";
      empty.hidden = true;

      panel.append(searchWrap, list, empty);
      select.tabIndex = -1;
      select.setAttribute("aria-hidden", "true");
      select.insertAdjacentElement("afterend", trigger);
      field.appendChild(panel);

      let items = [];
      let activeIndex = -1;

      function setActive(nextIndex) {
        activeIndex = nextIndex;
        items.forEach((item, i) => item.classList.toggle("is-active", i === nextIndex));
        const active = items[nextIndex];
        if (active) {
          search.setAttribute("aria-activedescendant", active.id);
          active.scrollIntoView({ block: "nearest" });
        } else {
          search.removeAttribute("aria-activedescendant");
        }
      }

      function render() {
        const query = search.value.trim().toLowerCase();
        list.innerHTML = "";
        items = [];

        Array.from(select.options).forEach((option) => {
          if (!option.value || option.disabled || option.hidden) return;
          const text = option.textContent.trim();
          if (query && !text.toLowerCase().includes(query)) return;

          const item = document.createElement("li");
          const selected = option.value === select.value;
          item.className = "register-combo-option";
          item.id = `${uid}Option${items.length}`;
          item.setAttribute("role", "option");
          item.setAttribute("aria-selected", String(selected));
          item.classList.toggle("is-selected", selected);
          item.dataset.value = option.value;
          item.textContent = text;
          list.appendChild(item);
          items.push(item);
        });

        empty.hidden = items.length > 0;
        const selectedIndex = items.findIndex((item) => item.classList.contains("is-selected"));
        setActive(selectedIndex >= 0 ? selectedIndex : items.length ? 0 : -1);
      }

      function sync() {
        const hasValue = Boolean(select.value);
        const parent = select.dataset.dependsOn && document.getElementById(select.dataset.dependsOn);
        const parentLabel = parent && form.querySelector(`label[for="${parent.id}"]`);

        if (hasValue) {
          trigger.textContent = select.selectedOptions[0]?.textContent.trim() || "";
        } else if (select.disabled && parentLabel) {
          trigger.textContent = `Select ${parentLabel.textContent.trim().toLowerCase()} first`;
        } else {
          trigger.textContent = placeholder;
        }

        trigger.classList.toggle("has-value", hasValue);
        trigger.disabled = select.disabled;
        field.classList.toggle("is-disabled", select.disabled);
      }

      function isOpen() {
        return !panel.hidden;
      }

      function open() {
        if (select.disabled || isOpen()) return;
        closeAll(combo);
        search.value = "";
        render();
        panel.hidden = false;
        field.classList.add("is-open");
        trigger.setAttribute("aria-expanded", "true");
        search.focus();
        panel.scrollIntoView({ block: "nearest" });
      }

      function close(restoreFocus) {
        if (!isOpen()) return;
        panel.hidden = true;
        field.classList.remove("is-open");
        trigger.setAttribute("aria-expanded", "false");
        if (restoreFocus) trigger.focus();
      }

      function choose(value) {
        if (select.value !== value) {
          select.value = value;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        }
        close(true);
      }

      field.addEventListener("mousedown", (e) => {
        if (e.target !== search) e.preventDefault();
      });

      field.addEventListener("click", (e) => {
        if (panel.contains(e.target)) {
          const item = e.target.closest(".register-combo-option");
          if (item) choose(item.dataset.value);
          return;
        }
        if (e.target.closest("label")) e.preventDefault();
        if (isOpen()) close(true);
        else open();
      });

      field.addEventListener("focusout", (e) => {
        if (!field.contains(e.relatedTarget)) close(false);
      });

      trigger.addEventListener("keydown", (e) => {
        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
          e.preventDefault();
          open();
        }
      });

      search.addEventListener("input", render);

      search.addEventListener("keydown", (e) => {
        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
          e.preventDefault();
          if (!items.length) return;
          const step = e.key === "ArrowDown" ? 1 : -1;
          setActive((activeIndex + step + items.length) % items.length);
        } else if (e.key === "Enter") {
          e.preventDefault();
          if (items[activeIndex]) choose(items[activeIndex].dataset.value);
        } else if (e.key === "Escape") {
          e.preventDefault();
          e.stopPropagation();
          escapeHandled = true;
          window.setTimeout(() => {
            escapeHandled = false;
          }, 0);
          close(true);
        } else if (e.key === "Tab") {
          close(false);
        }
      });

      select.addEventListener("change", sync);

      const combo = { select, sync, close, isOpen, filter: null };
      combos.push(combo);
      sync();
    });

    combos.forEach((child) => {
      const parent = child.select.dataset.dependsOn && document.getElementById(child.select.dataset.dependsOn);
      if (!parent) {
        child.sync();
        return;
      }

      child.filter = () => {
        const parentValue = parent.value;
        Array.from(child.select.options).forEach((option) => {
          if (!option.value) return;
          const match = Boolean(parentValue) && option.dataset.parent === parentValue;
          option.hidden = !match;
          option.disabled = !match;
        });

        let reset = false;
        if (child.select.value && child.select.selectedOptions[0]?.disabled) {
          child.select.value = "";
          reset = true;
        }

        child.select.disabled = !parentValue;
        if (!parentValue) child.close(false);
        child.sync();
        if (reset) child.select.dispatchEvent(new Event("change", { bubbles: true }));
      };

      parent.addEventListener("change", child.filter);
      child.filter();
    });

    form.addEventListener("reset", () => {
      window.setTimeout(() => {
        combos.forEach((combo) => (combo.filter ? combo.filter() : combo.sync()));
      }, 0);
    });

    const dialog = form.closest("dialog");
    dialog?.addEventListener("cancel", (e) => {
      const openCombo = combos.find((combo) => combo.isOpen());
      if (escapeHandled || openCombo) {
        e.preventDefault();
        openCombo?.close(true);
      }
    });
    dialog?.addEventListener("close", () => closeAll());
  }

  const registerFormEl = document.getElementById("registerForm");
  if (registerFormEl) setupRegisterCombos(registerFormEl);

  /* ---------- Hash shortcuts ---------- */
  if (window.location.hash === "#login") openLogin();
  if (window.location.hash === "#register") openRegister();
})();

/* ===== Cascading selects: rite > diocese > forane > parish (loads options from server) ===== */
document.addEventListener("change", async function (e) {
  const parent = e.target;
  if (!(parent instanceof HTMLSelectElement) || !parent.dataset.child) return;

  const child = document.getElementById(parent.dataset.child);
  if (!child) return;

  child.dataset.ph = child.dataset.ph || (child.options[0] ? child.options[0].text : "Select");
  child.innerHTML = "";
  child.add(new Option(child.dataset.ph, ""));
  child.options[0].disabled = true;
  child.options[0].selected = true;

  if (parent.value) {
    try {
      const res = await fetch(parent.dataset.url + "/" + parent.value, {
        headers: { Accept: "application/json" },
      });
      (await res.json()).forEach(function (o) {
        child.add(new Option(o.name, o.id));
      });
    } catch (err) {
      /* leave the list empty */
    }
  }

  child.dispatchEvent(new Event("change", { bubbles: true })); // resets the levels below
});

/* ===== Header profile dropdown (server-rendered @auth markup) ===== */
document.addEventListener("DOMContentLoaded", function () {
  const profile = document.querySelector(".header-profile");
  if (!profile) return;

  const button = profile.querySelector(".header-profile-btn");
  const menu = profile.querySelector(".header-profile-menu");
  if (!button || !menu) return;

  button.addEventListener("click", function (e) {
    e.stopPropagation();
    const isOpen = !menu.hidden;
    menu.hidden = isOpen;
    button.setAttribute("aria-expanded", String(!isOpen));
  });

  document.addEventListener("click", function (e) {
    if (!profile.contains(e.target)) {
      menu.hidden = true;
      button.setAttribute("aria-expanded", "false");
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      menu.hidden = true;
      button.setAttribute("aria-expanded", "false");
      button.focus();
    }
  });
});