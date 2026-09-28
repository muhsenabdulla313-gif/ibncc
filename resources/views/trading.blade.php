@extends('layouts.trading')
@section('body')
   

    <section class="hero" aria-label="Promotional banners">
      <div class="hero-slider" id="heroSlider">
        <div class="hero-track" id="heroTrack">
          <article class="hero-slide is-active" data-index="0">
            <img
              src="assets/images/gift2.png"
              alt="Elegant jewelry collection against soft floral backdrop"
              class="hero-slide-img"
            />
            <div class="hero-slide-overlay"></div>
            <div class="hero-slide-content hero-content-right">
              <p class="hero-eyebrow">IBNCC Presents</p>
              <h2 class="hero-title">Floral Bloom</h2>
              <p class="hero-subtitle">
                Timeless pieces for moments that matter
              </p>
              <a href="#shop" class="hero-cta">Shop Now</a>
            </div>
          </article>

          <article class="hero-slide" data-index="1">
            <img
              src="assets/images/harvest.png"
              alt="Fresh farm produce harvested in golden sunlight"
              class="hero-slide-img"
            />
            <div class="hero-slide-overlay"></div>
            <div class="hero-slide-content hero-content-left">
              <p class="hero-eyebrow">Farm &amp; Garden</p>
              <h2 class="hero-title">Harvest Fresh</h2>
              <p class="hero-subtitle">
                Quality produce from trusted local growers
              </p>
              <a href="#farm" class="hero-cta">Explore Deals</a>
            </div>
          </article>

          <article class="hero-slide" data-index="2">
            <img
              src="assets/images/network1.png"
              alt="Professionals connecting at a business networking event"
              class="hero-slide-img"
            />
            <div class="hero-slide-overlay darker"></div>
            <div class="hero-slide-content hero-content-left">
              <p class="hero-eyebrow">Networking</p>
              <h2 class="hero-title">Connect &amp; Grow</h2>
              <p class="hero-subtitle">
                Build trusted partnerships across the community
              </p>
              <a href="#networking" class="hero-cta">Join Network</a>
            </div>
          </article>

          <article class="hero-slide" data-index="3">
            <img
              src="assets/images/sld3.png"
              alt="Colorful fresh food market with vegetables and groceries"
              class="hero-slide-img"
            />
            <div class="hero-slide-overlay"></div>
            <div class="hero-slide-content hero-content-right">
              <p class="hero-eyebrow">Food Items</p>
              <h2 class="hero-title">Market Essentials</h2>
              <p class="hero-subtitle">Everyday staples delivered with care</p>
              <a href="#food" class="hero-cta">Shop Food</a>
            </div>
          </article>

          <article class="hero-slide" data-index="4">
            <img
              src="assets/images/wholesale.png"
              alt="Cargo containers at a trading port during sunset"
              class="hero-slide-img"
            />
            <div class="hero-slide-overlay darker"></div>
            <div class="hero-slide-content hero-content-left">
              <p class="hero-eyebrow">Trading Hub</p>
              <h2 class="hero-title">Bulk. Better. Together.</h2>
              <p class="hero-subtitle">
                Commodities and wholesale deals you can trust
              </p>
              <a href="ch-trading.html" class="hero-cta">Start Trading</a>
            </div>
          </article>
        </div>

        <button
          class="hero-arrow hero-arrow-prev"
          id="heroPrev"
          aria-label="Previous slide"
        >
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button
          class="hero-arrow hero-arrow-next"
          id="heroNext"
          aria-label="Next slide"
        >
          <i class="fa-solid fa-chevron-right"></i>
        </button>

        <div
          class="hero-dots"
          id="heroDots"
          role="tablist"
          aria-label="Slide pagination"
        ></div>
      </div>
    </section>

    <main class="trading-main">
      <!-- TRADING — scrolls right → left -->
      <section
        class="trade-section"
        id="trading"
        aria-labelledby="trading-heading"
      >
        <header class="trade-section-intro">
          <h1 id="trading-heading" class="trade-section-title">Trading</h1>
          <p class="trade-section-subtitle">
            Bulk Trading. Better Value. Stronger Together.
          </p>
        </header>

        <div
          class="marquee marquee--rtl"
          data-marquee
          data-direction="rtl"
          aria-label="Trading categories carousel"
        >
          <button
            type="button"
            class="marquee-arrow marquee-arrow-prev"
            aria-label="Previous"
          >
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button
            type="button"
            class="marquee-arrow marquee-arrow-next"
            aria-label="Next"
          >
            <i class="fa-solid fa-chevron-right"></i>
          </button>

          <div class="marquee-viewport">
            <div class="marquee-track" data-marquee-track>
              <article class="trade-card trade-card--grains">
                <span class="trade-card-icon" aria-hidden="true"
                  ><i class="fa-solid fa-wheat-awn"></i
                ></span>
                <div class="trade-card-copy">
                  <h2>Food Grains</h2>
                  <p>Quality grains in bulk, delivered with trust</p>
                  <a href="#grains" class="trade-card-cta"
                    >Trade Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="trade-card-img"
                  src="https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80"
                  alt="Sacks and bowls of food grains"
                />
              </article>

              <article class="trade-card trade-card--agro">
                <span class="trade-card-icon" aria-hidden="true"
                  ><i class="fa-solid fa-seedling"></i
                ></span>
                <div class="trade-card-copy">
                  <h2>Agro Products</h2>
                  <p>Fresh produce for growing businesses</p>
                  <a href="#agro" class="trade-card-cta"
                    >Trade Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="trade-card-img"
                  src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80"
                  alt="Fresh vegetables in a wooden crate"
                />
              </article>

              <article class="trade-card trade-card--electronics">
                <span class="trade-card-icon" aria-hidden="true"
                  ><i class="fa-solid fa-desktop"></i
                ></span>
                <div class="trade-card-copy">
                  <h2>Electronics Wholesale</h2>
                  <p>Top brands. Best prices. For your business</p>
                  <a href="#electronics-trade" class="trade-card-cta"
                    >Trade Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="trade-card-img"
                  src="https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80"
                  alt="Electronics and appliances ready for wholesale"
                />
              </article>

              <article class="trade-card trade-card--home">
                <span class="trade-card-icon" aria-hidden="true"
                  ><i class="fa-solid fa-couch"></i
                ></span>
                <div class="trade-card-copy">
                  <h2>Home &amp; Furniture</h2>
                  <p>Premium quality furniture for every space</p>
                  <a href="#furniture" class="trade-card-cta"
                    >Trade Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="trade-card-img"
                  src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80"
                  alt="Modern living room furniture"
                />
              </article>

              <article class="trade-card trade-card--spices">
                <span class="trade-card-icon" aria-hidden="true"
                  ><i class="fa-solid fa-mortar-pestle"></i
                ></span>
                <div class="trade-card-copy">
                  <h2>Spices &amp; Oils</h2>
                  <p>Authentic flavours in wholesale quantities</p>
                  <a href="#spices" class="trade-card-cta"
                    >Trade Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="trade-card-img"
                  src="https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80"
                  alt="Colourful spices and cooking oils"
                />
              </article>

              <article class="trade-card trade-card--packaging">
                <span class="trade-card-icon" aria-hidden="true"
                  ><i class="fa-solid fa-box"></i
                ></span>
                <div class="trade-card-copy">
                  <h2>Packaging Supplies</h2>
                  <p>Reliable packing solutions for every shipment</p>
                  <a href="#packaging" class="trade-card-cta"
                    >Trade Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="trade-card-img"
                  src="https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=600&q=80"
                  alt="Stacked packaging boxes ready for shipping"
                />
              </article>
            </div>
          </div>

          <div class="marquee-dots" aria-hidden="true">
            <span class="marquee-dot is-active"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
          </div>
        </div>
      </section>

      <!-- MARKET PLACE — scrolls left → right -->
      <section
        class="trade-section"
        id="marketplace"
        aria-labelledby="marketplace-heading"
      >
        <header class="trade-section-intro">
          <h2 id="marketplace-heading" class="trade-section-title">
            Market Place
          </h2>
          <p class="trade-section-subtitle">
            Discover deals across top categories
          </p>
        </header>

        <div
          class="marquee marquee--ltr"
          data-marquee
          data-direction="ltr"
          aria-label="Marketplace deals carousel"
        >
          <button
            type="button"
            class="marquee-arrow marquee-arrow-prev"
            aria-label="Previous"
          >
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button
            type="button"
            class="marquee-arrow marquee-arrow-next"
            aria-label="Next"
          >
            <i class="fa-solid fa-chevron-right"></i>
          </button>

          <div class="marquee-viewport">
            <div class="marquee-track" data-marquee-track>
              <article class="market-card market-card--luggage">
                <div class="market-card-copy">
                  <h3>Luggage Collection</h3>
                  <p>Travel in style with top brands</p>
                  <strong class="market-card-offer">Up to 50% Off</strong>
                  <a href="#luggage" class="market-card-cta"
                    >Shop Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="market-card-img"
                  src="https://images.unsplash.com/photo-1565022449183-c4a2d0b2a0a0?auto=format&fit=crop&w=500&q=80"
                  alt="Red and blue travel luggage"
                  onerror="
                    this.src =
                      'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=500&q=80'
                  "
                />
              </article>

              <article class="market-card market-card--watches">
                <div class="market-card-copy">
                  <h3>Watches &amp; More</h3>
                  <p>Timeless style. Smart choices.</p>
                  <strong class="market-card-offer">Up to 60% Off</strong>
                  <a href="#watches" class="market-card-cta"
                    >Explore Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="market-card-img"
                  src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=500&q=80"
                  alt="Smartwatch and classic wristwatch"
                />
              </article>

              <article class="market-card market-card--jewellery">
                <div class="market-card-copy">
                  <h3>Jewellery Collection</h3>
                  <p>Shine with elegance</p>
                  <strong class="market-card-offer">Up to 60% Off</strong>
                  <a href="#jewellery" class="market-card-cta"
                    >Shop Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="market-card-img"
                  src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=500&q=80"
                  alt="Gold jewellery necklace"
                />
              </article>

              <article class="market-card market-card--kindle">
                <div class="market-card-copy">
                  <h3>Kindle Paperwhite</h3>
                  <p>Your next great read awaits</p>
                  <p class="market-card-price">
                    <strong>₹16,999</strong>
                    <span>₹21,999</span>
                  </p>
                  <a href="#kindle" class="market-card-cta"
                    >Buy Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="market-card-img"
                  src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=500&q=80"
                  alt="E-reader device"
                />
              </article>

              <article class="market-card market-card--fashion">
                <div class="market-card-copy">
                  <h3>Fashion Essentials</h3>
                  <p>Everyday style for the family</p>
                  <strong class="market-card-offer">Up to 40% Off</strong>
                  <a href="#fashion" class="market-card-cta"
                    >Shop Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="market-card-img"
                  src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=500&q=80"
                  alt="Fashion clothing and accessories"
                />
              </article>

              <article class="market-card market-card--beauty">
                <div class="market-card-copy">
                  <h3>Beauty &amp; Care</h3>
                  <p>Glow with trusted brands</p>
                  <strong class="market-card-offer">Up to 35% Off</strong>
                  <a href="#beauty" class="market-card-cta"
                    >Explore Now <i class="fa-solid fa-arrow-right"></i
                  ></a>
                </div>
                <img
                  class="market-card-img"
                  src="assets/images/bt.jpg"
                  alt="Beauty and personal care products"
                />
              </article>
            </div>
          </div>

          <div class="marquee-dots" aria-hidden="true">
            <span class="marquee-dot is-active"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
          </div>
        </div>
      </section>

      <!-- Trading / Marketplace Tabs -->
      <div
        class="trading-mode-tabs"
        role="tablist"
        aria-label="Trading and Marketplace"
      >
        <button
          type="button"
          class="trading-mode-tab is-active"
          id="tab-marketplace"
          role="tab"
          aria-selected="true"
          data-mode="marketplace"
        >
          <span class="trading-mode-tab-icon" aria-hidden="true">
            <i class="fa-solid fa-store"></i>
          </span>
          <span class="trading-mode-tab-label">Marketplace</span>
        </button>
        <button
          type="button"
          class="trading-mode-tab"
          id="tab-trading"
          role="tab"
          aria-selected="false"
          data-mode="trading"
        >
          <span class="trading-mode-tab-icon" aria-hidden="true">
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
          </span>
          <span class="trading-mode-tab-label">Trading</span>
        </button>
      </div>

      <!-- Galleries -->
      <section
        class="gallery-block"
        id="browse-category"
        aria-labelledby="browse-heading"
      >
        <header class="gallery-block-header">
          <div>
            <h2 id="browse-heading" class="gallery-block-title">
              Browse by Category
            </h2>
            <p class="gallery-block-subtitle">
              Find what you love from top categories
            </p>
          </div>
        </header>

        <div class="category-gallery">
          <a
            href="ch-product-list.html?cat=electronics"
            class="category-gallery-card"
            style="--cat-bg: #e8eef5"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
            </div>
            <div class="category-gallery-footer">
              <strong>Electronics</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=appliances"
            class="category-gallery-card"
            style="--cat-bg: #eef2f6"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
            </div>
            <div class="category-gallery-footer">
              <strong>TVs and Appliances</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=men"
            class="category-gallery-card"
            style="--cat-bg: #e8eef5"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1617137968427-85924c800a22?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img src="assets/images/glass2.jpg" alt="" />
              <!-- <img
                src="https://images.unsplash.com/photo-1542272454315-7ad9f8b4c3f6?auto=format&fit=crop&w=200&q=80"
                alt=""
              /> -->
              <img src="assets/images/men-outfit.jpg" alt="" />
              <!-- <img
                src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=200&q=80"
                alt=""
              /> -->
            </div>
            <div class="category-gallery-footer">
              <strong>Men</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=women"
            class="category-gallery-card"
            style="--cat-bg: #eaf4fb"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img src="assets/images/women-outfit.jpg" alt="" />
              <!-- <img
                src="https://images.unsplash.com/photo-1590874103328-eac38a67437f?auto=format&fit=crop&w=200&q=80"
                alt=""
              /> -->
            </div>
            <div class="category-gallery-footer">
              <strong>Women</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=kids"
            class="category-gallery-card"
            style="--cat-bg: #eef6fb"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1519689373023-dd07c7988603?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
            </div>
            <div class="category-gallery-footer">
              <strong>Baby and Kids</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=home"
            class="category-gallery-card"
            style="--cat-bg: #ebe6df"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
            </div>
            <div class="category-gallery-footer">
              <strong>Home and Furniture</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=sports"
            class="category-gallery-card"
            style="--cat-bg: #e8f0ea"
          >
            <div class="category-gallery-media">
              <img src="assets/images/sp.jpg" alt="" />
              <img
                src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1551958219-acbc608c6377?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
            </div>
            <div class="category-gallery-footer">
              <strong>Sports</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=sports"
            class="category-gallery-card"
            style="--cat-bg: #eef0f4"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img src="assets/images/bk.jpg" alt="" />
            </div>
            <div class="category-gallery-footer">
              <strong>Books &amp; More</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=farm"
            class="category-gallery-card"
            style="--cat-bg: #eaf3e6"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1592419044706-39796d40f98c?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img src="assets/images/plant.jpg" alt="" />
            </div>
            <div class="category-gallery-footer">
              <strong>Farm &amp; Garden</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>

          <a
            href="ch-product-list.html?cat=food"
            class="category-gallery-card"
            style="--cat-bg: #fbf6e4"
          >
            <div class="category-gallery-media">
              <img
                src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
              <img
                src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=200&q=80"
                alt=""
              />
            </div>
            <div class="category-gallery-footer">
              <strong>Food Items</strong>
              <span
                >Explore
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </div>
          </a>
        </div>
      </section>

      <section
        class="gallery-block"
        id="bestsellers"
        aria-labelledby="bestsellers-heading"
      >
        <header class="gallery-block-header gallery-block-header--row">
          <div>
            <h2 id="bestsellers-heading" class="gallery-block-title">
              Bestsellers
            </h2>
            <p class="gallery-block-subtitle">
              Top picks loved by our customers
            </p>
          </div>
          <a href="#all-bestsellers" class="gallery-view-all"
            >View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
          ></a>
        </header>

        <div class="bestseller-grid">
          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=400&q=80"
                alt="boAt Airdopes earbuds"
              />
            </div>
            <h3>boAt Airdopes 141</h3>
            <p>Wireless Earbuds</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.5
              <span>(12.4k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹1,299</strong><s>₹2,999</s><em>57% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80"
                alt="Noise ColorFit smartwatch"
              />
            </div>
            <h3>Noise ColorFit Pro 4</h3>
            <p>Smartwatch</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.4
              <span>(8.1k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹2,499</strong><s>₹5,999</s><em>58% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=80"
                alt="Sony headphones"
              />
            </div>
            <h3>Sony WH-CH520</h3>
            <p>Bluetooth Headphones</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.6
              <span>(5.2k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹4,490</strong><s>₹5,990</s><em>25% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?auto=format&fit=crop&w=400&q=80"
                alt="Skincare set"
              />
            </div>
            <h3>Minimalist SPF 50</h3>
            <p>Sunscreen</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.7
              <span>(21k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹399</strong><s>₹499</s><em>20% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80"
                alt="Running shoes"
              />
            </div>
            <h3>Nike Revolution 6</h3>
            <p>Men&apos;s Running Shoes</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.3
              <span>(3.8k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹3,695</strong><s>₹4,495</s><em>18% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=400&q=80"
                alt="Kitchen mixer"
              />
            </div>
            <h3>Prestige Mixer</h3>
            <p>Kitchen Appliance</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.2
              <span>(9.6k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹2,999</strong><s>₹5,495</s><em>45% OFF</em>
            </div>
          </article>
        </div>
      </section>

      <section
        class="gallery-block"
        id="similar-searches"
        aria-labelledby="similar-heading"
      >
        <header class="gallery-block-header gallery-block-header--row">
          <div>
            <h2 id="similar-heading" class="gallery-block-title">
              Similar to your searches
            </h2>
            <p class="gallery-block-subtitle">Handpicked just for you</p>
          </div>
          <a href="#all-similar" class="gallery-view-all"
            >View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
          ></a>
        </header>

        <div class="similar-rows">
          <div class="similar-row">
            <a
              href="#luggage"
              class="similar-lead"
              style="--lead-bg: #e8f0e4; --lead-color: #2f5d3a"
            >
              <h3>Luggage &amp; Travel</h3>
              <p>Built for every journey</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#luggage-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=500&q=80"
                alt="Hard-shell suitcase"
              />
            </a>
            <a href="#luggage-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1565026057447-bc90a3dceb87?auto=format&fit=crop&w=500&q=80"
                alt="Travel duffel bag"
              />
            </a>
            <a href="#luggage-3" class="similar-photo">
              <img src="assets/images/lag2.jpg" alt="Cabin luggage set" />
            </a>
          </div>

          <div class="similar-row">
            <a
              href="#smartwatches"
              class="similar-lead"
              style="--lead-bg: #eaf4fb; --lead-color: #0b2a4a"
            >
              <h3>Smartwatches</h3>
              <p>Track fitness in style</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#watch-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=500&q=80"
                alt="Black smartwatch"
              />
            </a>
            <a href="#watch-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?auto=format&fit=crop&w=500&q=80"
                alt="Sport smartwatch"
              />
            </a>
            <a href="#watch-3" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=500&q=80"
                alt="Classic smartwatch"
              />
            </a>
          </div>

          <div class="similar-row">
            <a
              href="#home-essentials"
              class="similar-lead"
              style="--lead-bg: #fbf6e4; --lead-color: #c9a017"
            >
              <h3>Home Essentials</h3>
              <p>Comfort for every room</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#home-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=500&q=80"
                alt="Bedroom furniture"
              />
            </a>
            <a href="#home-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=500&q=80"
                alt="Dining set"
              />
            </a>
            <a href="#home-3" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=500&q=80"
                alt="Living room sofa"
              />
            </a>
          </div>

          <div class="similar-row">
            <a
              href="#kitchen"
              class="similar-lead"
              style="--lead-bg: #e8f1fb; --lead-color: #1d4e89"
            >
              <h3>Kitchen Appliances</h3>
              <p>Cook smarter every day</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#kitchen-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=500&q=80"
                alt="Microwave oven"
              />
            </a>
            <a href="#kitchen-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1585515320310-259814833e62?auto=format&fit=crop&w=500&q=80"
                alt="Air fryer"
              />
            </a>
            <a href="#kitchen-3" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?auto=format&fit=crop&w=500&q=80"
                alt="Blender"
              />
            </a>
          </div>
        </div>
      </section>
    </main>

    <!-- ========== FOOTER ========== -->
   @endsection