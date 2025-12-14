
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get active loans
$loans_query = "SELECT p.*, s.nama_siswa, s.nis, b.judul, b.pengarang
                FROM peminjaman p
                JOIN siswa s ON p.id_siswa = s.id_siswa
                JOIN detail_pinjam dp ON p.id_pinjam = dp.id_pinjam
                JOIN buku b ON dp.id_buku = b.id_buku
                WHERE p.status_pinjam = 'Dipinjam'
                ORDER BY p.tanggal_jatuh_tempo ASC";
$loans_result = $conn->query($loans_query);

if ($_POST) {
    $id_pinjam = $_POST['id_pinjam'];
    $tanggal_kembali = date('Y-m-d');
    
    // Get loan data
    $loan_stmt = $conn->prepare("SELECT tanggal_jatuh_tempo FROM peminjaman WHERE id_pinjam = ?");
    $loan_stmt->bind_param("i", $id_pinjam);
    $loan_stmt->execute();
    $loan_data = $loan_stmt->get_result()->fetch_assoc();
    
    // Calculate late days and fine
    $jatuh_tempo = strtotime($loan_data['tanggal_jatuh_tempo']);
    $kembali = strtotime($tanggal_kembali);
    $telat = max(0, ceil(($kembali - $jatuh_tempo) / (60 * 60 * 24)));
    $denda = $telat * 500; // Rp 500 per day
    
    $conn->begin_transaction();
    
    try {
        // Insert return record
        $return_stmt = $conn->prepare("INSERT INTO pengembalian (id_pinjam, tanggal_kembali, telat, denda, status_denda) VALUES (?, ?, ?, ?, ?)");
        $status_denda = $denda > 0 ? 'Belum Lunas' : 'Lunas';
        $return_stmt->bind_param("isiis", $id_pinjam, $tanggal_kembali, $telat, $denda, $status_denda);
        $return_stmt->execute();
        
        // Update loan status
        $update_stmt = $conn->prepare("UPDATE peminjaman SET status_pinjam = 'Dikembalikan' WHERE id_pinjam = ?");
        $update_stmt->bind_param("i", $id_pinjam);
        $update_stmt->execute();
        
        // Update book stock
        $stock_stmt = $conn->prepare("UPDATE buku b 
                                     JOIN detail_pinjam dp ON b.id_buku = dp.id_buku 
                                     SET b.stok = b.stok + 1 
                                     WHERE dp.id_pinjam = ?");
        $stock_stmt->bind_param("i", $id_pinjam);
        $stock_stmt->execute();
        
        $conn->commit();
        
        $success_msg = 'Buku berhasil dikembalikan';
        if ($denda > 0) {
            $success_msg .= '. Denda: Rp ' . number_format($denda);
        }
        
        header('Location: index.php?success=' . urlencode($success_msg));
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Gagal memproses pengembalian: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kembalikan Buku - Perpustakaan SMK YMIK Jakarta</title>
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
                            Kembalikan Buku
                        </h1>
                        <p class="welcome-subtitle">
                            Proses pengembalian buku dan perhitungan denda
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock me-2"></i>
                                Tanggal: <?= date('d/m/Y') ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Denda: Rp 500/hari
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="index.php" class="btn btn-light-custom">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-list-check me-2"></i>
                                Pilih Peminjaman untuk Dikembalikan
                            </h3>
                            <div class="table-actions">
                                <span class="badge bg-info"><?= $loans_result ? $loans_result->num_rows : 0 ?> aktif</span>
                            </div>
                        </div>

                        <div class="table-content">
                            <form method="POST" id="returnForm">
                                <!-- Search Filter -->
                                <div class="search-container mb-4">
                                    <div class="search-input-group">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" id="searchLoan" 
                                               placeholder="Cari berdasarkan nama siswa, NIS, atau judul buku...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table modern-table">
                                        <thead>
                                            <tr>
                                                <th width="50">Pilih</th>
                                                <th>Siswa</th>
                                                <th>Buku</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Jatuh Tempo</th>
                                                <th>Status</th>
                                                <th>Potensi Denda</th>
                                            </tr>
                                        </thead>
                                        <tbody id="loanTableBody">
                                            <?php if ($loans_result && $loans_result->num_rows > 0): ?>
                                                <?php while ($loan = $loans_result->fetch_assoc()): ?>
                                                <?php
                                                $jatuh_tempo = strtotime($loan['tanggal_jatuh_tempo']);
                                                $today = time();
                                                $telat = max(0, ceil(($today - $jatuh_tempo) / (60 * 60 * 24)));
                                                $denda_potential = $telat * 500;
                                                ?>
                                                <tr class="loan-row" data-student="<?= strtolower(htmlspecialchars($loan['nama_siswa'])) ?>" 
                                                    data-nis="<?= $loan['nis'] ?>" data-book="<?= strtolower(htmlspecialchars($loan['judul'])) ?>">
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="radio" name="id_pinjam" value="<?= $loan['id_pinjam'] ?>" 
                                                                   class="form-check-input loan-radio" id="loan_<?= $loan['id_pinjam'] ?>" 
                                                                   data-late="<?= $telat ?>" data-fine="<?= $denda_potential ?>" required>
                                                            <label class="form-check-label" for="loan_<?= $loan['id_pinjam'] ?>"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="item-title"><?= htmlspecialchars($loan['nama_siswa']) ?></div>
                                                        <div class="item-subtitle"><?= $loan['nis'] ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="item-title"><?= htmlspecialchars($loan['judul']) ?></div>
                                                        <div class="item-subtitle"><?= htmlspecialchars($loan['pengarang']) ?></div>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($loan['tanggal_pinjam'])) ?></td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($loan['tanggal_jatuh_tempo'])) ?>
                                                        <?php if ($telat > 0): ?>
                                                            <br><small class="text-danger">
                                                                <i class="fas fa-exclamation-triangle"></i> <?= $telat ?> hari
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($telat > 0): ?>
                                                            <span class="status-badge status-danger">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>Terlambat
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-badge status-success">
                                                                <i class="fas fa-check me-1"></i>Normal
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($denda_potential > 0): ?>
                                                            <span class="text-danger fw-bold">Rp <?= number_format($denda_potential) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-success">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        <div class="empty-state-small">
                                                            <i class="fas fa-clipboard-list"></i>
                                                            <div class="empty-text">
                                                                <h4>Tidak ada peminjaman aktif</h4>
                                                                <p>Semua buku sudah dikembalikan</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <?php if ($loans_result && $loans_result->num_rows > 0): ?>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary-custom" disabled id="submitBtn">
                                        <i class="fas fa-undo me-2"></i>Kembalikan Buku
                                    </button>
                                    <a href="index.php" class="btn btn-light-custom">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Pengembalian
                            </h3>
                        </div>
                        <div class="table-content">
                            <!-- Selected Return Info -->
                            <div id="selectedInfo" class="alert alert-primary d-none">
                                <h6 class="alert-heading">
                                    <i class="fas fa-check-circle me-2"></i>Peminjaman Dipilih
                                </h6>
                                <div id="selectedDetails"></div>
                            </div>

                            <!-- Rules -->
                            <div class="info-alert">
                                <div class="alert-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="alert-content">
                                    <h6>Ketentuan Denda:</h6>
                                    <ul>
                                        <li>Denda keterlambatan: <strong>Rp 500/hari</strong></li>
                                        <li>Dihitung dari tanggal jatuh tempo</li>
                                        <li>Denda harus dilunasi sebelum peminjaman berikutnya</li>
                                        <li>Perpanjangan maksimal 1 kali (7 hari)</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="mt-4">
                                <h6 class="mb-3">
                                    <i class="fas fa-chart-line me-2"></i>Statistik Hari Ini
                                </h6>
                                <?php
                                $today_stats = $conn->query("SELECT COUNT(*) as returned_today FROM pengembalian WHERE tanggal_kembali = CURDATE()")->fetch_assoc();
                                $late_returns = $conn->query("SELECT COUNT(*) as late_today FROM pengembalian WHERE tanggal_kembali = CURDATE() AND telat > 0")->fetch_assoc();
                                $total_fine_today = $conn->query("SELECT COALESCE(SUM(denda), 0) as total FROM pengembalian WHERE tanggal_kembali = CURDATE()")->fetch_assoc();
                                ?>
                                <div class="stat-row">
                                    <span class="stat-label">Buku dikembalikan</span>
                                    <span class="stat-value text-success"><?= $today_stats['returned_today'] ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Pengembalian terlambat</span>
                                    <span class="stat-value text-warning"><?= $late_returns['late_today'] ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Total denda hari ini</span>
                                    <span class="stat-value text-danger">Rp <?= number_format($total_fine_today['total']) ?></span>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4">
                                <h6 class="mb-3">
                                    <i class="fas fa-bolt me-2"></i>Aksi Cepat
                                </h6>
                                <div class="d-grid gap-2">
                                    <a href="../peminjaman/index.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-list me-2"></i>Lihat Semua Peminjaman
                                    </a>
                                    <a href="../denda/index.php" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Kelola Denda
                                    </a>
                                    <a href="../laporan/index.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-chart-bar me-2"></i>Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('.loan-radio');
            const submitBtn = document.getElementById('submitBtn');
            const selectedInfo = document.getElementById('selectedInfo');
            const selectedDetails = document.getElementById('selectedDetails');
            const searchInput = document.getElementById('searchLoan');
            const loanRows = document.querySelectorAll('.loan-row');

            // Handle loan selection
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        submitBtn.disabled = false;
                        showSelectedInfo(this);
                    }
                });
            });

            function showSelectedInfo(radio) {
                const row = radio.closest('tr');
                const studentName = row.querySelector('.item-title').textContent;
                const bookTitle = row.querySelectorAll('.item-title')[1].textContent;
                const late = parseInt(radio.dataset.late);
                const fine = parseInt(radio.dataset.fine);

                let html = `
                    <div><strong>Siswa:</strong> ${studentName}</div>
                    <div><strong>Buku:</strong> ${bookTitle}</div>
                    <div><strong>Keterlambatan:</strong> ${late > 0 ? late + ' hari' : 'Tepat waktu'}</div>
                    <div><strong>Denda:</strong> ${fine > 0 ? 'Rp ' + fine.toLocaleString() : 'Tidak ada'}</div>
                `;
                
                selectedDetails.innerHTML = html;
                selectedInfo.classList.remove('d-none');
            }

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                loanRows.forEach(row => {
                    const student = row.dataset.student || '';
                    const nis = row.dataset.nis || '';
                    const book = row.dataset.book || '';
                    
                    const matches = student.includes(searchTerm) || 
                                   nis.includes(searchTerm) ||
                                   book.includes(searchTerm);
                    
                    row.style.display = matches ? '' : 'none';
                });
            });

            // Confirm before submit
            document.getElementById('returnForm').addEventListener('submit', function(e) {
                const selectedRadio = document.querySelector('.loan-radio:checked');
                if (selectedRadio) {
                    const late = parseInt(selectedRadio.dataset.late);
                    const fine = parseInt(selectedRadio.dataset.fine);
                    
                    let message = 'Konfirmasi pengembalian buku ini?';
                    if (fine > 0) {
                        message += `\n\nDenda yang harus dibayar: Rp ${fine.toLocaleString()}`;
                    }
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>
