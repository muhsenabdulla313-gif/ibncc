<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description"
    content="IBNCC — International Business Network. A project of Catholic Congress connecting Christian entrepreneurs, professionals and leaders globally." />
  <title>IBNCC — International Business Network</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Manrope:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/cchub/ch-login.css" />
  <link rel="icon" type="image/png" href="assets/images/logo.png" />
</head>

<body>
  <!-- SVG Filters for flag cloth displacement -->
  <svg class="svg-defs" aria-hidden="true" focusable="false">
    <defs>
      <filter id="cloth-wave" x="-10%" y="-10%" width="120%" height="120%">
        <feTurbulence type="fractalNoise" baseFrequency="0.012 0.04" numOctaves="3" seed="2" result="noise">
          <animate attributeName="baseFrequency" dur="11s" values="0.012 0.04;0.018 0.055;0.01 0.035;0.012 0.04"
            repeatCount="indefinite" />
        </feTurbulence>
        <feDisplacementMap in="SourceGraphic" in2="noise" scale="18" xChannelSelector="R" yChannelSelector="G" />
      </filter>
      <filter id="cloth-soft" x="-5%" y="-5%" width="110%" height="110%">
        <feTurbulence type="fractalNoise" baseFrequency="0.008 0.025" numOctaves="2" seed="5" result="noise2">
          <animate attributeName="baseFrequency" dur="9s" values="0.008 0.025;0.014 0.032;0.008 0.025"
            repeatCount="indefinite" />
        </feTurbulence>
        <feDisplacementMap in="SourceGraphic" in2="noise2" scale="10" xChannelSelector="R" yChannelSelector="B" />
      </filter>
    </defs>
  </svg>

  <!-- ========== HEADER ========== -->
  <header class="site-header" id="site-header">
    <div class="header-inner">
      <a href="#home" class="brand" aria-label="IBNCC Home">
        <!-- <img src="assets/images/logo_full.png" alt="IBNCC logo" class="brand-logo" width="52" height="52" /> -->
        <span class="brand-text">
          <img src="assets/images/logo_full.png" alt="IBNCC logo" class="brand-logo" width="52" height="52" />
          <!-- <span class="brand-name">IBNCC</span> -->
          <span class="brand-tagline">AN INITIATIVE OF CATHOLIC CONGRESS</span>
        </span>
      </a>

      <nav class="nav-desktop" aria-label="Primary">
        <a href="#home" class="nav-link active" data-section="home"><span>Home</span></a>
        <a href="#about" class="nav-link" data-section="about"><span>About</span></a>
        <a href="#events" class="nav-link" data-section="events"><span>Events</span></a>
        <a href="networking.html" class="nav-link" data-section="networking"><span>Networking</span></a>
        <a href="{{route('trading')}}" class="nav-link" data-section="trading"><span>Trading</span></a>
      </nav>

      <div class="header-actions">
        <a href="#events" class="header-notice" aria-label="Upcoming event announcement">
          <span class="header-notice-icon" aria-hidden="true">
            <i class="fa-solid fa-bell"></i>
          </span>
          <span class="header-notice-track">
            <span class="header-notice-marquee">
              <span class="header-notice-text">GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8 PM — Monsoon Empress
                Hotel, NH Bypass, Palarivattom, Kochi</span>
              <span class="header-notice-text" aria-hidden="true">GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8
                PM — Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi</span>
            </span>
          </span>
        </a>
        <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Open menu" aria-expanded="false"
          aria-controls="mobile-menu">
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
        </button>
      </div>
    </div>

    <nav class="nav-mobile" id="mobile-menu" aria-label="Mobile" hidden>
      <div class="nav-mobile-card">
        <a href="#events" class="header-notice header-notice--mobile" aria-label="Upcoming event announcement">
          <span class="header-notice-icon" aria-hidden="true">
            <i class="fa-solid fa-bell"></i>
          </span>
          <span class="header-notice-track">
            <span class="header-notice-marquee">
              <span class="header-notice-text">GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8 PM — Monsoon Empress
                Hotel, NH Bypass, Palarivattom, Kochi</span>
              <span class="header-notice-text" aria-hidden="true">GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8 PM
                — Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi</span>
            </span>
          </span>
          <!-- <span class="header-notice-chip">GLC</span> -->
        </a>
        <div class="nav-mobile-list">
          <a href="#home" class="nav-link active" data-section="home">
            <span class="nav-mobile-icon" aria-hidden="true"><i class="fa-solid fa-house"></i></span>
            <span class="nav-mobile-copy">
              <strong>Home</strong>
              <em>Welcome to IBNCC</em>
            </span>
            <i class="fa-solid fa-chevron-right nav-mobile-chevron" aria-hidden="true"></i>
          </a>
          <a href="#about" class="nav-link" data-section="about">
            <span class="nav-mobile-icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
            <span class="nav-mobile-copy">
              <strong>About</strong>
              <em>Know more about us</em>
            </span>
            <i class="fa-solid fa-chevron-right nav-mobile-chevron" aria-hidden="true"></i>
          </a>
          <a href="#events" class="nav-link" data-section="events">
            <span class="nav-mobile-icon" aria-hidden="true"><i class="fa-solid fa-calendar-days"></i></span>
            <span class="nav-mobile-copy">
              <strong>Events</strong>
              <em>Upcoming gatherings</em>
            </span>
            <i class="fa-solid fa-chevron-right nav-mobile-chevron" aria-hidden="true"></i>
          </a>
          <a href="networking.html" class="nav-link" data-section="networking">
            <span class="nav-mobile-icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
            <span class="nav-mobile-copy">
              <strong>Networking</strong>
              <em>Build global connections</em>
            </span>
            <i class="fa-solid fa-chevron-right nav-mobile-chevron" aria-hidden="true"></i>
          </a>
          <a href="ch-trading.html" class="nav-link" data-section="trading">
            <span class="nav-mobile-icon" aria-hidden="true"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
            <span class="nav-mobile-copy">
              <strong>Trading</strong>
              <em>Collaborate and grow</em>
            </span>
            <i class="fa-solid fa-chevron-right nav-mobile-chevron" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </nav>
  </header>

  <main>



@yield('body')



  </main>

  <!-- ========== FOOTER ========== -->
  <footer class="site-footer" id="footer">
    <div class="footer-card">
      <div class="container footer-grid">
        <div class="footer-brand">
          <div class="footer-brand-inner">
            <img src="assets/images/logo_full.png" alt="IBNCC" width="56" height="56" />
            <div>
              <strong class="footer-tagline">International Business Network <br> of Catholic Congress</strong>
              <!-- <span>International Business Network</span> -->
              <p>A global platform connecting Catholic entrepreneurs, professionals and organizations to collaborate,
                trade and grow together.</p>
            </div>
          </div>
        </div>

        
        <div class="footer-nav">
          <h3>Navigate</h3>
          <a href="#home"><span>Home</span></a>
          <a href="#about"><span>About</span></a>
          <a href="#events"><span>Events</span></a>
          <a href="#networking"><span>Networking</span></a>
          <a href="ch-trading.html"><span>Trading</span></a>
        </div>

        <div class="footer-connect">
          <h3>Connect</h3>
          <a href="mailto:ibncc26@gmail.com" class="footer-contact-link">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <span>ibncc26@gmail.com</span>
          </a>
          <a href="tel:+10000000000" class="footer-contact-link">
            <i class="fa-solid fa-phone" aria-hidden="true"></i>
            <span>+91 9847030064</span>
          </a>
          <!-- <div class="footer-location">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            <span>IBNCC Global Secretariat, Kochi, Kerala, India</span>
          </div> -->

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
        <p class="footer-copy">&copy; 2026 IBNCC. All rights reserved.</p>
        <div class="footer-design">
          <a href="#">Designed By : Astraz Software solutions</a>
          <!-- <span aria-hidden="true">|</span>
          <a href="#">Terms of Service</a>
          <span aria-hidden="true">|</span>
          <a href="#">Sitemap</a> -->
        </div>
      </div>
    </div>
  </footer>

  <button type="button" class="back-to-top" id="back-to-top" aria-label="Back to top">
    <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
  </button>

@include('components.auth-modals')

  <script src="js/script.js" defer></script>
  <script src="js/cchub/ch-login.js" defer></script>
</body>

</html>