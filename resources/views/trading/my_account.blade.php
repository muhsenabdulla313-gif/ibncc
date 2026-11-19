@extends('layouts.trading')

@section('title', 'My Account — CC Hub')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/cchub/my-account.css') }}">
  <style>
    .profile-avatar-wrap {
      position: relative;
      width: 80px;
      height: 80px;
      flex-shrink: 0;
    }
    .profile-avatar-img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--royal, #1e5bb8);
      box-shadow: 0 4px 12px rgba(30, 91, 184, 0.16);
    }
    .badge-role-pill {
      display: inline-block;
      background: #e8f0fe;
      color: #1a73e8;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .personal-info-header {
      display: flex;
      align-items: center;
      gap: 20px;
      padding-bottom: 22px;
      margin-bottom: 12px;
      border-bottom: 1px solid var(--border-subtle, #e2e8f0);
    }
    .personal-user-title {
      font-family: var(--font-sans);
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--primary, #0f2a4a);
      margin: 0 0 6px;
    }
    .status--cancelled {
      background: #fee2e2;
      color: #991b1b;
    }
    .form-field select {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 14px;
      color: #0f172a;
      background: #fff;
      box-sizing: border-box;
    }
    .form-field select:focus {
      border-color: #2563eb;
      outline: none;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
  </style>
@endpush

@section('body')
  <!-- Main My Account Content -->
  <main class="account-main" id="main-content">
    <div class="account-container">
      <nav class="account-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Home</a>
        <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
        <a href="{{ url('/trading') }}">Trading</a>
        <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
        <span class="current">My Account</span>
      </nav>

      <div class="account-layout-grid">
        <!-- 1. LEFT VERTICAL SECTION (MY ACCOUNT SIDEBAR) -->
        <aside class="account-sidebar" aria-label="Account Navigation">
          <div class="account-sidebar-card">
            <h2 class="account-sidebar-title">MY ACCOUNT</h2>
            <ul class="account-nav-list" role="tablist" aria-label="Account Tabs">
              <li role="presentation">
                <button type="button" class="account-nav-item is-active" id="tab-personal" role="tab" aria-selected="true"
                  aria-controls="panel-personal" data-tab="personal" data-title="PERSONAL INFORMATION">
                  <span class="account-nav-icon" aria-hidden="true">
                    <i class="fa-solid fa-user"></i>
                  </span>
                  <span class="account-nav-label">Personal Information</span>
                </button>
              </li>

              <li role="presentation">
                <button type="button" class="account-nav-item" id="tab-orders" role="tab" aria-selected="false"
                  aria-controls="panel-orders" data-tab="orders" data-title="ORDER HISTORY">
                  <span class="account-nav-icon" aria-hidden="true">
                    <i class="fa-solid fa-box-archive"></i>
                  </span>
                  <span class="account-nav-label">Order History</span>
                </button>
              </li>

              <li role="presentation">
                <button type="button" class="account-nav-item" id="tab-address" role="tab" aria-selected="false"
                  aria-controls="panel-address" data-tab="address" data-title="ADDRESS BOOK">
                  <span class="account-nav-icon" aria-hidden="true">
                    <i class="fa-solid fa-map-location-dot"></i>
                  </span>
                  <span class="account-nav-label">Address Book</span>
                </button>
              </li>

              <li role="presentation">
                <button type="button" class="account-nav-item" id="tab-contact" role="tab" aria-selected="false"
                  aria-controls="panel-contact" data-tab="contact" data-title="CONTACT US">
                  <span class="account-nav-icon" aria-hidden="true">
                    <i class="fa-solid fa-headset"></i>
                  </span>
                  <span class="account-nav-label">Contact Us</span>
                </button>
              </li>

              <li role="presentation" class="account-nav-divider"></li>

              <li role="presentation">
                <a href="#logout" class="account-nav-item account-nav-item--logout" id="accountLogoutBtn" role="button">
                  <span class="account-nav-icon" aria-hidden="true">
                    <i class="fa-solid fa-right-from-bracket"></i>
                  </span>
                  <span class="account-nav-label">Logout</span>
                </a>
                <form id="sidebarLogoutForm" method="POST" action="{{ route('logout') }}" style="display: none;">
                  @csrf
                </form>
              </li>
            </ul>
          </div>
        </aside>

        <!-- 2. RIGHT VERTICAL SECTION (TAB CONTENT AREA) -->
        <section class="account-content-area" aria-labelledby="accountHeading">
          <div class="account-content-card">
            <div class="account-card-header">
              <h1 class="account-section-title" id="accountHeading">
                PERSONAL INFORMATION
              </h1>
            </div>

            <!-- Tab Panel 1: Personal Information (Default Active) -->
            <div class="account-tab-panel is-active" id="panel-personal" role="tabpanel" aria-labelledby="tab-personal">
              <div class="personal-info-card">

                <!-- Profile Header / Avatar -->
                <div class="personal-info-header">
                  <div class="profile-avatar-wrap">
                    <img src="{{ $profile->photo_url }}" id="profileAvatarImg" alt="{{ $user->name }}" class="profile-avatar-img" onerror="this.src='{{ asset('assets/images/no-image.avif') }}'" />
                  </div>
                  <div>
                    <h2 class="personal-user-title">{{ $user->name }}</h2>
                    <span class="badge-role-pill">{{ ucfirst($user->role?->name ?? 'Member') }}</span>
                  </div>
                </div>

                <div class="info-row">
                  <span class="info-label">Full Name</span>
                  <span class="info-value" id="profileName">{{ $user->name }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Phone Number</span>
                  <span class="info-value" id="profilePhone">+91 {{ $user->phone }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Email Address</span>
                  <span class="info-value" id="profileEmail">{{ $user->email ?: 'Not added yet' }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">User Role</span>
                  <span class="info-value" id="profileRole">{{ ucfirst($user->role?->name ?? 'Member') }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Date of Birth</span>
                  <div class="info-value-wrap">
                    <span class="info-value" id="profileDob">{{ $profile->dob ? $profile->dob->format('j F Y') : 'Not added yet' }}</span>
                  </div>
                </div>

                <div class="info-row">
                  <span class="info-label">Gender</span>
                  <span class="info-value" id="profileGender">{{ $profile->gender ? ucfirst($profile->gender) : 'Not specified' }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Blood Group</span>
                  <span class="info-value" id="profileBloodGroup">{{ $profile->blood_group ?: 'Not specified' }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Rite</span>
                  <span class="info-value" id="profileRite">{{ $user->rite_name ?: 'Not specified' }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Diocese</span>
                  <span class="info-value" id="profileDiocese">{{ $user->diocese_name ?: 'Not specified' }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Forane</span>
                  <span class="info-value" id="profileForane">{{ $user->forane_name ?: 'Not specified' }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Parish</span>
                  <span class="info-value" id="profileParish">{{ $user->parish?->name ?: 'Not specified' }}</span>
                </div>

                <div class="personal-actions" style="display: flex; gap: 14px; flex-wrap: wrap;">
                  <button type="button" class="btn-gold-solid" id="btnEditDetails">
                    <i class="fa-solid fa-user-pen" aria-hidden="true"></i> EDIT DETAILS
                  </button>
                  <button type="button" class="btn-outline-gold" id="btnChangePassword">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i> CHANGE PASSWORD
                  </button>
                </div>
              </div>
            </div>

            <!-- Tab Panel 2: Order History -->
            <div class="account-tab-panel" id="panel-orders" role="tabpanel" aria-labelledby="tab-orders" hidden>
              <div class="orders-filter-bar" role="tablist" aria-label="Filter Orders">
                <button type="button" class="order-filter-btn is-active" data-filter="all">
                  All Orders
                </button>
                <button type="button" class="order-filter-btn" data-filter="placed">
                  Placed
                </button>
                <button type="button" class="order-filter-btn" data-filter="processing">
                  Processing
                </button>
                <button type="button" class="order-filter-btn" data-filter="completed">
                  Completed / Delivered
                </button>
              </div>

              <div class="orders-list" id="ordersListContainer">
                @forelse($orders as $order)
                  @php
                    $rawStatus = strtolower($order->status);
                    $filterGroup = match($rawStatus) {
                      'placed' => 'placed',
                      'processing', 'contacted', 'confirmed' => 'processing',
                      'completed', 'delivered', 'shipped' => 'completed',
                      default => 'all'
                    };
                    $statusClass = match($rawStatus) {
                      'completed', 'delivered' => 'status--delivered',
                      'shipped' => 'status--delivery',
                      'cancelled' => 'status--cancelled',
                      default => 'status--processing',
                    };
                    $firstItem = $order->items->first();
                    $img = $firstItem?->product?->images?->first()?->path;
                    $resolveImg = function ($p) {
                      if (!$p) return asset('assets/images/no-image.avif');
                      $clean = ltrim($p, '/\\');
                      if (file_exists(public_path($clean))) return asset($clean);
                      if (file_exists(public_path('storage/' . $clean))) return asset('storage/' . $clean);
                      return asset('assets/images/no-image.avif');
                    };
                  @endphp
                  <article class="order-card" data-status="{{ $filterGroup }}">
                    <div class="order-card-head">
                      <div class="order-meta">
                        <strong class="order-id">#{{ $order->order_no }}</strong>
                        <span class="order-date">{{ $order->created_at->format('M d, Y') }}</span>
                      </div>
                      <span class="order-status-badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                    </div>

                    <div class="order-card-body">
                      @if($firstItem)
                        <img src="{{ $resolveImg($img) }}" alt="{{ $firstItem->title }}" class="order-thumb" width="72" height="72" onerror="this.src='{{ asset('assets/images/no-image.avif') }}'" />
                      @endif
                      <div class="order-item-details">
                        <h3 class="order-item-title">{{ $firstItem?->title ?? 'Inquiry Item' }}</h3>
                        <p class="order-item-qty">{{ $order->items->count() }} line item{{ $order->items->count() > 1 ? 's' : '' }}</p>
                        <p class="order-item-price">₹{{ number_format($order->total, 2) }}</p>
                      </div>
                    </div>

                    <div class="order-card-foot">
                      <span class="order-footnote">
                        Delivery to: {{ $order->shipping_address['city'] ?? $order->buyer_name }}
                      </span>
                      <a href="{{ route('order.success', $order->order_no) }}" class="order-arrow-btn" aria-label="View order details" title="View details">
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                      </a>
                    </div>
                  </article>
                @empty
                  <div style="text-align: center; padding: 48px 16px; color: #64748b;">
                    <i class="fa-solid fa-box-open" style="font-size: 40px; margin-bottom: 12px; color: #cbd5e1;"></i>
                    <h3 style="font-size: 16px; font-weight: 600; color: #334155; margin-bottom: 4px;">No orders found</h3>
                    <p style="font-size: 14px; margin-bottom: 16px;">You haven't placed any orders or inquiries yet.</p>
                    <a href="{{ url('/products') }}" class="btn-gold-solid" style="display: inline-block;">Explore Marketplace</a>
                  </div>
                @endforelse
              </div>
            </div>

            <!-- Tab Panel 3: Address Book -->
            <div class="account-tab-panel" id="panel-address" role="tabpanel" aria-labelledby="tab-address" hidden>
              <div class="address-top-bar">
                <button type="button" class="btn-gold-solid" id="btnAddAddressBtn">
                  + ADD ADDRESS
                </button>
              </div>

              <div class="address-grid" id="addressGrid">
                @forelse($addresses as $idx => $addr)
                  <div class="address-card" data-address-id="{{ $addr->id }}">
                    <div class="address-card-header">
                      <span class="address-pin-icon"><i class="fa-solid fa-location-dot"></i></span>
                      <strong class="address-label-name">{{ ucfirst($addr->type ?? 'Address ' . ($idx + 1)) }}</strong>
                      @if($idx === 0)
                        <span class="address-tag-default">Default</span>
                      @endif
                    </div>
                    <div class="address-card-body">
                      <p class="address-user-name">{{ $user->name }}</p>
                      <p class="address-line">{{ $addr->house }}</p>
                      <p class="address-line">
                        {{ $addr->district?->name ?? '' }}@if($addr->state), {{ $addr->state->name }}@endif @if($addr->pin) - {{ $addr->pin }}@endif
                      </p>
                      <p class="address-phone">Phone: +91 {{ $user->phone }}</p>
                    </div>
                    <div class="address-card-actions">
                      <button type="button" class="address-btn address-btn--edit" data-action="edit"
                              data-id="{{ $addr->id }}" data-type="{{ $addr->type }}" data-house="{{ $addr->house }}" data-pin="{{ $addr->pin }}">
                        Edit
                      </button>
                      <button type="button" class="address-btn address-btn--delete" data-action="delete" data-id="{{ $addr->id }}">
                        Delete
                      </button>
                    </div>
                  </div>
                @empty
                  <div style="grid-column: span 3; text-align: center; padding: 40px 16px; color: #64748b;">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 36px; margin-bottom: 12px; color: #cbd5e1;"></i>
                    <p style="margin: 0; font-size: 14px;">No addresses saved in your address book yet.</p>
                  </div>
                @endforelse
              </div>
            </div>

            <!-- Tab Panel 4: Contact Us -->
            <div class="account-tab-panel" id="panel-contact" role="tabpanel" aria-labelledby="tab-contact" hidden>
              <div class="contact-support-grid">
                <div class="support-card">
                  <span class="support-icon"><i class="fa-solid fa-phone"></i></span>
                  <h3 class="support-title">PHONE SUPPORT</h3>
                  <a href="tel:+911234567890" class="support-link">+91 1234-567-890</a>
                  <p class="support-note">
                    Available: Monday - Friday, 10 AM - 6 PM
                  </p>
                </div>
                <div class="support-card">
                  <span class="support-icon"><i class="fa-solid fa-envelope"></i></span>
                  <h3 class="support-title">EMAIL SUPPORT</h3>
                  <a href="mailto:support@cchub.local" class="support-link">support@cchub.local</a>
                  <p class="support-note">Response time: within 24 hours</p>
                </div>
              </div>

              <div class="contact-form-section">
                <h3 class="contact-form-title">Send Us a Direct Message</h3>
                <form class="account-contact-form" id="accountContactForm">
                  <div class="form-row-2">
                    <div class="form-field">
                      <label for="contactSubject">Subject</label>
                      <input type="text" id="contactSubject" placeholder="How can we assist you?" required />
                    </div>
                    <div class="form-field">
                      <label for="contactOrderRef">Order Reference (Optional)</label>
                      <input type="text" id="contactOrderRef" placeholder="e.g. ORD-001" />
                    </div>
                  </div>
                  <div class="form-field">
                    <label for="contactMessage">Message</label>
                    <textarea id="contactMessage" rows="4" placeholder="Write your message here..." required></textarea>
                  </div>
                  <button type="submit" class="btn-gold-solid">
                    SEND MESSAGE
                  </button>
                </form>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>

  <!-- Edit Personal Details Dialog -->
  <dialog class="account-dialog" id="editProfileDialog" aria-labelledby="editProfileTitle">
    <div class="account-dialog-card" style="max-width: 580px;">
      <button type="button" class="account-dialog-close" id="editProfileClose" aria-label="Close dialog">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="account-dialog-title" id="editProfileTitle">
        Edit Personal Information
      </h2>
      <p class="account-dialog-sub">Update your account and church details below.</p>

      <form class="account-dialog-form" id="editProfileForm" method="POST"
            action="{{ route('network.myaccount.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-field">
          <label for="inputName">Full Name <span style="color: #dc2626;">*</span></label>
          <input type="text" id="inputName" name="name" value="{{ $user->name }}" required />
        </div>

        <div class="form-field verify-field" data-type="phone" data-original="{{ $user->phone }}">
          <label for="inputPhone">Phone Number <span style="color: #dc2626;">*</span></label>
          <div class="verify-row">
            <input type="tel" id="inputPhone" name="phone" value="{{ $user->phone }}" inputmode="numeric" maxlength="10" required />
            <button type="button" class="verify-btn" hidden>Verify</button>
            <span class="verified-badge" hidden><i class="fa-solid fa-circle-check"></i> Verified</span>
          </div>
          <div class="verify-otp" hidden>
            <p class="verify-otp-hint">Enter the OTP sent to your phone number</p>
            <div class="verify-otp-row">
              <input type="text" class="verify-otp-input" inputmode="numeric" maxlength="6" placeholder="Enter 6-digit OTP" autocomplete="one-time-code" />
              <button type="button" class="verify-otp-confirm">Confirm</button>
            </div>
            <p class="verify-otp-demo" hidden></p>
            <p class="verify-otp-msg" hidden></p>
          </div>
        </div>

        <div class="form-field verify-field" data-type="email" data-original="{{ $user->email }}">
          <label for="inputEmail">Email Address</label>
          <div class="verify-row">
            <input type="email" id="inputEmail" name="email" value="{{ $user->email }}" />
            <button type="button" class="verify-btn" hidden>Verify</button>
            <span class="verified-badge" hidden><i class="fa-solid fa-circle-check"></i> Verified</span>
          </div>
          <div class="verify-otp" hidden>
            <p class="verify-otp-hint">Enter the OTP sent to your email address</p>
            <div class="verify-otp-row">
              <input type="text" class="verify-otp-input" inputmode="numeric" maxlength="6" placeholder="Enter 6-digit OTP" autocomplete="one-time-code" />
              <button type="button" class="verify-otp-confirm">Confirm</button>
            </div>
            <p class="verify-otp-demo" hidden></p>
            <p class="verify-otp-msg" hidden></p>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-field">
            <label for="inputDob">Date of Birth</label>
            <input type="date" id="inputDob" name="dob" value="{{ $profile->dob?->format('Y-m-d') }}"
                   max="{{ now()->subDay()->format('Y-m-d') }}" />
          </div>

          <div class="form-field">
            <label for="inputGender">Gender</label>
            <select id="inputGender" name="gender">
              <option value="">Select Gender</option>
              <option value="male" {{ $profile->gender === 'male' ? 'selected' : '' }}>Male</option>
              <option value="female" {{ $profile->gender === 'female' ? 'selected' : '' }}>Female</option>
              <option value="other" {{ $profile->gender === 'other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-field">
            <label for="inputBloodGroup">Blood Group</label>
            <select id="inputBloodGroup" name="blood_group">
              <option value="">Select Blood Group</option>
              @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                <option value="{{ $bg }}" {{ $profile->blood_group === $bg ? 'selected' : '' }}>{{ $bg }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-field">
            <label for="inputPhoto">Profile Photo</label>
            <input type="file" id="inputPhoto" name="photo" accept="image/jpeg,image/png,image/webp" />
          </div>
        </div>

        <!-- Church Hierarchy Cascading Dropdowns -->
        <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-subtle, #e2e8f0);">
          <p style="font-weight: 700; font-size: 13px; color: var(--primary, #0f2a4a); margin-bottom: 10px; text-transform: uppercase;">
            <i class="fa-solid fa-church"></i> Church & Parish Details
          </p>

          <div class="form-row-2">
            <div class="form-field">
              <label for="selRite">Rite</label>
              <select id="selRite" name="rite_id">
                <option value="">Select Rite</option>
                @foreach($rites as $r)
                  <option value="{{ $r->id }}" {{ $currentRiteId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-field">
              <label for="selDiocese">Diocese</label>
              <select id="selDiocese" name="diocese_id">
                <option value="">Select Diocese</option>
                @foreach($dioceses as $d)
                  <option value="{{ $d->id }}" {{ $currentDioceseId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-field">
              <label for="selForane">Forane</label>
              <select id="selForane" name="forane_id">
                <option value="">Select Forane</option>
                @foreach($foranes as $f)
                  <option value="{{ $f->id }}" {{ $currentForaneId == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-field">
              <label for="selParish">Parish</label>
              <select id="selParish" name="parish_id">
                <option value="">Select Parish</option>
                @foreach($parishes as $p)
                  <option value="{{ $p->id }}" {{ $user->parish_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <p class="otp-error" id="editProfileError" hidden></p>

        <div class="dialog-btn-row">
          <button type="submit" class="btn-gold-solid" id="btnSaveProfile">SAVE CHANGES</button>
          <button type="button" class="btn-secondary" id="editProfileCancel">Cancel</button>
        </div>
      </form>
    </div>
  </dialog>

  <!-- Change Password Dialog -->
  <dialog class="account-dialog" id="changePasswordDialog" aria-labelledby="changePasswordTitle">
    <div class="account-dialog-card">
      <button type="button" class="account-dialog-close" id="changePasswordClose" aria-label="Close dialog">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="account-dialog-title" id="changePasswordTitle">
        Change Password
      </h2>
      <p class="account-dialog-sub">Ensure your account is using a strong password.</p>

      <form class="account-dialog-form" id="changePasswordForm" method="POST"
            action="{{ route('trading.myaccount.password') }}">
        @csrf

        <div class="form-field">
          <label for="current_password">Current Password <span style="color: #dc2626;">*</span></label>
          <input type="password" id="current_password" name="current_password" required autocomplete="current-password" />
        </div>

        <div class="form-field">
          <label for="new_password">New Password <span style="color: #dc2626;">*</span></label>
          <input type="password" id="new_password" name="new_password" required autocomplete="new-password" placeholder="At least 6 characters" />
        </div>

        <div class="form-field">
          <label for="new_password_confirmation">Confirm New Password <span style="color: #dc2626;">*</span></label>
          <input type="password" id="new_password_confirmation" name="new_password_confirmation" required autocomplete="new-password" />
        </div>

        <p class="otp-error" id="changePasswordError" hidden></p>

        <div class="dialog-btn-row">
          <button type="submit" class="btn-gold-solid" id="btnSubmitPassword">UPDATE PASSWORD</button>
          <button type="button" class="btn-secondary" id="changePasswordCancel">Cancel</button>
        </div>
      </form>
    </div>
  </dialog>

  <!-- Add/Edit Address Dialog -->
  <dialog class="account-dialog" id="addressDialog" aria-labelledby="addressDialogTitle">
    <div class="account-dialog-card">
      <button type="button" class="account-dialog-close" id="addressDialogClose" aria-label="Close dialog">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="account-dialog-title" id="addressDialogTitle">
        Add New Address
      </h2>
      <p class="account-dialog-sub">Fill in the address details below.</p>
      <form class="account-dialog-form" id="addressForm" method="POST" action="{{ route('trading.myaccount.address.save') }}">
        @csrf
        <input type="hidden" id="addressEditId" name="id" value="" />
        <div class="form-field">
          <label for="addrLabel">Address Label (e.g. Home, Office)</label>
          <input type="text" id="addrLabel" name="type" placeholder="e.g. Home, Office" required />
        </div>
        <div class="form-field">
          <label for="addrStreet">Street / House Address <span style="color: #dc2626;">*</span></label>
          <input type="text" id="addrStreet" name="house" placeholder="House name, Street, Landmark" required />
        </div>
        <div class="form-field">
          <label for="addrPincode">PIN Code</label>
          <input type="text" id="addrPincode" name="pin" placeholder="6-digit PIN code" />
        </div>
        <div class="dialog-btn-row">
          <button type="submit" class="btn-gold-solid" id="btnSaveAddress">SAVE ADDRESS</button>
          <button type="button" class="btn-secondary" id="addressDialogCancel">Cancel</button>
        </div>
      </form>
    </div>
  </dialog>

  <!-- Toast Notification -->
  <div class="account-toast" id="accountToast" role="status" aria-live="polite">
    <i class="fa-solid fa-circle-check"></i>
    <span id="accountToastMsg">Action completed successfully.</span>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('js/cchub/my-account.js') }}"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('accountToast');
    const toastMsg = document.getElementById('accountToastMsg');
    let toastTimer = null;

    function showToast(msg) {
      if (!toast) return;
      toastMsg.textContent = msg;
      toast.classList.add('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    // Sidebar logout button
    document.getElementById('accountLogoutBtn')?.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('sidebarLogoutForm')?.submit();
    });

    // Change Password Dialog Open/Close
    const passDialog = document.getElementById('changePasswordDialog');
    const passBtn = document.getElementById('btnChangePassword');
    const passClose = document.getElementById('changePasswordClose');
    const passCancel = document.getElementById('changePasswordCancel');
    const passForm = document.getElementById('changePasswordForm');
    const passErr = document.getElementById('changePasswordError');

    passBtn?.addEventListener('click', () => {
      passForm.reset();
      passErr.hidden = true;
      passDialog.showModal();
    });
    passClose?.addEventListener('click', () => passDialog.close());
    passCancel?.addEventListener('click', () => passDialog.close());

    passForm?.addEventListener('submit', async function (e) {
      e.preventDefault();
      passErr.hidden = true;
      const submitBtn = document.getElementById('btnSubmitPassword');
      submitBtn.disabled = true;

      try {
        const res = await fetch(passForm.action, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            new_password_confirmation: document.getElementById('new_password_confirmation').value,
          })
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok && data.success) {
          passDialog.close();
          passForm.reset();
          showToast(data.message || 'Password updated successfully!');
        } else {
          let msg = data.message || 'Failed to update password.';
          if (data.errors) {
            msg = Object.values(data.errors)[0][0];
          }
          passErr.textContent = msg;
          passErr.hidden = false;
        }
      } catch (err) {
        passErr.textContent = 'Network error. Please try again.';
        passErr.hidden = false;
      } finally {
        submitBtn.disabled = false;
      }
    });

    // Cascading Church Dropdowns
    const selRite = document.getElementById('selRite');
    const selDiocese = document.getElementById('selDiocese');
    const selForane = document.getElementById('selForane');
    const selParish = document.getElementById('selParish');

    selRite?.addEventListener('change', function () {
      const riteId = this.value;
      selDiocese.innerHTML = '<option value="">Select Diocese</option>';
      selForane.innerHTML = '<option value="">Select Forane</option>';
      selParish.innerHTML = '<option value="">Select Parish</option>';

      if (!riteId) return;

      fetch('/lookups/dioceses/' + riteId)
        .then(r => r.json())
        .then(list => {
          list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            selDiocese.appendChild(opt);
          });
        })
        .catch(() => {});
    });

    selDiocese?.addEventListener('change', function () {
      const dioceseId = this.value;
      selForane.innerHTML = '<option value="">Select Forane</option>';
      selParish.innerHTML = '<option value="">Select Parish</option>';

      if (!dioceseId) return;

      fetch('/lookups/foranes/' + dioceseId)
        .then(r => r.json())
        .then(list => {
          list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            selForane.appendChild(opt);
          });
        })
        .catch(() => {});
    });

    selForane?.addEventListener('change', function () {
      const foraneId = this.value;
      selParish.innerHTML = '<option value="">Select Parish</option>';

      if (!foraneId) return;

      fetch('/lookups/parishes/' + foraneId)
        .then(r => r.json())
        .then(list => {
          list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            selParish.appendChild(opt);
          });
        })
        .catch(() => {});
    });

    // Profile Edit OTP verification & submission logic
    const form = document.getElementById('editProfileForm');
    if (form) {
      const token = '{{ csrf_token() }}';
      const errBox = document.getElementById('editProfileError');
      const SEND = "{{ route('register.otp.send') }}";
      const VERIFY = "{{ route('register.otp.verify') }}";

      const norm = (type, v) => type === 'email' ? v.trim().toLowerCase() : v.replace(/\D/g, '').slice(-10);

      async function postOtp(url, body) {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
          body: JSON.stringify(body)
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
          let msg = json.message || 'Something went wrong.';
          if (json.errors) msg = Object.values(json.errors)[0][0];
          throw new Error(msg);
        }
        return json;
      }

      form.querySelectorAll('.verify-field').forEach(function (field) {
        const type = field.dataset.type;
        const input = field.querySelector('input[name]');
        const btn = field.querySelector('.verify-btn');
        const badge = field.querySelector('.verified-badge');
        const panel = field.querySelector('.verify-otp');
        const otp = field.querySelector('.verify-otp-input');
        const confirm = field.querySelector('.verify-otp-confirm');
        const demo = field.querySelector('.verify-otp-demo');
        const msg = field.querySelector('.verify-otp-msg');
        const original = norm(type, field.dataset.original || '');

        field.dataset.state = 'same';

        function say(text, ok) {
          msg.textContent = text || '';
          msg.hidden = !text;
          msg.style.color = ok ? '#15803d' : '#b91c1c';
        }

        input.addEventListener('input', function () {
          const v = norm(type, input.value);
          const changed = v !== '' && v !== original;
          field.dataset.state = changed ? 'needs' : 'same';
          btn.hidden = !changed;
          badge.hidden = true;
          panel.hidden = true;
          say('');
        });

        btn.addEventListener('click', async function () {
          const v = norm(type, input.value);
          if (type === 'phone' && !/^\d{10}$/.test(v)) {
            errBox.textContent = 'Enter a valid 10-digit phone number.';
            errBox.hidden = false;
            return;
          }
          if (type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
            errBox.textContent = 'Enter a valid email address.';
            errBox.hidden = false;
            return;
          }
          errBox.hidden = true;
          btn.disabled = true;
          try {
            const json = await postOtp(SEND, { type: type, value: v });
            panel.hidden = false;
            otp.value = '';
            otp.focus();
            demo.textContent = json.debug_otp ? 'Demo OTP: ' + json.debug_otp : '';
            demo.hidden = !json.debug_otp;
            say('OTP sent. It is valid for 5 minutes.', true);
          } catch (e) {
            errBox.textContent = e.message;
            errBox.hidden = false;
          } finally {
            btn.disabled = false;
          }
        });

        confirm.addEventListener('click', async function () {
          const code = otp.value.trim();
          if (!/^\d{6}$/.test(code)) {
            say('Enter the 6-digit OTP.', false);
            return;
          }
          confirm.disabled = true;
          try {
            await postOtp(VERIFY, { type: type, value: norm(type, input.value), otp: code });
            field.dataset.state = 'verified';
            input.readOnly = true;
            btn.hidden = true;
            panel.hidden = true;
            badge.hidden = false;
            errBox.hidden = true;
          } catch (e) {
            say(e.message, false);
          } finally {
            confirm.disabled = false;
          }
        });
      });

      // Submit profile edit form
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errBox.hidden = true;

        const pending = form.querySelector('.verify-field[data-state="needs"]');
        if (pending) {
          errBox.textContent = 'Please verify your new ' + pending.dataset.type + ' before saving.';
          errBox.hidden = false;
          return;
        }

        const saveBtn = document.getElementById('btnSaveProfile');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> SAVING...';

        try {
          const formData = new FormData(form);
          const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: formData
          });

          const data = await res.json().catch(() => ({}));

          if (res.ok && data.success) {
            window.location.reload();
          } else {
            let msg = data.message || 'Could not save profile details.';
            if (data.errors) {
              msg = Object.values(data.errors)[0][0];
            }
            errBox.textContent = msg;
            errBox.hidden = false;
          }
        } catch (err) {
          errBox.textContent = 'An unexpected error occurred. Please try again.';
          errBox.hidden = false;
        } finally {
          saveBtn.disabled = false;
          saveBtn.innerHTML = 'SAVE CHANGES';
        }
      });
    }

    // Address Book AJAX Form
    const addrForm = document.getElementById('addressForm');
    const addrDialog = document.getElementById('addressDialog');
    const addrGrid = document.getElementById('addressGrid');

    addrForm?.addEventListener('submit', async function (e) {
      e.preventDefault();
      const saveBtn = document.getElementById('btnSaveAddress');
      saveBtn.disabled = true;

      try {
        const res = await fetch(addrForm.action, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            id: document.getElementById('addressEditId').value || null,
            type: document.getElementById('addrLabel').value,
            house: document.getElementById('addrStreet').value,
            pin: document.getElementById('addrPincode').value,
          })
        });

        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
          addrDialog.close();
          window.location.reload();
        } else {
          alert(data.message || 'Could not save address.');
        }
      } catch (err) {
        alert('Failed to save address.');
      } finally {
        saveBtn.disabled = false;
      }
    });

    // Address edit & delete click handlers
    addrGrid?.addEventListener('click', async function (e) {
      const editBtn = e.target.closest('.address-btn--edit');
      const delBtn = e.target.closest('.address-btn--delete');

      if (editBtn) {
        document.getElementById('addressDialogTitle').textContent = 'Edit Address';
        document.getElementById('addressEditId').value = editBtn.dataset.id || '';
        document.getElementById('addrLabel').value = editBtn.dataset.type || '';
        document.getElementById('addrStreet').value = editBtn.dataset.house || '';
        document.getElementById('addrPincode').value = editBtn.dataset.pin || '';
        addrDialog.showModal();
      } else if (delBtn) {
        if (!confirm('Are you sure you want to delete this address?')) return;
        const id = delBtn.dataset.id;
        try {
          const res = await fetch('/trading/my_account/address/' + id, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          });
          const data = await res.json().catch(() => ({}));
          if (res.ok && data.success) {
            delBtn.closest('.address-card')?.remove();
            showToast('Address deleted successfully.');
          } else {
            alert(data.message || 'Failed to delete address.');
          }
        } catch (err) {
          alert('Failed to delete address.');
        }
      }
    });
  });
  </script>
@endpush
