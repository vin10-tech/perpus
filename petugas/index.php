
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get petugas with search
$search = $_GET['search'] ?? '';
$where_clause = '';
if ($search) {
    $where_clause = "WHERE nama_petugas LIKE '%$search%' OR username LIKE '%$search%'";
}

$query = "SELECT * FROM petugas $where_clause ORDER BY nama_petugas ASC";
$result = $conn->query($query);

// Get statistics
$total_petugas = $conn->query("SELECT COUNT(*) as total FROM petugas")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Petugas - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-user-cog me-3"></i>
                            Manajemen Petugas
                        </h1>
                        <p class="welcome-subtitle">
                            Kelola akun petugas dan administrator perpustakaan
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-users-cog me-2"></i>
                                <?= number_format($total_petugas) ?> Total Petugas
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-shield-alt me-2"></i>
                                Sistem Keamanan Aktif
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="add.php" class="btn btn-primary-custom">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Petugas
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
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_petugas) ?></div>
                        <div class="stat-label">Total Petugas</div>
                        <div class="stat-sublabel">Akun terdaftar</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($total_petugas) ?></div>
                        <div class="stat-label">Akun Aktif</div>
                        <div class="stat-sublabel">Dapat login</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">1</div>
                        <div class="stat-label">Administrator</div>
                        <div class="stat-sublabel">Akses penuh</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Online</div>
                        <div class="stat-label">Status Sistem</div>
                        <div class="stat-sublabel">Aktif sekarang</div>
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
                                   placeholder="Cari berdasarkan nama petugas atau username...">
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
                        Daftar Petugas
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
                                <th>Username</th>
                                <th>Nama Petugas</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= $row['id_petugas'] ?></strong></td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['username']) ?></div>
                                        <?php if ($row['username'] == 'admin'): ?>
                                            <div class="item-subtitle text-warning">
                                                <i class="fas fa-crown me-1"></i>Super Admin
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="item-title"><?= htmlspecialchars($row['nama_petugas']) ?></div>
                                        <div class="item-subtitle">
                                            <i class="fas fa-calendar me-1"></i>
                                            Terdaftar
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['username'] == 'admin'): ?>
                                            <span class="badge bg-danger">Administrator</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Petugas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-success">Aktif</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit.php?id=<?= $row['id_petugas'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($row['username'] != 'admin'): ?>
                                            <a href="delete.php?id=<?= $row['id_petugas'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus petugas ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-users-cog"></i>
                                            </div>
                                            <div class="empty-text">
                                                <h4>Tidak ada data petugas</h4>
                                                <p>Silakan tambah petugas baru atau coba kata kunci lain</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Security Info -->
            <div class="mt-4">
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Keamanan
                    </h6>
                    <ul class="mb-0 small">
                        <li>Pastikan setiap petugas memiliki password yang kuat</li>
                        <li>Jangan bagikan akun admin kepada sembarang orang</li>
                        <li>Lakukan backup data secara berkala</li>
                        <li>Monitor aktivitas login secara rutin</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
