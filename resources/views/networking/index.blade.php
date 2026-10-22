{{-- resources/views/networking/index.blade.php --}}
@extends('layouts.network')
@section('body')

  <form method="GET" action="{{ route('networking.index') }}" id="networkingForm">

    <div class="networking-hero-bar">
      <div class="networking-hero-inner">
        <div class="networking-brand">
          <span class="networking-brand-icon" aria-hidden="true">
            <i class="fa-solid fa-share-nodes"></i>
          </span>
          <div>
            <h2 id="networking-heading" class="networking-brand-title">
              Networking
            </h2>
            <p class="networking-brand-tagline">
              Connect. Collaborate. Grow Together.
            </p>
          </div>
        </div>

        <div class="networking-search" role="search" aria-label="Search profiles">
          <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or profession..."
            aria-label="Search by name or profession" />
          <button type="submit" class="networking-search-btn" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </div>

       <div class="networking-location-group">
  <button type="button" class="networking-location" aria-label="Current location">
    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
    <span>
      <small>Current Location:</small>
      {{ $currentLocation }}
    </span>
    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
  </button>
<a href="{{ route('profile.me') }}" class="app-nav-icon app-nav-icon--profile networking-profile-icon"
  aria-label="Your profile" title="Your profile"
  style="position:relative;overflow:hidden;padding:0;">
  <img src="{{ auth()->user()->profile->photo_url }}"
    onerror="this.onerror=null;this.src='{{ asset('assets/images/no-image.avif') }}'"
    alt="Your profile photo"
    style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;">
</a>
</div>
      </div>
    </div>

    <div class="networking-body">
      <aside class="networking-filters" aria-label="Profile filters">
        <header class="filters-header">
          <div class="filters-header-top">
            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
            <h3>Filters</h3>
          </div>
          <p>Refine your search to find the right connections.</p>
        </header>

        <div class="filter-group">
          <h4 class="filter-group-title">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            Location
          </h4>
          <div class="filter-fields">
            <label class="filter-select">
              <span class="visually-hidden">Country</span>
              <select name="country_id" id="nfCountry" data-child="nfState" data-url="{{ url('lookups/states') }}">
                <option value="">Country</option>
                @foreach ($countries as $row)
                  <option value="{{ $row->id }}" @selected(request('country_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
            <label class="filter-select">
              <span class="visually-hidden">State</span>
              <select name="state_id" id="nfState" data-child="nfDistrict" data-url="{{ url('lookups/districts') }}">
                <option value="">State</option>
                @foreach ($states as $row)
                  <option value="{{ $row->id }}" @selected(request('state_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
            <label class="filter-select">
              <span class="visually-hidden">District</span>
              <select name="district_id" id="nfDistrict">
                <option value="">District</option>
                @foreach ($districts as $row)
                  <option value="{{ $row->id }}" @selected(request('district_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
          </div>
        </div>

        <div class="filter-group">
          <h4 class="filter-group-title">
            <i class="fa-solid fa-church" aria-hidden="true"></i>
            Church Hierarchy
          </h4>
          <div class="filter-fields">
            <label class="filter-select">
              <span class="visually-hidden">Rite</span>
              <select name="rite_id" id="nfRite" data-child="nfDiocese" data-url="{{ url('lookups/dioceses') }}">
                <option value="">Rite</option>
                @foreach ($rites as $row)
                  <option value="{{ $row->id }}" @selected(request('rite_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
            <label class="filter-select">
              <span class="visually-hidden">Diocese</span>
              <select name="diocese_id" id="nfDiocese" data-child="nfForane" data-url="{{ url('lookups/foranes') }}">
                <option value="">Diocese</option>
                @foreach ($dioceses as $row)
                  <option value="{{ $row->id }}" @selected(request('diocese_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
            <label class="filter-select">
              <span class="visually-hidden">Forane</span>
              <select name="forane_id" id="nfForane" data-child="nfParish" data-url="{{ url('lookups/parishes') }}">
                <option value="">Forane</option>
                @foreach ($foranes as $row)
                  <option value="{{ $row->id }}" @selected(request('forane_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
            <label class="filter-select">
              <span class="visually-hidden">Parish</span>
              <select name="parish_id" id="nfParish">
                <option value="">Parish</option>
                @foreach ($parishes as $row)
                  <option value="{{ $row->id }}" @selected(request('parish_id') == $row->id)>{{ $row->name }}</option>
                @endforeach
              </select>
            </label>
          </div>
        </div>

        <div class="filter-group">
          <h4 class="filter-group-title">
            <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
            Employment Status
          </h4>
          <label class="filter-select filter-select-full">
            <span class="visually-hidden">Employment Status</span>
            <select name="employment_status">
              <option value="">Select status</option>
              @foreach ($employmentStatuses as $status)
                <option value="{{ $status }}" @selected(request('employment_status') == $status)>{{ $status }}</option>
              @endforeach
            </select>
          </label>
        </div>

        <div class="filter-group">
          <h4 class="filter-group-title">
            <i class="fa-solid fa-toolbox" aria-hidden="true"></i>
            Job / Profession
          </h4>
          <label class="filter-select filter-select-full">
            <span class="visually-hidden">Job / Profession</span>
            <select name="job_id">
              <option value="">Select profession</option>
              @foreach ($professions as $row)
                <option value="{{ $row->id }}" @selected(request('job_id') == $row->id)>{{ $row->name }}</option>
              @endforeach
            </select>
          </label>
        </div>

        @if ($isAdmin)
          <div class="filter-official">
            <div class="filter-official-copy">
              <i class="fa-solid fa-lock" aria-hidden="true"></i>
              <div>
                <strong>Filter Official</strong>
                <span>Access restricted to administrators only</span>
              </div>
            </div>
            <label class="toggle-switch">
              <input id="officialFilterToggle" type="checkbox" name="official" value="1" aria-label="Filter Official"
                aria-controls="officialExtraFilters" @checked(request('official')) />
              <span class="toggle-slider" aria-hidden="true"></span>
            </label>
          </div>

          <div class="official-extra-filters" id="officialExtraFilters" @if (!request('official')) hidden @endif>
            <div class="filter-group">
              <h4 class="filter-group-title">
                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                Age
                <span class="filter-age-value" id="officialAgeValue">Up to {{ request('age', 80) }} years</span>
              </h4>
              <div class="filter-age-range">
                <span>18</span>
                <input id="officialAgeRange" type="range" name="age" min="18" max="80" value="{{ request('age', 80) }}"
                  aria-label="Age" />
                <span>80</span>
              </div>
            </div>

            <div class="filter-fields official-extra-grid">
              <div class="filter-group">
                <h4 class="filter-group-title">
                  <i class="fa-solid fa-venus-mars" aria-hidden="true"></i>
                  Gender
                </h4>
                <label class="filter-select">
                  <span class="visually-hidden">Gender</span>
                  <select name="gender" id="officialGender">
                    <option value="">Select gender</option>
                    @foreach ($genders as $gender)
                      <option value="{{ $gender }}" @selected(request('gender') == $gender)>{{ $gender }}</option>
                    @endforeach
                  </select>
                </label>
              </div>

              <div class="filter-group">
                <h4 class="filter-group-title">
                  <i class="fa-solid fa-droplet" aria-hidden="true"></i>
                  Blood Group
                </h4>
                <label class="filter-select">
                  <span class="visually-hidden">Blood Group</span>
                  <select name="blood_group" id="officialBloodGroup">
                    <option value="">Select blood group</option>
                    @foreach ($bloodGroups as $bloodGroup)
                      <option value="{{ $bloodGroup }}" @selected(request('blood_group') == $bloodGroup)>{{ $bloodGroup }}
                      </option>
                    @endforeach
                  </select>
                </label>
              </div>

              <div class="filter-group">
                <h4 class="filter-group-title">
                  <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                  Job Category
                </h4>
                <label class="filter-select">
                  <span class="visually-hidden">Job Category</span>
                  <select name="job_category_id" id="officialJobCategory" aria-label="Category options"
                    data-child="officialJob" data-url="{{ url('lookups/jobs') }}">
                    <option value="">Select category</option>
                    @foreach ($jobCategories as $row)
                      <option value="{{ $row->id }}" @selected(request('job_category_id') == $row->id)>{{ $row->name }}</option>
                    @endforeach
                  </select>
                </label>
              </div>

              <div class="filter-group">
                <h4 class="filter-group-title">
                  <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                  Job
                </h4>
                <label class="filter-select">
                  <span class="visually-hidden">Job</span>
                  <select name="official_job_id" id="officialJob">
                    <option value="">Select job</option>
                    @foreach ($jobs as $row)
                      <option value="{{ $row->id }}" data-category="{{ $row->job_category_id }}"
                        @selected(request('official_job_id') == $row->id)>{{ $row->name }}</option>
                    @endforeach
                  </select>
                </label>
              </div>
            </div>
          </div>
        @endif

        <footer class="filters-footer">
          <div class="profiles-count-card">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <div>
              <span>Profiles Count</span>
              <strong>{{ $users->total() }} {{ \Illuminate\Support\Str::plural('profile', $users->total()) }}
                found</strong>
            </div>
          </div>
          <button type="submit" class="filters-results-btn">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            See Results
          </button>
        </footer>
      </aside>

      <div class="networking-results">
        <div class="results-toolbar">
          <p class="results-summary">
            Showing profiles
            <span class="results-badge">{{ $users->total() }}
              {{ \Illuminate\Support\Str::plural('Result', $users->total()) }}</span>
          </p>
          <label class="results-sort">
            <span>Sort by:</span>
            <select name="sort" onchange="this.form.submit()">
              <option value="recent" @selected(request('sort', 'recent') === 'recent')>Recently Added</option>
              <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
              <option value="nearest" @selected(request('sort') === 'nearest')>Nearest First</option>
            </select>
          </label>
        </div>

        <ul class="profile-list">
          @forelse ($users as $user)
            <li class="profile-card">
              <div class="profile-main">
                <img class="profile-avatar" src="{{ $user->profile->photo_url }}"
                  onerror="this.onerror=null;this.src='{{ asset('assets/images/no-image.avif') }}'"
                  alt="Portrait of {{ $user->name }}" width="72" height="72" />
                <div class="profile-info">
                  <h3 class="profile-name">
                    {{ $user->name }}
                    @if ($user->phone_verified_at)
                      <i class="fa-solid fa-circle-check" title="Verified" aria-label="Verified"></i>
                    @endif
                  </h3>
                  <p class="profile-role">
                    {{ $user->profile->job?->name ?: 'Profession not added yet' }}
                  </p>
                  <p class="profile-bio">
                    {{ $user->profile->about ?: "This member hasn't added a bio yet." }}
                  </p>
                  <p class="profile-status is-{{ $user->profile->status ?: 'available' }}">
                    <span class="status-dot" aria-hidden="true"></span>
                    {{ ucfirst($user->profile->status ?: 'available') }}
                  </p>
                </div>
              </div>

              <div class="profile-location">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <div>
                  @if ($user->presentAddress?->district || $user->presentAddress?->state)
                    <strong>{{ collect([$user->presentAddress?->district?->name, $user->presentAddress?->state?->name])->filter()->implode(', ') }}</strong>
                    <span>
                      {{ $user->presentAddress?->district ? $user->presentAddress->district->name . ' District' : ($user->presentAddress?->country?->name ?? '') }}
                    </span>
                  @else
                    <strong>Location not added yet</strong>
                    <span>Present address not provided</span>
                  @endif
                </div>
              </div>

              <div class="profile-contact">
                <a href="tel:+91{{ $user->phone }}">
                  <i class="fa-solid fa-phone" aria-hidden="true"></i> +91 {{ $user->phone }}
                </a>
                @if ($user->email)
                  <a href="mailto:{{ $user->email }}">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $user->email }}
                  </a>
                @else
                  <a>
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i> Email not added
                  </a>
                @endif
              </div>

              <a href="{{ route('members.show', $user->uuid) }}" class="profile-open"
                aria-label="View {{ $user->name }} profile">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
              </a>
            </li>
          @empty
            <li class="profile-card">
              <div class="profile-main">
                <div class="profile-info">
                  <h3 class="profile-name">No members found</h3>
                  <p class="profile-bio">No profiles match your search yet. Try removing a filter or searching a different
                    name.</p>
                </div>
              </div>
            </li>
          @endforelse
        </ul>

        {{ $users->onEachSide(1)->links('pagination.ibncc') }}
      </div>
    </div>

  </form>
@endsection