{{-- resources/views/members/activity.blade.php --}}
@extends('layouts.network')

@php
  $noImg = asset('assets/images/no-image.avif');
  $me = auth()->user();
  $p = $p ?? $me->profile;

  // badges, same numbers as the profile page
  $adminMessages = \App\Models\AdminMessage::where('user_id', $me->id)->latest()->limit(20)->get();
  $conversations = \App\Models\Message::conversationsFor($me);
  $unreadAdmin = $adminMessages->whereNull('read_at')->count();
  $unreadChats = $conversations->where('unread', true)->count();
  $profileUrl = route('members.show', $me->uuid);
@endphp

@section('title')My Activity — Networking Activities | CC Hub @endsection

@section('meta')
  <meta name="description" content="Manage your saved, shared, and blocked profiles on CC Hub." />
@endsection

@section('page_css')
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-profile.css') }}" />
  {{-- styles for the Messages panel + Admin Messages dropdown (same file the profile page uses) --}}
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-profile-page.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-activity.css') }}" />
@endsection

@section('body_class')profile-page page-profile page-activity @endsection
@section('nav_active', 'none')
@section('no_login_modals')@endsection
@section('body_attrs')data-owner="1" data-update-url="{{ route('profile.update') }}"@endsection

@section('after_header')
  <nav class="app-nav" aria-label="Profile navigation">
    <div class="app-nav-inner">
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

      <a href="{{ $profileUrl }}" class="app-nav-item" aria-label="Your profile" aria-haspopup="menu" aria-expanded="false">
        <span class="app-nav-avatar">
          <img src="{{ $p?->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'" alt="" width="52" height="52" />
        </span>
        <span class="app-nav-label">Profile</span>
      </a>
    </div>

    <div class="app-nav-dropdown" id="appNavDropdown" hidden role="menu" aria-label="Account menu">
      <a href="{{ route('activity') }}" class="app-nav-dropdown-item is-active" role="menuitem" data-nav-action="activity">
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

@section('after_footer')
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
@endsection

@section('scripts')
  <script>
    window.CCHUB = {
      csrf: @json(csrf_token()),
      noImg: @json($noImg),
      isOwner: true,
      listUrl: @json(route('messages.index')),
      threadUrl: @json(route('messages.thread', '__ID__')),
      sendUrl: @json(route('messages.send', '__ID__')),
      adminReadUrl: @json(route('admin-messages.read')),
      shareUrl: @json(route('share.members')),
      shareSendUrl: @json(route('members.share', '__ID__')),
      saveUrl: @json(route('members.save', '__ID__')),
      blockUrl: @json(route('members.block', '__ID__')),
    };
  </script>
  <script src="{{ asset('js/cchub/ch-profile.js') }}"></script>
  <script src="{{ asset('js/cchub/activity-live.js') }}?v={{ file_exists(public_path('js/cchub/activity-live.js')) ? filemtime(public_path('js/cchub/activity-live.js')) : '1' }}"></script>
@endsection