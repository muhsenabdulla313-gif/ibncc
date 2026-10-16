@php
  $noImg = asset('assets/images/no-image.avif');
  $loc = collect([$m->presentAddress?->district?->name, $m->presentAddress?->state?->name])->filter()->implode(', ');
@endphp
<li class="profile-card {{ $mode === 'blocked' ? 'profile-card--blocked' : '' }}" data-uuid="{{ $m->uuid }}">
  <div class="profile-main">
    <img class="profile-avatar" src="{{ $m->profile?->photo_url }}" onerror="this.onerror=null;this.src='{{ $noImg }}'"
         alt="Portrait of {{ $m->name }}" width="72" height="72" />
    <div class="profile-info">
      <h3 class="profile-name">
        {{ $m->name }}
        @if ($mode !== 'blocked' && $m->phone_verified_at)
          <i class="fa-solid fa-circle-check" title="Verified" aria-label="Verified"></i>
        @endif
      </h3>
      <p class="profile-role">{{ $m->profile?->job?->name ?: 'Profession not added yet' }}</p>
      <p class="profile-bio">{{ $m->profile?->about ?: "This member hasn't added a bio yet." }}</p>

      @if ($mode === 'saved')
        <p class="profile-status is-{{ $m->profile?->status ?: 'available' }}">
          <span class="status-dot" aria-hidden="true"></span>{{ ucfirst($m->profile?->status ?: 'available') }}
        </p>
      @elseif ($mode === 'shared')
        <span class="activity-meta-tag">
          <i class="fa-solid fa-share-nodes"></i>
          Shared{{ !empty($to) ? ' with ' . $to : '' }} on {{ $date->format('d M Y') }}
        </span>
      @else
        <span class="activity-meta-tag" style="background:#fee2e2;color:#b91c1c;">
          <i class="fa-solid fa-ban" style="color:#b91c1c;"></i> Blocked on {{ $date?->format('d M Y') }}
        </span>
      @endif
    </div>
  </div>

  <div class="profile-location">
    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
    <div>
      <strong>{{ $loc ?: 'Location not added yet' }}</strong>
      <span>{{ $m->presentAddress?->district ? $m->presentAddress->district->name . ' District' : '' }}</span>
    </div>
  </div>

  <div class="profile-contact">
    @if ($mode !== 'blocked')
      <a href="tel:+91{{ $m->phone }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> +91 {{ $m->phone }}</a>
      @if ($m->email)
        <a href="mailto:{{ $m->email }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $m->email }}</a>
      @endif
    @endif

    <div class="activity-action-wrap">
      @if ($mode === 'saved')
        <button type="button" class="activity-action-btn activity-action-btn--unsave" data-action="unsave" data-name="{{ $m->name }}" title="Remove from saved">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i> Unsave
        </button>
      @elseif ($mode === 'shared')
        <a href="{{ route('members.show', $m->uuid) }}?share=1" class="activity-action-btn activity-action-btn--share">
          <i class="fa-solid fa-share-nodes"></i> Share Again
        </a>
      @else
        <button type="button" class="activity-action-btn activity-action-btn--unblock" data-action="unblock" data-name="{{ $m->name }}">
          <i class="fa-solid fa-lock-open"></i> Unblock Profile
        </button>
      @endif
    </div>
  </div>

  @if ($mode === 'blocked')
    <span class="profile-open" style="opacity:.4;cursor:not-allowed;" aria-label="Profile blocked"><i class="fa-solid fa-ban" aria-hidden="true"></i></span>
  @else
    <a href="{{ route('members.show', $m->uuid) }}" class="profile-open" aria-label="View {{ $m->name }} profile">
      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </a>
  @endif
</li>