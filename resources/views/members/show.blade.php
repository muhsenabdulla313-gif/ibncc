{{-- resources/views/members/show.blade.php --}}
@extends('layouts.network')

@php
  // Safe defaults for optional data passed by the controller.
  $isOwner = $isOwner ?? (auth()->check() && isset($user) && auth()->id() === $user->id);
  $similar = $similar ?? collect();
  $opts = $opts ?? [];
  $jobsByCategory = $jobsByCategory ?? [];
  $conversations = $conversations ?? collect();
  $adminMessages = $adminMessages ?? collect();

  // hidden_sections may be cast to an array, or may arrive as JSON/text.
  $hiddenSections = $hiddenSections ?? ($p->hidden_sections ?? []);
  if (is_string($hiddenSections)) {
      $decodedHiddenSections = json_decode($hiddenSections, true);
      $hiddenSections = is_array($decodedHiddenSections) ? $decodedHiddenSections : [];
  }
  $hiddenSections = is_array($hiddenSections) ? $hiddenSections : (is_iterable($hiddenSections) ? iterator_to_array($hiddenSections) : []);

  $o = fn($k) => $opts[$k] ?? [];
  $isSaved = $isSaved ?? false;
  $isBlocked = $isBlocked ?? false;
  $savedIds = $savedIds ?? [];
  $shareList = $shareList ?? $similar;

  $unreadAdmin = $adminMessages->whereNull('read_at')->count();
  $unreadChats = $conversations->where('unread', true)->count();
  $show = fn($key) => $isOwner || !in_array($key, $hiddenSections, true);
  $noImg = asset('assets/images/no-image.avif');
@endphp
{{-- ===================== HEAD / BODY SETTINGS ===================== --}}
@section('title')
  {{ $user->name }} — {{ $isOwner ? 'My Profile' : 'Member Profile' }} | CC Hub
@endsection

@section('meta')
  <meta name="description"
    content="View {{ $user->name }}'s profile on CC Hub — connect, collaborate, and grow together." />
@endsection

@section('page_css')
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-profile.css') }}" />
  <style>
    /* WhatsApp + Preferred Contact sit side by side in one row */
    .contact-pair {
      grid-column: 1 / -1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      align-items: start;
    }
    @media (max-width: 480px) {
      .contact-pair { grid-template-columns: 1fr; }
    }

    .preferred-contact-field { min-width: 0; }

    /* Country code is a visual prefix only; the submitted input remains 10 digits. */
    .phone-input-wrap {
      display: flex;
      align-items: stretch;
      width: 100%;
      min-width: 0;
      overflow: hidden;
      border: 1px solid var(--input-border, #d1d5db);
      border-radius: 8px;
      background: var(--input-bg, #fff);
    }
    .phone-input-wrap:focus-within {
      border-color: var(--input-focus, #64748b);
      box-shadow: 0 0 0 3px rgba(100, 116, 139, .14);
    }
    .phone-country-code {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 12px;
      white-space: nowrap;
      color: #475569;
      background: #f1f5f9;
      border-right: 1px solid #d1d5db;
      font-weight: 600;
    }
    .phone-input-wrap input {
      flex: 1 1 auto;
      width: 1%;
      min-width: 0;
      border: 0 !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      outline: none;
    }

    /* Same box for WhatsApp and Preferred Contact so both look identical */
    .contact-pair .phone-input-wrap { min-height: 46px; }
    .phone-country-code { min-width: 52px; }
    .phone-input-wrap select {
      flex: 1 1 auto;
      width: 1%;
      min-width: 0;
      padding: 10px 12px;
      border: 0 !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      outline: none;
      background-color: transparent;
      color: inherit;
      font: inherit;
      cursor: pointer;
    }
    .phone-input-wrap select.cd-native { display: none; }
    .cd { position: relative; flex: 1 1 auto; min-width: 0; display: flex; }
    .cd-btn { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 12px; border: 0; background: transparent; font: inherit; color: inherit; text-align: left; cursor: pointer; }
    .cd-btn:focus-visible { outline: 2px solid #2563eb; outline-offset: -2px; }
    .cd-label.is-placeholder { color: #94a3b8; }
    .cd-btn i { font-size: 12px; color: #64748b; transition: transform .2s; }
    .cd.is-open .cd-btn i { transform: rotate(180deg); }
    .cd-list { position: fixed; z-index: 9999; margin: 0; padding: 4px; list-style: none; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; box-shadow: 0 10px 24px rgba(15,23,42,.15); max-height: 220px; overflow: auto; }
    .cd-list[hidden] { display: none; }
    .cd-list li { padding: 10px 12px; border-radius: 8px; cursor: pointer; }
    .cd-list li:hover, .cd-list li.is-active { background: #f1f5f9; }
    .cd-list li[aria-selected="true"] { font-weight: 600; }
  </style>
  @if ($isOwner)
    <link rel="stylesheet" href="{{ asset('css/cchub/ch-profile-page.css') }}" />
  @else
    <link rel="stylesheet" href="{{ asset('css/cchub/ch-other-profile.css') }}" />
  @endif
@endsection

@section('body_class')
  profile-page {{ $isOwner ? '' : 'other-profile-page' }} page-profile
@endsection

@section('body_attrs')
  data-owner="{{ $isOwner ? '1' : '0' }}" data-update-url="{{ $isOwner ? route('profile.update') : '' }}"
@endsection

{{-- No nav item highlighted on the profile page --}}
@section('nav_active', 'none')

@section('no_login_modals')
@endsection

@section('after_header')
  <nav class="app-nav" aria-label="Profile navigation">
    <div class="app-nav-inner">
      @if ($isOwner)
        <a href="#messages" class="app-nav-item" id="openMessagesBtn" aria-label="Messages, {{ $unreadChats }} unread"
          aria-controls="messagesPanel" aria-expanded="false" aria-haspopup="menu">
          <span class="app-nav-icon app-nav-icon--messages">
            <i class="fa-regular fa-comments" aria-hidden="true"></i>
            @if ($unreadChats > 0)
              <span class="app-nav-badge" aria-hidden="true">{{ $unreadChats }}</span>
            @endif
          </span>
          <span class="app-nav-label">Messages</span>
        </a>
        <a href="#admin-messages" class="app-nav-item" id="openAdminMessagesBtn"
          aria-label="Admin Messages, {{ $unreadAdmin }} unread" aria-controls="adminMessagesDropdown"
          aria-haspopup="dialog" aria-expanded="false">
          <span class="app-nav-icon app-nav-icon--admin">
            <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>
            @if ($unreadAdmin > 0)
              <span class="app-nav-badge" aria-hidden="true">{{ $unreadAdmin }}</span>
            @endif
          </span>
          <span class="app-nav-label">Admin Messages</span>
        </a>
      @else
        <a href="#save-profile" role="button" class="app-nav-item" data-save-profile
          data-url="{{ route('members.save', $user->uuid) }}" data-saved="{{ $isSaved ? 1 : 0 }}"
          aria-pressed="{{ $isSaved ? 'true' : 'false' }}" aria-label="Save Profile">
          <span class="app-nav-icon app-nav-icon--save">
            <i class="{{ $isSaved ? 'fa-solid' : 'fa-regular' }} fa-bookmark" aria-hidden="true"></i>
          </span>
          <span class="app-nav-label">{{ $isSaved ? 'Saved' : 'Save Profile' }}</span>
        </a>
        <a href="#block-profile" role="button" class="app-nav-item" data-block-profile
          data-url="{{ route('members.block', $user->uuid) }}" data-blocked="{{ $isBlocked ? 1 : 0 }}"
          aria-label="Block Profile">
          <span class="app-nav-icon app-nav-icon--block"><i class="fa-solid fa-ban" aria-hidden="true"></i></span>
          <span class="app-nav-label">{{ $isBlocked ? 'Unblock' : 'Block Profile' }}</span>
        </a>
      @endif

      <a href="#share-profile" class="app-nav-item" id="openShareProfileBtn" aria-label="Share Profile"
        aria-controls="shareProfileDropdown" aria-haspopup="dialog" aria-expanded="false">
        <span class="app-nav-icon app-nav-icon--share">
          <i class="fa-solid fa-share-from-square" aria-hidden="true"></i>
        </span>
        <span class="app-nav-label">Share Profile</span>
      </a>

      @if ($isOwner)
        <a href="{{ route('members.show', auth()->user()->uuid) }}" class="app-nav-item is-active" aria-current="page"
          aria-label="Your profile" aria-haspopup="menu" aria-expanded="false">
          <span class="app-nav-avatar">
            <img src="{{ $p->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="" width="52"
              height="52" />
          </span>
          <span class="app-nav-label">Profile</span>
        </a>
      @elseif (auth()->check())
        <a href="{{ route('members.show', auth()->user()->uuid) }}" class="app-nav-item" aria-label="Your profile"
          aria-haspopup="menu" aria-expanded="false">
          <span class="app-nav-icon app-nav-icon--profile">
            <i class="fa-regular fa-user" aria-hidden="true"></i>
          </span>
          <span class="app-nav-label">Your Profile</span>
        </a>
      @endif
    </div>

    <div class="app-nav-dropdown" id="appNavDropdown" hidden role="menu" aria-label="Account menu">
      <a href="{{ route('activity') }}" class="app-nav-dropdown-item" role="menuitem">
        <span class="app-nav-dropdown-icon" aria-hidden="true"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <span>Activity</span>
      </a>
      <a href="#logout" class="app-nav-dropdown-item app-nav-dropdown-item--logout" role="menuitem"
        data-nav-action="logout-server" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
        <span class="app-nav-dropdown-icon" aria-hidden="true"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span>Logout</span>
      </a>
      <form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden>@csrf</form>
    </div>

    @if ($isOwner)
      {{-- Admin messages dropdown --}}
      <div class="admin-messages-dropdown" id="adminMessagesDropdown" hidden role="dialog"
        aria-labelledby="adminMessagesTitle">
        <header class="admin-messages-header">
          <div class="admin-messages-heading">
            <span class="admin-messages-heading-icon" aria-hidden="true">
              <i class="fa-solid fa-shield-halved"></i>
            </span>
            <div>
              <h2 id="adminMessagesTitle">Admin Messages</h2>
              <p class="admin-messages-subtitle">Official updates from IBNCC</p>
            </div>
          </div>
          <span class="admin-messages-count" id="adminMessagesCount">{{ $unreadAdmin }} new</span>
        </header>

        <ul class="admin-messages-list" id="adminMessagesList">
          @forelse ($adminMessages as $am)
            <li class="admin-message-item {{ $am->read_at ? '' : 'is-unread' }}">
              <div class="admin-message-avatar" aria-hidden="true">
                <i class="fa-solid fa-user-shield"></i>
              </div>
              <div class="admin-message-body">
                <div class="admin-message-meta">
                  <strong class="admin-message-sender">IBNCC Admin</strong>
                  <time
                    datetime="{{ $am->created_at->toIso8601String() }}">{{ $am->created_at->diffForHumans(null, true, true) }}
                    ago</time>
                </div>
                <p class="admin-message-subject">{{ $am->subject }}</p>
                <p class="admin-message-text">{{ $am->body }}</p>
              </div>
            </li>
          @empty
            <li class="admin-message-item">
              <div class="admin-message-body">
                <p class="admin-message-text">No admin messages yet.</p>
              </div>
            </li>
          @endforelse
        </ul>
      </div>
    @endif

    {{-- Share profile dropdown --}}
    <div class="share-profile-dropdown" id="shareProfileDropdown" hidden role="dialog" aria-labelledby="shareProfileTitle"
      data-profile-path="{{ $profileUrl }}" data-profile-name="{{ $user->name }}" data-profile-uuid="{{ $user->uuid }}">
      <header class="share-profile-header">
        <div class="share-profile-heading">
          <span class="share-profile-heading-icon" aria-hidden="true">
            <i class="fa-solid fa-share-nodes"></i>
          </span>
          <div>
            <h2 id="shareProfileTitle">Share Profile</h2>
            <p class="share-profile-subtitle">
              Copy the link or find a saved profile
            </p>
          </div>
        </div>
      </header>

      <div class="share-profile-link-row">
        <a href="{{ $profileUrl }}" class="share-profile-link" id="shareProfileLink" title="Open profile link">
          <i class="fa-solid fa-link" aria-hidden="true"></i>
          <span id="shareProfileLinkText">{{ $profileUrl }}</span>
        </a>
        <button type="button" class="share-profile-copy" id="shareProfileCopyBtn" aria-label="Copy profile link"
          title="Copy link">
          <i class="fa-regular fa-copy" aria-hidden="true"></i>
        </button>
      </div>
      <p class="share-profile-copy-status" id="shareProfileCopyStatus" hidden aria-live="polite">
        Link copied
      </p>

      <div class="share-profile-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <label class="visually-hidden" for="shareProfileSearch">Search saved profiles</label>
        <input type="search" id="shareProfileSearch" placeholder="Search saved profiles..." autocomplete="off" />
      </div>

      <ul class="share-profile-results" id="shareProfileResults" role="listbox" aria-label="Saved profiles">
        @foreach ($shareList as $m)
          @php $isSv = in_array($m->id, $savedIds, true); @endphp
          <li class="share-profile-result" role="option" aria-selected="false" data-uuid="{{ $m->uuid }}">
            <img src="{{ $m->profile?->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="" width="36"
              height="36" />
            <span class="share-profile-result-copy">
              <strong>{{ $m->name }}</strong>
              <em>{{ $m->profile?->job?->name ?: 'Member' }}@if ($m->presentAddress?->district?->name) ·
              {{ $m->presentAddress->district->name }}@endif @if ($isSv) · <i class="fa-solid fa-bookmark"
                aria-hidden="true"></i> Saved @endif</em>
            </span>
            <button type="button" class="share-profile-send" hidden>Send</button>
          </li>
        @endforeach
      </ul>
      <p class="share-profile-empty" id="shareProfileEmpty" hidden>
        No saved profiles match your search.
      </p>
    </div>
  </nav>
@endsection

{{-- ===================== MAIN PAGE CONTENT ===================== --}}
@section('page')
  <main class="profile-main">
    <section class="profile-banner-card" aria-labelledby="profile-name">
      <div class="profile-cover">
        <img id="profileCoverImage" src="{{ $p->cover_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'"
          alt="Cover photo of {{ $user->name }}" />
        @if ($isOwner)
          <button type="button" class="profile-image-edit profile-image-edit--cover" id="editCoverImageBtn"
            aria-label="Change cover image" title="Change cover image">
            <i class="fa-solid fa-pen" aria-hidden="true"></i>
          </button>
          <input type="file" id="coverImageInput" class="profile-image-file-input" accept="image/*" tabindex="-1"
            aria-hidden="true" />
        @endif
      </div>

      <div class="profile-identity">
        <div class="profile-photo-wrap">
          <img class="profile-photo" id="profilePhotoImage" src="{{ $p->photo_url }}"
            onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="Portrait of {{ $user->name }}" width="120"
            height="120" />
          @if ($isOwner)
            <button type="button" class="profile-image-edit profile-image-edit--photo" id="editProfilePhotoBtn"
              aria-label="Change profile photo" title="Change profile photo">
              <i class="fa-solid fa-pen" aria-hidden="true"></i>
            </button>
            <input type="file" id="profilePhotoInput" class="profile-image-file-input" accept="image/*" tabindex="-1"
              aria-hidden="true" />
          @endif
        </div>

        <div class="profile-identity-body">
          <h1 id="profile-name" class="profile-display-name">
            {{ $user->name }}
            @if ($user->email_verified_at)
              <i class="fa-solid fa-shield-halved" title="Verified member" aria-label="Verified member"></i>
            @endif
          </h1>
          <p class="profile-profession">{{ $p->job?->name ?: 'Profession not added yet' }}</p>
          <p class="profile-about">
            {{ $p->about ?: "This member hasn't added a bio yet." }}
          </p>

          <ul class="profile-meta-pills">
            <li>
              <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
              {{ collect([$present?->district?->name, $present?->state?->name])->filter()->implode(', ') ?: 'Location not added yet' }}
            </li>
            <li>
              <i class="fa-solid fa-church" aria-hidden="true"></i>
              {{ $user->diocese_name ? $user->diocese_name . ' Diocese' : 'Diocese not added yet' }}
            </li>
            <li>
              <i class="fa-regular fa-calendar" aria-hidden="true"></i>
              Member since {{ $user->created_at->format('M Y') }}
            </li>
          </ul>
        </div>
      </div>
    </section>

    <div class="profile-details-row">
      <section class="profile-panel" aria-labelledby="profile-details-heading">
        <header class="profile-panel-header">
          <h2 id="profile-details-heading">
            <i class="fa-regular fa-user" aria-hidden="true"></i>
            Profile Details
          </h2>
        </header>

        <div class="profile-accordion" data-accordion>

          {{-- ---------- Church Details ---------- --}}
          @if ($show('church'))
            <details class="profile-detail-item" data-section="church">
              <summary>
                <span class="detail-left">
                  <span class="detail-icon"><i class="fa-solid fa-church" aria-hidden="true"></i></span>
                  <span class="detail-label">Church Details</span>
                </span>
                @include('members._detail-actions', ['label' => 'Church Details', 'key' => 'church'])
              </summary>
              <div class="detail-content">
                <dl class="detail-grid">
                  <div>
                    <dt>Rite</dt>
                    <dd>{{ $user->rite_name ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Diocese</dt>
                    <dd>{{ $user->diocese_name ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Forane</dt>
                    <dd>{{ $user->forane_name ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Parish</dt>
                    <dd>{{ $user->parish?->name ?: 'Not provided' }}</dd>
                  </div>
                </dl>
                @if ($isOwner)
                  <form class="detail-edit-form" data-section="church" hidden>
                    <div class="detail-edit-grid">
                      @include('members._edit-field', ['idx' => 0, 'field' => 'rite', 'label' => 'Rite', 'value' => $user->rite_name, 'options' => $o('rites')])
                      @include('members._edit-field', ['idx' => 1, 'field' => 'diocese', 'label' => 'Diocese', 'value' => $user->diocese_name, 'options' => $o('dioceses')])
                      @include('members._edit-field', ['idx' => 2, 'field' => 'forane', 'label' => 'Forane', 'value' => $user->forane_name, 'options' => $o('foranes')])
                      @include('members._edit-field', ['idx' => 3, 'field' => 'parish', 'label' => 'Parish', 'value' => $user->parish?->name, 'options' => $o('parishes')])
                    </div>
                    <div class="detail-edit-actions">
                      <button type="button" class="sidebar-btn-secondary" data-edit-cancel>Cancel</button>
                      <button type="submit" class="sidebar-btn-primary">Save</button>
                    </div>
                  </form>
                @endif
              </div>
            </details>
          @endif

          {{-- ---------- Personal Details ---------- --}}
          @if ($show('personal'))
            <details class="profile-detail-item" data-section="personal">
              <summary>
                <span class="detail-left">
                  <span class="detail-icon"><i class="fa-regular fa-id-card" aria-hidden="true"></i></span>
                  <span class="detail-label">Personal Details</span>
                </span>
                @include('members._detail-actions', ['label' => 'Personal Details', 'key' => 'personal'])
              </summary>
              <div class="detail-content">
                <dl class="detail-grid">
                  <div>
                    <dt>Full Name</dt>
                    <dd>{{ $user->name }}</dd>
                  </div>
                  @if ($isOwner)
                    <div>
                      <dt>Date of Birth</dt>
                      <dd>{{ $p->dob ? $p->dob->format('j F Y') : 'Not provided' }}</dd>
                    </div>
                  @endif
                  <div>
                    <dt>Gender</dt>
                    <dd>{{ $p->gender ?: 'Not provided' }}</dd>
                  </div>
                  @if ($isOwner)
                    <div>
                      <dt>Blood Group</dt>
                      <dd>{{ $p->blood_group ?: 'Not provided' }}</dd>
                    </div>
                  @endif
                </dl>
                @if ($isOwner)
                  <form class="detail-edit-form" data-section="personal" hidden>
                    <div class="detail-edit-grid">
                      @include('members._edit-field', ['idx' => 0, 'field' => 'name', 'label' => 'Full Name', 'value' => $user->name])
                      @include('members._edit-field', ['idx' => 1, 'field' => 'dob', 'label' => 'Date of Birth', 'value' => $p->dob?->format('j F Y')])
                      @include('members._edit-field', ['idx' => 2, 'field' => 'gender', 'label' => 'Gender', 'value' => $p->gender, 'options' => $o('genders') ?: ['Male', 'Female', 'Other', 'Prefer not to say']])
                      @include('members._edit-field', ['idx' => 3, 'field' => 'blood_group', 'label' => 'Blood Group', 'value' => $p->blood_group, 'options' => $o('blood_groups') ?: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']])
                    </div>
                    <div class="detail-edit-actions">
                      <button type="button" class="sidebar-btn-secondary" data-edit-cancel>Cancel</button>
                      <button type="submit" class="sidebar-btn-primary">Save</button>
                    </div>
                  </form>
                @endif
              </div>
            </details>
          @endif

          {{-- ---------- Contact Information ---------- --}}
          @if ($show('contact'))
            <details class="profile-detail-item" data-section="contact">
              <summary>
                <span class="detail-left">
                  <span class="detail-icon"><i class="fa-solid fa-address-book" aria-hidden="true"></i></span>
                  <span class="detail-label">Contact Information</span>
                </span>
                @include('members._detail-actions', ['label' => 'Contact Information', 'key' => 'contact'])
              </summary>
              <div class="detail-content">
                <dl class="detail-grid">
                  <div>
                    <dt>Mobile</dt>
                    <dd>+91 {{ $user->phone }}</dd>
                  </div>
                  <div>
                    <dt>Email</dt>
                    <dd>{{ $user->email ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>WhatsApp</dt>
                    <dd>{{ $p->whatsapp ? '+91 ' . $p->whatsapp : 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Preferred Contact</dt>
                    <dd>{{ ['call' => 'Call', 'whatsapp' => 'WhatsApp', 'both' => 'Both'][strtolower(trim((string) ($p->preferred_contact ?? '')))] ?? 'Not provided' }}</dd>
                  </div>
                </dl>
                @if ($isOwner)
                  <form class="detail-edit-form" data-section="contact" hidden>
                    <div class="detail-edit-grid">
                      @php
                        // Store/submit only the 10-digit national number; +91 is a visual prefix.
                        $mobileDigits = preg_replace('/\D+/', '', (string) $user->phone);
                        $mobileDigits = strlen($mobileDigits) > 10 ? substr($mobileDigits, -10) : $mobileDigits;
                      @endphp
                      <div class="edit-field phone-edit-field" data-idx="0">
                        <label for="edit-phone">Mobile</label>
                        <div class="phone-input-wrap">
                          <span class="phone-country-code" aria-hidden="true">+91</span>
                          <input id="edit-phone" type="tel" name="phone" value="{{ $mobileDigits }}"
                            data-field="phone" data-idx="0" inputmode="numeric" autocomplete="tel-national"
                            maxlength="10" minlength="10" pattern="[0-9]{10}"
                            title="Enter a 10-digit mobile number" placeholder="10-digit mobile number" required />
                        </div>
                      </div>

                      {{-- Email: the only field that needs verification --}}
                      <div class="verify-wrap" data-email-verify data-original="{{ $user->email }}"
                        data-verified="{{ $user->email_verified_at ? 1 : 0 }}">
                        @include('members._edit-field', ['idx' => 1, 'field' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $user->email])
                        <button type="button" class="verify-btn" data-verify="email" onclick="return window.cchubVerifyEmail(this)" data-send-url="{{ route('register.otp.send') }}" data-verify-url="{{ route('register.otp.verify') }}"
                          @if ($user->email_verified_at) hidden @endif>Verify</button>
                      </div>

                      {{-- WhatsApp + Preferred Contact in ONE row --}}
                      <div class="contact-pair">
                        @php
                          $whatsappDigits = preg_replace('/\D+/', '', (string) ($p->whatsapp ?? ''));
                          $whatsappDigits = strlen($whatsappDigits) > 10 ? substr($whatsappDigits, -10) : $whatsappDigits;
                          // Normalize the saved value so old values with different capitalization still select correctly.
                          $currentPref = strtolower(trim((string) ($p->preferred_contact ?? '')));
                          $hasPreferredContact = in_array($currentPref, ['call', 'whatsapp', 'both'], true);
                        @endphp

                        <div class="edit-field phone-edit-field" data-idx="2">
                          <label for="edit-whatsapp">WhatsApp</label>
                          <div class="phone-input-wrap">
                            <span class="phone-country-code" aria-hidden="true">+91</span>
                            <input id="edit-whatsapp" type="tel" name="whatsapp" value="{{ $whatsappDigits }}"
                              data-field="whatsapp" data-idx="2" inputmode="numeric" autocomplete="tel-national"
                              maxlength="10" minlength="10" pattern="[0-9]{10}"
                              title="Enter a 10-digit WhatsApp number" placeholder="10-digit WhatsApp number" />
                          </div>
                        </div>

                        <div class="edit-field phone-edit-field preferred-contact-field" data-idx="3">
                          <label for="edit-preferred_contact">Preferred Contact</label>
                          <div class="phone-input-wrap">
                            <span class="phone-country-code" aria-hidden="true"><i class="fa-solid fa-phone-volume"></i></span>
                            <select id="edit-preferred_contact" name="preferred_contact"
                              data-field="preferred_contact" data-idx="3" data-custom-select>
                              @unless ($hasPreferredContact)
                                <option value="" selected disabled>Choose one</option>
                              @endunless
                              <option value="Call" @selected($currentPref === 'call')>Call</option>
                              <option value="WhatsApp" @selected($currentPref === 'whatsapp')>WhatsApp</option>
                              <option value="Both" @selected($currentPref === 'both')>Both</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="detail-edit-actions">
                      <button type="button" class="sidebar-btn-secondary" data-edit-cancel>Cancel</button>
                      <button type="submit" class="sidebar-btn-primary">Save</button>
                    </div>
                  </form>
                @endif
              </div>
            </details>
          @endif

          {{-- ---------- Permanent + Present Address (owner only) ---------- --}}
          @if ($isOwner)
            @foreach ([
                ['key' => 'permanent', 'label' => 'Permanent Address', 'icon' => 'fa-house', 'addr' => $permanent],
                ['key' => 'present', 'label' => 'Present Address', 'icon' => 'fa-location-dot', 'addr' => $present],
              ] as $a)
              <details class="profile-detail-item" data-section="{{ $a['key'] }}">
                <summary>
                  <span class="detail-left">
                    <span class="detail-icon"><i class="fa-solid {{ $a['icon'] }}" aria-hidden="true"></i></span>
                    <span class="detail-label">{{ $a['label'] }}</span>
                  </span>
                  @include('members._detail-actions', ['label' => $a['label'], 'key' => $a['key']])
                </summary>
                <div class="detail-content">
                  <dl class="detail-grid">
                    <div>
                      <dt>House / Street</dt>
                      <dd>{{ $a['addr']?->house ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                      <dt>City</dt>
                      <dd>{{ $a['addr']?->district?->name ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                      <dt>State</dt>
                      <dd>{{ $a['addr']?->state?->name ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                      <dt>Country</dt>
                      <dd>{{ $a['addr']?->country?->name ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                      <dt>PIN</dt>
                      <dd>{{ $a['addr']?->pin ?: 'Not provided' }}</dd>
                    </div>
                  </dl>
                  <form class="detail-edit-form" data-section="{{ $a['key'] }}" hidden>
                    <div class="detail-edit-grid">
                      @include('members._edit-field', ['idx' => 0, 'field' => 'house', 'label' => 'House / Street', 'value' => $a['addr']?->house])
                      @include('members._edit-field', ['idx' => 1, 'field' => 'district', 'label' => 'City', 'value' => $a['addr']?->district?->name, 'options' => $o('districts')])
                      @include('members._edit-field', ['idx' => 2, 'field' => 'state', 'label' => 'State', 'value' => $a['addr']?->state?->name, 'options' => $o('states')])
                      @include('members._edit-field', ['idx' => 3, 'field' => 'country', 'label' => 'Country', 'value' => $a['addr']?->country?->name, 'options' => $o('countries')])
                      @include('members._edit-field', ['idx' => 4, 'field' => 'pin', 'label' => 'PIN', 'value' => $a['addr']?->pin])
                    </div>
                    <div class="detail-edit-actions">
                      <button type="button" class="sidebar-btn-secondary" data-edit-cancel>Cancel</button>
                      <button type="submit" class="sidebar-btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </details>
            @endforeach
          @endif

          {{-- ---------- Employment Status ---------- --}}
          @if ($show('employment'))
            <details class="profile-detail-item" data-section="employment">
              <summary>
                <span class="detail-left">
                  <span class="detail-icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></span>
                  <span class="detail-label">Employment Status</span>
                </span>
                @include('members._detail-actions', ['label' => 'Employment Status', 'key' => 'employment'])
              </summary>
              <div class="detail-content">
                <dl class="detail-grid">
                  <div>
                    <dt>Status</dt>
                    <dd>{{ $p->employment_status ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Organization</dt>
                    <dd>{{ $p->organization ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Work Mode</dt>
                    <dd>{{ $p->work_mode ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Experience</dt>
                    <dd>{{ $p->experience_years ? $p->experience_years . ' years' : 'Not provided' }}</dd>
                  </div>
                </dl>
                @if ($isOwner)
                  <form class="detail-edit-form" data-section="employment" hidden>
                    <div class="detail-edit-grid">
                      @include('members._edit-field', ['idx' => 0, 'field' => 'employment_status', 'label' => 'Status', 'value' => $p->employment_status, 'options' => $o('employment_statuses') ?: ['Employed', 'Self-Employed', 'Business', 'Student', 'Freelancer', 'Seeking Opportunities']])
                      @include('members._edit-field', ['idx' => 1, 'field' => 'organization', 'label' => 'Organization', 'value' => $p->organization])
                      @include('members._edit-field', ['idx' => 2, 'field' => 'work_mode', 'label' => 'Work Mode', 'value' => $p->work_mode, 'options' => $o('work_modes') ?: ['On-site', 'Remote', 'Hybrid', 'Field Work', 'Flexible']])
                      @include('members._edit-field', ['idx' => 3, 'field' => 'experience_years', 'label' => 'Experience', 'value' => $p->experience_years ? $p->experience_years . ' years' : '', 'options' => $o('experience') ?: ['0-2 years', '3-5 years', '6-10 years', '11-15 years', '16+ years']])
                    </div>
                    <div class="detail-edit-actions">
                      <button type="button" class="sidebar-btn-secondary" data-edit-cancel>Cancel</button>
                      <button type="submit" class="sidebar-btn-primary">Save</button>
                    </div>
                  </form>
                @endif
              </div>
            </details>
          @endif

          {{-- ---------- Job Details ---------- --}}
          @if ($show('job'))
            <details class="profile-detail-item" data-section="job">
              <summary>
                <span class="detail-left">
                  <span class="detail-icon"><i class="fa-solid fa-toolbox" aria-hidden="true"></i></span>
                  <span class="detail-label">Job Details</span>
                </span>
                @include('members._detail-actions', ['label' => 'Job Details', 'key' => 'job'])
              </summary>
              <div class="detail-content">
                <dl class="detail-grid">
                  <div>
                    <dt>Job Category</dt>
                    <dd>{{ $p->job?->category?->name ?: 'Not provided' }}</dd>
                  </div>
                  <div>
                    <dt>Job</dt>
                    <dd>{{ $p->job?->name ?: 'Not provided' }}</dd>
                  </div>
                </dl>
                @if ($isOwner)
                  <form class="detail-edit-form" data-section="job" hidden>
                    <div class="detail-edit-grid">
                      @include('members._edit-field', ['idx' => 0, 'field' => 'job_category', 'label' => 'Category', 'value' => $p->job?->category?->name, 'options' => $o('job_categories') ?: array_keys($jobsByCategory), 'role' => 'category'])
                      @include('members._edit-field', ['idx' => 1, 'field' => 'job', 'label' => 'Job', 'value' => $p->job?->name, 'options' => $o('jobs') ?: ($jobsByCategory[$p->job?->category?->name] ?? []), 'role' => 'job'])
                    </div>
                    <div class="detail-edit-actions">
                      <button type="button" class="sidebar-btn-secondary" data-edit-cancel>Cancel</button>
                      <button type="submit" class="sidebar-btn-primary">Save</button>
                    </div>
                  </form>
                @endif
              </div>
            </details>
          @endif
        </div>
      </section>

      {{-- ===================== SIDEBAR ===================== --}}
      <aside class="profile-sidebar" aria-label="Profile sidebar">

        @if ($isOwner)
          {{-- ---------- Profile brief (owner) ---------- --}}
          <article class="sidebar-card profile-brief-card" id="profile-brief-card">
            <header class="sidebar-card-header">
              <h3>Edit Your Profile Brief</h3>
              <button type="button" class="sidebar-icon-btn" id="briefEditBtn" aria-label="Edit profile brief"
                aria-pressed="false" aria-expanded="false" aria-controls="profileBriefForm">
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
              </button>
            </header>

            <div class="profile-brief-view" id="profileBriefView">
              <div class="profile-card profile-card--sidebar">
                <div class="profile-card-main">
                  <img class="profile-avatar" src="{{ $p->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'"
                    alt="Portrait of {{ $user->name }}" width="68" height="68" />
                  <div class="profile-card-info">
                    <h4 class="profile-name">
                      {{ $user->name }}
                      @if ($user->email_verified_at)
                        <i class="fa-solid fa-circle-check" title="Verified" aria-label="Verified"></i>
                      @endif
                    </h4>
                    <p class="profile-role">{{ $p->job?->name ?: 'Profession not added yet' }}</p>
                    <p class="profile-bio">{{ $p->about ?: "You haven't added a bio yet." }}</p>
                    <p class="profile-status is-{{ $p->status ?: 'available' }}">
                      <span class="status-dot" aria-hidden="true"></span>
                      {{ ucfirst($p->status ?: 'available') }}
                    </p>
                  </div>
                </div>
                <div class="profile-location">
                  <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                  <div>
                    <strong>{{ collect([$present?->district?->name, $present?->state?->name])->filter()->implode(', ') ?: 'Location not added yet' }}</strong>
                    <span>{{ $present?->district ? $present->district->name . ' District' : '' }}</span>
                  </div>
                </div>
                <div class="profile-contact">
                  <a href="tel:+91{{ $user->phone }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> +91
                    {{ $user->phone }}</a>
                  @if ($user->email)
                    <a href="mailto:{{ $user->email }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>
                      {{ $user->email }}</a>
                  @endif
                </div>
              </div>
            </div>

            <form class="profile-brief-form" id="profileBriefForm" data-section="brief" hidden>
              <label>
                <span>Full Name</span>
                <input type="text" name="name" value="{{ $user->name }}" maxlength="100" 
                  autocomplete="name" />
              </label>
              <label>
                <span>Profession</span>
                <input type="text" name="role" value="{{ $p->job?->name }}" maxlength="100" />
              </label>
              <label>
                <span>Bio</span>
                <textarea name="bio" rows="3" maxlength="300">{{ $p->about }}</textarea>
              </label>
              <label>
                <span>Availability</span>
                <select name="status">
                  <option value="available" @selected(($p->status ?: 'available') === 'available')>Available</option>
                  <option value="busy" @selected($p->status === 'busy')>Busy</option>
                </select>
              </label>
              <label>
                <span>Location</span>
                <input type="text" name="location" maxlength="100"
                  value="{{ collect([$present?->district?->name, $present?->state?->name])->filter()->implode(', ') }}" />
              </label>
              <label>
                <span>District</span>
                <input type="text" name="district" maxlength="100"
                  value="{{ $present?->district ? $present->district->name . ' District' : '' }}" />
              </label>
              @php
                $briefPhoneDigits = preg_replace('/\D+/', '', (string) $user->phone);
                $briefPhoneDigits = strlen($briefPhoneDigits) > 10 ? substr($briefPhoneDigits, -10) : $briefPhoneDigits;
              @endphp
              <label class="phone-edit-field">
                <span>Phone</span>
                <span class="phone-input-wrap">
                  <span class="phone-country-code" aria-hidden="true">+91</span>
                  <input type="tel" name="phone" value="{{ $briefPhoneDigits }}" inputmode="numeric"
                    maxlength="10" minlength="10" pattern="[0-9]{10}"
                    title="Enter a 10-digit mobile number" autocomplete="tel-national"
                    placeholder="10-digit mobile number" required />
                </span>
              </label>

              {{-- Email: the only field that needs verification --}}
              <div class="verify-wrap" data-email-verify data-original="{{ $user->email }}"
                data-verified="{{ $user->email_verified_at ? 1 : 0 }}">
                <label>
                  <span>Email</span>
                  <input type="email" name="email" value="{{ $user->email }}" maxlength="255"
                    autocomplete="email" />
                </label>
                <button type="button" class="verify-btn" data-verify="email" onclick="return window.cchubVerifyEmail(this)" data-send-url="{{ route('register.otp.send') }}" data-verify-url="{{ route('register.otp.verify') }}"
                  @if ($user->email_verified_at) hidden @endif>Verify</button>
              </div>

              <div class="profile-brief-form-actions">
                <button type="button" class="sidebar-btn-secondary" id="briefCancelBtn">Cancel</button>
                <button type="submit" class="sidebar-btn-primary">Save</button>
              </div>
            </form>
          </article>
        @else
          {{-- ---------- Connect + Similar members (visitor) ---------- --}}
          <article class="sidebar-card profile-brief-card" id="profile-brief-card">
            <div class="other-connect-block">
              <header class="sidebar-card-header">
                <h3>Connect</h3>
              </header>

              <div class="other-message-wrap">
                <button type="button" class="sidebar-btn-primary other-message-btn" id="sendMessageBtn"
                  aria-expanded="false" aria-controls="otherChatBox">
                  <i class="fa-regular fa-comments" aria-hidden="true"></i>
                  Send a Message
                </button>

                <div class="other-chat-box" id="otherChatBox" data-uuid="{{ $user->uuid }}" hidden>
                  <header class="other-chat-head">
                    <div class="other-chat-head-info">
                      <img src="{{ $p->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="" width="36"
                        height="36" />
                      <div>
                        <strong>{{ $user->name }}</strong>
                        <span>Usually replies within a day</span>
                      </div>
                    </div>
                    <button type="button" class="sidebar-icon-btn" id="closeChatBtn" aria-label="Close chat">
                      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                  </header>

                  <div class="other-chat-messages" id="otherChatMessages" role="log" aria-live="polite">
                    <p class="other-chat-empty" id="otherChatEmpty">
                      Start a conversation with {{ $user->first_name }}.
                    </p>
                  </div>

                  <form class="other-chat-compose" id="otherChatForm">
                    <label class="visually-hidden" for="otherChatInput">Message</label>
                    <textarea id="otherChatInput" name="message" rows="2" placeholder="Write a message…"
                      ></textarea>
                    <button type="submit" class="sidebar-btn-primary other-chat-send" aria-label="Send message">
                      <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    </button>
                  </form>
                </div>
              </div>

              <div class="other-social-links" aria-label="Social media profiles">
                <p class="other-social-label">Social Media Links</p>
                @if ($user->socialLinks->isNotEmpty())
                  <ul class="other-social-list">
                    @foreach ($user->socialLinks as $s)
                      <li>
                        <a href="{{ $s->url }}" class="other-social-link other-social-link--{{ $s->name }}" target="_blank"
                          rel="noopener noreferrer" aria-label="{{ ucfirst($s->name) }}">
                          <i class="fa-brands fa-{{ match ($s->name) { 'x' => 'x-twitter', 'facebook' => 'facebook-f', default => $s->name} }}"
                            aria-hidden="true"></i>
                        </a>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="other-social-label">No social media links added yet.</p>
                @endif
              </div>
            </div>

            <div class="similar-members" id="similar-members">
              <header class="sidebar-card-header similar-members-header">
                <h3>Similar Members</h3>
              </header>

              <ul class="profile-list similar-members-list">
                @forelse ($similar as $m)
                  <li class="profile-card">
                    <div class="profile-main">
                      <span class="profile-avatar-wrap">
                        <img class="profile-avatar" src="{{ $m->profile?->photo_url ?: $noImg }}"
                          onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="Portrait of {{ $m->name }}" width="72"
                          height="72" />
                      </span>
                      <div class="profile-info">
                        <h3 class="profile-name">
                          {{ $m->name }}
                          @if ($m->email_verified_at)
                            <i class="fa-solid fa-check" title="Verified" aria-label="Verified"></i>
                          @endif
                        </h3>
                        <p class="profile-role">{{ $m->profile?->job?->name ?: 'Profession not added yet' }}</p>
                        <p class="profile-bio">{{ $m->profile?->about ?: "This member hasn't added a bio yet." }}</p>
                        <p class="profile-status is-{{ $m->profile?->status ?: 'available' }}">
                          <span class="status-dot" aria-hidden="true"></span>
                          {{ ucfirst($m->profile?->status ?: 'available') }}
                        </p>
                      </div>
                    </div>
                    <div class="profile-card-meta">
                      <div class="profile-location">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <div>
                          @if ($m->presentAddress?->district || $m->presentAddress?->state)
                            <strong>{{ collect([$m->presentAddress?->district?->name, $m->presentAddress?->state?->name])->filter()->implode(', ') }}</strong>
                            <span>{{ $m->presentAddress?->district ? $m->presentAddress->district->name . ' District' : '' }}</span>
                          @else
                            <strong>Location not added yet</strong>
                            <span>Present address not provided</span>
                          @endif
                        </div>
                      </div>
                      <div class="profile-contact">
                        <a href="tel:+91{{ $m->phone }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> +91
                          {{ $m->phone }}</a>
                        @if ($m->email)
                          <a href="mailto:{{ $m->email }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            {{ $m->email }}</a>
                        @endif
                        <button type="button" class="profile-appointment">
                          <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                          Need an appointment
                        </button>
                      </div>
                    </div>
                    <a href="{{ route('members.show', $m->uuid) }}" class="profile-open"
                      aria-label="View {{ $m->name }} profile">
                      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </a>
                  </li>
                @empty
                  <li class="profile-card">
                    <div class="profile-main">
                      <div class="profile-info">
                        <h3 class="profile-name">No similar members yet</h3>
                        <p class="profile-bio">Members with the same profession will appear here.</p>
                      </div>
                    </div>
                  </li>
                @endforelse
              </ul>
            </div>
          </article>
        @endif

        {{-- ---------- Work details ---------- --}}
        @if ($p->work_description || $isOwner)
          <article class="sidebar-card work-details-card" id="work-details-card">
            <header class="sidebar-card-header">
              <h3>Work Details</h3>
              @if ($isOwner)
                <button type="button" class="sidebar-icon-btn" id="workEditBtn" aria-label="Edit work description"
                  aria-pressed="false">
                  <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </button>
              @endif
            </header>

            <div class="work-summary-view" id="workDetailsView">
              <p class="work-summary-meta" id="workSummaryMeta">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                <span class="work-summary-meta-text"
                  id="workSummaryMetaText">{{ collect([$p->job?->name, $p->job?->category?->name, collect([$present?->district?->name, $present?->state?->name])->filter()->implode(', ')])->filter()->implode(' · ') ?: 'Work details not added yet' }}</span>
              </p>
              <p class="work-summary-text" id="workSummaryText">
                {{ $p->work_description ?: 'No work description added yet.' }}
              </p>
              @if ($isOwner)
                <div class="profile-brief-form-actions work-summary-actions" id="workSummaryActions" hidden>
                  <button type="button" class="sidebar-btn-secondary" id="workCancelBtn">Cancel</button>
                  <button type="button" class="sidebar-btn-primary" id="workSaveBtn">Save</button>
                </div>
              @endif
            </div>
          </article>
        @endif

        @if ($isOwner)
          {{-- ---------- Change password link ---------- --}}
          <a href="#change-password" class="sidebar-settings-link" id="changePasswordLink">
            <span class="sidebar-settings-icon"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
            <span class="sidebar-settings-copy">
              <strong>Change Password</strong>
              <small>Update your account security settings</small>
            </span>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
          </a>

          {{-- ---------- Social links manager ---------- --}}
          <article class="sidebar-card social-links-card" id="social-links-card">
            <header class="sidebar-card-header">
              <h3>Social Media Links</h3>
              <button type="button" class="sidebar-add-btn" id="addSocialBtn">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                Add Another
              </button>
            </header>

            <div class="social-links-table" role="table" aria-label="Social media profiles">
              <div class="social-links-head" role="row">
                <span role="columnheader">Platform</span>
                <span role="columnheader">Profile URL</span>
                <span role="columnheader"><span class="visually-hidden">Actions</span></span>
              </div>
              <div class="social-links-body" id="socialLinksBody">
                @forelse ($user->socialLinks as $s)
                  <div class="social-link-row" role="row" data-social-row data-id="{{ $s->id }}">
                    <span class="social-platform" role="cell" data-field="platform">{{ ucfirst($s->name) }}</span>
                    <a class="social-url" role="cell" href="{{ $s->url }}" target="_blank" rel="noopener noreferrer"
                      data-field="url">{{ preg_replace('#^https?://(www\.)?#', '', $s->url) }}</a>
                    <span class="social-link-actions" role="cell">
                      <button type="button" class="sidebar-icon-btn social-edit-btn"
                        aria-label="Edit {{ ucfirst($s->name) }} profile">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                      </button>
                      <button type="button" class="sidebar-icon-btn social-delete-btn"
                        aria-label="Delete {{ ucfirst($s->name) }} profile">
                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                      </button>
                    </span>
                  </div>
                @empty
                  <p class="other-social-label" id="socialEmpty">No social media links added yet.</p>
                @endforelse
              </div>
            </div>
          </article>
        @endif
      </aside>
    </div>

    {{-- ===================== GALLERY ===================== --}}
    <section class="profile-panel gallery-panel" aria-labelledby="gallery-heading">
      <header class="profile-panel-header gallery-header">
        <h2 id="gallery-heading">
          <i class="fa-regular fa-image" aria-hidden="true"></i>
          Gallery
        </h2>
        @if ($isOwner)
          <button type="button" class="gallery-add-btn">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Add Image
          </button>
        @endif
      </header>

      @if ($user->galleryImages->isNotEmpty())
        <div class="gallery-grid">
          @foreach ($user->galleryImages as $g)
            <figure class="gallery-item">
              <img src="{{ asset('storage/' . $g->path) }}" onerror="this.onerror=null;this.src='{{ $noImg }}'"
                alt="{{ $g->title ?: 'Gallery image ' . $loop->iteration }}" />
            </figure>
          @endforeach
        </div>

        @if ($user->galleryImages->count() > 10)
          <button type="button" class="gallery-view-more">
            View More
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </button>
        @endif
      @else
        <div class="gallery-grid">
          <p>No images added yet.</p>
        </div>
      @endif
    </section>
  </main>

  {{-- Gallery lightbox --}}
  <div class="gallery-lightbox" id="galleryLightbox" hidden>
    <div class="gallery-lightbox-backdrop" data-gallery-close tabindex="-1" aria-hidden="true"></div>
    <div class="gallery-lightbox-dialog" role="dialog" aria-modal="true" aria-label="Gallery image viewer">
      <button type="button" class="gallery-lightbox-close" data-gallery-close aria-label="Close gallery">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>

      <div class="gallery-lightbox-frame">
        <button type="button" class="gallery-lightbox-nav is-prev" id="galleryLightboxPrev" aria-label="Previous image">
          <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <div class="gallery-lightbox-stage">
          <img id="galleryLightboxImage" class="is-active" src="" alt="" />
        </div>
        <button type="button" class="gallery-lightbox-nav is-next" id="galleryLightboxNext" aria-label="Next image">
          <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
      </div>

      <div class="gallery-lightbox-meta">
        <p class="gallery-lightbox-caption" id="galleryLightboxCaption"></p>
        <p class="gallery-lightbox-counter">
          <span id="galleryLightboxIndex">1</span> /
          <span id="galleryLightboxTotal">0</span>
        </p>
      </div>
    </div>
  </div>

  @if ($isOwner)
    {{-- ===================== EMAIL OTP VERIFY (owner) ===================== --}}
    <dialog class="password-dialog" id="emailOtpDialog" aria-labelledby="email-otp-title">
      <form method="dialog" class="password-dialog-form" id="emailOtpForm">
        <header class="password-dialog-header">
          <div class="password-dialog-heading">
            <span class="password-dialog-badge" aria-hidden="true"><i class="fa-solid fa-envelope-circle-check"></i></span>
            <div>
              <h2 id="email-otp-title">Verify Email</h2>
              <p class="password-dialog-subtitle" id="emailOtpSubtitle">Enter the 6-digit code sent to your email</p>
            </div>
          </div>
          <button type="button" class="password-dialog-close" id="closeEmailOtp" aria-label="Close">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          </button>
        </header>

        <div class="password-dialog-fields">
          <label class="password-field">
            <span>Verification Code</span>
            <span class="password-input-wrap">
              <i class="fa-solid fa-key password-input-icon" aria-hidden="true"></i>
              <input type="text" id="emailOtpInput" name="otp" inputmode="numeric" maxlength="6" pattern="\d{6}"
                autocomplete="one-time-code" placeholder="000000"  />
            </span>
          </label>
          <p class="password-tips" id="emailOtpMsg" aria-live="polite" hidden></p>
        </div>

        <div class="password-dialog-actions">
          <button type="button" class="password-btn-cancel" id="emailOtpResend">Resend code</button>
          <button type="submit" class="password-btn-submit">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Verify
          </button>
        </div>
      </form>
    </dialog>
  @endif
@endsection

{{-- ===================== OWNER OVERLAYS (after footer) ===================== --}}
@section('after_footer')
  @if ($isOwner)
    {{-- ===================== MESSAGES PANEL (owner) ===================== --}}
    <div class="msg-panel-root" id="messagesPanel" hidden>
      <div class="msg-panel-backdrop" data-msg-close tabindex="-1" aria-hidden="true"></div>
      <aside class="msg-panel" role="dialog" aria-modal="true" aria-labelledby="messages-panel-title">
        <div class="msg-panel-view" id="msgListView">
          <header class="msg-panel-header">
            <h2 id="messages-panel-title">Messages</h2>
            <button type="button" class="msg-panel-close" data-msg-close aria-label="Close messages">
              <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
          </header>
          <ul class="msg-list" id="msgConversationList" role="list">
            @forelse ($conversations as $c)
              <li>
                <button type="button" class="msg-row" data-chat-id="{{ $c->uuid }}" data-name="{{ $c->name }}"
                  data-avatar="{{ $c->photo_url }}" data-status="{{ $c->preview }}" @if ($c->online) data-online="true" @endif
                  @if ($c->unread) data-unread="true" @endif>
                  <span class="msg-avatar-wrap {{ $c->unread ? 'is-story' : '' }}">
                    <img src="{{ $c->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="" width="56"
                      height="56" />
                    @if ($c->online)
                      <span class="msg-online-dot" aria-hidden="true"></span>
                    @endif
                  </span>
                  <span class="msg-row-copy">
                    <span class="msg-row-name">{{ $c->name }}</span>
                    <span class="msg-row-preview {{ $c->unread ? 'is-unread' : '' }}">{{ $c->preview }}</span>
                  </span>
                </button>
              </li>
            @empty
              <li class="msg-empty">
                <p>No conversations yet.</p>
              </li>
            @endforelse
          </ul>
        </div>

        <div class="msg-panel-view" id="msgChatView" hidden>
          <header class="msg-chat-header">
            <button type="button" class="msg-chat-back" id="msgChatBack" aria-label="Back to messages">
              <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </button>
            <div class="msg-chat-person">
              <img id="msgChatAvatar" src="{{ $noImg }}" alt="" width="40" height="40" />
              <div>
                <strong id="msgChatName">Contact</strong>
                <span id="msgChatStatus">Active now</span>
              </div>
            </div>
            <button type="button" class="msg-panel-close" data-msg-close aria-label="Close messages">
              <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
          </header>

          <div class="msg-chat-log" id="msgChatLog" aria-live="polite"></div>
          @foreach ($conversations as $c)
            <div class="msg-thread" data-thread-id="{{ $c->uuid }}" hidden>
              @php $lastDay = null; @endphp
              @foreach ($c->messages as $msg)
                @if ($lastDay !== $msg->day_label)
                  @php $lastDay = $msg->day_label; @endphp
                  <div class="msg-day-sep" role="separator"><time>{{ $msg->day_label }}</time></div>
                @endif
                <div class="msg-bubble {{ $msg->is_out ? 'is-out' : 'is-in' }}">
                  <p class="msg-bubble-text">{{ $msg->body }}</p>
                  <div class="msg-bubble-meta">
                    <time>{{ $msg->time_label }}</time>
                    @if ($msg->is_out)
                      @php
                        $st = $msg->status ?? 'sent';
                        $stLabel = ['sent' => 'Sent', 'delivered' => 'Delivered', 'read' => 'Seen'][$st] ?? 'Sent';
                      @endphp
                      <span class="msg-ticks is-{{ $st === 'read' ? 'read' : $st }}" data-status="{{ $st }}"
                        title="{{ $stLabel }}" aria-label="{{ $stLabel }}">
                        <i class="fa-solid {{ $st === 'sent' ? 'fa-check' : 'fa-check-double' }}" aria-hidden="true"></i>
                      </span>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endforeach

          <div class="msg-attach-preview" id="msgAttachPreview" aria-live="polite"></div>
          <form class="msg-compose" id="msgChatForm" autocomplete="off">
            <input type="file" id="msgFileInput" class="msg-file-input" accept="image/*,.pdf,.doc,.docx,.txt" multiple
              tabindex="-1" aria-hidden="true" />
            <button type="button" class="msg-icon-btn" id="msgAttachBtn" aria-label="Attach file or image">
              <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
            </button>
            <textarea id="msgChatInput" rows="1" placeholder="Message…" aria-label="Type a message"></textarea>
            <button type="submit" class="msg-icon-btn is-send" id="msgSendBtn" aria-label="Send message">
              <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
          </form>
        </div>{{-- /msgChatView --}}
      </aside>
    </div>{{-- /msg-panel-root --}}

    {{-- ===================== CHANGE PASSWORD (owner) ===================== --}}
    <dialog class="password-dialog" id="changePasswordDialog" aria-labelledby="change-password-title">
      <form method="dialog" class="password-dialog-form" id="changePasswordForm">
        <header class="password-dialog-header">
          <div class="password-dialog-heading">
            <span class="password-dialog-badge" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
            <div>
              <h2 id="change-password-title">Change Password</h2>
              <p class="password-dialog-subtitle">Keep your account secure by using a strong password</p>
            </div>
          </div>
          <button type="button" class="password-dialog-close" id="closePasswordDialog" aria-label="Close">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          </button>
        </header>

        <div class="password-dialog-body">
          <aside class="password-dialog-aside" aria-hidden="true">
            <div class="password-hero">
              <span class="password-hero-orbit"></span>
              <span class="password-hero-orbit password-hero-orbit--inner"></span>
              <span class="password-hero-float password-hero-float--shield"><i class="fa-solid fa-shield"></i></span>
              <span class="password-hero-float password-hero-float--lock"><i class="fa-solid fa-lock"></i></span>
              <div class="password-hero-lock"><i class="fa-solid fa-lock"></i></div>
              <div class="password-hero-mask" aria-hidden="true">* * * *</div>
            </div>
            <div class="password-tips">
              <strong><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Password Tips</strong>
              <p>Use a combination of letters, numbers and special characters for stronger security.</p>
            </div>
          </aside>

          <div class="password-dialog-fields">
            <label class="password-field">
              <span>Current Password</span>
              <span class="password-input-wrap">
                <i class="fa-solid fa-lock password-input-icon password-input-icon--current" aria-hidden="true"></i>
                <input type="password" name="current" id="pwCurrent" autocomplete="current-password"
                  placeholder="Enter your current password" maxlength="64"  />
                <button type="button" class="password-toggle" data-toggle-password="pwCurrent"
                  aria-label="Show current password">
                  <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
              </span>
            </label>

            <label class="password-field">
              <span>New Password</span>
              <span class="password-input-wrap">
                <i class="fa-solid fa-lock password-input-icon password-input-icon--new" aria-hidden="true"></i>
                <input type="password" name="new" id="pwNew" autocomplete="new-password"
                  placeholder="Enter your new password" minlength="6" maxlength="64"  />
                <button type="button" class="password-toggle" data-toggle-password="pwNew" aria-label="Show new password">
                  <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
              </span>
              <div class="password-strength" id="passwordStrength" aria-live="polite">
                <div class="password-strength-bars" aria-hidden="true"><span></span><span></span><span></span></div>
                <em class="password-strength-label">Weak</em>
              </div>
            </label>

            <label class="password-field">
              <span>Confirm New Password</span>
              <span class="password-input-wrap">
                <i class="fa-solid fa-lock password-input-icon password-input-icon--confirm" aria-hidden="true"></i>
                <input type="password" name="confirm" id="pwConfirm" autocomplete="new-password"
                  placeholder="Re-enter your new password" minlength="6" maxlength="64"  />
                <button type="button" class="password-toggle" data-toggle-password="pwConfirm"
                  aria-label="Show confirm password">
                  <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
              </span>
            </label>
          </div>
        </div>

        <div class="password-dialog-actions">
          <button type="button" class="password-btn-cancel" id="cancelPasswordBtn">Cancel</button>
          <button type="submit" class="password-btn-submit">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            Update Password
          </button>
        </div>
      </form>
    </dialog>


    {{-- Dependent Job options source (category -> jobs), used by the Job Details edit form --}}
    <div id="profileFieldOptionSources" hidden>
      @foreach ($jobsByCategory as $category => $jobs)
        <select data-job-category="{{ $category }}" aria-hidden="true">
          @foreach ($jobs as $job)
            <option value="{{ $job }}">{{ $job }}</option>
          @endforeach
        </select>
      @endforeach
    </div>
  @endif
@endsection

{{-- ===================== PAGE SCRIPTS ===================== --}}
@section('scripts')
  <script>
    window.CCHUB = {
      csrf: @json(csrf_token()),
      noImg: @json($noImg),
      isOwner: @json((bool) $isOwner),
      listUrl: @json(route('messages.index')),
      threadUrl: @json(route('messages.thread', '__ID__')),
      sendUrl: @json(route('messages.send', '__ID__')),
      adminReadUrl: @json(route('admin-messages.read')),
      shareUrl: @json(route('share.members')),
      shareSendUrl: @json(route('members.share', '__ID__')),
    };
  </script>
  <script src="{{ asset('js/cchub/ch-profile.js') }}"></script>
  <script src="{{ asset('js/cchub/profile-save.js') }}"></script>
  @unless ($isOwner)
    <script src="{{ asset('js/cchub/ch-other-profile.js') }}"></script>
  @endunless
  <script src="{{ asset('js/cchub/profile-social.js') }}"></script>
  @if ($isOwner)
    <script src="{{ asset('js/cchub/profile-limits.js') }}?v={{ file_exists(public_path('js/cchub/profile-limits.js')) ? filemtime(public_path('js/cchub/profile-limits.js')) : '1' }}"></script>
  @endif
  @if ($isOwner)
    <script>
      /* Custom dropdown: always opens DOWNWARD (native selects open upward near the screen bottom) */
      (function () {
        var openCd = null;
        function sync(cd) {
          var sel = cd._sel, o = sel.options[sel.selectedIndex], ph = !o || !o.value;
          cd._label.textContent = o ? o.text : 'Choose one';
          cd._label.classList.toggle('is-placeholder', ph);
          [].forEach.call(cd._list.children, function (li) {
            li.setAttribute('aria-selected', li.dataset.value === sel.value ? 'true' : 'false');
          });
        }
        function place(cd) {
          var w = (cd.closest('.phone-input-wrap') || cd).getBoundingClientRect();
          cd._list.style.minWidth = w.width + 'px';
          cd._list.style.left = w.left + 'px';
          cd._list.style.top = (w.bottom + 4) + 'px';
        }
        function closeAll() {
          if (!openCd) return;
          openCd._list.hidden = true;
          openCd.classList.remove('is-open');
          openCd._btn.setAttribute('aria-expanded', 'false');
          openCd = null;
        }
        function openIt(cd) {
          closeAll();
          cd._list.hidden = false;
          place(cd);
          var over = cd._list.getBoundingClientRect().bottom + 8 - window.innerHeight;
          if (over > 0) { window.scrollBy(0, over); place(cd); }
          cd.classList.add('is-open');
          cd._btn.setAttribute('aria-expanded', 'true');
          openCd = cd;
        }
        function choose(cd, li) {
          cd._sel.value = li.dataset.value;
          cd._sel.dispatchEvent(new Event('input', { bubbles: true }));
          cd._sel.dispatchEvent(new Event('change', { bubbles: true }));
          sync(cd); closeAll(); cd._btn.focus();
        }
        function enhance(sel) {
          if (sel._cd) return;
          var cd = document.createElement('div'); cd.className = 'cd';
          var btn = document.createElement('button');
          btn.type = 'button'; btn.className = 'cd-btn';
          btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false');
          if (sel.id) { btn.id = sel.id; sel.removeAttribute('id'); }
          btn.innerHTML = '<span class="cd-label"></span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i>';
          var list = document.createElement('ul');
          list.className = 'cd-list'; list.setAttribute('role', 'listbox'); list.hidden = true;
          [].forEach.call(sel.options, function (o) {
            if (!o.value) return;
            var li = document.createElement('li');
            li.setAttribute('role', 'option'); li.dataset.value = o.value; li.textContent = o.text;
            list.appendChild(li);
          });
          sel.parentNode.insertBefore(cd, sel);
          cd.appendChild(sel); cd.appendChild(btn); cd.appendChild(list);
          sel.classList.add('cd-native'); sel.tabIndex = -1; sel.setAttribute('aria-hidden', 'true');
          cd._sel = sel; cd._btn = btn; cd._list = list; cd._label = btn.firstChild; sel._cd = cd;
          sel.addEventListener('change', function () { sync(cd); });
          sync(cd);
        }
        function syncAll() {
          document.querySelectorAll('.cd').forEach(sync);
        }
        function init() { document.querySelectorAll('select[data-custom-select]').forEach(enhance); }

        document.addEventListener('click', function (e) {
          var btn = e.target.closest && e.target.closest('.cd-btn');
          var li = e.target.closest && e.target.closest('.cd-list li');
          if (btn) { var cd = btn.closest('.cd'); if (openCd === cd) closeAll(); else openIt(cd); }
          else if (li) { choose(li.closest('.cd'), li); }
          else closeAll();
          setTimeout(syncAll, 0); // keep label right after Cancel / re-open
        });
        document.addEventListener('keydown', function (e) {
          var cd = openCd || (e.target.closest && e.target.closest('.cd'));
          if (!cd) return;
          var items = [].slice.call(cd._list.children), cur = cd._list.querySelector('.is-active');
          var i = items.indexOf(cur);
          if (e.key === 'Escape') { closeAll(); }
          else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (!openCd) openIt(cd);
            if (cur) cur.classList.remove('is-active');
            i = e.key === 'ArrowDown' ? Math.min(i + 1, items.length - 1) : Math.max(i - 1, 0);
            items[i].classList.add('is-active');
          } else if (e.key === 'Enter' && openCd && cur) { e.preventDefault(); choose(cd, cur); }
        });
        window.addEventListener('resize', closeAll);
        window.addEventListener('scroll', function () { if (openCd) place(openCd); }, true);

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
      })();
    </script>
  @endif
  @if ($isOwner)
    <script>
      (function () {
        console.log('[email-verify] ready');
        var CSRF = @json(csrf_token());
        var pending = { email: '', sendUrl: '', verifyUrl: '' };
        var timer = null;
        var $ = function (id) { return document.getElementById(id); };
        var field = function (wrap) { return wrap.querySelector('input:not([type="hidden"])'); };
        var validEmail = function (v) { return /^[^\s\x40]+\x40[^\s\x40]+\.[^\s\x40]+$/.test(v); };

        function post(url, data) {
          return fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': CSRF,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
          }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (json) {
              if (!res.ok) {
                var first = json.errors ? (Object.values(json.errors)[0] || [])[0] : null;
                throw new Error(first || json.message || ('Request failed (' + res.status + ')'));
              }
              return json;
            });
          });
        }

        function say(text, isError) {
          var msg = $('emailOtpMsg');
          if (!msg) return;
          msg.hidden = !text;
          msg.textContent = text || '';
          msg.style.color = isError ? '#c0392b' : '';
        }

        function cooldown(sec) {
          var resend = $('emailOtpResend');
          clearInterval(timer);
          var left = sec || 30;
          resend.disabled = true;
          resend.textContent = 'Resend in ' + left + 's';
          timer = setInterval(function () {
            left--;
            if (left <= 0) {
              clearInterval(timer);
              resend.disabled = false;
              resend.textContent = 'Resend code';
            } else {
              resend.textContent = 'Resend in ' + left + 's';
            }
          }, 1000);
        }

        function sendCode() {
          return post(pending.sendUrl, { email: pending.email }).then(function () { cooldown(30); });
        }

        function refreshButton(wrap) {
          var btn = wrap.querySelector('.verify-btn');
          var inp = field(wrap);
          if (!btn || !inp) return;
          var val = inp.value.trim().toLowerCase();
          var same = val === (wrap.dataset.original || '').trim().toLowerCase();
          btn.hidden = (wrap.dataset.verified === '1' && same) || val === '';
        }

        function setVerified(email) {
          document.querySelectorAll('[data-email-verify]').forEach(function (w) {
            w.dataset.original = email;
            w.dataset.verified = '1';
            var i = field(w);
            if (i) i.value = email;
            refreshButton(w);
          });
        }

        /* called by onclick on the Verify button */
        window.cchubVerifyEmail = function (btn) {
          console.log('[email-verify] clicked');
          var dlg = $('emailOtpDialog');
          if (!dlg) { alert('Verification popup is missing on this page. Refresh and try again.'); return false; }

          var wrap = btn.closest('[data-email-verify]');
          var email = field(wrap).value.trim().toLowerCase();
          if (!validEmail(email)) { alert('Enter a valid email address first.'); return false; }

          pending = { email: email, sendUrl: btn.dataset.sendUrl, verifyUrl: btn.dataset.verifyUrl };
          var label = btn.textContent;
          btn.disabled = true;
          btn.textContent = 'Sending...';

          sendCode().then(function () {
            $('emailOtpSubtitle').textContent = 'Enter the 6-digit code sent to ' + email;
            $('emailOtpInput').value = '';
            say('');
            if (!dlg.open) dlg.showModal();
            $('emailOtpInput').focus();
          }).catch(function (err) {
            console.error('[email-verify] send failed', err);
            alert(err.message);
          }).then(function () {
            btn.disabled = false;
            btn.textContent = label;
          });
          return false;
        };

        document.addEventListener('input', function (e) {
          var wrap = e.target.closest && e.target.closest('[data-email-verify]');
          if (wrap && e.target === field(wrap)) refreshButton(wrap);
          if (e.target.id === 'emailOtpInput') {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
          }
        });

        document.addEventListener('click', function (e) {
          if (e.target.closest && e.target.closest('#emailOtpResend')) {
            sendCode().then(function () { say('A new code has been sent.'); })
              .catch(function (err) { say(err.message, true); });
          }
          if (e.target.closest && e.target.closest('#closeEmailOtp')) { $('emailOtpDialog').close(); }
        });

        document.addEventListener('submit', function (e) {
          if (e.target.id !== 'emailOtpForm') return;
          e.preventDefault();
          post(pending.verifyUrl, { email: pending.email, code: $('emailOtpInput').value.trim() })
            .then(function (r) { setVerified(r.email || pending.email); $('emailOtpDialog').close(); })
            .catch(function (err) { say(err.message, true); });
        });
      })();
    </script>
  @endif
@endsection