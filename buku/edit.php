
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

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

if ($_POST) {
    $judul = $_POST['judul'];
    $isbn = $_POST['isbn'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    
    $stmt = $conn->prepare("UPDATE buku SET judul=?, isbn=?, pengarang=?, penerbit=?, tahun_terbit=?, kategori=?, stok=? WHERE id_buku=?");
    $stmt->bind_param("ssssssii", $judul, $isbn, $pengarang, $penerbit, $tahun_terbit, $kategori, $stok, $id);
    
    if ($stmt->execute()) {
        $success = 'Buku berhasil diupdate!';
        // Refresh book data
        $stmt = $conn->prepare("SELECT * FROM buku WHERE id_buku = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
    } else {
        $error = 'Gagal mengupdate buku: ' . $conn->error;
    }
}

// Get borrowing stats
$stmt = $conn->prepare("SELECT COUNT(*) as borrowed FROM detail_pinjam dp 
                       JOIN peminjaman p ON dp.id_pinjam = p.id_pinjam 
                       WHERE dp.id_buku = ? AND p.status_pinjam = 'Dipinjam'");
$stmt->bind_param("i", $id);
$stmt->execute();
$borrowed_count = $stmt->get_result()->fetch_assoc()['borrowed'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_borrowed FROM detail_pinjam WHERE id_buku = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$total_borrowed = $stmt->get_result()->fetch_assoc()['total_borrowed'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-edit me-3"></i>
                            Edit Buku
                        </h1>
                        <p class="welcome-subtitle">
                            Update informasi buku: <?= htmlspecialchars($book['judul']) ?>
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-hashtag me-2"></i>
                                ID: <?= $book['id_buku'] ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user-clock me-2"></i>
                                <?= $borrowed_count ?> Sedang Dipinjam
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-history me-2"></i>
                                <?= $total_borrowed ?> Total Peminjaman
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStockModal">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Stok
                        </button>
                        <a href="index.php" class="btn btn-light-custom">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
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

            <!-- Form -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-edit me-2"></i>
                        Form Edit Buku
                    </h3>
                </div>

                <div class="table-content">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="judul" class="form-label">
                                        <i class="fas fa-book me-2"></i>Judul Buku
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="judul" name="judul" 
                                           value="<?= htmlspecialchars($book['judul']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="isbn" class="form-label">
                                        <i class="fas fa-barcode me-2"></i>ISBN
                                    </label>
                                    <input type="text" class="form-control" id="isbn" name="isbn"
                                           value="<?= htmlspecialchars($book['isbn']) ?>">
                                </div>

                                <div class="form-group">
                                    <label for="pengarang" class="form-label">
                                        <i class="fas fa-user-edit me-2"></i>Pengarang
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="pengarang" name="pengarang" 
                                           value="<?= htmlspecialchars($book['pengarang']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="penerbit" class="form-label">
                                        <i class="fas fa-building me-2"></i>Penerbit
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="penerbit" name="penerbit" 
                                           value="<?= htmlspecialchars($book['penerbit']) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_terbit" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Tahun Terbit
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                           min="1900" max="<?= date('Y') ?>" value="<?= $book['tahun_terbit'] ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="kategori" class="form-label">
                                        <i class="fas fa-tags me-2"></i>Kategori
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Teknologi" <?= $book['kategori'] == 'Teknologi' ? 'selected' : '' ?>>Teknologi</option>
                                        <option value="Bisnis" <?= $book['kategori'] == 'Bisnis' ? 'selected' : '' ?>>Bisnis</option>
                                        <option value="Sains" <?= $book['kategori'] == 'Sains' ? 'selected' : '' ?>>Sains</option>
                                        <option value="Sejarah" <?= $book['kategori'] == 'Sejarah' ? 'selected' : '' ?>>Sejarah</option>
                                        <option value="Bahasa" <?= $book['kategori'] == 'Bahasa' ? 'selected' : '' ?>>Bahasa</option>
                                        <option value="Agama" <?= $book['kategori'] == 'Agama' ? 'selected' : '' ?>>Agama</option>
                                        <option value="Fiksi" <?= $book['kategori'] == 'Fiksi' ? 'selected' : '' ?>>Fiksi</option>
                                        <option value="Non-Fiksi" <?= $book['kategori'] == 'Non-Fiksi' ? 'selected' : '' ?>>Non-Fiksi</option>
                                        <option value="Lainnya" <?= $book['kategori'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="stok" class="form-label">
                                        <i class="fas fa-warehouse me-2"></i>Stok
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="stok" name="stok" 
                                           min="0" value="<?= $book['stok'] ?>" required>
                                </div>

                                <!-- Current Data Display -->
                                <div class="current-data">
                                    <div class="data-row">
                                        <span class="data-label">ID Buku:</span>
                                        <span class="data-value"><?= $book['id_buku'] ?></span>
                                    </div>
                                    <div class="data-row">
                                        <span class="data-label">Tanggal Ditambahkan:</span>
                                        <span class="data-value"><?= date('d/m/Y', strtotime($book['created_at'] ?? 'now')) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save me-2"></i>
                                Update Buku
                            </button>
                            <a href="index.php" class="btn btn-light-custom">
                                <i class="fas fa-times me-2"></i>
                                Batal
                            </a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-2"></i>
                                Hapus Buku
                            </button>
                        </div>
                    </form>
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

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h4>Yakin ingin menghapus buku ini?</h4>
                        <p class="text-muted">
                            <strong><?= htmlspecialchars($book['judul']) ?></strong><br>
                            Tindakan ini tidak dapat dibatalkan!
                        </p>
                    </div>

                    <?php if ($borrowed_count > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Peringatan:</strong> Buku ini sedang dipinjam oleh <?= $borrowed_count ?> orang. 
                            Pastikan semua peminjaman sudah dikembalikan sebelum menghapus.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <a href="delete.php?id=<?= $book['id_buku'] ?>" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
