<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrasi - Kemenaker</title>
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

    /* Bagian Kiri (Sisi Branding Hijau) */
    .left-side {
      flex: 1;
      background-color: #00796B;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      /* Kemiringan dibalik dari kanan ke kiri */
      clip-path: polygon(0 0, 75% 0, 100% 100%, 0% 100%);
      z-index: 0;
    }

    .logo-container {
      text-align: center;
      padding: 40px;
    }

    .logo-container img {
      width: 120px;
      margin-bottom: 25px;
    }

    .logo-container h1 {
      font-size: 48px;
      font-weight: 700;
      margin: 0 0 10px 0;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .logo-container h3 {
      font-weight: 400;
      font-size: 16px;
      line-height: 1.5;
      margin: 0;
    }

    /* Bagian Kanan (Sisi Form Putih) */
    .right-side {
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

    .register-button {
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
      text-align: center;
      font-size: 14px;
      margin-top: 20px;
    }

    .bottom-links a {
      color: blue;
      text-decoration: none;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .left-side {
        clip-path: none; /* Hilangkan kemiringan di mobile */
        width: 100%;
        padding: 30px 20px;
        order: -1; /* Pindahkan ke atas */
      }
      
      .right-side {
          padding-top: 40px;
      }

      .logo-container img {
        width: 80px;
      }
      
      .logo-container h1 {
        font-size: 36px;
      }

      .logo-container h3 {
        font-size: 14px;
      }

      .form-box h2 {
        font-size: 28px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="left-side">
      <div class="logo-container">
        <img src="{{ asset('images/logo-kemnaker.png') }}" alt="Logo Kemnaker">
        <h1>Tugas Kita</h1>
        <h3>KEMENTERIAN KETENAGAKERJAAN<br>REPUBLIK INDONESIA</h3>
      </div>
    </div>

    <div class="right-side">
      <form class="form-box" method="POST" action="{{ route('register') }}">
        @csrf
        <h2>DAFTAR AKUN</h2>

        <div class="input-group">
          <input type="text" name="name" placeholder="Nama Lengkap" :value="old('name')" required autofocus>
          <i class="fas fa-user"></i>
          <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="input-group">
          <input type="email" name="email" placeholder="Email" :value="old('email')" required>
          <i class="fas fa-envelope"></i>
          <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="input-group">
          <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
          <i class="fas fa-lock"></i>
          <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="input-group">
          <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required>
          <i class="fas fa-lock"></i>
          <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        
        <button type="submit" class="register-button">DAFTAR</button>
        
        <div class="bottom-links">
          <a href="{{ route('login') }}">Sudah Punya Akun? Login</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>