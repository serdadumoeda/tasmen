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

    .input-group input, .input-group select {
      width: 100%;
      padding: 12px 40px 12px 12px;
      font-size: 16px;
      border: none;
      border-bottom: 1px solid #aaa;
      outline: none;
      background-color: transparent;
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

    .register-button:disabled {
        background-color: #aaa;
        cursor: not-allowed;
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
          <input type="text" name="name" placeholder="Nama Lengkap" value="{{ old('name') }}" required autofocus>
          <i class="fas fa-user"></i>
          <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="input-group">
          <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
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

        <!-- Hierarchical Unit Selection -->
        <div class="input-group">
            <select id="eselon_i" class="unit-select" data-level="1" data-placeholder="Pilih Unit Eselon I*">
                <option value="">Pilih Unit Eselon I*</option>
                @foreach($eselonIUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
            <i class="fas fa-building"></i>
        </div>

        <div class="input-group">
            <select id="eselon_ii" class="unit-select" data-level="2" data-placeholder="Pilih Unit Eselon II*" disabled>
                <option value="">Pilih Unit Eselon I Dahulu</option>
            </select>
            <i class="fas fa-building"></i>
        </div>

        <div class="input-group">
            <select id="koordinator" class="unit-select" data-level="3" data-placeholder="Pilih Koordinator*" disabled>
                <option value="">Pilih Unit Eselon II Dahulu</option>
            </select>
            <i class="fas fa-sitemap"></i>
        </div>
        
        <div class="input-group">
            <select id="sub_koordinator" class="unit-select" data-level="4" data-placeholder="Pilih Sub Koordinator*" disabled>
                <option value="">Pilih Koordinator Dahulu</option>
            </select>
            <i class="fas fa-sitemap"></i>
        </div>

        <!-- Jabatan Selection -->
        <div class="input-group">
            <select name="jabatan_id" id="jabatan_id" required disabled>
                <option value="">Pilih Jabatan Tersedia*</option>
            </select>
            <i class="fas fa-id-badge"></i>
            <x-input-error :messages="$errors->get('jabatan_id')" class="mt-2" />
        </div>

        <!-- Hidden input to store the final selected unit_id -->
        <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', '') }}">

        <button type="submit" id="register_button" class="register-button">DAFTAR</button>
        
        <div class="bottom-links">
          <a href="{{ route('login') }}">Sudah Punya Akun? Login</a>
        </div>
      </form>
    </div>
  </div>

<!-- jQuery from CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const jabatanSelect = $('#jabatan_id');
    const unitIdInput = $('#unit_id');
    const unitSelects = $('.unit-select');
    const registerButton = $('#register_button');

    // Path for pre-selection on form validation failure
    const selectedUnitPath = @json($selectedUnitPath ?? []);
    const oldJabatanId = '{{ old('jabatan_id', '') }}';

    function checkFormValidity() {
        // Simple check if a jabatan is selected.
        const isJabatanSelected = jabatanSelect.val() !== '';
        registerButton.prop('disabled', !isJabatanSelected);
    }

    function fetchAndPopulateJabatans(unitId, selectedId = null) {
        jabatanSelect.prop('disabled', true).html('<option value="">Memuat Jabatan...</option>');
        checkFormValidity();

        if (!unitId) {
            jabatanSelect.html('<option value="">Pilih Unit Kerja Terakhir*</option>');
            checkFormValidity();
            return;
        }

        $.ajax({
            url: `/api/units/${unitId}/vacant-jabatans`,
            type: 'GET',
            success: function(data) {
                jabatanSelect.empty().append('<option value="">Pilih Jabatan Tersedia*</option>');
                if (data.length > 0) {
                    $.each(data, function(key, jabatan) {
                        jabatanSelect.append(new Option(jabatan.name, jabatan.id));
                    });
                    jabatanSelect.prop('disabled', false);
                } else {
                    jabatanSelect.html('<option value="">Tidak ada jabatan kosong</option>');
                }
                if (selectedId) {
                    jabatanSelect.val(selectedId).trigger('change');
                }
                checkFormValidity();
            },
            error: function() {
                jabatanSelect.html('<option value="">Gagal Memuat Jabatan</option>');
                checkFormValidity();
            }
        });
    }

    function resetSubsequentSelects(level) {
        for (let i = level; i < unitSelects.length; i++) {
            const select = $(unitSelects[i]);
            const placeholder = select.data('placeholder');
            select.empty().append(new Option(placeholder, '')).prop('disabled', true);
        }
        jabatanSelect.empty().append('<option value="">Pilih Unit Kerja Terakhir*</option>').prop('disabled', true);
        checkFormValidity();
    }

    unitSelects.on('change', function() {
        const selectedValue = $(this).val();
        const currentLevel = parseInt($(this).data('level'), 10);

        unitIdInput.val(selectedValue);
        resetSubsequentSelects(currentLevel);

        if (!selectedValue) {
            if (currentLevel > 1) {
                const prevSelect = $(unitSelects[currentLevel - 2]);
                unitIdInput.val(prevSelect.val());
            } else {
                unitIdInput.val('');
            }
            fetchAndPopulateJabatans(unitIdInput.val());
            return;
        }

        fetchAndPopulateJabatans(selectedValue);

        const nextLevel = currentLevel + 1;
        const nextSelect = $(`.unit-select[data-level='${nextLevel}']`);

        if (nextSelect.length) {
            nextSelect.prop('disabled', true).html('<option value="">Memuat...</option>');
            $.ajax({
                url: `/api/units/${selectedValue}/children`,
                type: 'GET',
                success: function(data) {
                    const placeholder = nextSelect.data('placeholder');
                    nextSelect.empty().append(new Option(placeholder, ''));
                    if (data.length > 0) {
                        $.each(data, function(key, unit) {
                            nextSelect.append(new Option(unit.name, unit.id));
                        });
                        nextSelect.prop('disabled', false);
                    } else {
                        nextSelect.html(new Option('Tidak ada unit bawahan', '')).prop('disabled', true);
                    }
                },
                error: function() {
                    nextSelect.html(new Option('Gagal memuat data', '')).prop('disabled', true);
                }
            });
        }
    });

    jabatanSelect.on('change', function() {
        checkFormValidity();
    });

    function initializePath() {
        // On validation failure, re-select the Eselon I unit
        const oldEselonI = '{{ old('eselon_i') }}'; // Assuming we name the first select 'eselon_i'
        if(oldEselonI) {
            $('#eselon_i').val(oldEselonI).trigger('change');
            // A more robust solution would re-trigger the whole chain based on old inputs,
            // but that adds significant complexity. This is a good starting point.
        }
        checkFormValidity();
    }

    // Initial check
    initializePath();
});
</script>

</body>
</html>