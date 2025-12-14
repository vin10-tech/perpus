
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get students with search
$search = $_GET['search'] ?? '';
$filter_kelas = $_GET['kelas'] ?? '';
$filter_jurusan = $_GET['jurusan'] ?? '';

$where_conditions = [];
if ($search) {
    $where_conditions[] = "(nama_siswa LIKE '%$search%' OR nis LIKE '%$search%' OR kontak LIKE '%$search%')";
}
if ($filter_kelas) {
    $where_conditions[] = "kelas = '$filter_kelas'";
}
if ($filter_jurusan) {
    $where_conditions[] = "jurusan = '$filter_jurusan'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$query = "SELECT * FROM siswa $where_clause ORDER BY nama_siswa ASC";
$result = $conn->query($query);

// Get statistics
$total_siswa = $conn->query("SELECT COUNT(*) as total FROM siswa")->fetch_assoc()['total'];
$siswa_aktif = $conn->query("SELECT COUNT(DISTINCT s.id_siswa) as total FROM siswa s JOIN peminjaman p ON s.id_siswa = p.id_siswa WHERE p.status_pinjam = 'Dipinjam'")->fetch_assoc()['total'];

// Get class and major options
$kelas_options = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
$jurusan_options = $conn->query("SELECT DISTINCT jurusan FROM siswa ORDER BY jurusan");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-users me-3"></i>
                            Manajemen Siswa
                        </h1>
                        <p class="welcome-subtitle">
                            Kelola data siswa dan anggota perpustakaan
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-user-graduate me-2"></i>
                                <?= number_format($total_siswa) ?> Total Siswa
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-book-reader me-2"></i>
                                <?= number_format($siswa_aktif) ?> Sedang Meminjam
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="add.php" class="btn btn-primary-custom">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Siswa
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_siswa) ?></div>
                        <div class="stat-label">Total Siswa</div>
                        <div class="stat-sublabel">Terdaftar</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($siswa_aktif) ?></div>
                        <div class="stat-label">Sedang Meminjam</div>
                        <div class="stat-sublabel">Aktif</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_siswa - $siswa_aktif) ?></div>
                        <div class="stat-label">Tidak Meminjam</div>
                        <div class="stat-sublabel">Tersedia</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $kelas_options->num_rows ?></div>
                        <div class="stat-label">Kelas</div>
                        <div class="stat-sublabel">Berbeda</div>
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
                                   placeholder="Cari berdasarkan nama, NIS, atau kontak...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="kelas">
                            <option value="">Semua Kelas</option>
                            <?php 
                            $kelas_options->data_seek(0);
                            while ($kelas = $kelas_options->fetch_assoc()): 
                            ?>
                                <option value="<?= $kelas['kelas'] ?>" <?= $filter_kelas == $kelas['kelas'] ? 'selected' : '' ?>>
                                    <?= $kelas['kelas'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="jurusan">
                            <option value="">Semua Jurusan</option>
                            <?php 
                            $jurusan_options->data_seek(0);
                            while ($jurusan = $jurusan_options->fetch_assoc()): 
                            ?>
                                <option value="<?= $jurusan['jurusan'] ?>" <?= $filter_jurusan == $jurusan['jurusan'] ? 'selected' : '' ?>>
                                    <?= $jurusan['jurusan'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary-custom flex-grow-1">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                            <?php if ($search || $filter_kelas || $filter_jurusan): ?>
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
                        Daftar Siswa
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
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Kontak</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php 
                                    // Check if student has active borrowing
                                    $check_borrow = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE id_siswa = {$row['id_siswa']} AND status_pinjam = 'Dipinjam'");
                                    $has_active_borrow = $check_borrow->fetch_assoc()['count'] > 0;
                                    ?>
                                <tr>
                                    <td><strong><?= $row['id_siswa'] ?></strong></td>
                                    <td><?= htmlspecialchars($row['nis']) ?></td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['nama_siswa']) ?></div>
                                        <?php if ($has_active_borrow): ?>
                                            <div class="item-subtitle text-warning">
                                                <i class="fas fa-book me-1"></i>Sedang meminjam
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['kelas']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($row['jurusan']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['kontak']) ?></td>
                                    <td>
                                        <?php if ($has_active_borrow): ?>
                                            <span class="status-badge status-warning">Meminjam</span>
                                        <?php else: ?>
                                            <span class="status-badge status-success">Tersedia</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-info" title="Detail" onclick="showStudentDetail(<?= $row['id_siswa'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit.php?id=<?= $row['id_siswa'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $row['id_siswa'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="empty-text">
                                                <h4>Tidak ada data siswa</h4>
                                                <p>Silakan tambah siswa baru atau coba kata kunci lain</p>
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

    <!-- Student Detail Modal -->
    <div class="modal fade" id="studentDetailModal" tabindex="-1" aria-labelledby="studentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailModalLabel">
                        <i class="fas fa-user me-2"></i>Detail Siswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="studentDetailContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showStudentDetail(id_siswa) {
            const modal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
            modal.show();
            
            // Reset content
            document.getElementById('studentDetailContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Fetch student detail
            fetch(`detail.php?id=${id_siswa}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('studentDetailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('studentDetailContent').innerHTML = 
                        '<div class="alert alert-danger">Error loading student detail</div>';
                });
        }
    </script>
</body>
</html>
