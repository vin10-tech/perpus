
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

if ($_POST) {
    $judul = $_POST['judul'];
    $isbn = $_POST['isbn'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    
    $stmt = $conn->prepare("INSERT INTO buku (judul, isbn, pengarang, penerbit, tahun_terbit, kategori, stok) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $judul, $isbn, $pengarang, $penerbit, $tahun_terbit, $kategori, $stok);
    
    if ($stmt->execute()) {
        header('Location: index.php?success=Buku berhasil ditambahkan');
        exit();
    } else {
        $error = 'Gagal menambah buku!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-plus-circle me-3"></i>
                            Tambah Buku Baru
                        </h1>
                        <p class="welcome-subtitle">
                            Tambahkan buku baru ke koleksi perpustakaan
                        </p>
                    </div>
                    <div class="welcome-actions">
                        <a href="index.php" class="btn btn-light-custom">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alert -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-edit me-2"></i>
                        Form Tambah Buku
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
                                    <input type="text" class="form-control" id="judul" name="judul" required 
                                           placeholder="Masukkan judul buku">
                                </div>

                                <div class="form-group">
                                    <label for="isbn" class="form-label">
                                        <i class="fas fa-barcode me-2"></i>ISBN
                                    </label>
                                    <input type="text" class="form-control" id="isbn" name="isbn"
                                           placeholder="Masukkan nomor ISBN">
                                </div>

                                <div class="form-group">
                                    <label for="pengarang" class="form-label">
                                        <i class="fas fa-user-edit me-2"></i>Pengarang
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="pengarang" name="pengarang" required
                                           placeholder="Masukkan nama pengarang">
                                </div>

                                <div class="form-group">
                                    <label for="penerbit" class="form-label">
                                        <i class="fas fa-building me-2"></i>Penerbit
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="penerbit" name="penerbit" required
                                           placeholder="Masukkan nama penerbit">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_terbit" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Tahun Terbit
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                           min="1900" max="<?= date('Y') ?>" required 
                                           placeholder="<?= date('Y') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="kategori" class="form-label">
                                        <i class="fas fa-tags me-2"></i>Kategori
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Teknologi">Teknologi</option>
                                        <option value="Bisnis">Bisnis</option>
                                        <option value="Sains">Sains</option>
                                        <option value="Sejarah">Sejarah</option>
                                        <option value="Bahasa">Bahasa</option>
                                        <option value="Agama">Agama</option>
                                        <option value="Fiksi">Fiksi</option>
                                        <option value="Non-Fiksi">Non-Fiksi</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="stok" class="form-label">
                                        <i class="fas fa-warehouse me-2"></i>Stok
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="stok" name="stok" min="0" required
                                           placeholder="Masukkan jumlah stok">
                                </div>

                                <!-- Info Alert -->
                                <div class="info-alert">
                                    <div class="alert-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="alert-content">
                                        <h6>Informasi Penting</h6>
                                        <ul>
                                            <li>Field bertanda (*) wajib diisi</li>
                                            <li>ISBN bersifat opsional tapi disarankan diisi</li>
                                            <li>Stok minimal adalah 0</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save me-2"></i>
                                Simpan Buku
                            </button>
                            <a href="index.php" class="btn btn-light-custom">
                                <i class="fas fa-times me-2"></i>
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
