
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get available books
$books_query = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul";
$books_result = $conn->query($books_query);

// Get students
$students_query = "SELECT * FROM siswa ORDER BY nama_siswa";
$students_result = $conn->query($students_query);

if ($_POST) {
    $id_siswa = $_POST['id_siswa'];
    $selected_books = $_POST['books'] ?? [];
    
    if (empty($selected_books)) {
        $error = 'Pilih minimal satu buku';
    } else {
        // Check if student has reached borrowing limit
        $limit_check = $conn->prepare("SELECT COUNT(*) as active_loans FROM peminjaman WHERE id_siswa = ? AND status_pinjam = 'Dipinjam'");
        $limit_check->bind_param("i", $id_siswa);
        $limit_check->execute();
        $active_loans = $limit_check->get_result()->fetch_assoc()['active_loans'];
        
        if ($active_loans + count($selected_books) > 3) {
            $error = 'Siswa sudah mencapai batas maksimal peminjaman (3 buku)';
        } else {
            $conn->begin_transaction();
            
            try {
                // Insert loan record
                $tanggal_pinjam = date('Y-m-d');
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
                
                $stmt = $conn->prepare("INSERT INTO peminjaman (id_siswa, id_petugas, tanggal_pinjam, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $id_siswa, $_SESSION['id_petugas'], $tanggal_pinjam, $tanggal_jatuh_tempo);
                $stmt->execute();
                
                $id_pinjam = $conn->insert_id;
                
                // Insert loan details and update stock
                foreach ($selected_books as $id_buku) {
                    $detail_stmt = $conn->prepare("INSERT INTO detail_pinjam (id_pinjam, id_buku) VALUES (?, ?)");
                    $detail_stmt->bind_param("ii", $id_pinjam, $id_buku);
                    $detail_stmt->execute();
                    
                    $stock_stmt = $conn->prepare("UPDATE buku SET stok = stok - 1 WHERE id_buku = ?");
                    $stock_stmt->bind_param("i", $id_buku);
                    $stock_stmt->execute();
                }
                
                $conn->commit();
                header('Location: index.php?success=Peminjaman berhasil dicatat');
                exit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Gagal mencatat peminjaman: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peminjaman - Perpustakaan SMK YMIK Jakarta</title>
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
                            Tambah Peminjaman
                        </h1>
                        <p class="welcome-subtitle">
                            Catat peminjaman buku baru untuk siswa
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar me-2"></i>
                                <?= date('d/m/Y') ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user me-2"></i>
                                <?= $_SESSION['nama_petugas'] ?>
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="index.php" class="btn btn-light-custom">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Form -->
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-edit me-2"></i>
                                Form Peminjaman
                            </h3>
                        </div>
                        
                        <div class="table-content">
                            <form method="POST" id="loanForm">
                                <!-- Student Selection -->
                                <div class="form-group mb-4">
                                    <label for="id_siswa" class="form-label">
                                        <i class="fas fa-user me-2"></i>Pilih Siswa
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-select" id="id_siswa" name="id_siswa" required>
                                        <option value="">Pilih Siswa</option>
                                        <?php while ($student = $students_result->fetch_assoc()): ?>
                                            <option value="<?= $student['id_siswa'] ?>">
                                                <?= $student['nis'] ?> - <?= htmlspecialchars($student['nama_siswa']) ?> (<?= $student['kelas'] ?> <?= $student['jurusan'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <!-- Book Selection -->
                                <div class="form-group mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-book me-2"></i>Pilih Buku (Maksimal 3)
                                        <span class="text-danger">*</span>
                                    </label>
                                    
                                    <!-- Book Search -->
                                    <div class="search-container mb-3">
                                        <div class="search-input-group">
                                            <i class="fas fa-search search-icon"></i>
                                            <input type="text" class="form-control search-input" id="bookSearch" 
                                                   placeholder="Cari buku berdasarkan judul, pengarang, atau kategori...">
                                        </div>
                                    </div>
                                    
                                    <!-- Selected Books Counter -->
                                    <div class="selection-summary mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="selection-counter">0/3 buku dipilih</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                                                <i class="fas fa-times me-1"></i>Bersihkan Pilihan
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Books List -->
                                    <div class="book-selection" id="booksList">
                                        <?php 
                                        $books_result = $conn->query($books_query); // Reset pointer
                                        while ($book = $books_result->fetch_assoc()): 
                                        ?>
                                            <div class="book-item" data-title="<?= strtolower($book['judul']) ?>" 
                                                 data-author="<?= strtolower($book['pengarang']) ?>" 
                                                 data-category="<?= strtolower($book['kategori']) ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input book-checkbox" type="checkbox" 
                                                           value="<?= $book['id_buku'] ?>" name="books[]" 
                                                           id="book_<?= $book['id_buku'] ?>">
                                                    <label class="form-check-label" for="book_<?= $book['id_buku'] ?>">
                                                        <div class="book-info">
                                                            <h6 class="book-title"><?= htmlspecialchars($book['judul']) ?></h6>
                                                            <div class="book-details">
                                                                <div class="book-meta">
                                                                    <span class="book-author">
                                                                        <i class="fas fa-user-edit me-1"></i>
                                                                        <?= htmlspecialchars($book['pengarang']) ?>
                                                                    </span>
                                                                    <span class="book-publisher">
                                                                        <i class="fas fa-building me-1"></i>
                                                                        <?= htmlspecialchars($book['penerbit']) ?>
                                                                    </span>
                                                                </div>
                                                                <div class="book-stats">
                                                                    <span class="book-stock">
                                                                        <i class="fas fa-layer-group me-1"></i>
                                                                        Stok: <?= $book['stok'] ?>
                                                                    </span>
                                                                    <span class="book-year">
                                                                        <i class="fas fa-calendar me-1"></i>
                                                                        <?= $book['tahun_terbit'] ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-secondary book-category"><?= htmlspecialchars($book['kategori']) ?></span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    
                                    <!-- No Results Message -->
                                    <div class="no-results" id="noResults" style="display: none;">
                                        <div class="empty-state-small">
                                            <i class="fas fa-search"></i>
                                            <div class="empty-text">
                                                <h5>Tidak ada buku ditemukan</h5>
                                                <p>Coba kata kunci yang berbeda</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Loan Rules Info -->
                                <div class="info-alert mb-4">
                                    <div class="alert-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="alert-content">
                                        <h6>Ketentuan Peminjaman:</h6>
                                        <ul>
                                            <li>Maksimal peminjaman per siswa: 3 buku</li>
                                            <li>Masa peminjaman: 7 hari</li>
                                            <li>Denda keterlambatan: Rp 500/hari per buku</li>
                                            <li>Perpanjangan maksimal 1 kali (7 hari)</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Form Actions -->
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>Simpan Peminjaman
                                    </button>
                                    <a href="index.php" class="btn btn-light-custom">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Statistics Card -->
                    <div class="data-table-container mb-4">
                        <div class="table-header">
                            <h6 class="table-title">
                                <i class="fas fa-chart-bar me-2"></i>Statistik Peminjaman
                            </h6>
                        </div>
                        <div class="table-content">
                            <?php
                            $stats = $conn->query("SELECT 
                                COUNT(*) as total_pinjam,
                                COUNT(CASE WHEN status_pinjam = 'Dipinjam' THEN 1 END) as aktif,
                                COUNT(CASE WHEN status_pinjam = 'Dikembalikan' THEN 1 END) as kembali
                                FROM peminjaman")->fetch_assoc();
                            ?>
                            <div class="stat-row">
                                <span class="stat-label">Total Peminjaman:</span>
                                <span class="stat-value"><?= $stats['total_pinjam'] ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Sedang Dipinjam:</span>
                                <span class="stat-value text-warning"><?= $stats['aktif'] ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Sudah Dikembalikan:</span>
                                <span class="stat-value text-success"><?= $stats['kembali'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Late Books Card -->
                    <div class="data-table-container">
                        <div class="table-header">
                            <h6 class="table-title">
                                <i class="fas fa-clock me-2"></i>Buku Terlambat
                            </h6>
                        </div>
                        <div class="table-content">
                            <?php
                            $late_query = "SELECT COUNT(*) as late_count FROM peminjaman 
                                          WHERE status_pinjam = 'Dipinjam' AND tanggal_jatuh_tempo < CURDATE()";
                            $late_result = $conn->query($late_query);
                            $late_count = $late_result->fetch_assoc()['late_count'];
                            ?>
                            <div class="text-center">
                                <div class="late-count <?= $late_count > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= $late_count ?>
                                </div>
                                <small class="text-muted">Buku terlambat</small>
                            </div>
                            <?php if ($late_count > 0): ?>
                                <div class="alert alert-warning mt-3">
                                    <small>
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Ada buku yang terlambat dikembalikan
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookCheckboxes = document.querySelectorAll('.book-checkbox');
            const selectionCounter = document.querySelector('.selection-counter');
            const bookSearch = document.getElementById('bookSearch');
            const booksList = document.getElementById('booksList');
            const noResults = document.getElementById('noResults');
            const clearButton = document.getElementById('clearSelection');
            
            // Update selection counter and limit
            function updateSelection() {
                const checked = document.querySelectorAll('.book-checkbox:checked').length;
                const unchecked = document.querySelectorAll('.book-checkbox:not(:checked)');
                
                selectionCounter.textContent = `${checked}/3 buku dipilih`;
                
                if (checked >= 3) {
                    unchecked.forEach(function(cb) {
                        if (!cb.disabled) {
                            cb.disabled = true;
                            cb.closest('.book-item').style.opacity = '0.5';
                        }
                    });
                } else {
                    unchecked.forEach(function(cb) {
                        cb.disabled = false;
                        cb.closest('.book-item').style.opacity = '1';
                    });
                }
                
                // Update clear button visibility
                clearButton.style.display = checked > 0 ? 'block' : 'none';
            }
            
            // Add event listeners to checkboxes
            bookCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', updateSelection);
            });
            
            // Clear selection
            clearButton.addEventListener('click', function() {
                bookCheckboxes.forEach(function(cb) {
                    cb.checked = false;
                    cb.disabled = false;
                    cb.closest('.book-item').style.opacity = '1';
                });
                updateSelection();
            });
            
            // Search functionality
            bookSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const bookItems = document.querySelectorAll('.book-item');
                let visibleCount = 0;
                
                bookItems.forEach(function(item) {
                    const title = item.dataset.title || '';
                    const author = item.dataset.author || '';
                    const category = item.dataset.category || '';
                    
                    const matches = title.includes(searchTerm) || 
                                   author.includes(searchTerm) || 
                                   category.includes(searchTerm);
                    
                    if (matches || searchTerm === '') {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                if (visibleCount === 0 && searchTerm !== '') {
                    noResults.style.display = 'block';
                    booksList.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    booksList.style.display = 'block';
                }
            });
            
            // Initialize
            updateSelection();
        });
    </script>
</body>
</html>
