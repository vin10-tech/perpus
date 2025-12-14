
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Handle search and messages
$search = isset($_GET['search']) ? $_GET['search'] : '';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$where_clause = '';
if ($search) {
    $where_clause = "WHERE s.nama_siswa LIKE '%$search%' OR s.nis LIKE '%$search%' OR b.judul LIKE '%$search%'";
}

$query = "SELECT p.*, s.nama_siswa, s.nis, s.kelas, s.jurusan,
          GROUP_CONCAT(CONCAT(b.judul, ' (', b.pengarang, ')') SEPARATOR ', ') as buku_detail,
          COUNT(dp.id_buku) as jumlah_buku,
          pet.nama_petugas
          FROM peminjaman p 
          JOIN siswa s ON p.id_siswa = s.id_siswa 
          JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam 
          JOIN buku b ON dp.id_buku = b.id_buku 
          LEFT JOIN petugas pet ON p.id_petugas = pet.id_petugas
          $where_clause 
          GROUP BY p.id_pinjam
          ORDER BY p.tanggal_pinjam DESC";
$result = $conn->query($query);

// Get statistics
$total_pinjam = $conn->query("SELECT COUNT(*) as total FROM peminjaman")->fetch_assoc()['total'];
$aktif_pinjam = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status_pinjam = 'Dipinjam'")->fetch_assoc()['total'];
$selesai_pinjam = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status_pinjam = 'Dikembalikan'")->fetch_assoc()['total'];
$terlambat = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status_pinjam = 'Dipinjam' AND tanggal_jatuh_tempo < CURDATE()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-hand-holding me-3"></i>
                            Manajemen Peminjaman
                        </h1>
                        <p class="welcome-subtitle">
                            Kelola transaksi peminjaman buku
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-chart-line me-2"></i>
                                <?= number_format($total_pinjam) ?> Total Transaksi
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock me-2"></i>
                                <?= number_format($aktif_pinjam) ?> Aktif
                            </span>
                            <?php if ($terlambat > 0): ?>
                            <span class="meta-item text-danger">
                                <i class="fas fa-triangle-exclamation me-2"></i>
                                <?= number_format($terlambat) ?> Terlambat
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="add.php" class="btn btn-primary-custom">
                            <i class="fas fa-plus me-2"></i>
                            Pinjam Buku
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
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_pinjam) ?></div>
                        <div class="stat-label">Total Peminjaman</div>
                        <div class="stat-sublabel">Semua transaksi</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($aktif_pinjam) ?></div>
                        <div class="stat-label">Sedang Dipinjam</div>
                        <div class="stat-sublabel">Belum dikembalikan</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($selesai_pinjam) ?></div>
                        <div class="stat-label">Selesai</div>
                        <div class="stat-sublabel">Sudah dikembalikan</div>
                    </div>
                </div>

                <div class="stat-card stat-danger">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($terlambat) ?></div>
                        <div class="stat-label">Terlambat</div>
                        <div class="stat-sublabel">Perlu tindakan</div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-container">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control search-input" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Cari berdasarkan nama siswa, NIS, atau judul buku...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                            <?php if ($search): ?>
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
                        Daftar Peminjaman
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
                                <th>Tanggal Pinjam</th>
                                <th>Siswa</th>
                                <th>Buku (Jumlah)</th>
                                <th>Jatuh Tempo</th>
                                <th>Petugas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?= str_pad($row['id_pinjam'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['nama_siswa']) ?></div>
                                        <div class="item-subtitle"><?= $row['nis'] ?> - <?= $row['kelas'] ?> <?= $row['jurusan'] ?></div>
                                    </td>
                                    <td>
                                        <div class="item-title">
                                            <span class="badge bg-primary me-1"><?= $row['jumlah_buku'] ?> buku</span>
                                        </div>
                                        <div class="item-subtitle" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($row['buku_detail']) ?>">
                                            <?= htmlspecialchars($row['buku_detail']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?>
                                        <?php if (strtotime($row['tanggal_jatuh_tempo']) < time() && $row['status_pinjam'] == 'Dipinjam'): ?>
                                            <?php 
                                            $days_late = ceil((time() - strtotime($row['tanggal_jatuh_tempo'])) / (60 * 60 * 24));
                                            ?>
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> <?= $days_late ?> hari</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['nama_petugas']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($row['status_pinjam'] == 'Dipinjam'): ?>
                                            <?php if (strtotime($row['tanggal_jatuh_tempo']) < time()): ?>
                                                <span class="status-badge status-danger">Terlambat</span>
                                            <?php else: ?>
                                                <span class="status-badge status-warning">Dipinjam</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="status-badge status-success">Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-info" title="Detail" onclick="showDetail(<?= $row['id_pinjam'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($row['status_pinjam'] == 'Dipinjam'): ?>
                                            <a href="../pengembalian/add.php?id_pinjam=<?= $row['id_pinjam'] ?>" class="btn btn-sm btn-outline-success" title="Kembalikan">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-hand-holding"></i>
                                            </div>
                                            <div class="empty-text">
                                                <h4>Tidak ada data peminjaman</h4>
                                                <p>Silakan tambah peminjaman baru atau coba kata kunci lain</p>
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

    <!-- Modal Detail Peminjaman -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetail(id_pinjam) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
            
            // Fetch detail via AJAX
            fetch(`detail.php?id=${id_pinjam}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('detailContent').innerHTML = 
                        '<div class="alert alert-danger">Error loading detail</div>';
                });
        }
    </script>
</body>
</html>
