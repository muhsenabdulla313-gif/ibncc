@extends('layouts.master')
@section('body')
  <!-- ========== HERO ========== -->
  <section class="hero" id="home">
    <div class="sky" aria-hidden="true">
      <div class="hero-bg"></div>
      <div class="sky-gradient"></div>
      <div class="sunburst"></div>
      <div class="cloud cloud-1"></div>
      <div class="cloud cloud-2"></div>
      <div class="cloud cloud-3"></div>
      <div class="cloud cloud-4"></div>
      <canvas id="network-canvas" class="network-canvas" aria-hidden="true"></canvas>
      <div class="hero-waves">
        <svg viewBox="0 0 1440 160" preserveAspectRatio="none" aria-hidden="true">
          <path class="wave wave-1" d="M0,96 C240,140 480,40 720,80 C960,120 1200,40 1440,88 L1440,160 L0,160 Z" />
          <path class="wave wave-2" d="M0,110 C280,150 520,70 780,100 C1040,130 1240,70 1440,110 L1440,160 L0,160 Z" />
          <path class="wave wave-3" d="M0,128 C320,160 560,100 840,120 C1120,140 1280,100 1440,130 L1440,160 L0,160 Z" />
        </svg>
      </div>
    </div>

    <div class="hero-layout">
      <div class="flag-scene">
        <div class="flag-assembly">
          <div class="flag-cloth flag-cloth--main">
            <div class="flag-surface">
              <div class="flag-image-wrap">
                <img src="assets/images/crop_new.png" alt="Catholic Congress flag of IBNCC waving in the wind"
                  class="flag-image" decoding="async" />
              </div>
              <div class="flag-fold flag-fold--1"></div>
              <div class="flag-fold flag-fold--2"></div>
              <div class="flag-fold flag-fold--3"></div>
              <div class="flag-highlight"></div>
              <div class="flag-shade"></div>
            </div>
          </div>
          <div class="flag-cloth flag-cloth--ghost" aria-hidden="true"></div>
        </div>
      </div>

      <div class="hero-content">


        <h1 class="hero-title reveal">
          Connecting Businesses.<br />
          Building Global<br />
          Opportunities.
        </h1>
        <p class="hero-lead reveal">
          Empowering businesses and communities through collaboration, faith, and shared vision for a better tomorrow.
        </p>
        <div class="hero-ctas reveal">
          <a href="#objectives" class="btn btn-hero-primary">
            Explore Initiatives <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </a>
          <a href="#about" class="btn btn-hero-secondary">
            Learn More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </div>

    <a href="#about" class="scroll-indicator" aria-label="Scroll to explore">
      <span>Scroll to explore</span>
      <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
    </a>
  </section>

  <!-- ========== ABOUT ========== -->
  <section class="section about" id="about">
    <div class="container about-wrap">
      <div class="about-grid">
        <div class="about-copy reveal">
          <p class="about-eyebrow">About IBNCC</p>
          <h2 class="about-title">Building a Global Christian Business Network</h2>
          <div class="about-divider" aria-hidden="true">
            <span></span><span></span><span></span>
          </div>
          <p class="about-lead">
            IBNCC is a global platform created to connect Christian business people, entrepreneurs, professionals and
            leaders. Through networking, knowledge sharing, collaboration, mentorship and access to opportunities,
            IBNCC
            aims to strengthen businesses and create a connected ecosystem where people and ideas can grow together.
          </p>
          <ul class="about-pillars">
            <li><i class="fa-solid fa-globe" aria-hidden="true"></i> Global Network</li>
            <li><i class="fa-solid fa-heart" aria-hidden="true"></i> Shared Values</li>
            <li><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Business Growth</li>
            <li><i class="fa-solid fa-crown" aria-hidden="true"></i> Leadership</li>
            <li><i class="fa-solid fa-people-group" aria-hidden="true"></i> Collaboration</li>
          </ul>
        </div>

        <div class="about-visual reveal">
          <div class="about-visual-bg" aria-hidden="true">
            <div class="about-dot-grid"></div>
            <svg class="about-world-map" viewBox="0 0 400 400" aria-hidden="true">
              <circle cx="200" cy="200" r="160" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.12"
                stroke-dasharray="4 6" />
              <ellipse cx="200" cy="200" rx="160" ry="70" fill="none" stroke="currentColor" stroke-width="1"
                opacity="0.1" />
              <ellipse cx="200" cy="200" rx="90" ry="160" fill="none" stroke="currentColor" stroke-width="1"
                opacity="0.1" />
              <line x1="40" y1="200" x2="360" y2="200" stroke="currentColor" stroke-width="0.8" opacity="0.08" />
            </svg>
          </div>
          <div class="about-visual-frame">
            <img src="assets/images/ab2.png" alt="International business professionals collaborating around a table"
              loading="lazy" width="600" height="600" />
            <!-- <div class="about-badge">
                  <span class="about-badge-icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                  <span class="about-badge-text">Global<br />Community</span>
                </div> -->
            <div class="about-mission-card">
              <span class="about-mission-icon" aria-hidden="true"><i class="fa-solid fa-bullseye"></i></span>
              <h3>Our Mission</h3>
              <p>Empowering Christian entrepreneurs to build meaningful global connections and lasting impact.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="about-features reveal">
        <article class="about-feature">
          <div class="about-feature-icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></div>
          <h3>Global Reach</h3>
          <p>Connecting Christian business leaders across continents and cultures.</p>
        </article>
        <article class="about-feature">
          <div class="about-feature-icon"><i class="fa-solid fa-user-group" aria-hidden="true"></i></div>
          <h3>Meaningful Connections</h3>
          <p>Building trusted relationships that create real business opportunities.</p>
        </article>
        <article class="about-feature">
          <div class="about-feature-icon"><i class="fa-solid fa-handshake" aria-hidden="true"></i></div>
          <h3>Business Empowerment</h3>
          <p>Supporting growth through mentorship, resources and strategic partnerships.</p>
        </article>
        <article class="about-feature">
          <div class="about-feature-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></div>
          <h3>Knowledge Sharing</h3>
          <p>Access to insights, trends and expertise from industry leaders worldwide.</p>
        </article>
        <article class="about-feature">
          <div class="about-feature-icon"><i class="fa-solid fa-star" aria-hidden="true"></i></div>
          <h3>Kingdom Impact</h3>
          <p>Advancing businesses that reflect faith, integrity and purpose.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ========== CORE OBJECTIVES ========== -->
  <section class="section objectives" id="objectives">
    <div class="container">
      <div class="section-header reveal">
        <p class="section-eyebrow">Purpose</p>
        <h2 class="section-title">Core Objectives of IBNCC</h2>
        <p class="section-lead">Six pillars guiding how we connect, empower and grow Christian business communities
          worldwide.</p>
      </div>
      <div class="objectives-grid">
        <article class="obj-card reveal" data-stagger="0">
          <!-- <span class="obj-num">01</span> -->
          <div class="obj-icon"><i class="fa-solid fa-network-wired" aria-hidden="true"></i></div>
          <h3>Global Networking</h3>
          <p>Establish forums, events and platforms for connecting Christian business persons locally and globally.
          </p>
        </article>
        <article class="obj-card reveal" data-stagger="1">
          <!-- <span class="obj-num">02</span> -->
          <div class="obj-icon"><i class="fa-solid fa-earth-americas" aria-hidden="true"></i></div>
          <h3>Global Expansion</h3>
          <p>Assist Christian businesses in scaling beyond borders while maintaining their values.</p>
        </article>
        <article class="obj-card reveal" data-stagger="2">
          <!-- <span class="obj-num">03</span> -->
          <div class="obj-icon"><i class="fa-solid fa-rocket" aria-hidden="true"></i></div>
          <h3>Startup Support</h3>
          <p>Incubate and mentor startups initiated by Christian youth.</p>
        </article>
        <article class="obj-card reveal" data-stagger="3">
          <!-- <span class="obj-num">04</span> -->
          <div class="obj-icon"><i class="fa-solid fa-venus" aria-hidden="true"></i></div>
          <h3>Women Empowerment</h3>
          <p>Launch initiatives to promote Christian women entrepreneurs and leaders.</p>
        </article>
        <article class="obj-card reveal" data-stagger="4">
          <!-- <span class="obj-num">05</span> -->
          <div class="obj-icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
          <h3>Skill Development</h3>
          <p>Conduct workshops, training and resources to build business and leadership capabilities.</p>
        </article>
        <article class="obj-card reveal" data-stagger="5">
          <!-- <span class="obj-num">06</span> -->
          <div class="obj-icon"><i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i></div>
          <h3>Funding &amp; Investment</h3>
          <p>Facilitate angel investments, grants and crowdfunding opportunities within the community.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ========== ACTIVITIES ========== -->
  <section class="section activities" id="activities">
    <div class="activities-decor" aria-hidden="true">
      <div class="activities-dot-grid"></div>
      <!-- <svg class="activities-waves" viewBox="0 0 1200 600" preserveAspectRatio="none">
            <path d="M-50 180 C200 80, 400 280, 650 160 S1000 60, 1250 200" fill="none" stroke="currentColor" stroke-width="1.2" />
            <path d="M-50 280 C250 200, 450 360, 700 260 S1050 180, 1250 320" fill="none" stroke="currentColor" stroke-width="1" />
            <path d="M-50 420 C220 340, 480 500, 760 400 S1100 320, 1250 460" fill="none" stroke="currentColor" stroke-width="1" />
          </svg> -->
    </div>

    <div class="container">
      <div class="section-header activities-header reveal">
        <p class="section-eyebrow activities-eyebrow">Activities</p>
        <h2 class="section-title">What We Do</h2>
        <p class="section-lead">Creating meaningful connections, opportunities and knowledge-sharing platforms.</p>
      </div>

      <div class="activities-editorial">
        <!-- Card 01 — tall left -->
        <article class="act-card act-card--a reveal">
          <span class="act-num" aria-hidden="true">01</span>
          <div class="act-body">
            <div class="act-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></div>
            <h3>Chapter Meetings &amp; Webinars</h3>
            <p>Regular biweekly chapter meetings and monthly webinars that bring members together to learn, connect
              and collaborate.</p>
          </div>
          <div class="act-media">
            <img src="assets/images/ac4.jpg" alt="Business seminar and webinar presentation" loading="lazy" width="640"
              height="360" />
          </div>
        </article>

        <!-- Card 02 — horizontal, image left -->
        <article class="act-card act-card--b reveal" id="trading">
          <div class="act-media">
            <img src="assets/images/ac1.png" alt="Business handshake representing networking and trading" loading="lazy"
              width="480" height="360" />
          </div>
          <div class="act-body">
            <span class="act-num" aria-hidden="true">02</span>
            <div class="act-icon"><i class="fa-solid fa-handshake" aria-hidden="true"></i></div>
            <h3>Networking &amp; Trading Platform</h3>
            <p>A dedicated platform designed to facilitate business networking, partnerships, trading and commercial
              opportunities.</p>
          </div>
        </article>

        <!-- Card 03 — dark horizontal, image right -->
        <article class="act-card act-card--c reveal">
          <div class="act-body">
            <div class="act-icon"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></div>
            <h3>Global Business Conclaves</h3>
            <p>High-impact business gatherings that bring entrepreneurs, CEOs, founders, investors and industry
              experts together.</p>
          </div>
          <div class="act-media">
            <span class="act-num" aria-hidden="true">03</span>
            <img src="assets/images/ac6.jpg" alt="Global business conclave conference stage" loading="lazy" width="560"
              height="360" />
          </div>
        </article>
      </div>
    </div>
  </section>

  @include('partials.events-section')

  <section class="section cta-section" id="cta">
    <div class="cta-bg" aria-hidden="true">
      <div class="cta-globe"></div>
      <canvas id="cta-canvas" class="cta-canvas"></canvas>
    </div>
    <div class="container cta-inner reveal">
      <h2 class="section-title">Be Part of the Global Network</h2>
      <p class="section-lead">
        Connect with Christian business leaders, discover opportunities and help shape a stronger global business
        community.
      </p>
      <div class="cta-buttons">
        <a href="#login" class="btn btn-gold btn-login">Join IBNCC</a>
        <a href="#login" class="btn btn-outline-light btn-login">Explore Networking &amp; Trading</a>
      </div>
    </div>
  </section>
@endsection