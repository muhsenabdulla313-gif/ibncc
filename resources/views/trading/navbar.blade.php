
 <nav class="category-nav" aria-label="Product categories">
    <div class="category-nav-inner">

        @foreach($categories as $category)

            <div class="category-dropdown"
                 data-mega="{{ $category->slug }}">

                <a href="{{ url('/products') }}?cat={{ $category->slug }}"
                   class="category-item">

                    <span class="category-icon">
                        <i class="{{ $category->image ?? 'fa-solid fa-list' }}"></i>
                    </span>

                    <span class="category-label">
                        {{ $category->name }}
                    </span>

                    <i class="fa-solid fa-chevron-down category-chevron"
                       aria-hidden="true"></i>
                </a>

                <div class="mega-menu" role="menu">
                    <div class="mega-menu-scroll">
                        <div class="mega-menu-shell">

                            {{-- Featured section --}}
                            <aside class="mega-featured">
                                <p class="mega-featured-eyebrow">
                                    Shop {{ $category->name }}
                                </p>

                                <h3 class="mega-featured-title">
                                    {{ $category->name }}
                                </h3>

                                <p class="mega-featured-subtitle">
                                    Explore our {{ strtolower($category->name) }} collection.
                                </p>

                                <a href="{{ url('/products') }}?cat={{ $category->slug }}"
                                   class="mega-featured-cta">
                                    Shop Now
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </aside>

                            {{-- Dynamic groups --}}
                            <div class="mega-columns">

                                @foreach($category->children as $group)

                                    <div class="mega-col">
                                        <div class="mega-group">

                                            <a href="{{ url('/products') }}?cat={{ $category->slug }}&group={{ $group->slug }}"
                                               class="mega-heading">

                                                <span class="mega-heading-icon">
                                                    <i class="{{ $group->icon ?? 'fa-solid fa-angle-right' }}"></i>
                                                </span>

                                                <span class="mega-heading-text">
                                                    {{ $group->name }}
                                                </span>

                                                <i class="fa-solid fa-chevron-right mega-heading-chevron"></i>
                                            </a>

                                            {{-- Dynamic subcategories --}}
                                            @if($group->children->isNotEmpty())
                                                <ul class="mega-list">

                                                    @foreach($group->children as $item)
                                                        <li>
                                                            <a href="{{ url('/products') }}?cat={{ $category->slug }}&group={{ $group->slug }}&item={{ $item->slug }}">
                                                                {{ $item->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach

                                                </ul>
                                            @endif

                                        </div>
                                    </div>

                                @endforeach

                            </div>

                        </div>
                    </div>
                </div>

            </div>

        @endforeach

    </div>
</nav>