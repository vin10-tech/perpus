
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;

// Get book data
$stmt = $conn->prepare("SELECT * FROM buku WHERE id_buku = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    header('Location: index.php?error=Buku tidak ditemukan');
    exit();
}

// Get borrowing stats
$stmt = $conn->prepare("
    SELECT COUNT(*) as borrowed FROM detail_pinjam dp 
    JOIN peminjaman p ON dp.id_pinjam = p.id_pinjam 
    WHERE dp.id_buku = ? AND p.status_pinjam = 'Dipinjam'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$borrowed_count = $stmt->get_result()->fetch_assoc()['borrowed'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_borrowed FROM detail_pinjam WHERE id_buku = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$total_borrowed = $stmt->get_result()->fetch_assoc()['total_borrowed'];

// Get recent borrowers
$stmt = $conn->prepare("
    SELECT s.nama_siswa, s.kelas, s.jurusan, p.tanggal_pinjam, 
           pen.tanggal_kembali, p.status_pinjam
    FROM detail_pinjam dp
    JOIN peminjaman p ON dp.id_pinjam = p.id_pinjam
    JOIN siswa s ON p.id_siswa = s.id_siswa
    LEFT JOIN pengembalian pen ON p.id_pinjam = pen.id_pinjam
    WHERE dp.id_buku = ?
    ORDER BY p.tanggal_pinjam DESC
    LIMIT 10
");
$stmt->bind_param("i", $id);
$stmt->execute();
$recent_borrowers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - Perpustakaan SMK YMIK Jakarta</title>
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
                            Detail Buku
                        </h1>
                        <p class="welcome-subtitle">
                            Informasi lengkap: <?= htmlspecialchars($book['judul']) ?>
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-hashtag me-2"></i>
                                ID: <?= $book['id_buku'] ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-warehouse me-2"></i>
                                Stok: <?= $book['stok'] ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user-clock me-2"></i>
                                <?= $borrowed_count ?> Dipinjam
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="edit.php?id=<?= $book['id_buku'] ?>" class="btn btn-primary-custom">
                            <i class="fas fa-edit me-2"></i>
                            Edit Buku
                        </a>
                        <a href="index.php" class="btn btn-light-custom">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $book['stok'] ?></div>
                        <div class="stat-label">Stok Tersedia</div>
                        <div class="stat-sublabel">Eksemplar</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $borrowed_count ?></div>
                        <div class="stat-label">Sedang Dipinjam</div>
                        <div class="stat-sublabel">Eksemplar</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $total_borrowed ?></div>
                        <div class="stat-label">Total Peminjaman</div>
                        <div class="stat-sublabel">Sepanjang waktu</div>
                    </div>
                </div>

                <div class="stat-card <?= $book['stok'] > 0 ? 'stat-success' : 'stat-danger' ?>">
                    <div class="stat-icon">
                        <i class="fas fa-<?= $book['stok'] > 0 ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $book['stok'] > 0 ? 'Tersedia' : 'Habis' ?></div>
                        <div class="stat-label">Status</div>
                        <div class="stat-sublabel"><?= $book['stok'] > 0 ? 'Siap dipinjam' : 'Perlu restok' ?></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Book Information -->
                <div class="col-md-8">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Buku
                            </h3>
                        </div>

                        <div class="table-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="current-data">
                                        <div class="data-row">
                                            <span class="data-label">Judul:</span>
                                            <span class="data-value"><?= htmlspecialchars($book['judul']) ?></span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Pengarang:</span>
                                            <span class="data-value"><?= htmlspecialchars($book['pengarang']) ?></span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Penerbit:</span>
                                            <span class="data-value"><?= htmlspecialchars($book['penerbit']) ?></span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">ISBN:</span>
                                            <span class="data-value"><?= $book['isbn'] ?: '-' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="current-data">
                                        <div class="data-row">
                                            <span class="data-label">Kategori:</span>
                                            <span class="data-value">
                                                <span class="badge bg-secondary"><?= htmlspecialchars($book['kategori']) ?></span>
                                            </span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Tahun Terbit:</span>
                                            <span class="data-value"><?= $book['tahun_terbit'] ?></span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Stok:</span>
                                            <span class="data-value">
                                                <span class="fw-bold <?= $book['stok'] > 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= $book['stok'] ?> eksemplar
                                                </span>
                                            </span>
                                        </div>
                                        <div class="data-row">
                                            <span class="data-label">Status:</span>
                                            <span class="data-value">
                                                <?php if ($book['stok'] > 0): ?>
                                                    <span class="status-badge status-success">Tersedia</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-danger">Habis</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Borrowers -->
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-history me-2"></i>
                                Riwayat Peminjaman Terbaru
                            </h3>
                        </div>

                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Tgl Kembali</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_borrowers && $recent_borrowers->num_rows > 0): ?>
                                        <?php while ($borrower = $recent_borrowers->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="item-title"><?= htmlspecialchars($borrower['nama_siswa']) ?></div>
                                            </td>
                                            <td><?= $borrower['kelas'] ?> <?= $borrower['jurusan'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($borrower['tanggal_pinjam'])) ?></td>
                                            <td>
                                                <?php if ($borrower['tanggal_kembali']): ?>
                                                    <?= date('d/m/Y', strtotime($borrower['tanggal_kembali'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum dikembalikan</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($borrower['status_pinjam'] == 'Dipinjam'): ?>
                                                    <span class="status-badge status-warning">Dipinjam</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-success">Dikembalikan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <div class="empty-state-small">
                                                    <i class="fas fa-history"></i>
                                                    <h4>Belum ada riwayat peminjaman</h4>
                                                    <p>Buku ini belum pernah dipinjam</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="quick-actions-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-bolt me-2"></i>
                                Aksi Cepat
                            </h3>
                        </div>

                        <div class="quick-actions-grid" style="grid-template-columns: 1fr;">
                            <a href="edit.php?id=<?= $book['id_buku'] ?>" class="quick-action-card action-primary">
                                <div class="action-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Edit Buku</h4>
                                    <p>Update informasi buku</p>
                                </div>
                                <div class="action-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>

                            <button type="button" class="quick-action-card action-success" data-bs-toggle="modal" data-bs-target="#addStockModal" style="border: none; background: var(--gray-100);">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Tambah Stok</h4>
                                    <p>Tambah eksemplar buku</p>
                                </div>
                                <div class="action-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </button>

                            <a href="../peminjaman/add.php?book_id=<?= $book['id_buku'] ?>" class="quick-action-card action-info">
                                <div class="action-icon">
                                    <i class="fas fa-hand-holding"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Pinjamkan</h4>
                                    <p>Tambah peminjaman baru</p>
                                </div>
                                <div class="action-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>

                            <a href="delete.php?id=<?= $book['id_buku'] ?>" class="quick-action-card action-warning" onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                <div class="action-icon">
                                    <i class="fas fa-trash"></i>
                                </div>
                                <div class="action-content">
                                    <h4>Hapus Buku</h4>
                                    <p>Hapus dari koleksi</p>
                                </div>
                                <div class="action-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Tambah Stok -->
    <div class="modal fade" id="addStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Stok Buku
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="add_stock.php">
                    <div class="modal-body">
                        <input type="hidden" name="id_buku" value="<?= $book['id_buku'] ?>">
                        
                        <div class="modal-stat-card">
                            <div class="stat-icon" style="background: var(--gradient-info);">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div class="modal-stat-content">
                                <h3><?= $book['stok'] ?></h3>
                                <p>Stok Saat Ini</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="additional_stock" class="form-label">
                                <i class="fas fa-plus me-2"></i>Jumlah Stok Tambahan
                            </label>
                            <input type="number" class="form-control" id="additional_stock" name="additional_stock" 
                                   min="1" required placeholder="Masukkan jumlah stok yang akan ditambahkan">
                        </div>

                        <div class="form-group">
                            <label for="stock_note" class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>Catatan (Opsional)
                            </label>
                            <textarea class="form-control" id="stock_note" name="stock_note" rows="3" 
                                      placeholder="Catatan penambahan stok..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Tambah Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
