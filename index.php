
<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get statistics with proper error handling
$total_buku = 0;
$total_siswa = 0;
$buku_dipinjam = 0;
$total_stok = 0;
$total_denda = 0;

try {
    $result = $conn->query("SELECT COUNT(*) as total FROM buku");
    if ($result) $total_buku = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM siswa");
    if ($result) $total_siswa = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status_pinjam = 'Dipinjam'");
    if ($result) $buku_dipinjam = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COALESCE(SUM(stok), 0) as total FROM buku");
    if ($result) $total_stok = $result->fetch_assoc()['total'];
    
    $buku_tersedia = $total_stok - $buku_dipinjam;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengembalian WHERE status_denda = 'Belum Lunas'");
    if ($result) $total_denda = $result->fetch_assoc()['total'];
} catch (Exception $e) {
    // Handle database errors gracefully
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan SMK Budi Luhur</title>
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
            <!-- Welcome Header -->
            <div class="welcome-header">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h1 class="welcome-title">
                            <i class="fas fa-home me-3"></i>
                            Dashboard Perpustakaan
                        </h1>
                        <p class="welcome-subtitle">
                            Selamat datang di Sistem Informasi Perpustakaan SMK Budi Luhur
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?= date('l, d F Y') ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock me-2"></i>
                                <?= date('H:i') ?> WIB
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <button class="btn btn-light-custom me-2" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Refresh
                        </button>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#quickStatsModal">
                            <i class="fas fa-chart-line me-2"></i>
                            Detail Statistik
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_buku) ?></div>
                        <div class="stat-label">Total Buku</div>
                        <div class="stat-sublabel">Koleksi tersedia</div>
                    </div>
                    </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_siswa) ?></div>
                        <div class="stat-label">Total Siswa</div>
                        <div class="stat-sublabel">Anggota aktif</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($buku_dipinjam) ?></div>
                        <div class="stat-label">Sedang Dipinjam</div>
                        <div class="stat-sublabel">Buku aktif</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_denda) ?></div>
                        <div class="stat-label">Denda Pending</div>
                        <div class="stat-sublabel">Perlu tindakan</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-grid">
                <div class="quick-actions-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-bolt me-2"></i>
                            Aksi Cepat
                        </h3>
                        <p class="section-subtitle">Operasi yang sering digunakan</p>
                    </div>
                    
                    <div class="quick-actions-grid">
                        <a href="buku/add.php" class="quick-action-card action-primary">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-content">
                                <h4>Tambah Buku</h4>
                                <p>Menambah koleksi buku baru</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>

                        <a href="siswa/add.php" class="quick-action-card action-success">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-content">
                                <h4>Daftar Siswa</h4>
                                <p>Mendaftarkan anggota baru</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>

                        <a href="peminjaman/add.php" class="quick-action-card action-info">
                            <div class="action-icon">
                                <i class="fas fa-hand-holding"></i>
                            </div>
                            <div class="action-content">
                                <h4>Pinjam Buku</h4>
                                <p>Transaksi peminjaman baru</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>

                        <a href="pengembalian/index.php" class="quick-action-card action-warning">
                            <div class="action-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="action-content">
                                <h4>Kembalikan Buku</h4>
                                <p>Proses pengembalian buku</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-history me-2"></i>
                            Aktivitas Terbaru
                        </h3>
                        <a href="peminjaman/index.php" class="view-all-link">
                            Lihat Semua <i class="fas fa-external-link-alt ms-1"></i>
                        </a>
                    </div>

                    <div class="activity-list">
                        <?php
                        $recent_query = "SELECT p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status_pinjam, 
                                       s.nama_siswa, s.kelas, b.judul 
                                       FROM peminjaman p 
                                       JOIN siswa s ON p.id_siswa = s.id_siswa 
                                       LEFT JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam 
                                       LEFT JOIN buku b ON dp.id_buku = b.id_buku 
                                       ORDER BY p.tanggal_pinjam DESC LIMIT 8";
                        $recent_result = $conn->query($recent_query);

                        if ($recent_result && $recent_result->num_rows > 0):
                            while ($row = $recent_result->fetch_assoc()):
                                $jatuh_tempo = strtotime($row['tanggal_jatuh_tempo']);
                                $today = time();
                                $is_overdue = $jatuh_tempo < $today && $row['status_pinjam'] == 'Dipinjam';
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $row['status_pinjam'] == 'Dipinjam' ? 'activity-warning' : 'activity-success' ?>">
                                <i class="fas <?= $row['status_pinjam'] == 'Dipinjam' ? 'fa-arrow-right' : 'fa-check' ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?= htmlspecialchars($row['nama_siswa']) ?> - <?= htmlspecialchars($row['judul'] ?? 'Multiple Books') ?>
                                </div>
                                <div class="activity-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?>
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($row['kelas']) ?>
                                    </span>
                                    <?php if ($is_overdue): ?>
                                    <span class="meta-item text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Terlambat
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="activity-status">
                                <span class="status-badge <?= $row['status_pinjam'] == 'Dipinjam' ? 'status-warning' : 'status-success' ?>">
                                    <?= $row['status_pinjam'] ?>
                                </span>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="empty-text">
                                <h4>Belum ada aktivitas</h4>
                                <p>Aktivitas peminjaman akan muncul di sini</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Popular Books & Status Overview -->
            <div class="bottom-grid">
                <div class="popular-books-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-star me-2"></i>
                            Buku Terpopuler
                        </h3>
                        <p class="section-subtitle">Koleksi yang paling diminati</p>
                    </div>

                    <div class="books-list">
                        <?php
                        $popular_query = "SELECT b.judul, b.pengarang, COUNT(dp.id_buku) as total_pinjam 
                                         FROM buku b 
                                         LEFT JOIN detail_pinjam dp ON b.id_buku = dp.id_buku 
                                         GROUP BY b.id_buku 
                                         ORDER BY total_pinjam DESC 
                                         LIMIT 6";
                        $popular_result = $conn->query($popular_query);
                        
                        if ($popular_result && $popular_result->num_rows > 0):
                            $rank = 1;
                            while ($row = $popular_result->fetch_assoc()):
                        ?>
                        <div class="book-item">
                            <div class="book-rank">
                                <?= $rank ?>
                            </div>
                            <div class="book-content">
                                <h4 class="book-title"><?= htmlspecialchars($row['judul']) ?></h4>
                                <p class="book-author"><?= htmlspecialchars($row['pengarang']) ?></p>
                            </div>
                            <div class="book-stats">
                                <span class="borrow-count"><?= $row['total_pinjam'] ?></span>
                                <small>peminjaman</small>
                            </div>
                        </div>
                        <?php 
                            $rank++;
                            endwhile;
                        else:
                        ?>
                        <div class="empty-state-small">
                            <i class="fas fa-book-open"></i>
                            <p>Belum ada data peminjaman</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="status-overview-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-chart-pie me-2"></i>
                            Ringkasan Status
                        </h3>
                        <p class="section-subtitle">Status koleksi saat ini</p>
                    </div>

                    <div class="status-grid">
                        <div class="status-item status-available">
                            <div class="status-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="status-content">
                                <div class="status-number"><?= number_format($buku_tersedia) ?></div>
                                <div class="status-label">Buku Tersedia</div>
                            </div>
                        </div>

                        <div class="status-item status-borrowed">
                            <div class="status-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="status-content">
                                <div class="status-number"><?= number_format($buku_dipinjam) ?></div>
                                <div class="status-label">Sedang Dipinjam</div>
                            </div>
                        </div>

                        <div class="status-item status-pending">
                            <div class="status-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="status-content">
                                <div class="status-number"><?= number_format($total_denda) ?></div>
                                <div class="status-label">Denda Pending</div>
                            </div>
                        </div>

                        <div class="status-item status-total">
                            <div class="status-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="status-content">
                                <div class="status-number"><?= number_format($total_stok) ?></div>
                                <div class="status-label">Total Stok</div>
                            </div>
                        </div>
                    </div>

                    <div class="availability-bar">
                        <div class="bar-header">
                            <span>Tingkat Ketersediaan</span>
                            <span class="percentage"><?= $total_stok > 0 ? number_format(($buku_tersedia / $total_stok) * 100, 1) : 0 ?>%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?= $total_stok > 0 ? ($buku_tersedia / $total_stok) * 100 : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Statistics Modal -->
    <div class="modal fade" id="quickStatsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Statistik Detail Perpustakaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="modal-stat-card">
                                <div class="modal-stat-icon text-primary">
                                    <i class="fas fa-database fa-2x"></i>
                                </div>
                                <div class="modal-stat-content">
                                    <h3><?= number_format($total_stok) ?></h3>
                                    <p>Total Stok Buku</p>
                                    <small class="text-muted">Seluruh koleksi perpustakaan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="modal-stat-card">
                                <div class="modal-stat-icon text-success">
                                    <i class="fas fa-percentage fa-2x"></i>
                                </div>
                                <div class="modal-stat-content">
                                    <h3><?= $total_stok > 0 ? number_format(($buku_tersedia / $total_stok) * 100, 1) : 0 ?>%</h3>
                                    <p>Tingkat Ketersediaan</p>
                                    <small class="text-muted">Persentase buku yang tersedia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
