{{-- Home page Events section (dynamic). Use: @include('partials.events-section') --}}
<section class="section events-gallery" id="events">
  <div class="container">

    <!-- Upcoming Events -->
    <div class="events-subsection events-upcoming">
      <div class="section-header section-header-upc reveal">
        <p class="section-eyebrow">Calendar</p>
        <h2 class="section-title">Upcoming Events</h2>
        <p class="section-lead">
          Join us for high-impact gatherings that connect Christian business leaders across the globe.
        </p>
      </div>

      <div class="upcoming-gallery">
        @forelse (\App\Models\Event::upcoming()->get() as $e)
          <article class="upcoming-card reveal" data-stagger="{{ $loop->index }}">
            <div class="upcoming-card-media">
              <img src="{{ $e->cover_url }}" alt="{{ $e->title }}" loading="lazy" width="900" height="520" />
              <span class="upcoming-badge">Upcoming</span>
            </div>
            <div class="upcoming-card-body">
              <h3 class="upcoming-card-title">{{ $e->title }}</h3>
              @if ($e->tagline)
                <p class="upcoming-card-tagline">{{ $e->tagline }}</p>
              @endif
              <ul class="upcoming-card-meta">
                <li>
                  <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                  <span>{{ $e->event_date->format('jS F Y') }}</span>
                </li>
                @if ($e->venue)
                  <li>
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    <span>{{ $e->venue }}</span>
                  </li>
                @endif
              </ul>
              <a href="{{ route('events.show', $e->slug) }}" class="btn btn-primary upcoming-card-btn">
                View Details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
            </div>
          </article>
        @empty
          <p class="section-lead">No upcoming events right now. Please check back soon.</p>
        @endforelse
      </div>
    </div>

    <!-- Previous Events -->
    <div class="events-subsection events-previous">
      <div class="section-header reveal">
        <p class="section-eyebrow">Archive</p>
        <h2 class="section-title">Previous Events</h2>
        <p class="section-lead">
          Highlights from past conclaves, forums and networking gatherings across the IBNCC community.
        </p>
      </div>

      <div class="previous-gallery">
        @foreach (\App\Models\Event::previous()->with('images')->get() as $e)
          <article class="previous-card reveal" data-stagger="{{ $loop->index }}" tabindex="0" aria-haspopup="dialog">
            <template class="previous-card-slides">
              @foreach ($e->slide_urls as $u)
                <img src="{{ $u }}" alt="{{ $e->title }}" />
              @endforeach
            </template>
            <div class="previous-card-media">
              <img src="{{ $e->cover_url }}" alt="{{ $e->title }}" loading="lazy" width="480" height="320" />
              <div class="previous-card-overlay">
                <div class="previous-card-details">
                  <p>
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    {{ $e->event_date->format('jS F Y') }}
                  </p>
                  @if ($e->city)
                    <p>
                      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                      {{ $e->city }}
                    </p>
                  @endif
                </div>
              </div>
            </div>
            <div class="previous-card-body">
              <h3>{{ $e->title }}</h3>
              <a href="#" class="btn btn-hero-secondary previous-card-btn">
                View More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
            </div>
          </article>
        @endforeach
      </div>
    </div>

  </div>
</section>
