@extends('layouts.master')

{{-- needs <body class="@yield('body_class')"> in layouts/master.blade.php --}}
@section('body_class', 'page-event-details')

@section('body')
@php
  $email = $event->contact_email ?: 'ibncc26@gmail.com';
  $phone = $event->contact_phone ?: '9847030064';
  $registerUrl = $event->register_url ?: 'mailto:' . $email . '?subject=' . rawurlencode('Registration - ' . $event->title);
$upcoming = $event->is_upcoming;
  $icons = ['fa-star', 'fa-handshake', 'fa-lightbulb', 'fa-chart-line', 'fa-building-columns'];
@endphp

<!-- ========== 1. EVENT HERO ========== -->
<section class="ed-hero" id="event-top">
  <div class="ed-hero-bg" aria-hidden="true">
    <div class="ed-hero-grain"></div>
    <div class="ed-hero-glow"></div>
    <div class="ed-hero-glow ed-hero-glow--soft"></div>
    <span class="ed-sparkle ed-sparkle--1"></span>
    <span class="ed-sparkle ed-sparkle--2"></span>
    <span class="ed-sparkle ed-sparkle--3"></span>
  </div>

  <div class="container ed-hero-inner">
    <div class="ed-hero-copy reveal">
      <span class="ed-badge">
        <i class="fa-solid fa-sparkles" aria-hidden="true"></i>
        {{ $upcoming ? 'Upcoming Event' : 'Past Event' }}
      </span>
      <p class="ed-eyebrow">IBNCC — A Project of Catholic Congress</p>
      <h1 class="ed-title">
        @if ($event->title_first)
          <span class="ed-title-line">{{ $event->title_first }}</span>
        @endif
        <span class="ed-title-accent">{{ $event->title_accent }}</span>
      </h1>
      @if ($event->tagline)
        <div class="ed-tagline-row">
          <p class="ed-tagline">{{ $event->tagline }}</p>
          <span class="ed-tagline-rule" aria-hidden="true"></span>
        </div>
      @endif
      @if ($event->intro)
        <p class="ed-intro">{{ $event->intro }}</p>
      @endif

      <div class="ed-meta">
        <div class="ed-meta-card">
          <span class="ed-meta-icon" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
          <div>
            <span class="ed-meta-label">Date</span>
            <strong>{{ $event->event_date->format('jS F Y') }}</strong>
            @if ($event->time_text)
              <span class="ed-meta-sub">{{ $event->time_text }}</span>
            @endif
          </div>
        </div>
        @if ($event->venue)
          <div class="ed-meta-card">
            <span class="ed-meta-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
            <div>
              <span class="ed-meta-label">Venue</span>
              <strong>{{ $event->venue_name }}</strong>
              @if ($event->venue_address)
                <span class="ed-meta-sub">{{ $event->venue_address }}</span>
              @endif
            </div>
          </div>
        @endif
      </div>

      <div class="ed-hero-ctas">
        @if ($upcoming)
          <a href="#register" class="ed-btn-primary">
            Register Now
            <span class="ed-btn-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
          </a>
        @endif
        @if (count($event->highlight_cards))
          <a href="#highlights" class="ed-btn-secondary">
            <i class="fa-solid fa-star"></i>
            Explore Highlights
          </a>
        @endif
      </div>
    </div>

    <div class="ed-hero-visual reveal">
      <div class="ed-hero-media">
        <div class="ed-hero-frame">
          <img src="{{ $event->cover_url }}" alt="{{ $event->title }}" width="640" height="520" decoding="async" />
        </div>

        <div class="ed-emblem" aria-hidden="true">
          <svg class="ed-emblem-ring" viewBox="0 0 120 120">
            <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="1.5" />
            <circle cx="60" cy="60" r="46" fill="none" stroke="currentColor" stroke-width="0.8" opacity="0.45" />
          </svg>
          <div class="ed-emblem-core">
            <i class="fa-solid fa-globe"></i>
            <span>Uniting Faith</span>
            <em></em>
            <span>Building Futures</span>
          </div>
        </div>
      </div>

      <div class="ed-feature-bar" aria-label="Conclave focus areas">
        <div class="ed-feature-bar-glow" aria-hidden="true"></div>
        <div class="ed-feature-bar-item"><i class="fa-solid fa-users" aria-hidden="true"></i><span>Global<br />Networking</span></div>
        <div class="ed-feature-bar-item"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i><span>Expert<br />Insights</span></div>
        <div class="ed-feature-bar-item"><i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Business<br />Growth</span></div>
        <div class="ed-feature-bar-item"><i class="fa-solid fa-handshake" aria-hidden="true"></i><span>Strategic<br />Partnerships</span></div>
      </div>
    </div>
  </div>
</section>

<!-- ========== 2. REGISTRATION (upcoming only) ========== -->
@if ($upcoming)
<section class="section ed-register" id="register">
  <div class="container">
    <div class="ed-register-board reveal">
      <div class="ed-register-bg" aria-hidden="true">
        <div class="ed-register-map"></div>
        <div class="ed-register-glow ed-register-glow--top"></div>
        <div class="ed-register-glow ed-register-glow--bottom"></div>
      </div>

      <div class="ed-register-top">
        @if ($event->qr_url)
          <div class="ed-qr-block">
            <div class="ed-qr-frame">
              <img src="{{ $event->qr_url }}" alt="QR code to register for {{ $event->title }}" width="200" height="200" loading="lazy" />
            </div>
            <p class="ed-qr-hint">
              <span class="ed-qr-hint-icon" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></span>
              Scan to register
            </p>
          </div>
        @endif

        <div class="ed-register-cta-wrap">
          <p class="ed-register-eyebrow">Ready to be part of</p>
          <h2 class="ed-register-headline">THE CHANGE?</h2>
          <p class="ed-register-desc">
            {{ $event->qr_url ? 'Scan the QR code or click the button below' : 'Click the button below' }} to register for the conclave.
          </p>
          <a href="{{ $registerUrl }}" class="ed-click-register">
            <span class="ed-click-register-text">Click to Register</span>
            <span class="ed-click-register-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
          </a>
          <a href="mailto:{{ $email }}" class="ed-register-email">
            <span class="ed-register-email-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
            {{ $email }}
          </a>
        </div>
      </div>

      <div class="ed-register-divider" aria-hidden="true"><hr></div>

      <div class="ed-register-bottom">
        <div class="ed-join-icon" aria-hidden="true">
          <span class="ed-join-icon-ring"></span>
          <i class="fa-solid fa-users"></i>
        </div>

        <div class="ed-join-banner">
          <h2>JOIN US</h2>
          <p>for this powerful gathering to shape the future of Christian entrepreneurship globally.</p>
        </div>

        <div class="ed-contact-divider" aria-hidden="true"></div>

        <div class="ed-contact-bar">
          <a href="https://wa.me/91{{ substr(preg_replace('/\D/', '', $phone), -10) }}" class="ed-contact-item" target="_blank" rel="noopener noreferrer">
            <span class="ed-contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
            <span>{{ $phone }}</span>
          </a>
          <a href="mailto:{{ $email }}" class="ed-contact-item">
            <span class="ed-contact-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
            <span>{{ $email }}</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- ========== 3. HIGHLIGHTS ========== -->
@if (count($event->highlight_cards))
<section class="section ed-features" id="highlights">
  <div class="container">
    <div class="section-header reveal">
      <p class="section-eyebrow">Why Attend</p>
      <h2 class="section-title">Event Highlights &amp; Features</h2>
      <p class="section-lead">
        Everything you will experience at {{ $event->title }} — curated for growth, connection and impact.
      </p>
    </div>

    <div class="ed-feature-list">
      @foreach ($event->highlight_cards as $card)
        <article class="ed-feature-card reveal">
          <div class="ed-feature-icon" aria-hidden="true"><i class="fa-solid {{ $icons[$loop->index % count($icons)] }}"></i></div>
          <div class="ed-feature-content">
            <span class="ed-feature-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3>{{ $card['title'] }}</h3>
            <ul class="ed-feature-bullets">
              @foreach ($card['bullets'] as $b)
                <li>
                  <strong>{{ $b[0] }}</strong>
                  @if ($b[1]) <span>{{ $b[1] }}</span> @endif
                </li>
              @endforeach
            </ul>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- ========== CLOSING CTA ========== -->
<section class="section ed-closing">
  <div class="container ed-closing-inner reveal">
    <h2>Ready to Connect. Learn. Grow. Expand?</h2>
    <p>
      Join Christian business leaders and help shape the future of Christian entrepreneurship globally.
    </p>
    <div class="ed-closing-actions">
      @if ($upcoming)
        <a href="#register" class="btn btn-gold">Click to Register</a>
      @endif
      <a href="{{ url('/#events') }}" class="btn btn-outline-light">Back to Events</a>
    </div>
  </div>
</section>
@endsection
