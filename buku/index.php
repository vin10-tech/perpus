
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get books with search
$search = $_GET['search'] ?? '';
$where_clause = '';
if ($search) {
    $where_clause = "WHERE judul LIKE '%$search%' OR pengarang LIKE '%$search%' OR isbn LIKE '%$search%' OR kategori LIKE '%$search%'";
}

$query = "SELECT * FROM buku $where_clause ORDER BY judul ASC";
$result = $conn->query($query);

// Get statistics
$total_buku = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];
$buku_tersedia = $conn->query("SELECT COUNT(*) as total FROM buku WHERE stok > 0")->fetch_assoc()['total'];
$buku_habis = $conn->query("SELECT COUNT(*) as total FROM buku WHERE stok = 0")->fetch_assoc()['total'];
$total_stok = $conn->query("SELECT COALESCE(SUM(stok), 0) as total FROM buku")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-book me-3"></i>
                            Manajemen Buku
                        </h1>
                        <p class="welcome-subtitle">
                            Kelola koleksi buku perpustakaan
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-book me-2"></i>
                                <?= number_format($total_buku) ?> Total Buku
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= number_format($buku_tersedia) ?> Tersedia
                            </span>
                            <?php if ($buku_habis > 0): ?>
                            <span class="meta-item text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= number_format($buku_habis) ?> Stok Habis
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="add.php" class="btn btn-primary-custom">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Buku
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
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_buku) ?></div>
                        <div class="stat-label">Total Buku</div>
                        <div class="stat-sublabel">Judul berbeda</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($buku_tersedia) ?></div>
                        <div class="stat-label">Tersedia</div>
                        <div class="stat-sublabel">Siap dipinjam</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($buku_habis) ?></div>
                        <div class="stat-label">Stok Habis</div>
                        <div class="stat-sublabel">Perlu restok</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_stok) ?></div>
                        <div class="stat-label">Total Stok</div>
                        <div class="stat-sublabel">Semua eksemplar</div>
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
                                   placeholder="Cari berdasarkan judul, pengarang, ISBN, atau kategori...">
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
                        Daftar Buku
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
                                <th>Buku</th>
                                <th>ISBN</th>
                                <th>Kategori</th>
                                <th>Tahun</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= $row['id_buku'] ?></strong></td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['judul']) ?></div>
                                        <div class="item-subtitle"><?= htmlspecialchars($row['pengarang']) ?></div>
                                        <div class="item-subtitle"><?= htmlspecialchars($row['penerbit']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($row['isbn']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($row['kategori']) ?></span>
                                    </td>
                                    <td><?= $row['tahun_terbit'] ?></td>
                                    <td>
                                        <span class="fw-bold <?= $row['stok'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $row['stok'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['stok'] > 0): ?>
                                            <span class="status-badge status-success">Tersedia</span>
                                        <?php else: ?>
                                            <span class="status-badge status-danger">Habis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="detail.php?id=<?= $row['id_buku'] ?>" class="btn btn-sm btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?= $row['id_buku'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $row['id_buku'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus buku ini?')">
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
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div class="empty-text">
                                                <h4>Tidak ada data buku</h4>
                                                <p>Silakan tambah buku baru atau coba kata kunci lain</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
