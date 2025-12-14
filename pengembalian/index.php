
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get returned books with search and filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$periode = $_GET['periode'] ?? '';

$where_conditions = [];

if ($search) {
    $where_conditions[] = "(s.nama_siswa LIKE '%$search%' OR s.nis LIKE '%$search%' OR b.judul LIKE '%$search%')";
}

if ($status_filter == 'lunas') {
    $where_conditions[] = "pg.status_denda = 'Lunas'";
} elseif ($status_filter == 'belum_lunas') {
    $where_conditions[] = "pg.status_denda = 'Belum Lunas'";
}

if ($periode == 'hari_ini') {
    $where_conditions[] = "pg.tanggal_kembali = CURDATE()";
} elseif ($periode == 'minggu_ini') {
    $where_conditions[] = "YEARWEEK(pg.tanggal_kembali, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($periode == 'bulan_ini') {
    $where_conditions[] = "MONTH(pg.tanggal_kembali) = MONTH(CURDATE()) AND YEAR(pg.tanggal_kembali) = YEAR(CURDATE())";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$query = "SELECT pg.*, p.tanggal_pinjam, p.tanggal_jatuh_tempo, s.nama_siswa, s.nis, s.kelas, b.judul, b.pengarang
          FROM pengembalian pg
          JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam
          JOIN siswa s ON p.id_siswa = s.id_siswa
          JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam
          JOIN buku b ON dp.id_buku = b.id_buku
          $where_clause
          ORDER BY pg.tanggal_kembali DESC";
$result = $conn->query($query);

// Get statistics
$total_kembali = $conn->query("SELECT COUNT(*) as total FROM pengembalian")->fetch_assoc()['total'];
$denda_pending = $conn->query("SELECT COUNT(*) as total FROM pengembalian WHERE status_denda = 'Belum Lunas'")->fetch_assoc()['total'];
$total_denda = $conn->query("SELECT COALESCE(SUM(denda), 0) as total FROM pengembalian WHERE status_denda = 'Belum Lunas'")->fetch_assoc()['total'];
$hari_ini = $conn->query("SELECT COUNT(*) as total FROM pengembalian WHERE tanggal_kembali = CURDATE()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengembalian - Perpustakaan SMK YMIK Jakarta</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="modern-body">
    <?php include '../includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-sidebar">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <main class="dashboard-main">
            <!-- Page Header -->
            <div class="welcome-header">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h1 class="welcome-title">
                            <i class="fas fa-undo me-3"></i>
                            Manajemen Pengembalian
                        </h1>
                        <p class="welcome-subtitle">
                            Kelola proses pengembalian buku dan denda
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= number_format($total_kembali) ?> Total Dikembalikan
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-calendar-day me-2"></i>
                                <?= number_format($hari_ini) ?> Hari Ini
                            </span>
                            <?php if ($denda_pending > 0): ?>
                            <span class="meta-item text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= number_format($denda_pending) ?> Denda Pending
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="add.php" class="btn btn-primary-custom">
                            <i class="fas fa-undo me-2"></i>
                            Kembalikan Buku
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_kembali) ?></div>
                        <div class="stat-label">Total Pengembalian</div>
                        <div class="stat-sublabel">Semua transaksi</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($hari_ini) ?></div>
                        <div class="stat-label">Hari Ini</div>
                        <div class="stat-sublabel">Dikembalikan</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($denda_pending) ?></div>
                        <div class="stat-label">Denda Pending</div>
                        <div class="stat-sublabel">Belum lunas</div>
                    </div>
                </div>

                <div class="stat-card stat-danger">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Rp <?= number_format($total_denda) ?></div>
                        <div class="stat-label">Total Denda</div>
                        <div class="stat-sublabel">Belum dibayar</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-container">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control search-input" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Cari berdasarkan nama siswa, NIS, atau judul buku...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="status_filter" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="lunas" <?= ($_GET['status_filter'] ?? '') == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                            <option value="belum_lunas" <?= ($_GET['status_filter'] ?? '') == 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="periode" class="form-select">
                            <option value="">Semua Periode</option>
                            <option value="hari_ini" <?= ($_GET['periode'] ?? '') == 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                            <option value="minggu_ini" <?= ($_GET['periode'] ?? '') == 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini</option>
                            <option value="bulan_ini" <?= ($_GET['periode'] ?? '') == 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <?php if ($search || isset($_GET['status_filter']) || isset($_GET['periode'])): ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-history me-2"></i>
                        Riwayat Pengembalian
                    </h3>
                    <div class="table-actions">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal Kembali</th>
                                <th>Siswa</th>
                                <th>Buku</th>
                                <th>Keterlambatan</th>
                                <th>Denda</th>
                                <th>Status Denda</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= $row['id_kembali'] ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['nama_siswa']) ?></div>
                                        <div class="item-subtitle"><?= $row['nis'] ?> - <?= $row['kelas'] ?></div>
                                    </td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['judul']) ?></div>
                                        <div class="item-subtitle"><?= htmlspecialchars($row['pengarang']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($row['telat'] > 0): ?>
                                            <span class="text-danger">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= $row['telat'] ?> hari
                                            </span>
                                        <?php else: ?>
                                            <span class="text-success">
                                                <i class="fas fa-check me-1"></i>
                                                Tepat waktu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['denda'] > 0): ?>
                                            <span class="text-danger fw-bold">Rp <?= number_format($row['denda']) ?></span>
                                        <?php else: ?>
                                            <span class="text-success">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $row['status_denda'] == 'Lunas' ? 'status-success' : 'status-warning' ?>">
                                            <?= $row['status_denda'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-info" title="Detail" onclick="showReturnDetail(<?= $row['id_kembali'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($row['status_denda'] == 'Belum Lunas' && $row['denda'] > 0): ?>
                                            <button class="btn btn-sm btn-outline-success" title="Bayar Denda" onclick="payFine(<?= $row['id_kembali'] ?>, <?= $row['denda'] ?>)">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-secondary" title="Print" onclick="printReceipt(<?= $row['id_kembali'] ?>)">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-undo"></i>
                                            </div>
                                            <div class="empty-text">
                                                <h4>Tidak ada data pengembalian</h4>
                                                <p>Riwayat pengembalian akan muncul di sini</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Detail Pengembalian -->
    <div class="modal fade" id="returnDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="returnDetailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bayar Denda -->
    <div class="modal fade" id="payFineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bayar Denda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="payFineForm" method="POST" action="pay_fine.php">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Konfirmasi pembayaran denda
                        </div>
                        <input type="hidden" name="id_kembali" id="fineReturnId">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">Jumlah Denda:</label>
                                <div class="form-control-plaintext fw-bold text-danger" id="fineAmount"></div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Tanggal Pembayaran:</label>
                                <input type="date" class="form-control" name="tanggal_bayar" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Catatan (opsional):</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Catatan pembayaran..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-money-bill me-2"></i>Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showReturnDetail(id_kembali) {
            const modal = new bootstrap.Modal(document.getElementById('returnDetailModal'));
            modal.show();
            
            // Fetch detail via AJAX (you'll need to create this endpoint)
            fetch(`return_detail.php?id=${id_kembali}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('returnDetailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('returnDetailContent').innerHTML = 
                        '<div class="alert alert-danger">Error loading detail</div>';
                });
        }

        function payFine(id_kembali, amount) {
            document.getElementById('fineReturnId').value = id_kembali;
            document.getElementById('fineAmount').textContent = 'Rp ' + amount.toLocaleString();
            
            const modal = new bootstrap.Modal(document.getElementById('payFineModal'));
            modal.show();
        }

        function printReceipt(id_kembali) {
            window.open(`print_receipt.php?id=${id_kembali}`, '_blank', 'width=800,height=600');
        }

        // Auto-refresh alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
