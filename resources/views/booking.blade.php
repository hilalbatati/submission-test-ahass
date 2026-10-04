<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AHASS Service Booking — Bengkel Resmi Honda</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --honda-red: #E52421;
            --honda-red-hover: #c41c19;
            --honda-dark: #121417;
            --honda-slate: #1e2329;
            --honda-gray-bg: #f8fafc;
            --card-radius: 16px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--honda-gray-bg);
            color: #1f2937;
            min-height: 100vh;
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-badge {
            background: var(--honda-red);
            color: white;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.95rem;
            letter-spacing: 1px;
        }

        .hero-banner {
            background: linear-gradient(135deg, #121417 0%, #1e242c 60%, #2b1717 100%);
            border-bottom: 3px solid var(--honda-red);
            color: white;
            padding: 30px 0 24px;
        }

        .stat-chip {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #e2e8f0;
        }

        .card-custom {
            background: #ffffff;
            border-radius: var(--card-radius);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-custom-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-custom-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .title-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(229, 36, 33, 0.1);
            color: var(--honda-red);
        }

        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--honda-red);
            box-shadow: 0 0 0 3px rgba(229, 36, 33, 0.15);
        }

        .plate-input {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .btn-honda {
            background-color: var(--honda-red);
            border-color: var(--honda-red);
            color: #ffffff;
            font-weight: 700;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(229, 36, 33, 0.35);
        }

        .btn-honda:hover,
        .btn-honda:focus {
            background-color: var(--honda-red-hover);
            border-color: var(--honda-red-hover);
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(229, 36, 33, 0.45);
            transform: translateY(-1px);
        }

        .table-custom th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            font-weight: 700;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 14px;
        }

        .table-custom td {
            padding: 14px;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .filter-toolbar {
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
        }

        .letter-spacing-1 {
            letter-spacing: 1.5px;
        }

        .fs-7 {
            font-size: 0.8rem;
        }

        .alert-custom {
            border-radius: 12px;
            border-width: 1px;
            padding: 14px 18px;
        }

        /* Pulse indicator */
        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse 1.6s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <header class="hero-banner">
        <div class="container-fluid px-lg-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="navbar-brand-custom">
                    <div class="brand-badge">AHASS</div>
                    <div>
                        <h1 class="h4 fw-bold m-0 text-white tracking-wide">
                            Astra Honda Authorized Service Station
                        </h1>
                        <p class="small text-secondary mb-0">
                            Sistem Booking Pendaftaran Servis Resmi Honda (SPA Real-Time)
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="stat-chip">
                        <span class="pulse-dot"></span>
                        <span>Jam Operasional: <strong>08:00 – 17:00 WIB</strong></span>
                    </div>
                    <div class="stat-chip">
                        <i class="bi bi-shield-check text-warning"></i>
                        <span>Maks. <strong>3 Motor</strong> / Slot Jam</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="container-fluid px-lg-5 my-4">
        <div class="row g-4">

            <!-- LEFT COLUMN: Form Booking (5 cols on lg) -->
            <div class="col-lg-5 col-xl-4">
                <div class="card card-custom sticky-top" style="top: 24px; z-index: 10;">
                    <div class="card-custom-header">
                        <h2 class="card-custom-title">
                            <span class="title-icon">
                                <i class="bi bi-tools fs-5"></i>
                            </span>
                            Pendaftaran Servis
                        </h2>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            Formulir Baru
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Dynamic Alert Box -->
                        <div id="formAlert" class="alert alert-custom d-none mb-3" role="alert"></div>

                        <form id="bookingForm" autocomplete="off">

                            <!-- Input Plat Nomor -->
                            <div class="mb-3">
                                <label for="plate_number" class="form-label-custom">
                                    <i class="bi bi-card-text me-1 text-danger"></i> Plat Nomor Kendaraan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-credit-card-2-front"></i>
                                    </span>
                                    <input type="text" id="plate_number" name="plate_number"
                                        class="form-control form-control-custom plate-input border-start-0"
                                        placeholder="Contoh: B 1234 ABC" maxlength="20" required>
                                </div>
                                <div class="form-text small">Huruf kapital otomatis (plat nomor polisi).</div>
                            </div>

                            <!-- Input Nama Pelanggan -->
                            <div class="mb-3">
                                <label for="customer_name" class="form-label-custom">
                                    <i class="bi bi-person me-1 text-danger"></i> Nama Pemilik / Pelanggan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" id="customer_name" name="customer_name"
                                        class="form-control form-control-custom border-start-0"
                                        placeholder="Contoh: Budi Santoso" maxlength="100" required>
                                </div>
                            </div>

                            <!-- Input Tipe Motor -->
                            <div class="mb-3">
                                <label for="motorcycle_type" class="form-label-custom">
                                    <i class="bi bi-bicycle me-1 text-danger"></i> Tipe Motor Honda
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-tag-fill"></i>
                                    </span>
                                    <input type="text" id="motorcycle_type" name="motorcycle_type"
                                        class="form-control form-control-custom border-start-0"
                                        placeholder="Contoh: Vario 160, Beat, PCX 160, Scoopy" maxlength="100" required>
                                </div>
                            </div>

                            <!-- Input Tanggal Servis -->
                            <div class="mb-3">
                                <label for="service_date" class="form-label-custom">
                                    <i class="bi bi-calendar3 me-1 text-danger"></i> Tanggal Rencana Servis
                                </label>
                                <input type="date" id="service_date" name="service_date"
                                    class="form-control form-control-custom" value="{{ date('Y-m-d') }}"
                                    min="{{ date('Y-m-d') }}" required>
                                <div class="form-text small">Ketersediaan kuota slot jam disesuaikan dengan tanggal ini.
                                </div>
                            </div>

                            <!-- Select Jam Servis (Slot Kuota) -->
                            <div class="mb-3">
                                <label for="service_time" class="form-label-custom">
                                    <i class="bi bi-clock-history me-1 text-danger"></i> Slot Jam Servis (Maks. 3
                                    Motor/Jam)
                                </label>
                                <select id="service_time" name="service_time" class="form-select form-select-custom"
                                    required>
                                    <option value="">Memeriksa ketersediaan slot...</option>
                                </select>
                            </div>

                            <!-- Select Paket Servis -->
                            <div class="mb-4">
                                <label for="service_package_id" class="form-label-custom">
                                    <i class="bi bi-box-seam me-1 text-danger"></i> Pilihan Paket Servis
                                </label>
                                <select id="service_package_id" name="service_package_id"
                                    class="form-select form-select-custom" required>
                                    <option value="">Memuat paket servis...</option>
                                </select>
                            </div>

                            <!-- Tombol Submit -->
                            <button type="submit" id="submitBookingBtn"
                                class="btn btn-honda w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                                <i class="bi bi-check-circle-fill"></i>
                                <span class="btn-text">Daftarkan Booking Servis</span>
                            </button>

                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Tabel List Booking & Filter (7 cols on lg) -->
            <div class="col-lg-7 col-xl-8">
                <div class="card card-custom">
                    <div class="card-custom-header flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="title-icon">
                                <i class="bi bi-table fs-5"></i>
                            </span>
                            <div>
                                <h2 class="card-custom-title">
                                    Daftar Booking Servis Masuk
                                </h2>
                                <div class="small text-muted">Data diperbarui secara real-time tanpa reload halaman
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="badge bg-dark px-3 py-2 fs-7 rounded-pill">
                                Total: <strong id="bookingsCountBadge" class="text-warning">0</strong> Booking
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">

                        <!-- Toolbar Filter -->
                        <div class="filter-toolbar mb-4">
                            <div class="row g-2 align-items-center">
                                <!-- Filter Tanggal -->
                                <div class="col-md-4">
                                    <label class="form-label-custom mb-1">Filter Tanggal</label>
                                    <input type="date" id="filter_date"
                                        class="form-control form-control-sm form-control-custom">
                                </div>

                                <!-- Filter Slot Jam -->
                                <div class="col-md-3">
                                    <label class="form-label-custom mb-1">Filter Jam</label>
                                    <select id="filter_time" class="form-select form-select-sm form-select-custom">
                                        <option value="">Semua Jam</option>
                                        <option value="08:00">08:00 WIB</option>
                                        <option value="09:00">09:00 WIB</option>
                                        <option value="10:00">10:00 WIB</option>
                                        <option value="11:00">11:00 WIB</option>
                                        <option value="12:00">12:00 WIB</option>
                                        <option value="13:00">13:00 WIB</option>
                                        <option value="14:00">14:00 WIB</option>
                                        <option value="15:00">15:00 WIB</option>
                                        <option value="16:00">16:00 WIB</option>
                                        <option value="17:00">17:00 WIB</option>
                                    </select>
                                </div>

                                <!-- Filter Search Plat/Nama -->
                                <div class="col-md-5">
                                    <label class="form-label-custom mb-1">Cari Pelanggan / Plat</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white text-muted border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="filter_search"
                                            class="form-control form-control-custom border-start-0"
                                            placeholder="Ketik Plat nomor atau nama...">
                                    </div>
                                </div>

                                <!-- Quick buttons -->
                                <div class="col-12 mt-2 d-flex flex-wrap gap-2">
                                    <button type="button" id="btnFilterToday"
                                        class="btn btn-sm btn-outline-danger px-2.5 py-1">
                                        <i class="bi bi-calendar-check me-1"></i>Hari Ini
                                    </button>
                                    <button type="button" id="btnFilterAllDate"
                                        class="btn btn-sm btn-outline-secondary px-2.5 py-1">
                                        <i class="bi bi-calendar-range me-1"></i>Semua Tanggal
                                    </button>
                                    <button type="button" id="btnResetFilter"
                                        class="btn btn-sm btn-light border px-2.5 py-1 text-muted ms-auto">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-responsive" id="tableContainer">
                            <table class="table table-custom table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 40px;">No</th>
                                        <th>Plat Nomor</th>
                                        <th>Pelanggan & Motor</th>
                                        <th>Jadwal Servis</th>
                                        <th>Paket Servis</th>
                                        <th>Biaya</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="bookingsTableBody">
                                    <!-- Populated via jQuery AJAX -->
                                </tbody>
                            </table>

                            <!-- Loading Spinner -->
                            <div id="tableLoading" class="text-center py-5 d-none">
                                <div class="spinner-border text-danger" role="status">
                                    <span class="visually-hidden">Memuat data...</span>
                                </div>
                                <div class="small text-muted mt-2">Memperbarui data booking...</div>
                            </div>

                            <!-- Empty State -->
                            <div id="tableEmpty" class="text-center py-5 d-none">
                                <div class="mb-3">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Belum Ada Data Booking</h6>
                                <p class="text-muted small mb-0">
                                    Tidak ditemukan pendaftaran servis dengan filter yang dipilih. Silakan ubah filter
                                    atau lakukan pendaftaran baru.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-muted small border-top bg-white mt-5">
        <div class="container">
            <p class="mb-1 fw-semibold text-dark">
                &copy; {{ date('Y') }} AHASS Service Booking — Honda Genuine Care & Parts.
            </p>
            <p class="mb-0 text-secondary">
                Dibuat dengan Laravel 12 &bull; MySQL &bull; jQuery AJAX Real-Time SPA
            </p>
        </div>
    </footer>

    <!-- jQuery 3.7.1 CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- App Scripts: booking.js -->
    @vite(['resources/js/booking.js'])
</body>

</html>
