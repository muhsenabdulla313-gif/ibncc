<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trading — CC Hub</title>
  <meta name="description" content="CC Hub Trading — bulk commodities and marketplace deals for the community." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Manrope:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="{{asset('css/cchub/ch-styles.css') }}">
  <link rel="stylesheet" href="{{asset('css/cchub/ch-trading.css') }}">
  @stack('styles')
</head>

<body class="trading-page">
  <!-- Header -->
  <header class="site-header">
    <div class="header-inner">
      <a href="index.html#home" class="brand" aria-label="IBNCC Home">
        <span class="brand-text">
          <img src="assets/images/logo_full.png" alt="IBNCC logo" class="brand-logo" width="52" height="52" />
          <span class="brand-tagline"><span class="brand-tagline-line">AN INITIATIVE OF</span><span
              class="brand-tagline-line">CATHOLIC CONGRESS</span></span>
        </span>
      </a>

      <form class="search-bar" role="search" action="#" method="get">
        <input type="search" name="q" placeholder="Search for products, brands and more..." aria-label="Search" />
        <button type="submit" class="search-btn" aria-label="Submit search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>

      <div class="header-actions">
        @auth
          <div class="header-profile">
            <button type="button" class="header-profile-btn" aria-haspopup="menu" aria-expanded="false"
              aria-label="Account menu for {{ auth()->user()->name }}">
              <span class="header-profile-initial"
                aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            </button>
            <div class="header-profile-menu" role="menu" hidden>
              <p class="header-profile-name">{{ auth()->user()->name }}</p>
              <a href="{{route('trading.myaccount')}}" class="header-profile-item" role="menuitem">
                <i class="fa-regular fa-user" aria-hidden="true"></i> My Account
              </a>
              <a href="seller_page.html" class="header-profile-item" role="menuitem">
                <i class="fa-regular fa-user" aria-hidden="true"></i> Seller Page
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

        <a href="wish-list.html" class="icon-btn" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></a>
        <a href="cart.html" class="icon-btn cart-btn" aria-label="Shopping cart">
          <i class="fa-solid fa-cart-shopping"></i><span class="cart-badge">0</span>
        </a>
      </div>
    </div>
  </header>

 

  @yield('body')

  <!-- ========== FOOTER ========== -->
  <footer class="site-footer" id="footer">
    <div class="footer-card">
      <div class="container footer-grid">
        <div class="footer-brand">
          <div class="footer-brand-inner">
            <img src="assets/images/logo_full.png" alt="IBNCC" width="56" height="56" />
            <div>
              <strong class="footer-tagline">International Business Network <br />
                of Catholic Congress</strong>
              <!-- <span>International Business Network</span> -->
              <p>
                A global platform connecting Catholic entrepreneurs,
                professionals and organizations to collaborate, trade and grow
                together.
              </p>
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






  @include('components.auth-modals')


 <script src="js/cchub/ch-mega-menu.js"></script>
    <script src="js/cchub/ch-main.js"></script>
    <script src="js/cchub/ch-trading.js"></script>
    <script src="js/cchub/ch-login.js"></script>
  @stack('scripts')
</body>

</html>