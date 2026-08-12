<?php
// ==========================================
// index.php (Smart Home Dashboard - Multi Lamp Timers)
// ==========================================
require 'db_connect.php';

// Ambil data terbaru dari database
$result = $conn->query("SELECT * FROM control_center WHERE id=1");
$data = $result->fetch_assoc();

// Variabel perangkat dasar
$lampu_depan    = $data['lampu_depan'];
$lampu_tidur    = $data['lampu_tidur'];
$lampu_tengah   = $data['lampu_tengah'];
$lampu_belakang = $data['lampu_belakang'];

// Variabel Automasi 4 Lampu (Pastikan kolom ini ada di database)
$lampu_depan_auto   = isset($data['lampu_depan_auto']) ? $data['lampu_depan_auto'] : 0;
$lampu_depan_on     = isset($data['lampu_depan_on']) ? $data['lampu_depan_on'] : '18:00';
$lampu_depan_off    = isset($data['lampu_depan_off']) ? $data['lampu_depan_off'] : '05:00';

$lampu_tidur_auto   = isset($data['lampu_tidur_auto']) ? $data['lampu_tidur_auto'] : 0;
$lampu_tidur_on     = isset($data['lampu_tidur_on']) ? $data['lampu_tidur_on'] : '21:00';
$lampu_tidur_off    = isset($data['lampu_tidur_off']) ? $data['lampu_tidur_off'] : '06:00';

$lampu_tengah_auto  = isset($data['lampu_tengah_auto']) ? $data['lampu_tengah_auto'] : 0;
$lampu_tengah_on    = isset($data['lampu_tengah_on']) ? $data['lampu_tengah_on'] : '18:00';
$lampu_tengah_off   = isset($data['lampu_tengah_off']) ? $data['lampu_tengah_off'] : '23:00';

$lampu_belakang_auto = isset($data['lampu_belakang_auto']) ? $data['lampu_belakang_auto'] : 0;
$lampu_belakang_on  = isset($data['lampu_belakang_on']) ? $data['lampu_belakang_on'] : '18:00';
$lampu_belakang_off = isset($data['lampu_belakang_off']) ? $data['lampu_belakang_off'] : '05:00';

$kipas_auto     = $data['kipas_auto'];
$kipas_interval = $data['kipas_interval'];
$kipas_angin    = $data['kipas_angin'];
$bell           = $data['bell'];
$servo          = $data['servo'];

$suhu           = $data['suhu'];
$kelembaban     = $data['kelembaban'];
$status_pintu   = $data['status_pintu'];
$status_gas     = $data['status_gas'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home Dashboard</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #3b82f6;
            --primary-light: #eff6ff;
            --success: #10b981;
            --success-light: #ecfdf5;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); padding-bottom: 60px; }

        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 18px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        .navbar-content { display: flex; align-items: center; justify-content: space-between; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 42px; height: 42px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .brand-text h1 { font-size: 1.25rem; font-weight: 700; color: var(--text-main); line-height: 1.2; }
        .brand-text p { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

        .container { max-width: 1140px; margin: 0 auto; padding: 24px 20px 0; }

        .emergency-banner {
            background-color: var(--danger-light);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 28px;
            font-weight: 600;
            font-size: 0.95rem;
            display: none;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow-sm);
        }

        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .section-title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 36px; }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: all 0.25s ease;
        }
        .card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .metric-card { display: flex; flex-direction: column; justify-content: space-between; }
        .metric-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .metric-icon-box { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .metric-title { font-size: 0.875rem; font-weight: 600; color: var(--text-muted); }
        .metric-value { font-size: 2.1rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; }
        .metric-unit { font-size: 1rem; font-weight: 600; color: var(--text-muted); margin-left: 2px; }

        .badge { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; }
        .badge-success { background: var(--success-light); color: var(--success); }
        .badge-danger { background: var(--danger-light); color: var(--danger); }

        .device-card { display: flex; flex-direction: column; justify-content: space-between; min-height: 150px; }
        .device-card.active { border-color: rgba(59, 130, 246, 0.3); background: linear-gradient(180deg, rgba(239, 246, 255, 0.3) 0%, rgba(255, 255, 255, 1) 100%); }
        .device-top { display: flex; align-items: flex-start; justify-content: space-between; }
        .device-icon-box { width: 44px; height: 44px; border-radius: 12px; background: var(--bg-body); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; transition: all 0.3s ease; }
        .device-card.active .device-icon-box { background: var(--primary); color: var(--surface); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        .device-info h3 { font-size: 1rem; font-weight: 700; margin-top: 12px; color: var(--text-main); }
        .device-status { font-size: 0.8rem; font-weight: 500; color: var(--text-muted); margin-top: 2px; }

        .switch-btn { position: relative; display: inline-block; width: 48px; height: 26px; }
        .switch-btn input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 30px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(22px); }

        /* Form Automasi Multi-Lampu */
        .auto-card { grid-column: 1 / -1; }
        .auto-section-title { font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        
        .lamp-schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .lamp-schedule-item {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 15px;
        }

        .lamp-schedule-item h4 {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-main);
        }

        .form-row { display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-between; }
        .custom-checkbox { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; }
        .custom-checkbox input { width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; }
        
        .time-input-group {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .time-box {
            flex: 1;
        }

        .time-box label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            display: block;
            margin-bottom: 3px;
        }

        select, input[type="time"] { 
            width: 100%; padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border); outline: none; background-color: var(--surface); font-size: 0.85rem; font-weight: 600; color: var(--text-main); 
        }
        input[type="time"] { font-family: monospace; }
        
        .btn-submit { background-color: var(--primary); color: white; padding: 12px 20px; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 15px; }
        .btn-submit:hover { background-color: #2563eb; }
        
        .divider { border: 0; border-top: 1px solid var(--border); margin: 20px 0; }
        .locked-ui { pointer-events: none; opacity: 0.45; filter: grayscale(80%); }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes ring { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(15deg); } 75% { transform: rotate(-15deg); } }
        .spinning { animation: spin 2s linear infinite; }
        .ringing { animation: ring 0.5s ease-in-out infinite; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container navbar-content">
            <div class="brand">
                <div class="brand-icon"><i class="fas fa-microchip"></i></div>
                <div class="brand-text">
                    <h1>Smart Home</h1>
                    <p>Dashboard Kontrol & Monitoring</p>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <div class="emergency-banner" id="emergency-banner">
            <i class="fas fa-triangle-exclamation fa-xl"></i> 
            <div>
                <strong>STATUS DARURAT TERDETEKSI!</strong>
                <p style="font-size: 0.85rem; font-weight: 400; margin-top: 2px;">Sensor mendeteksi keberadaan asap/gas berbahaya. Akses kontrol dinonaktifkan otomatis.</p>
            </div>
        </div>

        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-chart-line" style="color: var(--primary);"></i> Monitoring Sensor</h2>
        </div>
        
        <div class="grid">
            <div class="card metric-card">
                <div class="metric-header">
                    <span class="metric-title">Suhu Ruangan</span>
                    <div class="metric-icon-box" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-temperature-high"></i></div>
                </div>
                <div class="metric-value" id="suhu"><?= $suhu; ?><span class="metric-unit">°C</span></div>
            </div>

            <div class="card metric-card">
                <div class="metric-header">
                    <span class="metric-title">Kelembaban</span>
                    <div class="metric-icon-box" style="background: #e0f2fe; color: #0284c7;"><i class="fas fa-droplet"></i></div>
                </div>
                <div class="metric-value" id="kelembaban"><?= $kelembaban; ?><span class="metric-unit">%</span></div>
            </div>

            <div class="card metric-card">
                <div class="metric-header">
                    <span class="metric-title">Status Pintu</span>
                    <div class="metric-icon-box" style="background: #f1f5f9; color: var(--text-main);"><i class="fas fa-door-closed"></i></div>
                </div>
                <div id="status_pintu"><span class="badge badge-success">Memuat...</span></div>
            </div>

            <div class="card metric-card">
                <div class="metric-header">
                    <span class="metric-title">Sensor Gas/Asap</span>
                    <div class="metric-icon-box" style="background: #f3e8ff; color: #9333ea;"><i class="fas fa-smog"></i></div>
                </div>
                <div id="status_gas"><span class="badge badge-success">Memuat...</span></div>
            </div>
        </div>

        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-sliders" style="color: var(--primary);"></i> Kontrol Perangkat</h2>
        </div>

        <div class="grid" id="control-section">
            
            <div class="card device-card <?= $lampu_depan ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-lightbulb"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('lampu_depan', this.checked)" <?= $lampu_depan ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Lampu Depan</h3>
                    <p class="device-status"><?= $lampu_depan ? 'Menyala' : 'Mati'; ?></p>
                </div>
            </div>

            <div class="card device-card <?= $lampu_tidur ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-bed"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('lampu_tidur', this.checked)" <?= $lampu_tidur ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Lampu Tidur</h3>
                    <p class="device-status"><?= $lampu_tidur ? 'Menyala' : 'Mati'; ?></p>
                </div>
            </div>

            <div class="card device-card <?= $lampu_tengah ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-couch"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('lampu_tengah', this.checked)" <?= $lampu_tengah ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Lampu Tengah</h3>
                    <p class="device-status"><?= $lampu_tengah ? 'Menyala' : 'Mati'; ?></p>
                </div>
            </div>

            <div class="card device-card <?= $lampu_belakang ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-house-chimney"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('lampu_belakang', this.checked)" <?= $lampu_belakang ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Lampu Belakang</h3>
                    <p class="device-status"><?= $lampu_belakang ? 'Menyala' : 'Mati'; ?></p>
                </div>
            </div>

            <div class="card device-card <?= $kipas_angin ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-fan <?= $kipas_angin ? 'spinning' : ''; ?>"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('kipas_angin', this.checked)" <?= $kipas_angin ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Kipas Angin</h3>
                    <p class="device-status"><?= $kipas_angin ? 'Berputar' : 'Mati'; ?></p>
                </div>
            </div>

            <div class="card device-card <?= $bell ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-bell <?= $bell ? 'ringing' : ''; ?>"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('bell', this.checked)" <?= $bell ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Buzzer Alarm</h3>
                    <p class="device-status"><?= $bell ? 'Berbunyi' : 'Mati'; ?></p>
                </div>
            </div>

            <div class="card device-card <?= $servo ? 'active' : ''; ?>">
                <div class="device-top">
                    <div class="device-icon-box"><i class="fas fa-lock"></i></div>
                    <label class="switch-btn">
                        <input type="checkbox" onchange="toggleDevice('servo', this.checked)" <?= $servo ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="device-info">
                    <h3>Servo Override</h3>
                    <p class="device-status"><?= $servo ? 'OFF Manual' : 'Aktif Stby'; ?></p>
                </div>
            </div>

        </div>

        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-robot" style="color: var(--primary);"></i> Sistem Automasi</h2>
        </div>

        <div class="grid" id="automation-section">
            <div class="card auto-card">
                <form action="update_auto.php" method="POST">
                    
                    <!-- PENGATURAN JADWAL 4 LAMPU -->
                    <div class="auto-section-title"><i class="far fa-lightbulb"></i> Timer Jadwal 4 Lampu Terpisah</div>
                    
                    <div class="lamp-schedule-grid">
                        
                        <!-- Lampu Depan -->
                        <div class="lamp-schedule-item">
                            <h4><i class="fas fa-lightbulb" style="color:var(--primary);"></i> Lampu Depan</h4>
                            <label class="custom-checkbox">
                                <input type="checkbox" name="lampu_depan_auto" value="1" <?= $lampu_depan_auto ? "checked" : "" ?>>
                                <span>Aktifkan Otomatis</span>
                            </label>
                            <div class="time-input-group">
                                <div class="time-box"><label>Nyala</label><input type="time" name="lampu_depan_on" value="<?= $lampu_depan_on ?>"></div>
                                <div class="time-box"><label>Mati</label><input type="time" name="lampu_depan_off" value="<?= $lampu_depan_off ?>"></div>
                            </div>
                        </div>

                        <!-- Lampu Tidur -->
                        <div class="lamp-schedule-item">
                            <h4><i class="fas fa-bed" style="color:var(--primary);"></i> Lampu Tidur</h4>
                            <label class="custom-checkbox">
                                <input type="checkbox" name="lampu_tidur_auto" value="1" <?= $lampu_tidur_auto ? "checked" : "" ?>>
                                <span>Aktifkan Otomatis</span>
                            </label>
                            <div class="time-input-group">
                                <div class="time-box"><label>Nyala</label><input type="time" name="lampu_tidur_on" value="<?= $lampu_tidur_on ?>"></div>
                                <div class="time-box"><label>Mati</label><input type="time" name="lampu_tidur_off" value="<?= $lampu_tidur_off ?>"></div>
                            </div>
                        </div>

                        <!-- Lampu Tengah -->
                        <div class="lamp-schedule-item">
                            <h4><i class="fas fa-couch" style="color:var(--primary);"></i> Lampu Tengah</h4>
                            <label class="custom-checkbox">
                                <input type="checkbox" name="lampu_tengah_auto" value="1" <?= $lampu_tengah_auto ? "checked" : "" ?>>
                                <span>Aktifkan Otomatis</span>
                            </label>
                            <div class="time-input-group">
                                <div class="time-box"><label>Nyala</label><input type="time" name="lampu_tengah_on" value="<?= $lampu_tengah_on ?>"></div>
                                <div class="time-box"><label>Mati</label><input type="time" name="lampu_tengah_off" value="<?= $lampu_tengah_off ?>"></div>
                            </div>
                        </div>

                        <!-- Lampu Belakang -->
                        <div class="lamp-schedule-item">
                            <h4><i class="fas fa-house-chimney" style="color:var(--primary);"></i> Lampu Belakang</h4>
                            <label class="custom-checkbox">
                                <input type="checkbox" name="lampu_belakang_auto" value="1" <?= $lampu_belakang_auto ? "checked" : "" ?>>
                                <span>Aktifkan Otomatis</span>
                            </label>
                            <div class="time-input-group">
                                <div class="time-box"><label>Nyala</label><input type="time" name="lampu_belakang_on" value="<?= $lampu_belakang_on ?>"></div>
                                <div class="time-box"><label>Mati</label><input type="time" name="lampu_belakang_off" value="<?= $lampu_belakang_off ?>"></div>
                            </div>
                        </div>

                    </div>

                    <hr class="divider">

                    <!-- PENGATURAN KIPAS OTOMATIS -->
                    <div class="auto-section-title"><i class="fas fa-fan"></i> Kipas Otomatis (Berdasarkan Suhu)</div>
                    <div class="form-row">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="kipas_auto" value="1" <?= $kipas_auto ? "checked" : "" ?>>
                            <span>Aktifkan Mode Kipas Otomatis</span>
                        </label>

                        <div style="flex-grow: 1; max-width: 300px;">
                            <select name="kipas_interval">
                                <?php
                                for($i=1; $i<=5; $i++) {
                                    $selected = ($kipas_interval == $i) ? "selected" : "";
                                    echo "<option value='$i' $selected>⏳ Interval $i Menit</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-floppy-disk"></i> Simpan Semua Pengaturan
                    </button>

                </form>
            </div>
        </div>

    </div>

    <script>
    function toggleDevice(device, state) {
        let val = state ? 1 : 0;
        fetch(`update.php?device=${device}&state=${val}`)
        .then(response => {
            if(!response.ok) console.error("Gagal memperbarui status perangkat.");
        })
        .catch(err => console.error(err));
    }

    function updateDashboard(){
        fetch("get_data.php")
        .then(res => res.json())
        .then(data => {
            document.getElementById("suhu").innerHTML = data.suhu + '<span class="metric-unit">°C</span>';
            document.getElementById("kelembaban").innerHTML = data.kelembaban + '<span class="metric-unit">%</span>';

            let pintu = document.getElementById("status_pintu");
            if(data.status_pintu === "TERBUKA" || data.status_pintu == 1) {
                pintu.innerHTML = '<span class="badge badge-danger"><i class="fas fa-door-open"></i> Terbuka</span>';
            } else {
                pintu.innerHTML = '<span class="badge badge-success"><i class="fas fa-door-closed"></i> Tertutup</span>';
            }

            let gas = document.getElementById("status_gas");
            let banner = document.getElementById("emergency-banner");
            let controls = document.getElementById("control-section");
            let automations = document.getElementById("automation-section");

            if(data.status_gas === "BAHAYA" || data.status_gas == 1) {
                gas.innerHTML = '<span class="badge badge-danger"><i class="fas fa-triangle-exclamation"></i> Bahaya Gas!</span>';
                banner.style.display = "flex";
                controls.classList.add("locked-ui");
                automations.classList.add("locked-ui");
            } else {
                gas.innerHTML = '<span class="badge badge-success"><i class="fas fa-shield-halved"></i> Kondisi Aman</span>';
                banner.style.display = "none";
                controls.classList.remove("locked-ui");
                automations.classList.remove("locked-ui");
            }
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    updateDashboard();
    setInterval(updateDashboard, 3000); 
    </script>

</body>
</html>