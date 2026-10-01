@extends('layouts.trading')
@section('body')
   

    <!-- Main My Account Content -->
    <main class="account-main" id="main-content">
      <div class="account-container">
        <nav class="account-breadcrumbs" aria-label="Breadcrumb">
          <a href="index.html#home"><i class="fa-solid fa-house"></i> Home</a>
          <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
          <a href="ch-trading.html">Trading</a>
          <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
          <span class="current">My Account</span>
        </nav>

        <div class="account-layout-grid">
          <!-- 1. LEFT VERTICAL SECTION (MY ACCOUNT SIDEBAR) -->
          <aside class="account-sidebar" aria-label="Account Navigation">
            <div class="account-sidebar-card">
              <h2 class="account-sidebar-title">MY ACCOUNT</h2>
              <ul
                class="account-nav-list"
                role="tablist"
                aria-label="Account Tabs"
              >
                <li role="presentation">
                  <button
                    type="button"
                    class="account-nav-item is-active"
                    id="tab-personal"
                    role="tab"
                    aria-selected="true"
                    aria-controls="panel-personal"
                    data-tab="personal"
                    data-title="PERSONAL INFORMATION"
                  >
                    <span class="account-nav-icon" aria-hidden="true">
                      <i class="fa-solid fa-user"></i>
                    </span>
                    <span class="account-nav-label">Personal Information</span>
                  </button>
                </li>

                <li role="presentation">
                  <button
                    type="button"
                    class="account-nav-item"
                    id="tab-orders"
                    role="tab"
                    aria-selected="false"
                    aria-controls="panel-orders"
                    data-tab="orders"
                    data-title="ORDER HISTORY"
                  >
                    <span class="account-nav-icon" aria-hidden="true">
                      <i class="fa-solid fa-box-archive"></i>
                    </span>
                    <span class="account-nav-label">Order History</span>
                  </button>
                </li>

                <li role="presentation">
                  <button
                    type="button"
                    class="account-nav-item"
                    id="tab-address"
                    role="tab"
                    aria-selected="false"
                    aria-controls="panel-address"
                    data-tab="address"
                    data-title="ADDRESS BOOK"
                  >
                    <span class="account-nav-icon" aria-hidden="true">
                      <i class="fa-solid fa-map-location-dot"></i>
                    </span>
                    <span class="account-nav-label">Address Book</span>
                  </button>
                </li>

                <li role="presentation">
                  <button
                    type="button"
                    class="account-nav-item"
                    id="tab-contact"
                    role="tab"
                    aria-selected="false"
                    aria-controls="panel-contact"
                    data-tab="contact"
                    data-title="CONTACT US"
                  >
                    <span class="account-nav-icon" aria-hidden="true">
                      <i class="fa-solid fa-headset"></i>
                    </span>
                    <span class="account-nav-label">Contact Us</span>
                  </button>
                </li>

                <li role="presentation" class="account-nav-divider"></li>

                <li role="presentation">
                  <a
                    href="#logout"
                    class="account-nav-item account-nav-item--logout"
                    id="accountLogoutBtn"
                    role="button"
                  >
                    <span class="account-nav-icon" aria-hidden="true">
                      <i class="fa-solid fa-right-from-bracket"></i>
                    </span>
                    <span class="account-nav-label">Logout</span>
                  </a>
                </li>
              </ul>
            </div>
          </aside>

          <!-- 2. RIGHT VERTICAL SECTION (TAB CONTENT AREA) -->
          <section
            class="account-content-area"
            aria-labelledby="accountHeading"
          >
            <div class="account-content-card">
              <div class="account-card-header">
                <h1 class="account-section-title" id="accountHeading">
                  PERSONAL INFORMATION
                </h1>
              </div>

              <!-- Tab Panel 1: Personal Information (Default Active) -->
              <div
                class="account-tab-panel is-active"
                id="panel-personal"
                role="tabpanel"
                aria-labelledby="tab-personal"
              >
                <div class="personal-info-card">
                  <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value" id="profileName">John Doe</span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value" id="profilePhone"
                      >+91 9876543210</span
                    >
                  </div>

                  <div class="info-row">
                    <span class="info-label">Email ID</span>
                    <span class="info-value" id="profileEmail"
                      >john.doe@example.com</span
                    >
                  </div>

                  <div class="info-row">
                    <span class="info-label">Date of Birth</span>
                    <div class="info-value-wrap">
                      <span class="info-value" id="profileDob"
                        >Not added yet</span
                      >
                      <button
                        type="button"
                        class="btn-outline-gold"
                        id="btnAddDob"
                      >
                        Add Date of Birth
                      </button>
                    </div>
                  </div>

                  <div class="personal-actions">
                    <button
                      type="button"
                      class="btn-gold-solid"
                      id="btnEditDetails"
                    >
                      EDIT DETAILS
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tab Panel 2: Order History -->
              <div
                class="account-tab-panel"
                id="panel-orders"
                role="tabpanel"
                aria-labelledby="tab-orders"
                hidden
              >
                <div
                  class="orders-filter-bar"
                  role="tablist"
                  aria-label="Filter Orders"
                >
                  <button
                    type="button"
                    class="order-filter-btn is-active"
                    data-filter="all"
                  >
                    All Orders
                  </button>
                  <button
                    type="button"
                    class="order-filter-btn"
                    data-filter="processing"
                  >
                    Processing
                  </button>
                  <button
                    type="button"
                    class="order-filter-btn"
                    data-filter="out-for-delivery"
                  >
                    Out for Delivery
                  </button>
                  <button
                    type="button"
                    class="order-filter-btn"
                    data-filter="delivered"
                  >
                    Delivered
                  </button>
                </div>

                <div class="orders-list" id="ordersListContainer">
                  <!-- Order 1 -->
                  <article class="order-card" data-status="delivered">
                    <div class="order-card-head">
                      <div class="order-meta">
                        <strong class="order-id">ORD-001</strong>
                        <span class="order-date">January 15, 2024</span>
                      </div>
                      <span class="order-status-badge status--delivered"
                        >Delivered</span
                      >
                    </div>
                    <div class="order-card-body">
                      <img
                        src="assets/images/lr1.jpg"
                        alt="Gold Necklace"
                        class="order-thumb"
                        width="72"
                        height="72"
                      />
                      <div class="order-item-details">
                        <h3 class="order-item-title">Gold Necklace</h3>
                        <p class="order-item-qty">1 item</p>
                        <p class="order-item-price">&#8377;45,000.00</p>
                      </div>
                    </div>
                    <div class="order-card-foot">
                      <span class="order-footnote"
                        >Delivered on January 20, 2024</span
                      >
                      <button
                        type="button"
                        class="order-arrow-btn"
                        aria-label="View order ORD-001 details"
                        title="View details"
                      >
                        <i
                          class="fa-solid fa-arrow-right"
                          aria-hidden="true"
                        ></i>
                      </button>
                    </div>
                  </article>

                  <!-- Order 2 -->
                  <article class="order-card" data-status="processing">
                    <div class="order-card-head">
                      <div class="order-meta">
                        <strong class="order-id">ORD-002</strong>
                        <span class="order-date">February 1, 2024</span>
                      </div>
                      <span class="order-status-badge status--processing"
                        >Processing</span
                      >
                    </div>
                    <div class="order-card-body">
                      <img
                        src="assets/images/tt1.webp"
                        alt="Diamond Ring"
                        class="order-thumb"
                        width="72"
                        height="72"
                      />
                      <div class="order-item-details">
                        <h3 class="order-item-title">Diamond Ring</h3>
                        <p class="order-item-qty">1 item</p>
                        <p class="order-item-price">&#8377;75,000.00</p>
                      </div>
                    </div>
                    <div class="order-card-foot">
                      <span class="order-footnote"
                        >Estimated delivery by February 8, 2024</span
                      >
                      <button
                        type="button"
                        class="order-arrow-btn"
                        aria-label="View order ORD-002 details"
                        title="View details"
                      >
                        <i
                          class="fa-solid fa-arrow-right"
                          aria-hidden="true"
                        ></i>
                      </button>
                    </div>
                  </article>

                  <!-- Order 3 -->
                  <article class="order-card" data-status="out-for-delivery">
                    <div class="order-card-head">
                      <div class="order-meta">
                        <strong class="order-id">ORD-003</strong>
                        <span class="order-date">February 18, 2024</span>
                      </div>
                      <span class="order-status-badge status--delivery"
                        >Out for Delivery</span
                      >
                    </div>
                    <div class="order-card-body">
                      <img
                        src="assets/images/lr2.jpg"
                        alt="Handcrafted Platinum Bracelet"
                        class="order-thumb"
                        width="72"
                        height="72"
                      />
                      <div class="order-item-details">
                        <h3 class="order-item-title">
                          Handcrafted Platinum Bracelet
                        </h3>
                        <p class="order-item-qty">1 item</p>
                        <p class="order-item-price">&#8377;62,500.00</p>
                      </div>
                    </div>
                    <div class="order-card-foot">
                      <span class="order-footnote"
                        >Expected delivery today by 8:00 PM</span
                      >
                      <button
                        type="button"
                        class="order-arrow-btn"
                        aria-label="View order ORD-003 details"
                        title="View details"
                      >
                        <i
                          class="fa-solid fa-arrow-right"
                          aria-hidden="true"
                        ></i>
                      </button>
                    </div>
                  </article>

                  <!-- Order 4 -->
                  <article class="order-card" data-status="delivered">
                    <div class="order-card-head">
                      <div class="order-meta">
                        <strong class="order-id">ORD-004</strong>
                        <span class="order-date">December 22, 2023</span>
                      </div>
                      <span class="order-status-badge status--delivered"
                        >Delivered</span
                      >
                    </div>
                    <div class="order-card-body">
                      <img
                        src="assets/images/tt3.webp"
                        alt="Pure Silk Traditional Kurta Set"
                        class="order-thumb"
                        width="72"
                        height="72"
                      />
                      <div class="order-item-details">
                        <h3 class="order-item-title">
                          Pure Silk Traditional Kurta Set
                        </h3>
                        <p class="order-item-qty">2 items</p>
                        <p class="order-item-price">&#8377;18,200.00</p>
                      </div>
                    </div>
                    <div class="order-card-foot">
                      <span class="order-footnote"
                        >Delivered on December 26, 2023</span
                      >
                      <button
                        type="button"
                        class="order-arrow-btn"
                        aria-label="View order ORD-004 details"
                        title="View details"
                      >
                        <i
                          class="fa-solid fa-arrow-right"
                          aria-hidden="true"
                        ></i>
                      </button>
                    </div>
                  </article>
                </div>
              </div>

              <!-- Tab Panel 3: Address Book -->
              <div
                class="account-tab-panel"
                id="panel-address"
                role="tabpanel"
                aria-labelledby="tab-address"
                hidden
              >
                <div class="address-top-bar">
                  <button
                    type="button"
                    class="btn-gold-solid"
                    id="btnAddAddressBtn"
                  >
                    + ADD ADDRESS
                  </button>
                </div>

                <div class="address-grid" id="addressGrid">
                  <!-- Address Card 1 -->
                  <div class="address-card" data-address-id="addr-1">
                    <div class="address-card-header">
                      <span class="address-pin-icon"
                        ><i class="fa-solid fa-location-dot"></i
                      ></span>
                      <strong class="address-label-name">Address_1</strong>
                      <span class="address-tag-default">Default</span>
                    </div>
                    <div class="address-card-body">
                      <p class="address-user-name">John Doe</p>
                      <p class="address-line">123 Main Street, Apartment 4B</p>
                      <p class="address-line">Mumbai, MAHARASHTRA - 400001</p>
                      <p class="address-phone">Phone: +91 9876543210</p>
                    </div>
                    <div class="address-card-actions">
                      <button
                        type="button"
                        class="address-btn address-btn--edit"
                        data-action="edit"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="address-btn address-btn--delete"
                        data-action="delete"
                      >
                        Delete
                      </button>
                    </div>
                  </div>

                  <!-- Address Card 2 -->
                  <div class="address-card" data-address-id="addr-2">
                    <div class="address-card-header">
                      <span class="address-pin-icon"
                        ><i class="fa-solid fa-location-dot"></i
                      ></span>
                      <strong class="address-label-name">Address_2</strong>
                    </div>
                    <div class="address-card-body">
                      <p class="address-user-name">ertty rjghfjhg</p>
                      <p class="address-line">rtertytry, rtertyrt</p>
                      <p class="address-line">fgghjfghj, NAGALAND - 123456</p>
                      <p class="address-phone">Phone: trtrey</p>
                    </div>
                    <div class="address-card-actions">
                      <button
                        type="button"
                        class="address-btn address-btn--edit"
                        data-action="edit"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="address-btn address-btn--delete"
                        data-action="delete"
                      >
                        Delete
                      </button>
                    </div>
                  </div>

                  <!-- Address Card 3 -->
                  <div class="address-card" data-address-id="addr-3">
                    <div class="address-card-header">
                      <span class="address-pin-icon"
                        ><i class="fa-solid fa-location-dot"></i
                      ></span>
                      <strong class="address-label-name">Address_3</strong>
                    </div>
                    <div class="address-card-body">
                      <p class="address-user-name">John Doe (Office)</p>
                      <p class="address-line">
                        CC Hub Business Tower, 5th Floor, Suite 502
                      </p>
                      <p class="address-line">Kochi, KERALA - 682016</p>
                      <p class="address-phone">Phone: +91 9847030064</p>
                    </div>
                    <div class="address-card-actions">
                      <button
                        type="button"
                        class="address-btn address-btn--edit"
                        data-action="edit"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="address-btn address-btn--delete"
                        data-action="delete"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tab Panel 4: Contact Us -->
              <div
                class="account-tab-panel"
                id="panel-contact"
                role="tabpanel"
                aria-labelledby="tab-contact"
                hidden
              >
                <div class="contact-support-grid">
                  <div class="support-card">
                    <span class="support-icon"
                      ><i class="fa-solid fa-phone"></i
                    ></span>
                    <h3 class="support-title">PHONE SUPPORT</h3>
                    <a href="tel:+911234567890" class="support-link"
                      >+91 1234-567-890</a
                    >
                    <p class="support-note">
                      Available: Monday - Friday, 10 AM - 6 PM
                    </p>
                  </div>
                  <div class="support-card">
                    <span class="support-icon"
                      ><i class="fa-solid fa-envelope"></i
                    ></span>
                    <h3 class="support-title">EMAIL SUPPORT</h3>
                    <a href="mailto:support@vajra.com" class="support-link"
                      >support@vajra.com</a
                    >
                    <p class="support-note">response time: within 24 hours</p>
                  </div>
                </div>

                <div class="contact-form-section">
                  <h3 class="contact-form-title">Send Us a Direct Message</h3>
                  <form class="account-contact-form" id="accountContactForm">
                    <div class="form-row-2">
                      <div class="form-field">
                        <label for="contactSubject">Subject</label>
                        <input
                          type="text"
                          id="contactSubject"
                          placeholder="How can we assist you?"
                          required
                        />
                      </div>
                      <div class="form-field">
                        <label for="contactOrderRef"
                          >Order Reference (Optional)</label
                        >
                        <input
                          type="text"
                          id="contactOrderRef"
                          placeholder="e.g. ORD-001"
                        />
                      </div>
                    </div>
                    <div class="form-field">
                      <label for="contactMessage">Message</label>
                      <textarea
                        id="contactMessage"
                        rows="4"
                        placeholder="Write your message here..."
                        required
                      ></textarea>
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
    <dialog
      class="account-dialog"
      id="editProfileDialog"
      aria-labelledby="editProfileTitle"
    >
      <div class="account-dialog-card">
        <button
          type="button"
          class="account-dialog-close"
          id="editProfileClose"
          aria-label="Close dialog"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>
        <h2 class="account-dialog-title" id="editProfileTitle">
          Edit Personal Information
        </h2>
        <p class="account-dialog-sub">Update your account details below.</p>
        <form class="account-dialog-form" id="editProfileForm">
          <div class="form-field">
            <label for="inputName">Full Name</label>
            <input type="text" id="inputName" value="John Doe" required />
          </div>
          <div class="form-field">
            <label for="inputPhone">Phone Number</label>
            <input type="tel" id="inputPhone" value="+91 9876543210" required />
          </div>
          <div class="form-field">
            <label for="inputEmail">Email Address</label>
            <input
              type="email"
              id="inputEmail"
              value="john.doe@example.com"
              required
            />
          </div>
          <div class="form-field">
            <label for="inputDob">Date of Birth</label>
            <input type="date" id="inputDob" />
          </div>
          <div class="dialog-btn-row">
            <button type="submit" class="btn-gold-solid">SAVE CHANGES</button>
            <button type="button" class="btn-secondary" id="editProfileCancel">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <!-- Add/Edit Address Dialog -->
    <dialog
      class="account-dialog"
      id="addressDialog"
      aria-labelledby="addressDialogTitle"
    >
      <div class="account-dialog-card">
        <button
          type="button"
          class="account-dialog-close"
          id="addressDialogClose"
          aria-label="Close dialog"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>
        <h2 class="account-dialog-title" id="addressDialogTitle">
          Add New Address
        </h2>
        <p class="account-dialog-sub">Fill in the address details below.</p>
        <form class="account-dialog-form" id="addressForm">
          <input type="hidden" id="addressEditId" value="" />
          <div class="form-field">
            <label for="addrLabel">Address Label</label>
            <input
              type="text"
              id="addrLabel"
              placeholder="e.g. Address_1, Home, Office"
              required
            />
          </div>
          <div class="form-field">
            <label for="addrName">Recipient Name</label>
            <input type="text" id="addrName" placeholder="Full name" required />
          </div>
          <div class="form-field">
            <label for="addrStreet">Street Address</label>
            <input
              type="text"
              id="addrStreet"
              placeholder="House/Flat number, Street name"
              required
            />
          </div>
          <div class="form-row-2">
            <div class="form-field">
              <label for="addrCity">City / District</label>
              <input type="text" id="addrCity" placeholder="City" required />
            </div>
            <div class="form-field">
              <label for="addrState">State</label>
              <input type="text" id="addrState" placeholder="State" required />
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-field">
              <label for="addrPincode">PIN Code</label>
              <input
                type="text"
                id="addrPincode"
                placeholder="6-digit PIN code"
                required
              />
            </div>
            <div class="form-field">
              <label for="addrPhone">Contact Phone</label>
              <input
                type="tel"
                id="addrPhone"
                placeholder="Mobile number"
                required
              />
            </div>
          </div>
          <div class="dialog-btn-row">
            <button type="submit" class="btn-gold-solid">SAVE ADDRESS</button>
            <button
              type="button"
              class="btn-secondary"
              id="addressDialogCancel"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <!-- Toast Notification -->
    <div
      class="account-toast"
      id="accountToast"
      role="status"
      aria-live="polite"
    >
      <i class="fa-solid fa-circle-check"></i>
      <span id="accountToastMsg">Action completed successfully.</span>
    </div>

    <!-- ========== FOOTER ========== -->
   
@endsection

