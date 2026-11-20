@php
    /**
     * Trading Navbar – 100% Dynamic Mega-Menu.
     * Matches public/ch-trading.html exact design & structure.
     *
     * Displays all active parent categories from the database,
     * their corresponding subcategories (groups), and nested subcategories (items).
     * Links navigate to the Trading / Wholesale product listing pages.
     */
    $categories = $categories ?? \App\Models\Category::whereNull('parent_id')
        ->where('is_active', true)
        ->with(['children' => function ($q) {
            $q->where('is_active', true)
              ->with(['children' => fn($q2) => $q2->where('is_active', true)->orderBy('sort_order')])
              ->orderBy('sort_order');
        }])
        ->orderBy('sort_order')
        ->get();

    // Default icon map matching Font Awesome 6
    $iconMap = [
        'food-grains'              => 'fa-solid fa-wheat-awn',
        'agro-products'            => 'fa-solid fa-seedling',
        'electronics-wholesale'    => 'fa-solid fa-desktop',
        'home-furniture-trading'   => 'fa-solid fa-couch',
        'spices-oils'              => 'fa-solid fa-mortar-pestle',
        'packaging-supplies'       => 'fa-solid fa-box',
        'electronics'              => 'fa-solid fa-mobile-screen-button',
        'furniture'                => 'fa-solid fa-couch',
        'tvs-appliances'           => 'fa-solid fa-tv',
        'clothing'                 => 'fa-solid fa-shirt',
        'men'                      => 'fa-solid fa-person',
        'women'                    => 'fa-solid fa-person-dress',
        'baby-kids'                => 'fa-solid fa-baby',
        'home-furniture'           => 'fa-solid fa-house',
        'sports-books-more'        => 'fa-solid fa-futbol',
        'farm-garden'              => 'fa-solid fa-leaf',
        'food-items'               => 'fa-solid fa-apple-whole',

        // Subcategory icons
        'mobiles'                  => 'fa-solid fa-mobile-screen-button',
        'televisions'              => 'fa-solid fa-tv',
        'television'               => 'fa-solid fa-tv',
        'laptops'                  => 'fa-solid fa-laptop',
        'tablets'                  => 'fa-solid fa-tablet-screen-button',
        'smart-wearable-tech'      => 'fa-solid fa-clock',
        'health-care-appliances'   => 'fa-solid fa-heart-pulse',
        'mobile-accessories'       => 'fa-solid fa-headphones',
        'mobile-accessories-bulk'  => 'fa-solid fa-headphones',
        'computer-accessories'     => 'fa-solid fa-keyboard',
        'computer-peripherals'     => 'fa-solid fa-print',
        'commercial-displays'      => 'fa-solid fa-tv',
        'audio-peripherals-wholesale' => 'fa-solid fa-volume-high',
        'desktop-pcs'              => 'fa-solid fa-desktop',
        'speakers'                 => 'fa-solid fa-volume-high',
        'refrigerators'            => 'fa-solid fa-temperature-low',
        'top-brands'               => 'fa-solid fa-star',
        'footwear'                 => 'fa-solid fa-shoe-prints',
        'mens-grooming'            => 'fa-solid fa-scissors',
        'top-wear'                 => 'fa-solid fa-shirt',
        'western-wear'             => 'fa-solid fa-vest',
        'ethnic-wear'              => 'fa-solid fa-vest-patches',
        'lingerie-sleepwear'       => 'fa-solid fa-vest',
        'boys-clothing'            => 'fa-solid fa-child',
        'girls-clothing'           => 'fa-solid fa-child-dress',
        'kids-footwear'            => 'fa-solid fa-shoe-prints',
        'kitchen-cookware'         => 'fa-solid fa-fire-burner',
        'kitchen-storage'          => 'fa-solid fa-box-archive',
        'bedroom-furniture'        => 'fa-solid fa-bed',
        'office-commercial-furniture' => 'fa-solid fa-chair',
        'hospitality-dining-furniture' => 'fa-solid fa-utensils',
        'sports'                   => 'fa-solid fa-futbol',
        'exercise-fitness'         => 'fa-solid fa-dumbbell',
        'books'                    => 'fa-solid fa-book',
        'seeds-saplings'           => 'fa-solid fa-seedling',
        'soil-fertilizers'         => 'fa-solid fa-flask',
        'gardening-tools'          => 'fa-solid fa-trowel',
        'featured-stores'          => 'fa-solid fa-store',
        'fresh-produce'            => 'fa-solid fa-carrot',
        'dairy-eggs'               => 'fa-solid fa-egg',
        'rice-paddy'               => 'fa-solid fa-wheat-awn',
        'wheat-flour'              => 'fa-solid fa-bowl-rice',
        'pulses-dal'               => 'fa-solid fa-seedling',
        'bulk-vegetables'          => 'fa-solid fa-carrot',
        'plantation-crops'         => 'fa-solid fa-tree',
        'organic-fertilizers'      => 'fa-solid fa-flask',
        'whole-spices-bulk'        => 'fa-solid fa-pepper-hot',
        'ground-spices-wholesale'  => 'fa-solid fa-mortar-pestle',
        'commercial-oils'          => 'fa-solid fa-bottle-droplet',
        'corrugated-boxes'         => 'fa-solid fa-box',
        'sacks-and-bags'           => 'fa-solid fa-bag-shopping',
        'protective-and-tape'      => 'fa-solid fa-tape',
    ];

    // Helper: Category icon resolution
    $getCategoryIcon = function ($category, $default = 'fa-solid fa-tag') use ($iconMap) {
        if (!empty($category->icon)) {
            $icon = trim($category->icon);
            if (!str_starts_with($icon, 'fa-solid') && !str_starts_with($icon, 'fa-regular') && !str_starts_with($icon, 'fa-brands')) {
                return 'fa-solid ' . $icon;
            }
            return $icon;
        }
        return $iconMap[$category->slug] ?? $default;
    };

    // Default featured image map matching ch-trading.html
    $featuredMediaMap = [
        'electronics'            => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=640&q=80',
        'tvs-appliances'         => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=640&q=80',
        'men'                    => 'https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?auto=format&fit=crop&w=640&q=80',
        'women'                  => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=640&q=80',
        'baby-kids'              => 'https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?auto=format&fit=crop&w=640&q=80',
        'home-furniture'         => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=640&q=80',
        'furniture'              => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=640&q=80',
        'sports-books-more'      => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=640&q=80',
        'farm-garden'            => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=640&q=80',
        'food-items'             => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=640&q=80',
        'clothing'               => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=640&q=80',
        'food-grains'            => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=640&q=80',
        'agro-products'          => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=640&q=80',
        'electronics-wholesale'  => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=640&q=80',
        'home-furniture-trading' => 'https://images.unsplash.com/photo-1580481077195-c3a821a58875?auto=format&fit=crop&w=640&q=80',
        'spices-oils'            => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=640&q=80',
        'packaging-supplies'     => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=640&q=80',
    ];

    $resolveCatImage = function ($cat) use ($featuredMediaMap) {
        if (!empty($cat->image)) {
            $img = $cat->image;
            if (\Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '//'])) {
                return $img;
            }
            $clean = ltrim($img, '/\\');
            if (file_exists(public_path($clean))) return asset($clean);
            if (file_exists(public_path('storage/' . $clean))) return asset('storage/' . $clean);
            return asset($clean);
        }
        return $featuredMediaMap[$cat->slug] ?? 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=640&q=80';
    };
@endphp

{{-- ==================== TRADING NAVBAR (CH-TRADING.HTML COMPLIANT) ==================== --}}
<nav class="category-nav" aria-label="Product categories">
    <div class="category-nav-inner">

        @foreach($categories as $category)
            @php
                $catIcon = $getCategoryIcon($category, 'fa-solid fa-boxes-stacked');
                $catUrl  = route('trading.products.index', ['cat' => $category->slug]);
                $hasChildren = $category->children->isNotEmpty();
                $featuredImg = $resolveCatImage($category);

                // Calculate columns and groups per column
                $groups = $category->children;
                $colCount = min(max($groups->count(), 1), 4);
                $perCol = $colCount > 0 ? (int) ceil($groups->count() / $colCount) : 1;
                $columns = $groups->chunk($perCol > 0 ? $perCol : 1);
            @endphp

            <div class="category-dropdown" data-mega="{{ $category->slug }}">

                <a href="{{ $catUrl }}" class="category-item">
                    <span class="category-icon">
                        <i class="{{ $catIcon }}" aria-hidden="true"></i>
                    </span>
                    <span class="category-label">{{ $category->name }}</span>
                    @if($hasChildren)
                        <i class="fa-solid fa-chevron-down category-chevron" aria-hidden="true"></i>
                    @endif
                </a>

                @if($hasChildren)
                    <div class="mega-menu" role="menu">
                        <div class="mega-menu-scroll">
                            <div class="mega-menu-shell">

                                {{-- Featured Aside Card --}}
                                <aside class="mega-featured">
                                    <p class="mega-featured-eyebrow">Shop {{ $category->name }}</p>
                                    <h3 class="mega-featured-title">
                                        {{ $category->is_trading ? 'Wholesale & Bulk Supply' : $category->name }}
                                    </h3>
                                    <p class="mega-featured-subtitle">
                                        {{ $category->description ?: 'Discover top quality ' . strtolower($category->name) . ' in bulk with wholesale tiers.' }}
                                    </p>
                                    <a href="{{ $catUrl }}" class="mega-featured-cta">
                                        Shop Now
                                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                    <div class="mega-featured-media">
                                        <img src="{{ $featuredImg }}" alt="{{ $category->name }}" loading="lazy" />
                                    </div>
                                </aside>

                                {{-- Subcategory Mega Columns --}}
                                <div class="mega-columns" style="--mega-cols: {{ $colCount }}">
                                    @foreach($columns as $chunk)
                                        <div class="mega-col">
                                            @foreach($chunk as $group)
                                                @php
                                                    $groupIcon = $getCategoryIcon($group, 'fa-solid fa-angle-right');
                                                    $groupUrl = route('trading.products.index', [
                                                        'cat' => $category->slug,
                                                        'group' => $group->slug,
                                                    ]);
                                                @endphp
                                                <div class="mega-group">
                                                    <a href="{{ $groupUrl }}" class="mega-heading">
                                                        <span class="mega-heading-icon">
                                                            <i class="{{ $groupIcon }}" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="mega-heading-text">{{ $group->name }}</span>
                                                        <i class="fa-solid fa-chevron-right mega-heading-chevron" aria-hidden="true"></i>
                                                    </a>

                                                    @if($group->children->isNotEmpty())
                                                        <ul class="mega-list">
                                                            @foreach($group->children as $item)
                                                                <li>
                                                                    <a href="{{ route('trading.products.index', ['cat' => $category->slug, 'group' => $group->slug, 'item' => $item->slug]) }}">
                                                                        {{ $item->name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @endforeach

    </div>
</nav>
