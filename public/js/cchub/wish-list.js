(function () {
    'use strict';
    const cfg = window.WISHLIST || {};
    const KEY = 'cc_wishlist';

    const read  = () => { try { return (JSON.parse(localStorage.getItem(KEY)) || []).map(Number); } catch (e) { return []; } };
    const write = a  => { try { localStorage.setItem(KEY, JSON.stringify(a)); } catch (e) {} };
    const toast = m  => { if (typeof window.pdToast === 'function') window.pdToast(m); };

    function paint(btn, on) {
        btn.classList.toggle('is-wishlisted', on);
        const i = btn.querySelector('i');
        if (i) { i.classList.toggle('fa-solid', on); i.classList.toggle('fa-regular', !on); }
        const t = on ? 'Remove from wishlist' : 'Add to wishlist';
        btn.setAttribute('aria-label', t);
        btn.title = t;
    }

    function pop(btn) {
        btn.classList.remove('heart-pop');
        void btn.offsetWidth;
        btn.classList.add('heart-pop');
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfg.csrf
            },
            body: JSON.stringify(body)
        }).then(r => r.json().catch(() => ({})).then(d => ({ status: r.status, data: d })));
    }

    function paintAll() {
        const ids = read();
        document.querySelectorAll('[data-wish]').forEach(btn => {
            if (!cfg.auth) paint(btn, ids.includes(Number(btn.dataset.wish)));
        });
    }

    /* guest toggle (localStorage) */
    function guestToggle(btn, pid) {
        let ids = read();
        const on = !ids.includes(pid);
        ids = on ? ids.concat(pid) : ids.filter(x => x !== pid);
        write(ids);
        paint(btn, on);
        if (on) pop(btn);
        toast(on ? 'Saved! Log in to keep it in your account.' : 'Removed from wishlist.');
    }

    /* merge guest list into account after login */
    function merge() {
        if (!cfg.auth) return;
        const ids = read();
        if (!ids.length) return;
        post(cfg.mergeUrl, { product_ids: ids }).then(r => {
            if (r.data && r.data.success) {
                localStorage.removeItem(KEY);
                document.querySelectorAll('[data-wish]').forEach(btn => {
                    if (ids.includes(Number(btn.dataset.wish))) paint(btn, true);
                });
                toast('Your saved items were added to your wishlist.');
            }
        }).catch(() => {});
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-wish]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();

        const pid = Number(btn.dataset.wish);
        if (!pid || btn.disabled) return;

        if (!cfg.auth) return guestToggle(btn, pid);

        btn.disabled = true;
        post(cfg.toggleUrl, { product_id: pid })
            .then(r => {
                if (r.status === 401) return guestToggle(btn, pid); // session expired
                if (r.data && r.data.success) {
                    paint(btn, !!r.data.wishlisted);
                    if (r.data.wishlisted) pop(btn);
                }
                toast((r.data && r.data.message) || 'Done.');
            })
            .catch(() => toast('Something went wrong. Please try again.'))
            .finally(() => { btn.disabled = false; });
    });

    paintAll();
    merge();
})();