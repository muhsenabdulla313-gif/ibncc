<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<style>
* { box-sizing: border-box; }
body { margin: 0; }

.adlgn-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: #0b0f1f;
    position: relative;
    overflow: hidden;
    padding: 20px;
}

/* animated gradient blobs */
.adlgn-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.55;
    z-index: 0;
    animation: adlgnFloat 12s ease-in-out infinite;
}
.adlgn-blob.b1 {
    width: 420px; height: 420px;
    background: #6a11cb;
    top: -120px; left: -100px;
    animation-delay: 0s;
}
.adlgn-blob.b2 {
    width: 380px; height: 380px;
    background: #2575fc;
    bottom: -140px; right: -100px;
    animation-delay: 2s;
}
.adlgn-blob.b3 {
    width: 260px; height: 260px;
    background: #ff6a88;
    top: 40%; left: 60%;
    animation-delay: 4s;
    opacity: 0.35;
}

@keyframes adlgnFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -40px) scale(1.08); }
    66% { transform: translate(-25px, 25px) scale(0.95); }
}

/* subtle moving grid */
.adlgn-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 42px 42px;
    z-index: 0;
    animation: adlgnGridDrift 30s linear infinite;
}
@keyframes adlgnGridDrift {
    from { background-position: 0 0; }
    to { background-position: 200px 200px; }
}

/* floating particles */
.adlgn-particle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    z-index: 0;
    animation: adlgnRise linear infinite;
}
@keyframes adlgnRise {
    0% { transform: translateY(0) translateX(0); opacity: 0; }
    10% { opacity: 0.8; }
    90% { opacity: 0.5; }
    100% { transform: translateY(-100vh) translateX(20px); opacity: 0; }
}

.adlgn-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 390px;
    background: rgba(20, 22, 40, 0.6);
    border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 18px;
    padding: 42px 36px 30px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    color: #fff;
    opacity: 0;
    transform: translateY(24px);
    animation: adlgnCardIn 0.7s ease forwards 0.15s;
}

@keyframes adlgnCardIn {
    to { opacity: 1; transform: translateY(0); }
}

.adlgn-brand {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 28px;
}

.adlgn-brand-mark-wrap {
    position: relative;
    width: 52px;
    height: 52px;
    margin-bottom: 14px;
}

.adlgn-brand-mark-wrap::before {
    content: '';
    position: absolute;
    inset: -8px;
    border-radius: 18px;
    border: 2px solid transparent;
    border-top-color: #7c3aed;
    border-right-color: #2575fc;
    animation: adlgnSpin 2.5s linear infinite;
}

@keyframes adlgnSpin {
    to { transform: rotate(360deg); }
}

.adlgn-brand-mark {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    background-size: 200% 200%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 8px 22px rgba(37,117,252,0.4);
    animation: adlgnGradientShift 4s ease infinite, adlgnPulse 3s ease-in-out infinite;
}

.adlgn-brand-mark img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 14px;
}

@keyframes adlgnGradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes adlgnPulse {
    0%, 100% { box-shadow: 0 8px 22px rgba(37,117,252,0.4); }
    50% { box-shadow: 0 8px 30px rgba(106,17,203,0.6); }
}

.adlgn-brand h1 {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 4px;
}

.adlgn-brand p {
    font-size: 13px;
    color: rgba(255,255,255,0.55);
    margin: 0;
}

.adlgn-field {
    margin-bottom: 18px;
    opacity: 0;
    transform: translateY(12px);
    animation: adlgnFieldIn 0.5s ease forwards;
}
.adlgn-field:nth-child(1) { animation-delay: 0.35s; }
.adlgn-field:nth-child(2) { animation-delay: 0.45s; }

@keyframes adlgnFieldIn {
    to { opacity: 1; transform: translateY(0); }
}

.adlgn-field label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: rgba(255,255,255,0.65);
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

.adlgn-input-group {
    position: relative;
}

.adlgn-input-group .adlgn-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.4);
    font-size: 15px;
    line-height: 1;
}

.adlgn-input-group input {
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    background: rgba(255,255,255,0.05);
    color: #fff;
    transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}
.adlgn-input-group input::placeholder { color: rgba(255,255,255,0.3); }

.adlgn-input-group input:focus {
    border-color: #7c3aed;
    background: rgba(255,255,255,0.09);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
}

.adlgn-underline {
    position: absolute;
    bottom: -2px;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #7c3aed, #2575fc);
    transition: width 0.3s ease, left 0.3s ease;
    border-radius: 2px;
}

.adlgn-input-group input:focus ~ .adlgn-underline {
    width: 100%;
    left: 0;
}

.adlgn-toggle-pass {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: rgba(255,255,255,0.5);
    font-size: 11.5px;
    font-weight: 600;
    user-select: none;
}

.adlgn-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
    font-size: 12.5px;
    opacity: 0;
    animation: adlgnFieldIn 0.5s ease forwards 0.5s;
}

.adlgn-remember {
    display: flex;
    align-items: center;
    gap: 6px;
    color: rgba(255,255,255,0.65);
}

.adlgn-forgot {
    color: #a78bfa;
    font-weight: 600;
    text-decoration: none;
}

.adlgn-btn {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    background-size: 200% 200%;
    color: #fff;
    font-size: 14.5px;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: 0.3px;
    box-shadow: 0 10px 24px rgba(37,117,252,0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    opacity: 0;
    animation: adlgnFieldIn 0.5s ease forwards 0.6s, adlgnGradientShift 4s ease infinite;
}

.adlgn-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(37,117,252,0.5);
}

.adlgn-btn:active {
    transform: translateY(0) scale(0.98);
}

.adlgn-btn {
    position: relative;
    overflow: hidden;
}

.adlgn-btn::after {
    content: '';
    position: absolute;
    top: 0;
    left: -60%;
    width: 40%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.35), transparent);
    transform: skewX(-20deg);
    animation: adlgnShine 3.2s ease-in-out infinite;
}

@keyframes adlgnShine {
    0% { left: -60%; }
    50% { left: 130%; }
    100% { left: 130%; }
}

.adlgn-error {
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.35);
    color: #fca5a5;
    font-size: 13px;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 16px;
    animation: adlgnShake 0.4s ease;
}

@keyframes adlgnShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    75% { transform: translateX(6px); }
}

.adlgn-footer {
    text-align: center;
    margin-top: 22px;
    font-size: 11.5px;
    color: rgba(255,255,255,0.35);
}
</style>
</head>
<body>

<div class="adlgn-page">
    <div class="adlgn-blob b1"></div>
    <div class="adlgn-blob b2"></div>
    <div class="adlgn-blob b3"></div>
    <div class="adlgn-grid"></div>
    <div id="adlgn-particles"></div>

    <div class="adlgn-card">

        <div class="adlgn-brand">
            <div class="adlgn-brand-mark-wrap">
                <div class="adlgn-brand-mark">
                    <img src="{{ asset('assets/images/cchub/logo_ibncc.PNG') }}" alt="{{ config('app.name') }}">
                </div>
            </div>
            <h1>Admin Sign In</h1>
            <p>{{ config('app.name', 'Dashboard') }} control panel</p>
        </div>

        @if ($errors->any())
            <div class="adlgn-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="adlgn-field">
                <label>Email address</label>
                <div class="adlgn-input-group">
                    <span class="adlgn-icon">&#9993;</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
                    <span class="adlgn-underline"></span>
                </div>
            </div>

            <div class="adlgn-field">
                <label>Password</label>
                <div class="adlgn-input-group">
                    <span class="adlgn-icon">&#128274;</span>
                    <input type="password" name="password" id="adlgn-password" placeholder="••••••••" required>
                    <span class="adlgn-toggle-pass" onclick="adlgnTogglePass()">Show</span>
                    <span class="adlgn-underline"></span>
                </div>
            </div>

            <div class="adlgn-row">
                <label class="adlgn-remember">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="{{ route('password.request') }}" class="adlgn-forgot">Forgot password?</a>
            </div>

            <button type="submit" class="adlgn-btn">Sign in</button>
        </form>

        <div class="adlgn-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} — Protected area
        </div>
    </div>
</div>

<script>
(function () {
    const wrap = document.getElementById('adlgn-particles');
    const count = 26;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('span');
        const size = 2 + Math.random() * 4;
        p.className = 'adlgn-particle';
        p.style.width = size + 'px';
        p.style.height = size + 'px';
        p.style.left = Math.random() * 100 + '%';
        p.style.bottom = '-10px';
        p.style.animationDuration = (8 + Math.random() * 10) + 's';
        p.style.animationDelay = (Math.random() * 10) + 's';
        wrap.appendChild(p);
    }
})();

function adlgnTogglePass() {
    const input = document.getElementById('adlgn-password');
    const btn = document.querySelector('.adlgn-toggle-pass');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}
</script>

</body>
</html>