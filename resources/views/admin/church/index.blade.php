
@extends('layouts.admin')

@section('head')
    {{-- Font Awesome icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Church directory styles --}}
    <link rel="stylesheet"
          href="{{ asset('css/cchub/admin-church.css') }}">
@endsection

@section('body')

<main class="ca" id="churchAdmin" data-base="{{ url('admin/church') }}">

    <header class="ca-head">
        <h1>
            <i class="fa-solid fa-church" aria-hidden="true"></i>
            Church Directory
        </h1>
        <p>
            Manage the rites, dioceses, foranes and parishes members can choose in their profile.
        </p>
    </header>

    {{-- Church level tabs --}}
    <nav class="ca-tabs" role="tablist" aria-label="Church levels">
        @foreach ([
            'rite'    => ['Rites', 'fa-cross'],
            'diocese' => ['Dioceses', 'fa-landmark'],
            'forane'  => ['Foranes', 'fa-sitemap'],
            'parish'  => ['Parishes', 'fa-place-of-worship'],
        ] as $key => [$label, $icon])
            <button
                type="button"
                role="tab"
                class="ca-tab"
                data-type="{{ $key }}"
                aria-selected="false"
            >
                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                {{ $label }}

                <span class="ca-badge" data-count="{{ $key }}">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </button>
        @endforeach
    </nav>

    {{-- Search, filters and add button --}}
    <section class="ca-toolbar">
        <div class="ca-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>

            <label class="ca-sr" for="caSearch">Search</label>

            <input
                type="search"
                id="caSearch"
                placeholder="Search by name..."
                maxlength="100"
                autocomplete="off"
            >
        </div>

        <div class="ca-filters" id="caFilters" data-chain-box></div>

        <button type="button" class="ca-btn ca-btn-primary" id="caAddBtn">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            <span id="caAddLabel">Add Rite</span>
        </button>
    </section>

    {{-- Church directory table --}}
    <section class="ca-card">
        <div class="ca-table-wrap">
            <table class="ca-table">
                <thead id="caHead"></thead>
                <tbody id="caBody"></tbody>
            </table>
        </div>

        <footer class="ca-pager">
            <span id="caInfo">&nbsp;</span>

            <div class="ca-pager-btns">
                <button
                    type="button"
                    class="ca-btn"
                    id="caPrev"
                    aria-label="Previous page"
                >
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    Prev
                </button>

                <button
                    type="button"
                    class="ca-btn"
                    id="caNext"
                    aria-label="Next page"
                >
                    Next
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </footer>
    </section>

</main>

{{-- Add / Edit dialog --}}
<dialog class="ca-dialog" id="caFormDialog" aria-labelledby="caFormTitle">
    <form id="caForm" novalidate>
        <header class="ca-dialog-head">
            <h2 id="caFormTitle">Add Rite</h2>

            <button
                type="button"
                class="ca-icon-btn"
                data-close
                aria-label="Close"
            >
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="ca-dialog-body">
            <div id="caParents" class="ca-parents" data-chain-box></div>

            <p class="ca-error" data-err="parent" hidden></p>

            <label class="ca-field" for="caName">
                <span id="caNameLabel">Name</span>

                <input
                    type="text"
                    id="caName"
                    name="name"
                    maxlength="100"
                    autocomplete="off"
                    placeholder="Enter name"
                >
            </label>

            <p class="ca-error" data-err="name" hidden></p>
        </div>

        <footer class="ca-dialog-foot">
            <button type="button" class="ca-btn" data-close>
                Cancel
            </button>

            <button
                type="submit"
                class="ca-btn ca-btn-primary"
                id="caSaveBtn"
            >
                Save
            </button>
        </footer>
    </form>
</dialog>

{{-- Delete confirmation dialog --}}
<dialog
    class="ca-dialog ca-dialog-sm"
    id="caDeleteDialog"
    aria-labelledby="caDeleteTitle"
>
    <div class="ca-dialog-head">
        <h2 id="caDeleteTitle">Delete</h2>

        <button
            type="button"
            class="ca-icon-btn"
            data-close
            aria-label="Close"
        >
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="ca-dialog-body">
        <p id="caDeleteText"></p>
    </div>

    <div class="ca-dialog-foot">
        <button type="button" class="ca-btn" data-close>
            Cancel
        </button>

        <button
            type="button"
            class="ca-btn ca-btn-danger"
            id="caDeleteConfirm"
        >
            Delete
        </button>
    </div>
</dialog>

{{-- Toast notification --}}
<div
    class="ca-toast"
    id="caToast"
    role="status"
    aria-live="polite"
    hidden
></div>

@endsection

@push('scripts')
    <script src="{{ asset('js/cchub/admin-church.js') }}"></script>
@endpush
