
<?php
require_once 'config/session.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan - Perpustakaan SMK YMIK Jakarta</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="modern-body">
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-sidebar">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <main class="dashboard-main">
            <!-- Page Header -->
            <div class="welcome-header">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h1 class="welcome-title">
                            <i class="fas fa-question-circle me-3"></i>
                            Pusat Bantuan
                        </h1>
                        <p class="welcome-subtitle">
                            Panduan lengkap penggunaan Sistem Informasi Perpustakaan SMK YMIK Jakarta
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-book me-2"></i>
                                Panduan Penggunaan
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-tools me-2"></i>
                                Tips & Trik
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-question me-2"></i>
                                FAQ
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <button class="btn btn-primary-custom" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            Cetak Panduan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="content-grid mb-4">
                <div class="quick-actions-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-compass me-2"></i>
                            Navigasi Cepat
                        </h3>
                        <p class="section-subtitle">Langsung ke topik yang Anda cari</p>
                    </div>
                    
                    <div class="quick-actions-grid">
                        <a href="#pengenalan" class="quick-action-card action-primary">
                            <div class="action-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="action-content">
                                <h4>Pengenalan Sistem</h4>
                                <p>Memahami fitur utama</p>
                            </div>
                        </a>

                        <a href="#manajemen-buku" class="quick-action-card action-success">
                            <div class="action-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="action-content">
                                <h4>Manajemen Buku</h4>
                                <p>Kelola koleksi perpustakaan</p>
                            </div>
                        </a>

                        <a href="#peminjaman" class="quick-action-card action-info">
                            <div class="action-icon">
                                <i class="fas fa-hand-holding"></i>
                            </div>
                            <div class="action-content">
                                <h4>Peminjaman & Pengembalian</h4>
                                <p>Proses transaksi buku</p>
                            </div>
                        </a>

                        <a href="#laporan" class="quick-action-card action-warning">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-content">
                                <h4>Laporan & Statistik</h4>
                                <p>Analisis data perpustakaan</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="recent-activity-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Informasi Sistem
                        </h3>
                    </div>

                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon activity-success">
                                <i class="fas fa-code"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Versi Sistem: 1.0.0</div>
                                <div class="activity-meta">
                                    <span>Sistem Informasi Perpustakaan Modern</span>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon activity-info">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">SMK YMIK Jakarta</div>
                                <div class="activity-meta">
                                    <span>Yayasan Mandiri Informatika dan Komputer</span>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon activity-warning">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Dukungan Teknis</div>
                                <div class="activity-meta">
                                    <span>Hubungi admin sistem untuk bantuan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="data-table-container">
                <div class="table-header">
                    <h2 class="table-title">
                        <i class="fas fa-book-open me-2"></i>
                        Panduan Lengkap Sistem
                    </h2>
                </div>
                <div class="table-content">
                    <!-- Pengenalan Sistem -->
                    <section id="pengenalan" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-home me-2"></i>
                            1. Pengenalan Sistem
                        </h3>
                        
                        <div class="info-alert">
                            <div class="alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="alert-content">
                                <h6>Tentang Sistem Perpustakaan SMK YMIK</h6>
                                <p class="mb-2">Sistem Informasi Perpustakaan ini dirancang khusus untuk membantu pengelolaan perpustakaan SMK YMIK Jakarta secara digital dan efisien.</p>
                                <ul>
                                    <li>Interface modern dan user-friendly</li>
                                    <li>Manajemen koleksi buku yang komprehensif</li>
                                    <li>Sistem peminjaman dan pengembalian otomatis</li>
                                    <li>Tracking denda dan keterlambatan</li>
                                    <li>Laporan statistik yang detail</li>
                                    <li>Manajemen data siswa dan petugas</li>
                                </ul>
                            </div>
                        </div>

                        <div class="row g-4 mt-3">
                            <div class="col-md-4">
                                <div class="stat-card stat-primary">
                                    <div class="stat-icon">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Dashboard</div>
                                        <div class="stat-sublabel">Monitoring real-time perpustakaan</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card stat-success">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Multi User</div>
                                        <div class="stat-sublabel">Akses untuk berbagai petugas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card stat-info">
                                    <div class="stat-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Responsive</div>
                                        <div class="stat-sublabel">Dapat diakses dari berbagai device</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Manajemen Buku -->
                    <section id="manajemen-buku" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-book me-2"></i>
                            2. Manajemen Buku
                        </h3>

                        <h5 class="text-secondary mb-3">2.1 Menambah Buku Baru</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="alert alert-light">
                                    <h6>Langkah-langkah:</h6>
                                    <ol class="mb-0 small">
                                        <li>Klik menu "Manajemen Buku" di sidebar</li>
                                        <li>Klik tombol "Tambah Buku Baru"</li>
                                        <li>Isi formulir dengan lengkap</li>
                                        <li>Pastikan ISBN unik (jika ada)</li>
                                        <li>Set stok awal buku</li>
                                        <li>Klik "Simpan"</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <h6>Tips Penting:</h6>
                                    <ul class="mb-0 small">
                                        <li>Gunakan kategori yang sesuai</li>
                                        <li>Isi tahun terbit dengan benar</li>
                                        <li>Stok tidak boleh kosong</li>
                                        <li>Judul harus deskriptif</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-secondary mb-3">2.2 Mengelola Stok Buku</h5>
                        <div class="alert alert-info">
                            <h6>Fitur Stok Management:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Tambah Stok:</strong> Gunakan fitur "Tambah Stok" untuk menambah eksemplar buku yang sudah ada</li>
                                <li><strong>Edit Buku:</strong> Ubah informasi buku seperti judul, pengarang, atau kategori</li>
                                <li><strong>Detail Buku:</strong> Lihat informasi lengkap termasuk riwayat peminjaman</li>
                                <li><strong>Hapus Buku:</strong> Hanya buku yang tidak sedang dipinjam yang bisa dihapus</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Manajemen Siswa -->
                    <section id="manajemen-siswa" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-users me-2"></i>
                            3. Manajemen Siswa
                        </h3>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="alert alert-light">
                                    <h6>Data yang Diperlukan:</h6>
                                    <ul class="mb-0 small">
                                        <li>Nama lengkap siswa</li>
                                        <li>NIS (Nomor Induk Siswa)</li>
                                        <li>Kelas dan jurusan</li>
                                        <li>Nomor kontak/telepon</li>
                                        <li>Email (opsional)</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6>Fitur Tambahan:</h6>
                                    <ul class="mb-0 small">
                                        <li>Riwayat peminjaman siswa</li>
                                        <li>Status peminjaman aktif</li>
                                        <li>Edit informasi siswa</li>
                                        <li>Hapus data siswa (jika tidak ada peminjaman aktif)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Peminjaman dan Pengembalian -->
                    <section id="peminjaman" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-hand-holding me-2"></i>
                            4. Peminjaman & Pengembalian
                        </h3>

                        <h5 class="text-secondary mb-3">4.1 Proses Peminjaman</h5>
                        <div class="info-alert mb-4">
                            <div class="alert-icon">
                                <i class="fas fa-hand-holding"></i>
                            </div>
                            <div class="alert-content">
                                <h6>Langkah Peminjaman:</h6>
                                <ol>
                                    <li>Pilih siswa yang akan meminjam</li>
                                    <li>Cari dan pilih buku yang akan dipinjam</li>
                                    <li>Sistem akan otomatis set tanggal jatuh tempo (7 hari)</li>
                                    <li>Konfirmasi peminjaman</li>
                                    <li>Cetak struk peminjaman (opsional)</li>
                                </ol>
                            </div>
                        </div>

                        <h5 class="text-secondary mb-3">4.2 Proses Pengembalian</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="alert alert-light">
                                    <h6>Pengembalian Tepat Waktu:</h6>
                                    <ul class="mb-0 small">
                                        <li>Scan atau pilih ID peminjaman</li>
                                        <li>Periksa kondisi buku</li>
                                        <li>Konfirmasi pengembalian</li>
                                        <li>Status otomatis berubah</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <h6>Pengembalian Terlambat:</h6>
                                    <ul class="mb-0 small">
                                        <li>Sistem otomatis hitung denda</li>
                                        <li>Denda Rp 1.000 per hari per buku</li>
                                        <li>Proses pembayaran denda</li>
                                        <li>Cetak kwitansi denda</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Laporan dan Statistik -->
                    <section id="laporan" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-chart-bar me-2"></i>
                            5. Laporan & Statistik
                        </h3>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="stat-card stat-primary">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Laporan Harian</div>
                                        <div class="stat-sublabel">Aktivitas peminjaman hari ini</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card stat-success">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-week"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Laporan Mingguan</div>
                                        <div class="stat-sublabel">Ringkasan aktivitas seminggu</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card stat-info">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Laporan Bulanan</div>
                                        <div class="stat-sublabel">Analisis mendalam bulanan</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <h6>Fitur Laporan:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Export PDF:</strong> Semua laporan bisa diekspor ke format PDF</li>
                                <li><strong>Filter Data:</strong> Filter berdasarkan tanggal, siswa, atau buku</li>
                                <li><strong>Statistik Visual:</strong> Grafik dan chart untuk analisis data</li>
                                <li><strong>Laporan Denda:</strong> Khusus untuk tracking pembayaran denda</li>
                            </ul>
                        </div>
                    </section>

                    <!-- FAQ -->
                    <section id="faq" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-question-circle me-2"></i>
                            6. Frequently Asked Questions (FAQ)
                        </h3>

                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Bagaimana cara mengubah password petugas?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Saat ini fitur ubah password belum tersedia. Hubungi administrator sistem untuk mereset password Anda.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Apa yang terjadi jika siswa hilang buku?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Jika buku hilang, siswa harus mengganti dengan buku yang sama atau membayar denda sesuai harga buku. Hubungi kepala perpustakaan untuk prosedur lebih lanjut.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Berapa lama batas waktu peminjaman?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Batas waktu peminjaman adalah 7 hari. Setelah itu akan dikenakan denda Rp 1.000 per hari per buku.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        Bisakah siswa meminjam lebih dari 1 buku?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Ya, siswa bisa meminjam beberapa buku sekaligus dalam satu transaksi peminjaman. Namun maksimal 3 buku per siswa.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                        Bagaimana cara backup data?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Backup data harus dilakukan secara manual oleh administrator sistem. Disarankan backup dilakukan setiap minggu dan disimpan di tempat yang aman.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Troubleshooting -->
                    <section id="troubleshooting" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-tools me-2"></i>
                            7. Troubleshooting
                        </h3>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Masalah Umum:</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Login gagal:</strong> Periksa username dan password</li>
                                        <li><strong>Data tidak muncul:</strong> Refresh halaman atau cek koneksi</li>
                                        <li><strong>Error saat simpan:</strong> Pastikan semua field wajib terisi</li>
                                        <li><strong>Tidak bisa hapus:</strong> Cek apakah ada data terkait</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-lightbulb me-2"></i>Tips Performa:</h6>
                                    <ul class="mb-0 small">
                                        <li>Gunakan browser modern (Chrome, Firefox, Edge)</li>
                                        <li>Clear cache browser secara berkala</li>
                                        <li>Pastikan koneksi internet stabil</li>
                                        <li>Logout setelah selesai menggunakan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Kontak -->
                    <section id="kontak" class="mb-5">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-phone me-2"></i>
                            8. Kontak & Dukungan
                        </h3>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="stat-card stat-info">
                                    <div class="stat-icon">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">SMK YMIK Jakarta</div>
                                        <div class="stat-sublabel">Perpustakaan Sekolah</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-card stat-warning">
                                    <div class="stat-icon">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Administrator Sistem</div>
                                        <div class="stat-sublabel">Dukungan Teknis</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary mt-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Penting:</h6>
                            <p class="mb-2">Sistem ini dikembangkan khusus untuk SMK YMIK Jakarta dengan fitur-fitur yang disesuaikan dengan kebutuhan perpustakaan sekolah.</p>
                            <ul class="mb-0 small">
                                <li>Untuk masalah teknis, hubungi administrator sistem</li>
                                <li>Untuk kebijakan perpustakaan, hubungi kepala perpustakaan</li>
                                <li>Sistem ini akan terus dikembangkan sesuai kebutuhan</li>
                                <li>Feedback dan saran sangat kami hargai</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-collapse accordion items when opening new one
        document.addEventListener('DOMContentLoaded', function() {
            const accordionButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');
            accordionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Add slight delay for smooth animation
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                });
            });
        });
    </script>
</body>
</html>
