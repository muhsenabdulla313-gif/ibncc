{{-- resources/views/members/show.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $user->name }} — Member Profile | CC Hub</title>
  <meta name="description" content="View {{ $user->name }}'s public profile on CC Hub — send a message and discover similar members." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-styles.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-profile.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/cchub/ch-other-profile.css') }}" />
</head>
<body class="profile-page other-profile-page page-profile">

  {{-- ================= HEADER ================= --}}
  <header class="site-header" id="site-header">
    <div class="header-inner">
      <a href="{{ url('/') }}#home" class="brand" aria-label="IBNCC Home">
        <span class="brand-text">
          <img src="{{ asset('assets/images/logo_full.png') }}" alt="IBNCC logo" class="brand-logo" width="52" height="52" />
          <span class="brand-tagline">AN INITIATIVE OF CATHOLIC CONGRESS</span>
        </span>
      </a>

      <nav class="nav-desktop" aria-label="Primary">
        <a href="{{ url('/') }}#home" class="nav-link" data-section="home"><span>Home</span></a>
        <a href="{{ url('/') }}#about" class="nav-link" data-section="about"><span>About</span></a>
        <a href="{{ url('/') }}#events" class="nav-link" data-section="events"><span>Events</span></a>
        <a href="{{ url('networking') }}" class="nav-link" data-section="networking"><span>Networking</span></a>
        <a href="{{ url('ch-trading') }}" class="nav-link" data-section="trading"><span>Trading</span></a>
      </nav>

      <div class="header-actions">
        <a href="{{ url('event-details') }}" class="header-notice" aria-label="Upcoming event announcement">
          <span class="header-notice-icon" aria-hidden="true"><i class="fa-solid fa-bell"></i></span>
          <span class="header-notice-track">
            <span class="header-notice-marquee">
              <span class="header-notice-text">{{ $notice }}</span>
              <span class="header-notice-text" aria-hidden="true">{{ $notice }}</span>
            </span>
          </span>
        </a>

        @auth
          <div class="header-profile">
            <button type="button" class="header-profile-btn" aria-haspopup="menu" aria-expanded="false"
                    aria-label="Account menu for {{ auth()->user()->name }}">
              <span class="header-profile-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            </button>
            <div class="header-profile-menu" role="menu" hidden>
              <p class="header-profile-name">{{ auth()->user()->name }}</p>
              <a href="{{ route('members.show', auth()->user()) }}" class="header-profile-item" role="menuitem">
                <i class="fa-regular fa-user" aria-hidden="true"></i> My Account
              </a>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-profile-item" role="menuitem">
                  <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout
                </button>
              </form>
            </div>
          </div>
        @else
          <a href="#login" class="btn-login">Login</a>
        @endauth

        <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Open menu"
                aria-expanded="false" aria-controls="mobile-menu">
          <span class="menu-bar"></span><span class="menu-bar"></span><span class="menu-bar"></span>
        </button>
      </div>
    </div>

    <nav class="nav-mobile" id="mobile-menu" aria-label="Mobile" hidden>
      <div class="nav-mobile-card">
        <a href="{{ url('event-details') }}" class="header-notice header-notice--mobile" aria-label="Upcoming event announcement">
          <span class="header-notice-icon" aria-hidden="true"><i class="fa-solid fa-bell"></i></span>
          <span class="header-notice-track">
            <span class="header-notice-marquee">
              <span class="header-notice-text">{{ $notice }}</span>
              <span class="header-notice-text" aria-hidden="true">{{ $notice }}</span>
            </span>
          </span>
        </a>
        <div class="nav-mobile-list">
          @foreach ([
            ['url' => url('/').'#home',        'section' => 'home',       'icon' => 'fa-house',                'title' => 'Home',       'sub' => 'Welcome to IBNCC'],
            ['url' => url('/').'#about',       'section' => 'about',      'icon' => 'fa-circle-info',          'title' => 'About',      'sub' => 'Know more about us'],
            ['url' => url('/').'#events',      'section' => 'events',     'icon' => 'fa-calendar-days',        'title' => 'Events',     'sub' => 'Upcoming gatherings'],
            ['url' => url('networking'),       'section' => 'networking', 'icon' => 'fa-handshake',            'title' => 'Networking', 'sub' => 'Build global connections'],
            ['url' => url('ch-trading'),       'section' => 'trading',    'icon' => 'fa-arrow-right-arrow-left','title' => 'Trading',   'sub' => 'Collaborate and grow'],
          ] as $item)
            <a href="{{ $item['url'] }}" class="nav-link" data-section="{{ $item['section'] }}">
              <span class="nav-mobile-icon" aria-hidden="true"><i class="fa-solid {{ $item['icon'] }}"></i></span>
              <span class="nav-mobile-copy"><strong>{{ $item['title'] }}</strong><em>{{ $item['sub'] }}</em></span>
              <i class="fa-solid fa-chevron-right nav-mobile-chevron" aria-hidden="true"></i>
            </a>
          @endforeach
        </div>
      </div>
    </nav>
  </header>

  {{-- ================= APP NAV + SHARE ================= --}}
  <nav class="app-nav" aria-label="Profile navigation">
    <div class="app-nav-inner">
      <a href="#save-profile" class="app-nav-item" aria-label="Save Profile" aria-haspopup="menu" aria-expanded="false">
        <span class="app-nav-icon app-nav-icon--save"><i class="fa-regular fa-bookmark" aria-hidden="true"></i></span>
        <span class="app-nav-label">Save Profile</span>
      </a>
      <a href="#block-profile" class="app-nav-item" aria-label="Block Profile" aria-haspopup="menu" aria-expanded="false">
        <span class="app-nav-icon app-nav-icon--block"><i class="fa-solid fa-ban" aria-hidden="true"></i></span>
        <span class="app-nav-label">Block Profile</span>
      </a>
      <a href="#share-profile" class="app-nav-item" id="openShareProfileBtn" aria-label="Share Profile"
         aria-controls="shareProfileDropdown" aria-haspopup="dialog" aria-expanded="false">
        <span class="app-nav-icon app-nav-icon--share"><i class="fa-solid fa-share-from-square" aria-hidden="true"></i></span>
        <span class="app-nav-label">Share Profile</span>
      </a>
      @auth
        <a href="{{ route('members.show', auth()->user()) }}" class="app-nav-item" aria-label="Your profile" aria-haspopup="menu" aria-expanded="false">
          <span class="app-nav-icon app-nav-icon--profile"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
          <span class="app-nav-label">Your Profile</span>
        </a>
      @endauth
    </div>

    <div class="app-nav-dropdown" id="appNavDropdown" hidden role="menu" aria-label="Account menu">
      <a href="{{ url('activity') }}" class="app-nav-dropdown-item" role="menuitem" data-nav-action="activity">
        <span class="app-nav-dropdown-icon" aria-hidden="true"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <span>Activity</span>
      </a>
      <form method="POST" action="{{ route('logout') }}" style="margin:0">
        @csrf
        <button type="submit" class="app-nav-dropdown-item app-nav-dropdown-item--logout" role="menuitem" data-nav-action="logout">
          <span class="app-nav-dropdown-icon" aria-hidden="true"><i class="fa-solid fa-right-from-bracket"></i></span>
          <span>Logout</span>
        </button>
      </form>
    </div>

    <div class="share-profile-dropdown" id="shareProfileDropdown" hidden role="dialog"
         aria-labelledby="shareProfileTitle"
         data-profile-path="{{ $profileUrl }}" data-profile-name="{{ $user->name }}">
      <header class="share-profile-header">
        <div class="share-profile-heading">
          <span class="share-profile-heading-icon" aria-hidden="true"><i class="fa-solid fa-share-nodes"></i></span>
          <div>
            <h2 id="shareProfileTitle">Share Profile</h2>
            <p class="share-profile-subtitle">Copy the link or find a saved profile</p>
          </div>
        </div>
      </header>

      <div class="share-profile-link-row">
        <a href="{{ $profileUrl }}" class="share-profile-link" id="shareProfileLink" title="Open profile link">
          <i class="fa-solid fa-link" aria-hidden="true"></i>
          <span id="shareProfileLinkText">{{ $profileUrl }}</span>
        </a>
        <button type="button" class="share-profile-copy" id="shareProfileCopyBtn" aria-label="Copy profile link" title="Copy link">
          <i class="fa-regular fa-copy" aria-hidden="true"></i>
        </button>
      </div>
      <p class="share-profile-copy-status" id="shareProfileCopyStatus" hidden aria-live="polite">Link copied</p>

      <div class="share-profile-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <label class="visually-hidden" for="shareProfileSearch">Search saved profiles</label>
        <input type="search" id="shareProfileSearch" placeholder="Search saved profiles..." autocomplete="off" />
      </div>

      <ul class="share-profile-results" id="shareProfileResults" role="listbox" aria-label="Saved profiles">
        @foreach ($similar as $m)
          <li class="share-profile-result" role="option" aria-selected="false"
              data-name="{{ $m->name }}" data-role="{{ $m->profile->job?->name }}">
            <img src="{{ $m->profile->photo_url }}" alt="" width="36" height="36" />
            <span class="share-profile-result-copy">
              <strong>{{ $m->name }}</strong>
              <em>{{ $m->profile->job?->name }}@if($m->presentAddress?->district?->name) · {{ $m->presentAddress?->district?->name }}@endif</em>
            </span>
            <button type="button" class="share-profile-send" hidden>Send</button>
          </li>
        @endforeach
      </ul>
      <p class="share-profile-empty" id="shareProfileEmpty" hidden>No saved profiles match your search.</p>
    </div>
  </nav>

  {{-- ================= MAIN ================= --}}
  <main class="profile-main">

    {{-- Banner --}}
    <section class="profile-banner-card" aria-labelledby="profile-name">
      <div class="profile-cover">
        <img src="{{ $p->cover_url }}" alt="Cover photo" />
      </div>

      <div class="profile-identity">
        <div class="profile-photo-wrap">
          <img class="profile-photo" src="{{ $p->photo_url }}"
               alt="Portrait of {{ $user->name }}" width="120" height="120" />
        </div>

        <div class="profile-identity-body">
          <h1 id="profile-name" class="profile-display-name">
            {{ $user->name }}
            @if ($user->phone_verified_at)
              <i class="fa-solid fa-shield-halved" title="Verified member" aria-label="Verified member"></i>
            @endif
          </h1>
          @if ($p->job?->name)<p class="profile-profession">{{ $p->job?->name }}</p>@endif
          @if ($p->about)<p class="profile-about">{{ $p->about }}</p>@endif

          <ul class="profile-meta-pills">
            @if ($present?->district?->name || $present?->state?->name)
              <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                {{ collect([$present?->district?->name, $present?->state?->name])->filter()->implode(', ') }}</li>
            @endif
            <li><i class="fa-solid fa-church" aria-hidden="true"></i> {{ $user->diocese_name }} Diocese</li>
            <li><i class="fa-regular fa-calendar" aria-hidden="true"></i> Member since {{ $user->created_at->format('M Y') }}</li>
          </ul>
        </div>
      </div>
    </section>

    <div class="profile-details-row">

      {{-- Details accordion --}}
      <section class="profile-panel" aria-labelledby="profile-details-heading">
        <header class="profile-panel-header">
          <h2 id="profile-details-heading"><i class="fa-regular fa-user" aria-hidden="true"></i> Profile Details</h2>
        </header>

        <div class="profile-accordion" data-accordion>

          {{-- Church --}}
          <details class="profile-detail-item">
            <summary>
              <span class="detail-left">
                <span class="detail-icon"><i class="fa-solid fa-church" aria-hidden="true"></i></span>
                <span class="detail-label">Church Details</span>
              </span>
              <span class="detail-actions"><span class="detail-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span></span>
            </summary>
            <div class="detail-content">
              <dl class="detail-grid">
                <div><dt>Rite</dt><dd>{{ $user->rite_name }}</dd></div>
                <div><dt>Diocese</dt><dd>{{ $user->diocese_name }}</dd></div>
                <div><dt>Forane</dt><dd>{{ $user->forane_name ?: '—' }}</dd></div>
                <div><dt>Parish</dt><dd>{{ $user->parish?->name ?: '—' }}</dd></div>
              </dl>
            </div>
          </details>

          {{-- Personal: DOB and blood group only for the owner --}}
          <details class="profile-detail-item">
            <summary>
              <span class="detail-left">
                <span class="detail-icon"><i class="fa-regular fa-id-card" aria-hidden="true"></i></span>
                <span class="detail-label">Personal Details</span>
              </span>
              <span class="detail-actions"><span class="detail-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span></span>
            </summary>
            <div class="detail-content">
              <dl class="detail-grid">
                <div><dt>Full Name</dt><dd>{{ $user->name }}</dd></div>
                <div><dt>Gender</dt><dd>{{ $p->gender ?: '—' }}</dd></div>
                @if ($isOwner)
                  <div><dt>Date of Birth</dt><dd>{{ $p->dob ? $p->dob->format('j F Y') : '—' }}</dd></div>
                  <div><dt>Blood Group</dt><dd>{{ $p->blood_group ?: '—' }}</dd></div>
                @endif
              </dl>
            </div>
          </details>

          {{-- Contact: logged-in members only --}}
          <details class="profile-detail-item">
            <summary>
              <span class="detail-left">
                <span class="detail-icon"><i class="fa-solid fa-address-book" aria-hidden="true"></i></span>
                <span class="detail-label">Contact Information</span>
              </span>
              <span class="detail-actions"><span class="detail-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span></span>
            </summary>
            <div class="detail-content">
              @auth
                <dl class="detail-grid">
                  <div><dt>Mobile</dt><dd>+91 {{ $user->phone }}</dd></div>
                  <div><dt>Email</dt><dd>{{ $user->email ?: '—' }}</dd></div>
                  <div><dt>WhatsApp</dt><dd>{{ $p->whatsapp ? '+91 '.$p->whatsapp : '—' }}</dd></div>
                  <div><dt>Preferred Contact</dt><dd>{{ $p->preferred_contact ? ucfirst($p->preferred_contact) : '—' }}</dd></div>
                </dl>
              @else
                <p><a href="#login" class="btn-login">Login</a> to view contact details.</p>
              @endauth
            </div>
          </details>

          {{-- Addresses: owner only --}}
          @if ($isOwner)
            @foreach (['Permanent Address' => [$permanent, 'fa-house'], 'Present Address' => [$present, 'fa-location-dot']] as $title => [$addr, $icon])
              <details class="profile-detail-item">
                <summary>
                  <span class="detail-left">
                    <span class="detail-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <span class="detail-label">{{ $title }}</span>
                  </span>
                  <span class="detail-actions"><span class="detail-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span></span>
                </summary>
                <div class="detail-content">
                  <dl class="detail-grid">
                    <div><dt>House Name</dt><dd>{{ $addr?->house ?: '—' }}</dd></div>
                    <div><dt>Country</dt><dd>{{ $addr?->country?->name ?: '—' }}</dd></div>
                    <div><dt>State</dt><dd>{{ $addr?->state?->name ?: '—' }}</dd></div>
                    <div><dt>District</dt><dd>{{ $addr?->district?->name ?: '—' }}</dd></div>
                    <div><dt>PIN</dt><dd>{{ $addr?->pin ?: '—' }}</dd></div>
                  </dl>
                </div>
              </details>
            @endforeach
          @endif

          {{-- Employment --}}
          <details class="profile-detail-item">
            <summary>
              <span class="detail-left">
                <span class="detail-icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></span>
                <span class="detail-label">Employment Status</span>
              </span>
              <span class="detail-actions"><span class="detail-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span></span>
            </summary>
            <div class="detail-content">
              <dl class="detail-grid">
                <div><dt>Status</dt><dd>{{ $p->employment_status ?: '—' }}</dd></div>
                <div><dt>Organization</dt><dd>{{ $p->organization ?: '—' }}</dd></div>
                <div><dt>Work Mode</dt><dd>{{ $p->work_mode ?: '—' }}</dd></div>
                <div><dt>Experience</dt><dd>{{ $p->experience_years ? $p->experience_years.' years' : '—' }}</dd></div>
              </dl>
            </div>
          </details>

          {{-- Job --}}
          <details class="profile-detail-item">
            <summary>
              <span class="detail-left">
                <span class="detail-icon"><i class="fa-solid fa-toolbox" aria-hidden="true"></i></span>
                <span class="detail-label">Job Details</span>
              </span>
              <span class="detail-actions"><span class="detail-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span></span>
            </summary>
            <div class="detail-content">
              <dl class="detail-grid">
                <div><dt>Job Category</dt><dd>{{ $p->job?->category?->name ?: '—' }}</dd></div>
                <div><dt>Job</dt><dd>{{ $p->job?->name ?: '—' }}</dd></div>
              </dl>
            </div>
          </details>
        </div>
      </section>

      {{-- Sidebar --}}
      <aside class="profile-sidebar" aria-label="Profile sidebar">
        <article class="sidebar-card profile-brief-card" id="profile-brief-card">

          <div class="other-connect-block">
            <header class="sidebar-card-header"><h3>Connect</h3></header>

            @unless ($isOwner)
              <div class="other-message-wrap">
                <button type="button" class="sidebar-btn-primary other-message-btn" id="sendMessageBtn"
                        aria-expanded="false" aria-controls="otherChatBox">
                  <i class="fa-regular fa-comments" aria-hidden="true"></i> Send a Message
                </button>

                <div class="other-chat-box" id="otherChatBox" hidden>
                  <header class="other-chat-head">
                    <div class="other-chat-head-info">
                      <img src="{{ $p->photo_url }}" alt="" width="36" height="36" />
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
                    <p class="other-chat-empty" id="otherChatEmpty">Start a conversation with {{ $user->first_name }}.</p>
                  </div>

                  <form class="other-chat-compose" id="otherChatForm">
                    <label class="visually-hidden" for="otherChatInput">Message</label>
                    <textarea id="otherChatInput" name="message" rows="2" placeholder="Write a message…" required></textarea>
                    <button type="submit" class="sidebar-btn-primary other-chat-send" aria-label="Send message">
                      <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    </button>
                  </form>
                </div>
              </div>
            @endunless

            @if ($user->socialLinks->isNotEmpty())
              <div class="other-social-links" aria-label="Social media profiles">
                <p class="other-social-label">Social Media Links</p>
                <ul class="other-social-list">
                  @foreach ($user->socialLinks as $s)
                    <li>
                      <a href="{{ $s->url }}" class="other-social-link other-social-link--{{ $s->name }}"
                         target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($s->name) }}">
                        <i class="fa-brands fa-{{ match ($s->name) { 'x' => 'x-twitter', 'facebook' => 'facebook-f', default => $s->name } }}" aria-hidden="true"></i>
                      </a>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif
          </div>

          {{-- Similar members --}}
          <div class="similar-members" id="similar-members">
            <header class="sidebar-card-header similar-members-header"><h3>Similar Members</h3></header>

            <ul class="profile-list similar-members-list">
              @forelse ($similar as $m)
                <li class="profile-card">
                  <div class="profile-main">
                    <span class="profile-avatar-wrap">
                      <img class="profile-avatar" src="{{ $m->profile->photo_url }}"
                           alt="Portrait of {{ $m->name }}" width="72" height="72" />
                    </span>
                    <div class="profile-info">
                      <h3 class="profile-name">
                        {{ $m->name }}
                        @if ($m->phone_verified_at)
                          <i class="fa-solid fa-check" title="Verified" aria-label="Verified"></i>
                        @endif
                      </h3>
                      <p class="profile-role">{{ $m->profile->job?->name }}</p>
                      <p class="profile-bio">{{ $m->profile->about }}</p>
                    </div>
                  </div>

                  <div class="profile-card-meta">
                    <div class="profile-location">
                      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                      <div>
                        <strong>{{ collect([$m->presentAddress?->district?->name, $m->presentAddress?->state?->name])->filter()->implode(', ') ?: ($m->diocese_name) }}</strong>
                        <span>{{ $m->diocese_name }} Diocese</span>
                      </div>
                    </div>
                    @auth
                      <div class="profile-contact">
                        <a href="tel:+91{{ $m->phone }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> +91 {{ $m->phone }}</a>
                        @if ($m->email)
                          <a href="mailto:{{ $m->email }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $m->email }}</a>
                        @endif
                        <button type="button" class="profile-appointment">
                          <i class="fa-regular fa-calendar" aria-hidden="true"></i> Need an appointment
                        </button>
                      </div>
                    @endauth
                  </div>

                  <a href="{{ route('members.show', $m) }}" class="profile-open" aria-label="View {{ $m->name }} profile">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                  </a>
                </li>
              @empty
                <li class="profile-card"><p>No similar members yet.</p></li>
              @endforelse
            </ul>
          </div>
        </article>
      </aside>
    </div>

    {{-- Gallery --}}
    <section class="profile-panel gallery-panel" aria-labelledby="gallery-heading">
      <header class="profile-panel-header gallery-header">
        <h2 id="gallery-heading"><i class="fa-regular fa-image" aria-hidden="true"></i> Gallery</h2>
        @if ($isOwner)
          <button type="button" class="gallery-add-btn">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Add Image
          </button>
        @endif
      </header>

      @if ($user->galleryImages->isNotEmpty())
        <div class="gallery-grid">
          @foreach ($user->galleryImages as $g)
            <figure class="gallery-item">
              <img src="{{ asset('storage/' . $g->path) }}" alt="{{ $g->title ?: 'Gallery image '.$loop->iteration }}" />
            </figure>
          @endforeach
        </div>

        @if ($user->galleryImages->count() > 10)
          <button type="button" class="gallery-view-more">
            View More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </button>
        @endif
      @else
        <p>No images yet.</p>
      @endif
    </section>
  </main>

  {{-- Lightbox --}}
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
          <span id="galleryLightboxIndex">1</span> / <span id="galleryLightboxTotal">0</span>
        </p>
      </div>
    </div>
  </div>

  {{-- ================= FOOTER ================= --}}
  <footer class="site-footer" id="footer">
    <div class="footer-card">
      <div class="container footer-grid">
        <div class="footer-brand">
          <div class="footer-brand-inner">
            <img src="{{ asset('assets/images/logo_full.png') }}" alt="IBNCC" width="56" height="56" />
            <div>
              <strong class="footer-tagline">International Business Network <br /> of Catholic Congress</strong>
              <p>A global platform connecting Catholic entrepreneurs, professionals and organizations to collaborate, trade and grow together.</p>
            </div>
          </div>
        </div>

        <div class="footer-nav">
          <h3>Navigate</h3>
          <a href="{{ url('/') }}#home"><span>Home</span></a>
          <a href="{{ url('/') }}#about"><span>About</span></a>
          <a href="{{ url('/') }}#events"><span>Events</span></a>
          <a href="{{ url('networking') }}"><span>Networking</span></a>
          <a href="{{ url('ch-trading') }}"><span>Trading</span></a>
        </div>

        <div class="footer-connect">
          <h3>Connect</h3>
          <a href="mailto:ibncc26@gmail.com" class="footer-contact-link">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i><span>ibncc26@gmail.com</span>
          </a>
          <a href="tel:+919847030064" class="footer-contact-link">
            <i class="fa-solid fa-phone" aria-hidden="true"></i><span>+91 9847030064</span>
          </a>
          <h3 class="footer-follow">Follow Us</h3>
          <div class="socials" aria-label="Social links">
            <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
            <a href="#" aria-label="X / Twitter"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i></a>
            <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
            <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
          </div>
        </div>
      </div>

      <div class="container footer-bottom">
        <p class="footer-copy">&copy; {{ date('Y') }} IBNCC. All rights reserved.</p>
        <div class="footer-design"><a href="#">Designed By : Astraz Software solutions</a></div>
      </div>
    </div>
  </footer>

  <button type="button" class="back-to-top" id="back-to-top" aria-label="Back to top">
    <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
  </button>

  {{-- Login / Register / OTP modals (same partial used on the other pages) --}}
  @guest
    @include('partials.auth-modals')
  @endguest

  <script src="{{ asset('js/script.js') }}"></script>
  <script src="{{ asset('js/cchub/ch-profile.js') }}"></script>
  <script src="{{ asset('js/cchub/ch-other-profile.js') }}"></script>
  <script src="{{ asset('js/cchub/ch-login.js') }}"></script>
</body>
</html>
