
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get denda with search
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$where_conditions = [];
if ($search) {
    $where_conditions[] = "(s.nama_siswa LIKE '%$search%' OR s.nis LIKE '%$search%' OR b.judul LIKE '%$search%')";
}
if ($filter_status) {
    $where_conditions[] = "pg.status_denda = '$filter_status'";
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
$total_denda = $conn->query("SELECT COALESCE(SUM(denda), 0) as total FROM pengembalian")->fetch_assoc()['total'];
$denda_belum_lunas = $conn->query("SELECT COALESCE(SUM(denda), 0) as total FROM pengembalian WHERE status_denda = 'Belum Lunas'")->fetch_assoc()['total'];
$denda_lunas = $conn->query("SELECT COALESCE(SUM(denda), 0) as total FROM pengembalian WHERE status_denda = 'Lunas'")->fetch_assoc()['total'];
$jumlah_siswa_denda = $conn->query("SELECT COUNT(DISTINCT p.id_siswa) as total FROM pengembalian pg JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam WHERE pg.status_denda = 'Belum Lunas'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Denda - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-exclamation-triangle me-3"></i>
                            Manajemen Denda
                        </h1>
                        <p class="welcome-subtitle">
                            Kelola denda keterlambatan pengembalian buku
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Rp <?= number_format($total_denda) ?> Total Denda
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-users me-2"></i>
                                <?= number_format($jumlah_siswa_denda) ?> Siswa Terkena Denda
                            </span>
                            <?php if ($denda_belum_lunas > 0): ?>
                            <span class="meta-item text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Rp <?= number_format($denda_belum_lunas) ?> Belum Lunas
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <button class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Refresh
                        </button>
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
                <div class="stat-card stat-danger">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Rp <?= number_format($total_denda) ?></div>
                        <div class="stat-label">Total Denda</div>
                        <div class="stat-sublabel">Keseluruhan</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Rp <?= number_format($denda_belum_lunas) ?></div>
                        <div class="stat-label">Belum Lunas</div>
                        <div class="stat-sublabel">Perlu dibayar</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Rp <?= number_format($denda_lunas) ?></div>
                        <div class="stat-label">Sudah Lunas</div>
                        <div class="stat-sublabel">Terbayar</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($jumlah_siswa_denda) ?></div>
                        <div class="stat-label">Siswa Terdenda</div>
                        <div class="stat-sublabel">Yang belum lunas</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-container">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control search-input" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Cari berdasarkan nama siswa, NIS, atau judul buku...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="Belum Lunas" <?= $filter_status == 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                            <option value="Lunas" <?= $filter_status == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                            <?php if ($search || $filter_status): ?>
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
                        <i class="fas fa-list me-2"></i>
                        Daftar Denda
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
                                <th>Status</th>
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
                                            <?php if ($row['status_denda'] == 'Belum Lunas' && $row['denda'] > 0): ?>
                                            <button class="btn btn-sm btn-outline-success" title="Lunasi" 
                                                    onclick="lunasi(<?= $row['id_kembali'] ?>, '<?= htmlspecialchars($row['nama_siswa']) ?>', <?= $row['denda'] ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
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
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div class="empty-text">
                                                <h4>Tidak ada data denda</h4>
                                                <p>Belum ada denda atau coba kata kunci lain</p>
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

    <!-- Modal Lunasi Denda -->
    <div class="modal fade" id="lunasiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lunasi Denda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin melunasi denda untuk:</p>
                    <div class="alert alert-info">
                        <strong>Siswa:</strong> <span id="modal-siswa"></span><br>
                        <strong>Jumlah Denda:</strong> Rp <span id="modal-denda"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-lunasi">Lunasi</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function lunasi(id, nama, denda) {
            document.getElementById('modal-siswa').textContent = nama;
            document.getElementById('modal-denda').textContent = new Intl.NumberFormat('id-ID').format(denda);
            
            const modal = new bootstrap.Modal(document.getElementById('lunasiModal'));
            modal.show();
            
            document.getElementById('btn-lunasi').onclick = function() {
                window.location.href = 'lunasi.php?id=' + id;
            };
        }
    </script>
</body>
</html>
