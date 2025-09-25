<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Kemenaker</title>
  <link rel="icon" href="{{ asset('images/logo-kemnaker.png') }}" type="image/png">
  {{-- Font Awesome dari CDN --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
    }

    .container {
      display: flex;
      height: 100vh;
    }

    .left-side {
      flex: 1;
      background-color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      z-index: 1;
    }

    .form-box {
      max-width: 350px;
      width: 100%;
      padding: 20px;
      position: relative;
    }

    .form-box h2 {
      text-align: center;
      font-size: 32px;
      margin-bottom: 30px;
    }

    .input-group {
      margin-bottom: 20px;
      position: relative;
    }

    .input-group input {
      width: 100%;
      padding: 12px 40px 12px 12px;
      font-size: 16px;
      border: none;
      border-bottom: 1px solid #aaa;
      outline: none;
    }

    .input-group i {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #555;
    }

    .login-button {
      width: 100%;
      background-color: #00796B;
      color: white;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
    }

    .bottom-links {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      margin-top: 10px;
    }

    .bottom-links a {
      color: blue;
      text-decoration: none;
    }

    .right-side {
      flex: 1;
      background-color: #00796B;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      clip-path: polygon(25% 0, 100% 0, 100% 100%, 0% 100%);
      z-index: 0;
    }

    .logo-container {
      text-align: center;
      padding: 40px;
    }

    .logo-container img {
      width: 120px; /* Logo diperbesar */
      margin-bottom: 25px;
    }

    .logo-container h1 {
      font-size: 48px; /* Ukuran font nama aplikasi */
      font-weight: 700;
      margin: 0 0 10px 0;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2); /* Efek bayangan untuk highlight */
    }

    .logo-container h3 {
      font-weight: 400;
      font-size: 16px; /* Disesuaikan agar lebih kecil dari nama aplikasi */
      line-height: 1.5;
      margin: 0;
    }

    .video-background-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -2;
    }

    #background-video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .video-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5); /* Overlay gelap */
    }


    .container {
        position: relative;
        z-index: 1;
    }

    .left-side, .right-side {
        background-color: transparent !important;
    }

    .form-box {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .corner-badge {
        position: absolute;
        width: 660px;
        max-width: 90%;
        height: auto;
        pointer-events: none;
    }

    .corner-badge--login {
        top: -160px;
        right: -200px;
    }

    .right-side {
        clip-path: none !important; /* Hapus clip-path agar tidak memotong video */
    }


    /* RESPONSIVE */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .right-side {
        clip-path: none;
        width: 100%;
        padding: 30px 20px;
        order: -1;
      }

      .logo-container img {
        width: 80px; /* Logo disesuaikan untuk mobile */
      }
      
      .logo-container h1 {
        font-size: 36px; /* Ukuran font nama aplikasi di mobile */
      }

      .logo-container h3 {
        font-size: 14px;
      }

      .form-box h2 {
        font-size: 28px;
      }

      .corner-badge {
        width: 400px;
        top: -90px;
        right: -110px;
      }

      .bottom-links {
        flex-direction: column;
        align-items: center;
        gap: 5px;
      }

    }
  </style>
</head>
<body>

  <div class="video-background-container">
    <video autoplay muted loop id="background-video">
      <source src="{{ asset('videos/background.mp4') }}" type="video/mp4">
      Your browser does not support the video tag.
    </video>
    <div class="video-overlay"></div>
  </div>

  <div class="container">
    <div class="left-side">
      <form class="form-box" method="POST" action="{{ route('login') }}">
        <img src="{{ asset('images/tamasya-badge.png') }}" alt="Tagline TAMASYA" class="corner-badge corner-badge--login">
        @csrf
        <h2>LOGIN</h2>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="input-group">
          <input type="text" name="identity" placeholder="Email, NIK, atau NIP" :value="old('identity')" required autofocus>
          <i class="fas fa-user"></i>
          <x-input-error :messages="$errors->get('identity')" class="mt-2" />
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
          <i class="fas fa-lock"></i>
          <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <button type="submit" class="login-button">LOGIN</button>
        <div class="bottom-links">
          <span>Belum Punya Akun? <a href="{{ route('register') }}">Daftar</a></span>
          <!-- @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">Lupa Password?</a>
          @endif -->
        </div>
      </form>
    </div>

    <div class="right-side">
      <div class="logo-container">
        {{-- Pastikan logo ada di public/images/logo-kemnaker.png --}}
        <img src="{{ asset('images/logo-kemnaker.png') }}" alt="Logo Kemnaker">
        <h1>TAMASYA</h1>
        <h3>KEMENTERIAN KETENAGAKERJAAN<br>REPUBLIK INDONESIA</h3>
      </div>
    </div>
  </div>

</body>
</html>
