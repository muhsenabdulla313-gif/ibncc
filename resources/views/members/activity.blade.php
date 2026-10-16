{{-- resources/views/members/activity.blade.php --}}
@extends('layouts.network')

@php
  $noImg = asset('assets/images/no-image.avif');
  $me = auth()->user();
@endphp

@section('title')My Activity — Networking Activities | CC Hub @endsection

@section('meta')
  <meta name="description" content="Manage your saved, shared, and blocked profiles on CC Hub." />
@endsection

@section('page_css')
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-profile.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-activity.css') }}" />
@endsection

@section('body_class')profile-page page-profile page-activity @endsection
@section('nav_active', 'none')
@section('no_login_modals')@endsection

@section('after_header')
  <nav class="app-nav" aria-label="Profile navigation">
    <div class="app-nav-inner">
      <a href="{{ route('members.show', $me->uuid) }}#messages" class="app-nav-item" aria-label="Messages">
        <span class="app-nav-icon app-nav-icon--messages"><i class="fa-regular fa-comments" aria-hidden="true"></i></span>
        <span class="app-nav-label">Messages</span>
      </a>
      <a href="{{ route('members.show', $me->uuid) }}#admin-messages" class="app-nav-item" aria-label="Admin Messages">
        <span class="app-nav-icon app-nav-icon--admin"><i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i></span>
        <span class="app-nav-label">Admin Messages</span>
      </a>
      <a href="{{ route('members.show', $me->uuid) }}" class="app-nav-item" aria-label="Your profile" aria-haspopup="menu" aria-expanded="false">
        <span class="app-nav-avatar">
          <img src="{{ $p->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="" width="52" height="52" />
        </span>
        <span class="app-nav-label">Profile</span>
      </a>
    </div>

    <div class="app-nav-dropdown" id="appNavDropdown" hidden role="menu" aria-label="Account menu">
      <a href="{{ url('activity') }}" class="app-nav-dropdown-item is-active" role="menuitem" data-nav-action="activity">
        <span class="app-nav-dropdown-icon" aria-hidden="true"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <span>Activity</span>
      </a>
      <a href="#logout" class="app-nav-dropdown-item app-nav-dropdown-item--logout" role="menuitem"
         data-nav-action="logout-server"
         onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
        <span class="app-nav-dropdown-icon" aria-hidden="true"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span>Logout</span>
      </a>
      <form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden>@csrf</form>
    </div>
  </nav>
@endsection

@section('page')
<main class="activity-main" id="main-content">
  <div class="activity-container">
    <nav class="activity-breadcrumbs" aria-label="Breadcrumbs">
      <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Home</a>
      <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
      <a href="{{ url('networking') }}">Networking</a>
      <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
      <span class="current">My Activity</span>
    </nav>

    <div class="activity-layout-grid">
      <aside class="activity-sidebar" aria-label="Activity Categories">
        <div class="activity-sidebar-card">
          <h2 class="activity-sidebar-title">My Activity</h2>
          <ul class="activity-tabs-list" role="tablist" aria-label="Activity Sections">
            <li role="presentation">
              <button type="button" class="activity-tab-btn is-active" id="tab-saved" role="tab" aria-selected="true"
                aria-controls="panel-saved" data-tab="saved" data-title="Saved Profiles"
                data-subtitle="Profiles you have bookmarked for quick access and collaboration."
                data-label="Saved Members" data-icon="fa-user-check">
                <span class="activity-tab-icon" aria-hidden="true"><i class="fa-solid fa-bookmark"></i></span>
                <span class="activity-tab-label">Saved Profiles</span>
                <span class="activity-tab-badge" id="savedCountBadge">{{ $saved->count() }}</span>
              </button>
            </li>
            <li role="presentation">
              <button type="button" class="activity-tab-btn" id="tab-shared" role="tab" aria-selected="false"
                aria-controls="panel-shared" data-tab="shared" data-title="Shared Profiles"
                data-subtitle="Profiles you have shared with other members and business groups."
                data-label="Shared Profiles" data-icon="fa-share-nodes">
                <span class="activity-tab-icon" aria-hidden="true"><i class="fa-solid fa-share-nodes"></i></span>
                <span class="activity-tab-label">Shared Profiles</span>
                <span class="activity-tab-badge" id="sharedCountBadge">{{ $shared->count() }}</span>
              </button>
            </li>
            <li role="presentation">
              <button type="button" class="activity-tab-btn" id="tab-blocked" role="tab" aria-selected="false"
                aria-controls="panel-blocked" data-tab="blocked" data-title="Blocked Profiles"
                data-subtitle="Members currently blocked from interacting with your profile."
                data-label="Blocked Members" data-icon="fa-ban">
                <span class="activity-tab-icon" aria-hidden="true"><i class="fa-solid fa-ban"></i></span>
                <span class="activity-tab-label">Blocked Profiles</span>
                <span class="activity-tab-badge" id="blockedCountBadge">{{ $blocked->count() }}</span>
              </button>
            </li>
          </ul>
        </div>
      </aside>

      <section class="activity-content" aria-labelledby="activitySectionTitle">
        <div class="activity-content-card">
          <div class="activity-content-header">
            <div class="activity-header-info">
              <h1 class="activity-section-title" id="activitySectionTitle">Saved Profiles</h1>
              <p class="activity-section-subtitle" id="activitySectionSubtitle">Profiles you have bookmarked for quick access and collaboration.</p>
            </div>
            <div class="activity-count-pill" id="activityHeaderBadge">
              <i class="fa-solid fa-user-check" id="activityHeaderIcon"></i>
              <span id="activityHeaderBadgeText">{{ $saved->count() }} Saved Members</span>
            </div>
          </div>

          {{-- SAVED --}}
          <div class="activity-tab-panel" id="panel-saved" role="tabpanel" aria-labelledby="tab-saved">
            <ul class="profile-list" id="savedProfileList">
              @foreach ($saved as $m)
                @include('members._activity-card', ['m' => $m, 'mode' => 'saved'])
              @endforeach
              <li class="activity-empty" @if ($saved->isNotEmpty()) hidden @endif>
                <p>No saved profiles yet. Tap “Save Profile” on any member to see them here.</p>
              </li>
            </ul>
          </div>

          {{-- SHARED --}}
          <div class="activity-tab-panel" id="panel-shared" role="tabpanel" aria-labelledby="tab-shared" hidden>
            <ul class="profile-list" id="sharedProfileList">
              @foreach ($shared as $s)
                @include('members._activity-card', ['m' => $s->target, 'mode' => 'shared', 'date' => $s->created_at, 'to' => $s->recipient?->name])
              @endforeach
              <li class="activity-empty" @if ($shared->isNotEmpty()) hidden @endif>
                <p>You haven't shared any profiles yet.</p>
              </li>
            </ul>
          </div>

          {{-- BLOCKED --}}
          <div class="activity-tab-panel" id="panel-blocked" role="tabpanel" aria-labelledby="tab-blocked" hidden>
            <div class="activity-notice-banner">
              <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
              <div>
                <strong>Blocked Members</strong>
                <p style="margin:3px 0 0 0;">Blocked profiles cannot view your profile or message you on CC Hub.</p>
              </div>
            </div>
            <ul class="profile-list" id="blockedProfileList">
              @foreach ($blocked as $m)
                @include('members._activity-card', ['m' => $m, 'mode' => 'blocked', 'date' => $m->pivot->created_at])
              @endforeach
              <li class="activity-empty" @if ($blocked->isNotEmpty()) hidden @endif>
                <p>You haven't blocked anyone.</p>
              </li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </div>
</main>

<div class="activity-toast" id="activityToast" role="status" aria-live="polite">
  <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
  <span id="activityToastText">Done</span>
</div>
@endsection

@section('scripts')
  <script>
    window.CCHUB = {
      csrf: @json(csrf_token()),
      saveUrl: @json(route('members.save', '__ID__')),
      blockUrl: @json(route('members.block', '__ID__')),
    };
  </script>
  <script src="{{ asset('js/cchub/ch-profile.js') }}"></script>
  <script src="{{ asset('js/cchub/activity-live.js') }}"></script>
@endsection