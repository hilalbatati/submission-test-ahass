/**
 * AHASS Service Booking SPA - jQuery AJAX Controller
 */

$(function () {
    'use strict';

    // Global CSRF Token Setup for all jQuery AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        }
    });

    // Cache DOM Elements
    const $bookingForm = $('#bookingForm');
    const $submitBtn = $('#submitBookingBtn');
    const $btnText = $submitBtn.find('.btn-text');
    const $btnSpinner = $submitBtn.find('.spinner-border');

    const $serviceDateInput = $('#service_date');
    const $serviceTimeSelect = $('#service_time');
    const $servicePackageSelect = $('#service_package_id');
    const $plateNumberInput = $('#plate_number');

    // Filter elements
    const $filterDate = $('#filter_date');
    const $filterTime = $('#filter_time');
    const $filterSearch = $('#filter_search');
    const $btnResetFilter = $('#btnResetFilter');
    const $btnFilterToday = $('#btnFilterToday');
    const $btnFilterAllDate = $('#btnFilterAllDate');

    // Table elements
    const $bookingsTableBody = $('#bookingsTableBody');
    const $bookingsCountBadge = $('#bookingsCountBadge');
    const $tableLoading = $('#tableLoading');
    const $tableEmpty = $('#tableEmpty');
    const $tableContainer = $('#tableContainer');

    // Form Alert Box
    const $formAlert = $('#formAlert');

    let searchTimeout = null;

    /**
     * Format number to Indonesian Rupiah currency format.
     */
    function formatRupiah(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    /**
     * Format date string (YYYY-MM-DD) to friendly Indonesian format.
     */
    function formatFriendlyDate(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        const day = parseInt(parts[2], 10);
        const monthIndex = parseInt(parts[1], 10) - 1;
        const year = parts[0];
        return `${day} ${months[monthIndex]} ${year}`;
    }

    /**
     * Show notification alert in form.
     */
    function showAlert(type, title, message) {
        $formAlert
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass(`alert-${type}`)
            .html(`
                <div class="d-flex align-items-start gap-2">
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} fs-5 flex-shrink-0"></i>
                    <div>
                        <strong>${title}</strong>
                        <div class="small mt-1">${message}</div>
                    </div>
                </div>
            `);

        // Auto-scroll to alert if needed
        $formAlert[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        $formAlert.addClass('d-none').empty();
    }

    /**
     * Load service packages from backend.
     */
    function loadPackages() {
        $servicePackageSelect.html('<option value="">Memuat paket servis...</option>');

        $.ajax({
            url: '/packages',
            method: 'GET',
            success: function (res) {
                if (res.success && Array.isArray(res.data)) {
                    let html = '<option value="">-- Pilih Paket Servis --</option>';
                    res.data.forEach(pkg => {
                        html += `<option value="${pkg.id}" data-price="${pkg.price}">
                            ${pkg.name} — ${formatRupiah(pkg.price)}
                        </option>`;
                    });
                    $servicePackageSelect.html(html);
                }
            },
            error: function () {
                $servicePackageSelect.html('<option value="">Gagal memuat paket servis</option>');
            }
        });
    }

    /**
     * Load available slots for selected date.
     */
    function loadSlots(date) {
        if (!date) return;

        $serviceTimeSelect.prop('disabled', true).html('<option value="">Memeriksa ketersediaan slot...</option>');

        $.ajax({
            url: '/slots',
            method: 'GET',
            data: { date: date },
            success: function (res) {
                if (res.success && Array.isArray(res.data)) {
                    let html = '<option value="">-- Pilih Jam Servis (08:00 - 17:00) --</option>';
                    let anyAvailable = false;

                    res.data.forEach(slot => {
                        if (slot.is_full) {
                            html += `<option value="${slot.time}" disabled class="text-danger fw-semibold">
                                🚫 ${slot.time} WIB (PENUH - 3/3 motor)
                            </option>`;
                        } else {
                            anyAvailable = true;
                            const badgeText = slot.remaining === 1 
                                ? '⚠️ Sisa 1 slot terakhir!' 
                                : `✅ Tersedia (Sisa ${slot.remaining} slot)`;
                            html += `<option value="${slot.time}">
                                ⏱️ ${slot.time} WIB — ${badgeText}
                            </option>`;
                        }
                    });

                    $serviceTimeSelect.html(html).prop('disabled', false);

                    if (!anyAvailable) {
                        showAlert('warning', 'Hari Penuh', `Semua slot pada tanggal ${formatFriendlyDate(date)} sudah penuh. Silakan pilih tanggal lain.`);
                    }
                }
            },
            error: function () {
                $serviceTimeSelect.html('<option value="">Gagal memuat slot jam</option>').prop('disabled', false);
            }
        });
    }

    /**
     * Load bookings list with active filters.
     */
    function loadBookings() {
        $tableLoading.removeClass('d-none');
        $tableEmpty.addClass('d-none');

        const params = {
            date: $filterDate.val(),
            time: $filterTime.val(),
            search: $filterSearch.val().trim(),
        };

        $.ajax({
            url: '/bookings',
            method: 'GET',
            data: params,
            success: function (res) {
                $tableLoading.addClass('d-none');

                if (res.success && Array.isArray(res.data)) {
                    $bookingsCountBadge.text(res.count);

                    if (res.data.length === 0) {
                        $bookingsTableBody.empty();
                        $tableEmpty.removeClass('d-none');
                        return;
                    }

                    let rows = '';
                    res.data.forEach((b, idx) => {
                        const pkgName = b.service_package ? b.service_package.name : '-';
                        const pkgPrice = b.service_package ? formatRupiah(b.service_package.price) : '-';

                        rows += `
                            <tr class="align-middle border-bottom transition-all">
                                <td class="text-muted small text-center fw-medium">${idx + 1}</td>
                                <td>
                                    <div class="d-inline-flex align-items-center gap-1 px-2.5 py-1 bg-dark text-white rounded font-monospace fw-bold fs-7 letter-spacing-1">
                                        <i class="bi bi-card-text text-danger me-1"></i>${escapeHtml(b.plate_number)}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">${escapeHtml(b.customer_name)}</div>
                                    <div class="small text-muted"><i class="bi bi-bicycle me-1"></i>${escapeHtml(b.motorcycle_type)}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><i class="bi bi-calendar-event me-1 text-danger"></i>${formatFriendlyDate(b.service_date)}</div>
                                    <div>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-clock me-1"></i>${b.service_time} WIB
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">${escapeHtml(pkgName)}</div>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">${pkgPrice}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill">
                                        <i class="bi bi-check2-circle me-1"></i>Tercatat
                                    </span>
                                </td>
                            </tr>
                        `;
                    });

                    $bookingsTableBody.html(rows);
                }
            },
            error: function () {
                $tableLoading.addClass('d-none');
                $bookingsTableBody.html(`
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-octagon fs-4 d-block mb-1"></i>
                            Gagal memuat daftar booking. Silakan coba lagi.
                        </td>
                    </tr>
                `);
            }
        });
    }

    /**
     * Escape HTML string to prevent XSS.
     */
    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }

    /**
     * Clear field validation highlights.
     */
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    /**
     * Show validation errors under inputs.
     */
    function displayValidationErrors(errors) {
        clearValidationErrors();
        for (const [field, messages] of Object.entries(errors)) {
            const $field = $(`#${field}`);
            if ($field.length) {
                $field.addClass('is-invalid');
                $field.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
            }
        }
    }

    // Auto uppercase plate number
    $plateNumberInput.on('input', function () {
        this.value = this.value.toUpperCase();
    });

    // Date change on form: reload slots
    $serviceDateInput.on('change', function () {
        hideAlert();
        loadSlots($(this).val());
    });

    // Filter event handlers
    $filterDate.on('change', function () {
        loadBookings();
    });

    $filterTime.on('change', function () {
        loadBookings();
    });

    $filterSearch.on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            loadBookings();
        }, 300);
    });

    $btnResetFilter.on('click', function () {
        $filterDate.val('');
        $filterTime.val('');
        $filterSearch.val('');
        loadBookings();
    });

    $btnFilterToday.on('click', function () {
        const today = new Date().toISOString().split('T')[0];
        $filterDate.val(today);
        loadBookings();
    });

    $btnFilterAllDate.on('click', function () {
        $filterDate.val('');
        loadBookings();
    });

    // Handle Form Submit
    $bookingForm.on('submit', function (e) {
        e.preventDefault();
        hideAlert();
        clearValidationErrors();

        // UI Loading state
        $submitBtn.prop('disabled', true);
        $btnText.text('Mendaftarkan...');
        $btnSpinner.removeClass('d-none');

        const formData = {
            plate_number: $plateNumberInput.val().trim(),
            customer_name: $('#customer_name').val().trim(),
            motorcycle_type: $('#motorcycle_type').val().trim(),
            service_date: $serviceDateInput.val(),
            service_time: $serviceTimeSelect.val(),
            service_package_id: $servicePackageSelect.val(),
        };

        $.ajax({
            url: '/bookings',
            method: 'POST',
            data: formData,
            success: function (res) {
                showAlert('success', 'Pendaftaran Berhasil! 🎉', res.message || 'Data servis berhasil dicatat.');

                // Reset inputs except date
                $plateNumberInput.val('');
                $('#customer_name').val('');
                $('#motorcycle_type').val('');
                $servicePackageSelect.val('');
                
                // Refresh slots for current date & refresh table
                loadSlots($serviceDateInput.val());
                loadBookings();
            },
            error: function (xhr) {
                const res = xhr.responseJSON;
                if (xhr.status === 422 && res) {
                    if (res.errors) {
                        displayValidationErrors(res.errors);
                    }
                    showAlert('danger', 'Gagal Mendaftar', res.message || 'Terjadi kesalahan pada data yang dimasukkan.');
                    
                    // If error was about slot full, refresh slot options immediately
                    if (res.errors && res.errors.service_time) {
                        loadSlots($serviceDateInput.val());
                    }
                } else {
                    showAlert('danger', 'Kesalahan Sistem', 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.');
                }
            },
            complete: function () {
                $submitBtn.prop('disabled', false);
                $btnText.text('Daftarkan Booking Servis');
                $btnSpinner.addClass('d-none');
            }
        });
    });

    // Initial Load
    loadPackages();
    loadSlots($serviceDateInput.val());
    loadBookings();
});
