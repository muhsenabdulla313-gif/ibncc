/* public/js/cchub/profile-limits.js
   Validation + input limits for EVERY profile field (owner only).

   - Allowed characters are filtered while typing / pasting
   - Min / max length, format checks, required fields
   - Inline error message under the field, save is blocked until all is valid
   - Covers: detail forms, profile brief, change password, email OTP
   - Email edit => badge hides, Verify button shows
*/
(() => {
  'use strict';

  /* ================= allowed-character filters ================= */
  const FILTERS = {
    digits: (v) => v.replace(/\D/g, ''),
    // letters (any language), marks, space . ' -
    name: (v) => v.replace(/[^\p{L}\p{M} .'’\-]/gu, ''),
    // city / state / country
    place: (v) => v.replace(/[^\p{L}\p{M} .,'’()\-]/gu, ''),
    // church, job, organization, status, work mode, experience
    words: (v) => v.replace(/[^\p{L}\p{M}\p{N} .,'’&()\/+\-]/gu, ''),
    // house / street
    address: (v) => v.replace(/[^\p{L}\p{M}\p{N} .,'’&()#\/:\-]/gu, ''),
    email: (v) => v.replace(/[^A-Za-z0-9._%+\-@]/g, '').toLowerCase(),
    date: (v) => v.replace(/[^0-9A-Za-z ,\/\-]/g, ''),
    blood: (v) => v.toUpperCase().replace(/[^ABO+\-]/g, ''),
    bio: (v) => v.replace(/[<>]/g, '').replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g, ''),
    password: (v) => v.replace(/[\u0000-\u001F\u007F]/g, ''),
  };

  /* ================= date helpers ================= */
  const MONTHS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

  // accepts: 5 March 1995 | 05/03/1995 | 05-03-1995 | 1995-03-05
  const parseDate = (raw) => {
    const s = raw.replace(/,/g, ' ').replace(/\s+/g, ' ').trim();
    let d, m, y, t;
    if ((t = s.match(/^(\d{1,2}) ([A-Za-z]{3,}) (\d{4})$/))) {
      d = +t[1]; y = +t[3];
      const name = t[2].toLowerCase();
      m = MONTHS.findIndex((x) => x.startsWith(name));
    } else if ((t = s.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/))) {
      d = +t[1]; m = +t[2] - 1; y = +t[3];
    } else if ((t = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/))) {
      y = +t[1]; m = +t[2] - 1; d = +t[3];
    } else return null;
    if (m < 0) return null;
    const dt = new Date(y, m, d);
    return dt.getFullYear() === y && dt.getMonth() === m && dt.getDate() === d ? dt : null;
  };

  const dobCheck = (v) => {
    const dt = parseDate(v);
    if (!dt) return 'Enter a valid date, e.g. 5 March 1995';
    if (dt > new Date()) return 'Date of birth cannot be in the future';
    if (dt.getFullYear() < 1900) return 'Enter a valid year of birth';
    return '';
  };

  /* ================= rules ================= */
  const R = (label, f, o = {}) => ({ label, f, ...o });

  const PHONE = R('Mobile number', 'digits', {
    req: true, min: 10, max: 10, mode: 'numeric', re: /^[6-9]\d{9}$/,
    msg: 'Enter a valid 10-digit mobile number (starts with 6-9)',
  });
  const WHATSAPP = R('WhatsApp number', 'digits', {
    min: 10, max: 10, mode: 'numeric', re: /^[6-9]\d{9}$/,
    msg: 'Enter a valid 10-digit WhatsApp number (starts with 6-9)',
  });
  const EMAIL = R('Email', 'email', {
    max: 255, re: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/, msg: 'Enter a valid email address',
  });
  const PREF = R('Preferred contact', 'words', {
    max: 20, in: ['Call', 'WhatsApp', 'Both'], msg: 'Choose Call, WhatsApp or Both',
  });

  const ADDRESS = [
    R('House / Street', 'address', { min: 3, max: 255 }),
    R('City', 'place', { min: 2, max: 100 }),
    R('State', 'place', { min: 2, max: 100 }),
    R('Country', 'place', { min: 2, max: 100 }),
    R('PIN', 'digits', { min: 4, max: 10, mode: 'numeric', msg: 'Enter a valid PIN code (4-10 digits)' }),
  ];

  // Same order as the form fields (used for the order fallback)
  const SECTIONS = {
    church: [
      R('Rite', 'words', { min: 2, max: 100 }),
      R('Diocese', 'words', { min: 2, max: 100 }),
      R('Forane', 'words', { min: 2, max: 100 }),
      R('Parish', 'words', { min: 2, max: 100 }),
    ],
    personal: [
      R('Full name', 'name', { req: true, min: 2, max: 100, msg: 'Enter your full name (letters only, at least 2 characters)' }),
      R('Date of birth', 'date', { max: 20, check: dobCheck }),
      R('Gender', 'words', { max: 30 }),
      R('Blood group', 'blood', { max: 3, re: /^(A|B|AB|O)[+-]$/, msg: 'Enter a valid blood group (A+, B-, AB+, O-)' }),
    ],
    contact: [PHONE, EMAIL, WHATSAPP, PREF],
    permanent: ADDRESS,
    present: ADDRESS,
    employment: [
      R('Employment status', 'words', { max: 60 }),
      R('Organization', 'words', { min: 2, max: 255 }),
      R('Work mode', 'words', { max: 40 }),
      R('Experience', 'words', { max: 20 }),
    ],
    job: [
      R('Job category', 'words', { min: 2, max: 100 }),
      R('Job', 'words', { min: 2, max: 100 }),
    ],
  };

  // Profile brief form (matched by input name)
  const BRIEF = {
    name: SECTIONS.personal[0],
    role: R('Profession', 'words', { min: 2, max: 100 }),
    bio: R('Bio', 'bio', { max: 300 }),
    status: R('Availability', 'words', { req: true, in: ['available', 'busy'], msg: 'Choose Available or Busy' }),
    location: R('Location', 'place', { max: 100 }),
    district: R('District', 'place', { max: 100 }),
    phone: PHONE,
    email: EMAIL,
  };

  const PASSWORD = {
    pwCurrent: R('Current password', 'password', { req: true, max: 64, raw: true }),
    pwNew: R('New password', 'password', {
      req: true, min: 6, max: 64, raw: true,
      check: (v) => {
        if (!/[A-Za-z]/.test(v) || !/\d/.test(v)) return 'Use at least one letter and one number';
        const cur = document.getElementById('pwCurrent');
        if (cur && cur.value === v) return 'New password must be different from the current one';
        return '';
      },
    }),
    pwConfirm: R('Confirm password', 'password', {
      req: true, min: 6, max: 64, raw: true,
      check: (v) => {
        const nw = document.getElementById('pwNew');
        return nw && nw.value !== v ? 'Passwords do not match' : '';
      },
    }),
  };

  const OTP = R('Verification code', 'digits', { req: true, min: 6, max: 6, mode: 'numeric', msg: 'Enter the 6-digit code' });

  const FORMS = 'form.detail-edit-form, #profileBriefForm, #changePasswordForm, #emailOtpForm';

  /* ================= helpers ================= */
  const SKIP = ['hidden', 'file', 'button', 'submit', 'checkbox', 'radio'];
  const controls = (form) =>
    [...form.querySelectorAll('input, select, textarea')].filter((el) => !SKIP.includes(el.type));
  const keyOf = (el) => (el.dataset.field || el.dataset.name || el.name || '').replace(/\[.*\]/, '').toLowerCase();

  const clean = (v, r, init) => {
    let out = FILTERS[r.f] ? FILTERS[r.f](v) : v;
    if (!r.raw) out = out.replace(/^\s+/, '');
    if (!r.raw && r.f !== 'bio') out = out.replace(/ {2,}/g, ' ');
    if (r.max && out.length > r.max) {
      // on load keep the LAST digits (drops a stray 91 country code); while typing keep the first
      out = init && r.f === 'digits' ? out.slice(-r.max) : out.slice(0, r.max);
    }
    return out;
  };

  const apply = (el, rule) => {
    if (!el || !rule) return;
    el._rule = rule;
    if (el.tagName === 'SELECT') return;
    if (rule.max) el.maxLength = rule.max;
    if (rule.min) el.minLength = rule.min;
    if (rule.mode) el.inputMode = rule.mode;
    if (rule.f === 'email') el.autocomplete = 'email';
    el.value = clean(el.value, rule, true);
  };

  const applySection = (form) => {
    const rules = SECTIONS[form.dataset.section];
    const keys = SECTION_KEYS[form.dataset.section] || [];
    if (!rules) return;
    const list = controls(form);
    rules.forEach((r, i) => {
      // match by field name first, fall back to the field order
      apply(list.find((el) => keyOf(el) === keys[i]) || list[i], r);
    });
  };

  // field keys in form order, so rules attach to the right control by name
  const SECTION_KEYS = {
    church: ['rite', 'diocese', 'forane', 'parish'],
    personal: ['name', 'dob', 'gender', 'blood_group'],
    contact: ['phone', 'email', 'whatsapp', 'preferred_contact'],
    permanent: ['house', 'district', 'state', 'country', 'pin'],
    present: ['house', 'district', 'state', 'country', 'pin'],
    employment: ['employment_status', 'organization', 'work_mode', 'experience_years'],
    job: ['job_category', 'job'],
  };

  /* ================= validation ================= */
  const validate = (el) => {
    const r = el._rule;
    if (!r) return '';
    const v = r.raw ? el.value : el.value.trim();
    if (!v) return r.req ? `${r.label} is required` : '';
    if (r.in && !r.in.includes(v)) return r.msg || `Choose a valid ${r.label.toLowerCase()}`;
    if (r.min && v.length < r.min) return r.msg || `${r.label} must be at least ${r.min} characters`;
    if (r.max && v.length > r.max) return `${r.label} must be at most ${r.max} characters`;
    if (r.re && !r.re.test(v)) return r.msg || `Enter a valid ${r.label.toLowerCase()}`;
    return r.check ? r.check(v, el) || '' : '';
  };

  const holder = (el) => el.closest('.phone-input-wrap, .password-input-wrap') || el;

  const setError = (el, msg) => {
    const h = holder(el);
    if (!el._err) {
      if (!msg) return;
      const n = document.createElement('small');
      n.className = 'field-error';
      n.setAttribute('role', 'alert');
      h.insertAdjacentElement('afterend', n);
      el._err = n;
    }
    el._err.textContent = msg || '';
    el._err.hidden = !msg;
    el.classList.toggle('is-invalid', !!msg);
    h.classList.toggle('is-invalid', !!msg);
    el.setAttribute('aria-invalid', msg ? 'true' : 'false');
  };

  const clearErrors = (form) => controls(form).forEach((el) => setError(el, ''));

  // returns the first invalid control (or null)
  const validateForm = (form) => {
    let first = null;
    controls(form).forEach((el) => {
      if (!el._rule) return;
      const msg = validate(el);
      setError(el, msg);
      if (msg && !first) first = el;
    });

    // cross-field: WhatsApp number needed when it is the preferred way to reach you
    if (form.dataset.section === 'contact') {
      const pref = form.querySelector('[data-field="preferred_contact"]');
      const wa = form.querySelector('[data-field="whatsapp"]');
      if (pref && wa && /^(whatsapp|both)$/i.test(pref.value) && !wa.value.trim()) {
        setError(wa, 'Add a WhatsApp number or change Preferred Contact');
        if (!first) first = wa;
      }
    }
    return first;
  };

  /* ================= init ================= */
  const injectStyles = () => {
    if (document.getElementById('profileLimitsCss')) return;
    const st = document.createElement('style');
    st.id = 'profileLimitsCss';
    st.textContent =
      '.field-error{display:block;margin-top:4px;color:#c0392b;font-size:12px;line-height:1.3}' +
      '.field-error[hidden]{display:none}' +
      'input.is-invalid,select.is-invalid,textarea.is-invalid{border-color:#c0392b!important}' +
      '.phone-input-wrap.is-invalid,.password-input-wrap.is-invalid{border-color:#c0392b!important}';
    document.head.appendChild(st);
  };

  const init = () => {
    injectStyles();

    document.querySelectorAll('form.detail-edit-form[data-section]').forEach((form) => {
      form.noValidate = true; // we show our own messages
      applySection(form);
    });

    const brief = document.getElementById('profileBriefForm');
    if (brief) {
      brief.noValidate = true;
      Object.entries(BRIEF).forEach(([name, rule]) => apply(brief.querySelector(`[name="${name}"]`), rule));
    }

    const pw = document.getElementById('changePasswordForm');
    if (pw) {
      pw.noValidate = true;
      Object.entries(PASSWORD).forEach(([id, rule]) => apply(document.getElementById(id), rule));
    }

    const otpForm = document.getElementById('emailOtpForm');
    if (otpForm) {
      otpForm.noValidate = true;
      apply(document.getElementById('emailOtpInput'), OTP);
    }
  };

  /* ================= events ================= */

  // Live filtering while typing / pasting
  document.addEventListener('input', (e) => {
    const el = e.target;
    const r = el._rule;
    if (!r || e.isComposing || el.tagName === 'SELECT') return;
    const v = clean(el.value, r, false);
    if (v !== el.value) el.value = v;
    if (el._err && !el._err.hidden) setError(el, validate(el));
  });

  // Validate a field when you leave it
  document.addEventListener('focusout', (e) => {
    const el = e.target;
    const r = el && el._rule;
    if (!r) return;
    if (el.tagName !== 'SELECT' && !r.raw) el.value = el.value.trim();
    setError(el, validate(el));
  });

  document.addEventListener('change', (e) => {
    const el = e.target;
    if (el && el._rule && el.tagName === 'SELECT') setError(el, validate(el));
  });

  // Block saving while anything is invalid (runs before the save scripts)
  const guard = (e, form) => {
    if (!form || !form.matches || !form.matches(FORMS)) return;
    const bad = validateForm(form);
    if (bad) {
      e.preventDefault();
      e.stopImmediatePropagation();
      bad.focus();
    }
  };

  document.addEventListener('click', (e) => {
    const t = e.target.closest && e.target.closest('button[type="submit"], [data-edit-cancel], #briefCancelBtn, #cancelPasswordBtn, #closePasswordDialog');
    if (!t) return;
    if (t.matches('button[type="submit"]')) {
      guard(e, t.form || t.closest('form'));
    } else {
      const form = t.closest('form');
      if (form) clearErrors(form);
    }
  }, true);

  document.addEventListener('submit', (e) => guard(e, e.target), true);

  // After saving: whichever form saved the email, keep both email blocks in sync.
  // Changed email => saved as unverified, badge hidden, Verify button shown.
  document.addEventListener('submit', (e) => {
    const wrap = e.target.querySelector && e.target.querySelector('[data-email-verify]');
    if (!wrap) return;
    const inp = wrap.querySelector('input:not([type="hidden"])');
    if (!inp) return;
    const val = inp.value.trim().toLowerCase();
    if (val === (wrap.dataset.original || '').trim().toLowerCase()) return;

    document.querySelectorAll('[data-email-verify]').forEach((w) => {
      w.dataset.original = val;
      w.dataset.verified = '0';
      const i = w.querySelector('input:not([type="hidden"])');
      if (i) i.value = val;
      const badge = w.querySelector('.verified-badge');
      const btn = w.querySelector('.verify-btn');
      if (badge) badge.hidden = true;
      if (btn) btn.hidden = val === '';
    });
  }, true);

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();