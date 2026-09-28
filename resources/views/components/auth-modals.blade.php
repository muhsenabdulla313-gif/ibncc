<dialog class="login-modal" id="loginModal" aria-labelledby="login-title">
    <div class="login-card">
      <button type="button" class="login-modal-close" id="loginModalClose" aria-label="Close login">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>

      <header class="login-card-head">
        <span class="login-lock" aria-hidden="true">
          <i class="fa-solid fa-lock"></i>
        </span>
        <div>
          <h2 id="login-title">Login to IBNCC</h2>
          <p class="login-tagline">Connect<span>•</span>Trade<span>•</span>Grow</p>
        </div>
      </header>

<form class="login-form" id="loginForm" method="POST" action="{{ route('login.submit') }}">
  @csrf
          <div class="login-field">
          <i class="fa-solid fa-phone login-field-icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="loginPhone">Phone Number</label>
          <input
            id="loginPhone"
            type="tel"
            name="phone"
            placeholder="Phone Number"
            autocomplete="tel"
            required
          />
        </div>

        <div class="login-field">
          <i class="fa-solid fa-lock login-field-icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="loginPassword">Password</label>
          <input
            id="loginPassword"
            type="password"
            name="password"
            placeholder="Password"
            autocomplete="current-password"
            required
          />
          <button
            type="button"
            class="login-password-toggle"
            id="loginPasswordToggle"
            aria-label="Show password"
          >
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>

        <div class="login-row">
          <label class="login-remember">
            <input type="checkbox" name="remember" />
            Remember me
          </label>
          <a href="#forgot-password" class="login-forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="login-submit">
          Login
          <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
        </button>

        <div class="login-divider">or</div>

        <a href="#register" class="login-register">
          <i class="fa-solid fa-user-group" aria-hidden="true"></i>
          New Member? Register Here
        </a>
      </form>

      <div class="login-wave" aria-hidden="true">
        <svg viewBox="0 0 420 72" preserveAspectRatio="none">
          <path fill="#f3c43a" d="M0 26c48 22 86-16 138-4 54 12 78 36 132 26 50-8 82-30 150-6v30H0z" />
          <path fill="#e8a31a" d="M0 42c58 16 92-14 150-4 56 10 88 26 142 14 48-10 72-6 128 10v10H0z" />
          <path fill="#d97706" d="M0 58c72 10 112-8 182 2 56 8 92 12 238 2v10H0z" />
        </svg>
      </div>
    </div>
  </dialog>

  <dialog class="login-modal otp-modal" id="otpModal" aria-labelledby="otp-title">
    <div class="login-card otp-card">
      <button type="button" class="login-modal-close" id="otpModalClose" aria-label="Close OTP verification">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>

      <header class="login-card-head">
        <span class="login-lock" aria-hidden="true">
          <i class="fa-solid fa-shield-halved"></i>
        </span>
        <div>
          <h2 id="otp-title">OTP Verification</h2>
          <p class="login-tagline">Verify your registered mobile</p>
        </div>
      </header>

      <p class="otp-sent-text">We've sent a 6-digit OTP to the mobile number associated with your signup account.</p>
      <p class="otp-phone">Sent to <span id="otpMaskedPhone">your registered mobile</span></p>

      <div class="otp-sms" id="otpSms" hidden>
        <span class="otp-sms-label">Message from CC Hub</span>
        <p>Your OTP is <strong id="otpSmsCode"></strong>. Do not share this code with anyone.</p>
      </div>

      <form class="otp-form" id="otpForm">
        <div class="otp-inputs" role="group" aria-label="One-time password">
          <label class="visually-hidden" for="otpDigit1">Digit 1</label>
          <input id="otpDigit1" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" required />
          <label class="visually-hidden" for="otpDigit2">Digit 2</label>
          <input id="otpDigit2" type="text" inputmode="numeric" maxlength="1" required />
          <label class="visually-hidden" for="otpDigit3">Digit 3</label>
          <input id="otpDigit3" type="text" inputmode="numeric" maxlength="1" required />
          <label class="visually-hidden" for="otpDigit4">Digit 4</label>
          <input id="otpDigit4" type="text" inputmode="numeric" maxlength="1" required />
          <label class="visually-hidden" for="otpDigit5">Digit 5</label>
          <input id="otpDigit5" type="text" inputmode="numeric" maxlength="1" required />
          <label class="visually-hidden" for="otpDigit6">Digit 6</label>
          <input id="otpDigit6" type="text" inputmode="numeric" maxlength="1" required />
        </div>

        <p class="otp-error" id="otpError" hidden></p>
        <p class="otp-success" id="otpSuccess" hidden></p>

        <button type="submit" class="login-submit">
          Verify OTP
          <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </button>

        <div class="otp-resend-row">
          <span>Didn't receive the code?</span>
          <button type="button" class="otp-resend" id="otpResend">Resend OTP</button>
          <span class="otp-timer" id="otpTimer"></span>
        </div>

        <a href="#login" class="otp-back-login" id="otpBackLogin">Back to Login</a>
      </form>

      <div class="login-wave" aria-hidden="true">
        <svg viewBox="0 0 420 72" preserveAspectRatio="none">
          <path fill="#f3c43a" d="M0 26c48 22 86-16 138-4 54 12 78 36 132 26 50-8 82-30 150-6v30H0z" />
          <path fill="#e8a31a" d="M0 42c58 16 92-14 150-4 56 10 88 26 142 14 48-10 72-6 128 10v10H0z" />
          <path fill="#d97706" d="M0 58c72 10 112-8 182 2 56 8 92 12 238 2v10H0z" />
        </svg>
      </div>
    </div>
  </dialog>

  <dialog class="login-modal" id="resetPasswordModal" aria-labelledby="reset-password-title">
    <div class="login-card otp-card">
      <button type="button" class="login-modal-close" id="resetPasswordModalClose" aria-label="Close set new password">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>

      <header class="login-card-head">
        <span class="login-lock" aria-hidden="true">
          <i class="fa-solid fa-key"></i>
        </span>
        <div>
          <h2 id="reset-password-title">Set New Password</h2>
          <p class="login-tagline">Create a new password for your account</p>
        </div>
      </header>

      <p class="reset-hint">Your mobile number has been verified. Enter a new password to continue.</p>

      <form class="login-form" id="resetPasswordForm">
        <div class="login-field">
          <i class="fa-solid fa-lock login-field-icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="resetPassword">New Password</label>
          <input id="resetPassword" type="password" name="password" placeholder="New Password" autocomplete="new-password" minlength="6" required />
          <button type="button" class="login-password-toggle" data-password-toggle="resetPassword" aria-label="Show password">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>

        <div class="login-field">
          <i class="fa-solid fa-lock login-field-icon" aria-hidden="true"></i>
          <label class="visually-hidden" for="resetPasswordConfirm">Confirm New Password</label>
          <input id="resetPasswordConfirm" type="password" name="confirm" placeholder="Confirm New Password" autocomplete="new-password" minlength="6" required />
          <button type="button" class="login-password-toggle" data-password-toggle="resetPasswordConfirm" aria-label="Show password">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>

        <p class="otp-error" id="resetPasswordError" hidden></p>
        <p class="otp-success" id="resetPasswordSuccess" hidden></p>

        <button type="submit" class="login-submit">
          Save Password
          <i class="fa-solid fa-check" aria-hidden="true"></i>
        </button>

        <a href="#login" class="otp-back-login" id="resetBackLogin">Back to Login</a>
      </form>

      <div class="login-wave" aria-hidden="true">
        <svg viewBox="0 0 420 72" preserveAspectRatio="none">
          <path fill="#f3c43a" d="M0 26c48 22 86-16 138-4 54 12 78 36 132 26 50-8 82-30 150-6v30H0z" />
          <path fill="#e8a31a" d="M0 42c58 16 92-14 150-4 56 10 88 26 142 14 48-10 72-6 128 10v10H0z" />
          <path fill="#d97706" d="M0 58c72 10 112-8 182 2 56 8 92 12 238 2v10H0z" />
        </svg>
      </div>
    </div>
  </dialog>

  <dialog class="login-modal register-modal" id="registerModal" aria-labelledby="register-title">
    <div class="register-card">
      <button type="button" class="login-modal-close" id="registerModalClose" aria-label="Close registration">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>

      <header class="register-card-head">
        <span class="register-user-icon" aria-hidden="true">
          <i class="fa-solid fa-user-plus"></i>
        </span>
        <h2 id="register-title">Create Your Account</h2>
        <p>Sign up to get started with IBNCC</p>
      </header>

<form class="register-form" id="registerForm" method="POST"
      action="{{ route('register.store') }}"
      data-otp-send="{{ route('register.otp.send') }}"
      data-otp-verify="{{ route('register.otp.verify') }}">
  @csrf
          <div class="register-field">
          <i class="fa-solid fa-user" aria-hidden="true"></i>
          <div class="register-field-body">
            <label for="registerName">Name</label>
            <input id="registerName" type="text" name="name" placeholder="Enter your full name" autocomplete="name" required />
          </div>
        </div>

        <div class="register-field">
          <i class="fa-solid fa-church" aria-hidden="true"></i>
          <div class="register-field-body">
            <label for="registerParish">Parish</label>
            <input id="registerParish" type="text" name="parish" placeholder="Enter your parish" required />
          </div>
        </div>

        <div class="register-field">
          <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
          <div class="register-field-body">
            <label for="registerDiocese">Diocese</label>
            <select id="registerDiocese" name="diocese" required>
              <option value="" disabled selected>Select your diocese</option>
              <option value="kozhikode">Kozhikode</option>
              <option value="ernakulam">Ernakulam-Angamaly</option>
              <option value="thrissur">Thrissur</option>
              <option value="kannur">Kannur</option>
              <option value="palakkad">Palakkad</option>
              <option value="thamarassery">Thamarassery</option>
            </select>
          </div>
          <i class="fa-solid fa-chevron-down register-chevron" aria-hidden="true"></i>
        </div>

        <div class="register-field">
          <i class="fa-solid fa-cross" aria-hidden="true"></i>
          <div class="register-field-body">
            <label for="registerRite">Rite</label>
            <select id="registerRite" name="rite" required>
              <option value="" disabled selected>Select your rite</option>
              <option value="syro-malabar">Syro-Malabar</option>
              <option value="syro-malankara">Syro-Malankara</option>
              <option value="latin">Latin</option>
            </select>
          </div>
          <i class="fa-solid fa-chevron-down register-chevron" aria-hidden="true"></i>
        </div>

        <div class="register-field">
          <i class="fa-solid fa-lock" aria-hidden="true"></i>
          <div class="register-field-body">
            <label for="registerPassword">Password</label>
            <input id="registerPassword" type="password" name="password" placeholder="Enter your password" autocomplete="new-password" required />
          </div>
          <button type="button" class="login-password-toggle" data-password-toggle="registerPassword" aria-label="Show password">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>

        <div class="register-field">
          <i class="fa-solid fa-lock" aria-hidden="true"></i>
          <div class="register-field-body">
            <label for="registerConfirm">Confirm Password</label>
            <input id="registerConfirm" type="password" name="confirm" placeholder="Confirm your password" autocomplete="new-password" required />
          </div>
          <button type="button" class="login-password-toggle" data-password-toggle="registerConfirm" aria-label="Show password">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
          </button>
        </div>

        <div class="register-verify-group" data-verify-type="phone">
          <div class="register-field register-field--verify" id="registerPhoneField">
            <i class="fa-solid fa-phone" aria-hidden="true"></i>
            <div class="register-field-body">
              <label for="registerPhone">Phone Number</label>
              <input id="registerPhone" type="tel" name="phone" placeholder="Enter your phone number" autocomplete="tel" required />
            </div>
            <button type="button" class="register-verify-btn" id="registerPhoneVerifyBtn" hidden>Verify</button>
            <span class="register-verified-badge" id="registerPhoneVerified" hidden aria-live="polite">
              <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified
            </span>
          </div>
          <div class="register-inline-otp" id="registerPhoneOtpPanel" hidden>
            <p class="register-inline-otp-hint">Enter the OTP sent to your phone number</p>
            <div class="register-inline-otp-row">
              <label class="visually-hidden" for="registerPhoneOtpInput">Phone OTP</label>
              <input
                id="registerPhoneOtpInput"
                class="register-inline-otp-input"
                type="text"
                inputmode="numeric"
                maxlength="6"
                autocomplete="one-time-code"
                placeholder="Enter 6-digit OTP"
              />
              <button type="button" class="register-otp-confirm" id="registerPhoneOtpConfirm">Confirm</button>
            </div>
            <p class="register-inline-otp-demo" id="registerPhoneOtpDemo" hidden></p>
            <p class="register-inline-otp-msg" id="registerPhoneOtpMsg" hidden></p>
          </div>
        </div>

        <div class="register-verify-group" data-verify-type="email">
          <div class="register-field register-field--verify" id="registerEmailField">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <div class="register-field-body">
              <label for="registerEmail">Email (Optional)</label>
              <input id="registerEmail" type="email" name="email" placeholder="Enter your email address" autocomplete="email" />
            </div>
            <button type="button" class="register-verify-btn" id="registerEmailVerifyBtn" hidden>Verify</button>
            <span class="register-verified-badge" id="registerEmailVerified" hidden aria-live="polite">
              <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified
            </span>
          </div>
          <div class="register-inline-otp" id="registerEmailOtpPanel" hidden>
            <p class="register-inline-otp-hint">Enter the OTP sent to your email address</p>
            <div class="register-inline-otp-row">
              <label class="visually-hidden" for="registerEmailOtpInput">Email OTP</label>
              <input
                id="registerEmailOtpInput"
                class="register-inline-otp-input"
                type="text"
                inputmode="numeric"
                maxlength="6"
                autocomplete="one-time-code"
                placeholder="Enter 6-digit OTP"
              />
              <button type="button" class="register-otp-confirm" id="registerEmailOtpConfirm">Confirm</button>
            </div>
            <p class="register-inline-otp-demo" id="registerEmailOtpDemo" hidden></p>
            <p class="register-inline-otp-msg" id="registerEmailOtpMsg" hidden></p>
          </div>
        </div>

        <p class="otp-error" id="registerError" hidden></p>
        <button type="submit" class="login-submit register-submit">
          <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
          Sign Up
        </button>

        <div class="login-divider">OR</div>

        <button type="button" class="register-google" id="registerGoogleBtn">
          <svg viewBox="0 0 48 48" width="20" height="20" aria-hidden="true">
            <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.6 32.4 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3.1l5.7-5.7C34.2 6.1 29.4 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z"/>
            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 12 24 12c3.1 0 5.8 1.2 8 3.1l5.7-5.7C34.2 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
            <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.5-11.2-8.3l-6.5 5C9.6 39.6 16.3 44 24 44z"/>
            <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1.1 3.2-3.5 5.8-6.6 7.3l6.2 5.2C37.5 38.3 44 33 44 24c0-1.3-.1-2.7-.4-3.5z"/>
          </svg>
          Sign up with Google
        </button>

        <p class="register-legal">
          By signing up, you agree to our<br />
          <a href="#terms">Terms of Use</a>
          <span>| and</span>
          <a href="#privacy">Privacy Policy</a>
        </p>
      </form>
    </div>
  </dialog>