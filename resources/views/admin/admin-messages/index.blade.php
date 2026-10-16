{{-- resources/views/admin/admin-messages/index.blade.php --}}

@extends('layouts.admin')

@section('head')
<style>
    .am-page,
    .am-page * {
        box-sizing: border-box;
    }

    .am-page {
        color: #1f2933;
        font-family: system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
        padding: 24px 16px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .am-page h1 {
        margin: 0 0 20px;
        font-size: 26px;
        font-weight: 700;
    }

    .am-page h2 {
        margin: 0 0 12px;
        font-size: 18px;
        font-weight: 700;
    }

    .am-page .am-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .am-page label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .am-page input:not([type="checkbox"]),
    .am-page select,
    .am-page textarea {
        display: block;
        width: 100%;
        margin-top: 6px;
        padding: 10px;
        border: 1px solid #cbd2d9;
        border-radius: 8px;
        font: inherit;
        color: #1f2933;
        background: #fff;
    }

    .am-page input[type="checkbox"] {
        display: inline-block;
        width: auto;
        margin: 0;
    }

    .am-page textarea {
        min-height: 110px;
        resize: vertical;
    }

    .am-page .am-btn {
        display: inline-block;
        padding: 10px 18px;
        border: 0;
        border-radius: 8px;
        background: #1d4ed8;
        color: #fff;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        line-height: 1.4;
    }

    .am-page .am-btn:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    .am-page .am-btn.sec {
        background: #e4e7eb;
        color: #1f2933;
    }

    .am-page .am-btn.danger {
        background: #dc2626;
        color: #fff;
    }

    .am-page .am-btn.sm {
        padding: 6px 12px;
        font-size: 13px;
    }

    .am-page .am-msg {
        padding: 10px 14px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .am-page .am-msg.ok {
        background: #dcfce7;
        color: #166534;
    }

    .am-page .am-msg.err {
        background: #fee2e2;
        color: #991b1b;
    }

    .am-page .am-msg ul {
        margin: 0;
        padding-left: 18px;
    }

    .am-page .am-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .am-page table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: #fff;
    }

    .am-page th,
    .am-page td {
        text-align: left;
        padding: 10px 8px;
        border-bottom: 1px solid #e4e7eb;
        vertical-align: top;
    }

    .am-page th {
        font-size: 12px;
        text-transform: uppercase;
        color: #616e7c;
        white-space: nowrap;
    }

    .am-page .am-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .am-page .am-badge.new {
        background: #fef3c7;
        color: #92400e;
    }

    .am-page .am-badge.read {
        background: #e0f2fe;
        color: #075985;
    }

    .am-page .am-actions {
        display: flex;
        gap: 6px;
        white-space: nowrap;
    }

    .am-page .am-search {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .am-page .am-search input {
        margin: 0;
        min-width: 0;
    }

    .am-page .am-body-cell {
        max-width: 320px;
        color: #52606d;
        overflow-wrap: anywhere;
    }

    .am-page .am-pager {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: flex-end;
        margin-top: 14px;
        font-size: 14px;
    }

    .am-page .am-check {
        display: flex;
        gap: 8px;
        align-items: center;
        font-weight: 400;
    }

    .am-page .am-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .am-page .am-head h2 {
        margin: 0;
    }

    .am-page .am-row-end {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-top: 8px;
    }

    .am-page .am-muted {
        color: #616e7c;
        font-weight: 400;
    }

    .am-page .am-empty {
        padding: 20px;
        text-align: center;
        color: #616e7c;
    }

    .am-page dialog {
        border: 0;
        border-radius: 12px;
        padding: 20px;
        width: min(560px, 92vw);
        max-width: 560px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .25);
        color: #1f2933;
    }

    .am-page dialog::backdrop {
        background: rgba(0, 0, 0, .45);
    }

    .am-page dialog h2 {
        margin-bottom: 16px;
    }

    .am-page .am-dialog-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .am-page .am-dialog-head h2 {
        margin: 0;
    }

    .am-page .am-dialog-info {
        color: #52606d;
        margin: 0 0 14px;
        overflow-wrap: anywhere;
    }

    @media (max-width: 700px) {
        .am-page {
            padding: 16px 10px;
        }

        .am-page .am-card {
            padding: 14px;
        }

        .am-page .am-hide-sm {
            display: none;
        }

        .am-page .am-head {
            align-items: flex-start;
        }

        .am-page .am-search {
            flex-wrap: wrap;
        }

        .am-page .am-search input {
            flex: 1 1 100%;
        }

        .am-page .am-search button {
            flex: 1;
        }

        .am-page .am-btn {
            padding: 8px 12px;
        }

        .am-page .am-actions {
            flex-wrap: wrap;
        }

        .am-page .am-pager {
            flex-wrap: wrap;
        }
    }
</style>
@endsection

@section('body')
<div class="am-page">

    <h1>Admin Messages</h1>

    <div id="flash" aria-live="polite"></div>

    {{-- Messages list --}}
    <section class="am-card">
        <div class="am-head">
            <h2>
                All messages
                <small id="total" class="am-muted"></small>
            </h2>

            <button type="button" class="am-btn" id="newBtn">
                + New message
            </button>
        </div>

        <form id="searchForm" class="am-search">
            <input
                type="search"
                id="searchInput"
                placeholder="Search subject, message, name or phone"
                aria-label="Search messages"
            />

            <button type="submit" class="am-btn sec">
                Search
            </button>

            <button
                type="button"
                class="am-btn sec"
                id="clearSearch"
                hidden
            >
                Clear
            </button>
        </form>

        <div class="am-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>To</th>
                        <th>Subject</th>
                        <th class="am-hide-sm">Message</th>
                        <th>Status</th>
                        <th class="am-hide-sm">Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="rows">
                    <tr>
                        <td colspan="6" class="am-empty">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="am-pager">
            <button type="button" class="am-btn sec sm" id="prevBtn">
                Prev
            </button>

            <span id="pageInfo">Page 1 of 1</span>

            <button type="button" class="am-btn sec sm" id="nextBtn">
                Next
            </button>
        </div>
    </section>

    {{-- Create message dialog --}}
    <dialog id="createDialog">
        <div class="am-dialog-head">
            <h2>New message</h2>
        </div>

        <div id="createErrors" aria-live="polite"></div>

        <form id="sendForm" novalidate>
            <label for="audience">Send to
                <select name="audience" id="audience">
                    <option value="one">One member (by phone number)</option>
                    <option value="all">All members</option>
                </select>
            </label>

            <label id="phoneRow" for="phoneInput">
                Member phone number
                <input
                    type="tel"
                    name="phone"
                    id="phoneInput"
                    inputmode="numeric"
                    maxlength="10"
                    placeholder="10-digit phone number"
                    autocomplete="tel-national"
                />
            </label>

            <label for="subject">Subject
                <input
                    type="text"
                    name="subject"
                    id="subject"
                    maxlength="150"
                    required
                />
            </label>

            <label for="body">Message
                <textarea
                    name="body"
                    id="body"
                    maxlength="2000"
                    required
                ></textarea>
            </label>

            <div class="am-row-end">
                <button type="button" class="am-btn sec" id="createCancel">
                    Cancel
                </button>

                <button type="submit" class="am-btn" id="sendBtn">
                    Send message
                </button>
            </div>
        </form>
    </dialog>

    {{-- Edit message dialog --}}
    <dialog id="editDialog">
        <h2>Edit message</h2>

        <p id="editTo" class="am-dialog-info"></p>

        <div id="editErrors" aria-live="polite"></div>

        <form id="editForm" novalidate>
            <input type="hidden" id="editId" />

            <label for="editSubject">Subject
                <input
                    type="text"
                    id="editSubject"
                    maxlength="150"
                    required
                />
            </label>

            <label for="editBody">Message
                <textarea
                    id="editBody"
                    maxlength="2000"
                    required
                ></textarea>
            </label>

            <label class="am-check" for="editUnread">
                <input type="checkbox" id="editUnread" />
                <span>Mark as unread so the member sees it as new</span>
            </label>

            <div class="am-row-end">
                <button type="button" class="am-btn sec" id="editCancel">
                    Cancel
                </button>

                <button type="submit" class="am-btn" id="editSave">
                    Save changes
                </button>
            </div>
        </form>
    </dialog>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var CSRF = @json(csrf_token());
    var LIST_URL = @json(route('admin.admin-messages.index'));
    var STORE_URL = @json(route('admin.admin-messages.store'));
    var ITEM_URL = @json(route('admin.admin-messages.update', '__ID__'));

    var $ = function (id) {
        return document.getElementById(id);
    };

    var state = {
        page: 1,
        last: 1,
        q: '',
        items: {}
    };

    function esc(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c];
        });
    }

    function api(url, method, body) {
        var options = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (body !== undefined && body !== null) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(body);
        }

        return fetch(url, options).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (json) {
                if (!response.ok) {
                    var errors = json.errors
                        ? [].concat.apply([], Object.values(json.errors))
                        : [json.message || ('Request failed (' + response.status + ')')];

                    var error = new Error(errors[0] || 'The request failed.');
                    error.list = errors;
                    error.status = response.status;

                    throw error;
                }

                return json;
            });
        });
    }

    function flash(target, type, messages) {
        var items = Array.isArray(messages) ? messages : [messages];

        target.innerHTML =
            '<div class="am-msg ' + (type === 'ok' ? 'ok' : 'err') + '">' +
                '<ul>' +
                    items.map(function (message) {
                        return '<li>' + esc(message) + '</li>';
                    }).join('') +
                '</ul>' +
            '</div>';

        if (type === 'ok') {
            window.setTimeout(function () {
                target.innerHTML = '';
            }, 4000);
        }
    }

    function showError(target, error) {
        flash(target, 'err', error.list || error.message || 'Something went wrong.');
    }

    function safeText(value) {
        return value == null ? '' : String(value);
    }

    /* ------------------------------
       Load messages
    ------------------------------ */

    function load() {
        var url = LIST_URL +
            '?page=' + encodeURIComponent(state.page) +
            (state.q ? '&q=' + encodeURIComponent(state.q) : '');

        $('rows').innerHTML =
            '<tr><td colspan="6" class="am-empty">Loading...</td></tr>';

        api(url, 'GET').then(function (response) {
            var data = Array.isArray(response.data) ? response.data : [];

            state.last = Math.max(1, Number(response.last) || 1);
            state.page = Number(response.page) || 1;
            state.items = {};

            data.forEach(function (message) {
                state.items[String(message.id)] = message;
            });

            $('total').textContent = '(' + (response.total || 0) + ')';
            $('pageInfo').textContent =
                'Page ' + state.page + ' of ' + state.last;

            $('prevBtn').disabled = state.page <= 1;
            $('nextBtn').disabled = state.page >= state.last;
            $('clearSearch').hidden = !state.q;

            if (!data.length) {
                $('rows').innerHTML =
                    '<tr><td colspan="6" class="am-empty">No messages found.</td></tr>';
                return;
            }

            $('rows').innerHTML = data.map(function (message) {
                var messageBody = safeText(message.body);
                var shortBody = messageBody.length > 80
                    ? messageBody.slice(0, 80) + '...'
                    : messageBody;

                var isRead = Boolean(message.read);

                return '<tr>' +
                    '<td>' +
                        esc(message.name) +
                        '<br><small>' + esc(message.phone) + '</small>' +
                    '</td>' +

                    '<td>' + esc(message.subject) + '</td>' +

                    '<td class="am-body-cell am-hide-sm">' +
                        esc(shortBody) +
                    '</td>' +

                    '<td>' +
                        (isRead
                            ? '<span class="am-badge read">Read</span>'
                            : '<span class="am-badge new">Unread</span>') +
                    '</td>' +

                    '<td class="am-hide-sm">' + esc(message.sent) + '</td>' +

                    '<td>' +
                        '<div class="am-actions">' +
                            '<button type="button" class="am-btn sec sm" ' +
                                'data-act="edit" data-id="' + esc(message.id) + '">' +
                                'Edit' +
                            '</button>' +

                            '<button type="button" class="am-btn danger sm" ' +
                                'data-act="delete" data-id="' + esc(message.id) + '">' +
                                'Delete' +
                            '</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
            }).join('');
        }).catch(function (error) {
            $('rows').innerHTML =
                '<tr><td colspan="6" class="am-empty">Unable to load messages.</td></tr>';

            showError($('flash'), error);
        });
    }

    /* ------------------------------
       Create / send message
    ------------------------------ */

    var createDialog = $('createDialog');
    var audience = $('audience');
    var phoneRow = $('phoneRow');
    var phoneInput = $('phoneInput');

    function toggleAudience() {
        var oneMember = audience.value === 'one';

        phoneRow.style.display = oneMember ? '' : 'none';
        phoneInput.required = oneMember;

        if (!oneMember) {
            phoneInput.value = '';
        }
    }

    audience.addEventListener('change', toggleAudience);

    phoneInput.addEventListener('input', function () {
        phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
    });

    toggleAudience();

    $('newBtn').addEventListener('click', function () {
        $('sendForm').reset();
        $('createErrors').innerHTML = '';
        toggleAudience();
        createDialog.showModal();
    });

    $('createCancel').addEventListener('click', function () {
        createDialog.close();
    });

    $('sendForm').addEventListener('submit', function (event) {
        event.preventDefault();

        var form = event.target;
        var button = $('sendBtn');

        var payload = {
            audience: form.elements.audience.value,
            phone: form.elements.phone.value.trim(),
            subject: form.elements.subject.value.trim(),
            body: form.elements.body.value.trim()
        };

        $('createErrors').innerHTML = '';

        if (!payload.subject || !payload.body) {
            flash($('createErrors'), 'err', 'Subject and message are required.');
            return;
        }

        if (payload.audience === 'one' && !/^\d{10}$/.test(payload.phone)) {
            flash($('createErrors'), 'err', 'Enter a valid 10-digit phone number.');
            return;
        }

        button.disabled = true;

        api(STORE_URL, 'POST', payload).then(function (response) {
            createDialog.close();

            flash(
                $('flash'),
                'ok',
                response.message || 'Message sent successfully.'
            );

            state.page = 1;
            load();
        }).catch(function (error) {
            showError($('createErrors'), error);
        }).then(function () {
            button.disabled = false;
        });
    });

    /* ------------------------------
       Search
    ------------------------------ */

    $('searchForm').addEventListener('submit', function (event) {
        event.preventDefault();

        state.q = $('searchInput').value.trim();
        state.page = 1;

        load();
    });

    $('clearSearch').addEventListener('click', function () {
        $('searchInput').value = '';
        state.q = '';
        state.page = 1;

        load();
    });

    /* ------------------------------
       Pagination
    ------------------------------ */

    $('prevBtn').addEventListener('click', function () {
        if (state.page > 1) {
            state.page--;
            load();
        }
    });

    $('nextBtn').addEventListener('click', function () {
        if (state.page < state.last) {
            state.page++;
            load();
        }
    });

    /* ------------------------------
       Edit and delete messages
    ------------------------------ */

    var editDialog = $('editDialog');

    $('rows').addEventListener('click', function (event) {
        var button = event.target.closest('button[data-act]');

        if (!button) {
            return;
        }

        var id = String(button.dataset.id);
        var message = state.items[id];

        if (button.dataset.act === 'edit' && message) {
            $('editId').value = id;
            $('editSubject').value = safeText(message.subject);
            $('editBody').value = safeText(message.body);
            $('editUnread').checked = false;

            $('editTo').textContent =
                'To: ' + safeText(message.name) +
                (message.phone ? ' (' + safeText(message.phone) + ')' : '');

            $('editErrors').innerHTML = '';

            editDialog.showModal();
        }

        if (button.dataset.act === 'delete') {
            if (!window.confirm('Delete this message?')) {
                return;
            }

            button.disabled = true;

            api(ITEM_URL.replace('__ID__', encodeURIComponent(id)), 'DELETE')
                .then(function (response) {
                    flash(
                        $('flash'),
                        'ok',
                        response.message || 'Message deleted successfully.'
                    );

                    load();
                })
                .catch(function (error) {
                    showError($('flash'), error);
                    button.disabled = false;
                });
        }
    });

    $('editCancel').addEventListener('click', function () {
        editDialog.close();
    });

    $('editForm').addEventListener('submit', function (event) {
        event.preventDefault();

        var button = $('editSave');
        var id = $('editId').value;

        var payload = {
            subject: $('editSubject').value.trim(),
            body: $('editBody').value.trim(),
            mark_unread: $('editUnread').checked
        };

        $('editErrors').innerHTML = '';

        if (!payload.subject || !payload.body) {
            flash($('editErrors'), 'err', 'Subject and message are required.');
            return;
        }

        button.disabled = true;

        api(ITEM_URL.replace('__ID__', encodeURIComponent(id)), 'PUT', payload)
            .then(function (response) {
                editDialog.close();

                flash(
                    $('flash'),
                    'ok',
                    response.message || 'Message updated successfully.'
                );

                load();
            })
            .catch(function (error) {
                showError($('editErrors'), error);
            })
            .then(function () {
                button.disabled = false;
            });
    });

    /* ------------------------------
       Initial page load
    ------------------------------ */

    load();
})();
</script>
@endpush