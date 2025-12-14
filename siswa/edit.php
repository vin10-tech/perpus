
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'];

// Get student data
$stmt = $conn->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php?error=Siswa tidak ditemukan');
    exit();
}

$student = $result->fetch_assoc();

if ($_POST) {
    $nis = $_POST['nis'];
    $nama_siswa = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $kontak = $_POST['kontak'];
    
    // Check if NIS already exists for other students
    $check_nis = $conn->prepare("SELECT COUNT(*) as count FROM siswa WHERE nis = ? AND id_siswa != ?");
    $check_nis->bind_param("si", $nis, $id);
    $check_nis->execute();
    $nis_exists = $check_nis->get_result()->fetch_assoc()['count'] > 0;
    
    if ($nis_exists) {
        $error = 'NIS sudah digunakan oleh siswa lain. Silakan gunakan NIS lain.';
    } else {
        $stmt = $conn->prepare("UPDATE siswa SET nis = ?, nama_siswa = ?, kelas = ?, jurusan = ?, kontak = ? WHERE id_siswa = ?");
        $stmt->bind_param("sssssi", $nis, $nama_siswa, $kelas, $jurusan, $kontak, $id);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=Data siswa berhasil diperbarui');
            exit();
        } else {
            $error = 'Gagal memperbarui data siswa: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-user-edit me-3"></i>
                            Edit Siswa
                        </h1>
                        <p class="welcome-subtitle">
                            Perbarui data siswa <?= htmlspecialchars($student['nama_siswa']) ?>
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-id-card me-2"></i>
                                NIS: <?= htmlspecialchars($student['nis']) ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <?= htmlspecialchars($student['kelas']) ?> <?= htmlspecialchars($student['jurusan']) ?>
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

            <!-- Alert Messages -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
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
                                Form Edit Siswa
                            </h3>
                        </div>
                        
                        <div class="table-content">
                            <form method="POST" id="editStudentForm">
                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="nis" class="form-label">
                                                <i class="fas fa-id-card me-2"></i>NIS
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nis" name="nis" required 
                                                   value="<?= htmlspecialchars($student['nis']) ?>"
                                                   placeholder="Contoh: 2024001" maxlength="20">
                                            <div class="form-text">Nomor Induk Siswa harus unik</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="nama_siswa" class="form-label">
                                                <i class="fas fa-user me-2"></i>Nama Lengkap
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" required 
                                                   value="<?= htmlspecialchars($student['nama_siswa']) ?>"
                                                   placeholder="Masukkan nama lengkap siswa" maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="kelas" class="form-label">
                                                <i class="fas fa-graduation-cap me-2"></i>Kelas
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-select" id="kelas" name="kelas" required>
                                                <option value="">Pilih Kelas</option>
                                                <option value="X" <?= $student['kelas'] == 'X' ? 'selected' : '' ?>>X (Sepuluh)</option>
                                                <option value="XI" <?= $student['kelas'] == 'XI' ? 'selected' : '' ?>>XI (Sebelas)</option>
                                                <option value="XII" <?= $student['kelas'] == 'XII' ? 'selected' : '' ?>>XII (Duabelas)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="jurusan" class="form-label">
                                                <i class="fas fa-tools me-2"></i>Jurusan
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-select" id="jurusan" name="jurusan" required>
                                                <option value="">Pilih Jurusan</option>
                                                <option value="RPL" <?= $student['jurusan'] == 'RPL' ? 'selected' : '' ?>>Rekayasa Perangkat Lunak (RPL)</option>
                                                <option value="TKJ" <?= $student['jurusan'] == 'TKJ' ? 'selected' : '' ?>>Teknik Komputer dan Jaringan (TKJ)</option>
                                                <option value="MM" <?= $student['jurusan'] == 'MM' ? 'selected' : '' ?>>Multimedia (MM)</option>
                                                <option value="OTKP" <?= $student['jurusan'] == 'OTKP' ? 'selected' : '' ?>>Otomatisasi dan Tata Kelola Perkantoran (OTKP)</option>
                                                <option value="AKL" <?= $student['jurusan'] == 'AKL' ? 'selected' : '' ?>>Akuntansi dan Keuangan Lembaga (AKL)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="form-group mb-4">
                                    <label for="kontak" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Nomor Kontak
                                    </label>
                                    <input type="text" class="form-control" id="kontak" name="kontak" 
                                           value="<?= htmlspecialchars($student['kontak']) ?>"
                                           placeholder="Contoh: 081234567890" maxlength="20">
                                    <div class="form-text">Nomor HP atau telepon (opsional)</div>
                                </div>

                                <!-- Form Actions -->
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>
                                        Perbarui Data
                                    </button>
                                    <a href="index.php" class="btn btn-light-custom">
                                        <i class="fas fa-times me-2"></i>
                                        Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Student Information Panel -->
                    <div class="data-table-container">
                        <div class="table-header">
                            <h6 class="table-title">
                                <i class="fas fa-user me-2"></i>
                                Data Saat Ini
                            </h6>
                        </div>
                        <div class="table-content">
                            <div class="current-data">
                                <div class="data-row">
                                    <span class="data-label">NIS:</span>
                                    <span class="data-value"><?= htmlspecialchars($student['nis']) ?></span>
                                </div>
                                <div class="data-row">
                                    <span class="data-label">Nama:</span>
                                    <span class="data-value"><?= htmlspecialchars($student['nama_siswa']) ?></span>
                                </div>
                                <div class="data-row">
                                    <span class="data-label">Kelas:</span>
                                    <span class="data-value">
                                        <span class="badge bg-primary"><?= htmlspecialchars($student['kelas']) ?></span>
                                    </span>
                                </div>
                                <div class="data-row">
                                    <span class="data-label">Jurusan:</span>
                                    <span class="data-value">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($student['jurusan']) ?></span>
                                    </span>
                                </div>
                                <div class="data-row">
                                    <span class="data-label">Kontak:</span>
                                    <span class="data-value"><?= htmlspecialchars($student['kontak']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Panel -->
                    <div class="data-table-container mt-3">
                        <div class="table-header">
                            <h6 class="table-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi
                            </h6>
                        </div>
                        <div class="table-content">
                            <div class="info-alert">
                                <div class="alert-icon">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div class="alert-content">
                                    <h6>Petunjuk Edit:</h6>
                                    <ul>
                                        <li>Pastikan NIS tidak sama dengan siswa lain</li>
                                        <li>Nama harus sesuai dengan data resmi</li>
                                        <li>Periksa kelas dan jurusan dengan teliti</li>
                                        <li>Update kontak jika ada perubahan</li>
                                        <li>Semua field bertanda (*) wajib diisi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Status -->
                    <div class="data-table-container mt-3">
                        <div class="table-header">
                            <h6 class="table-title">
                                <i class="fas fa-activity me-2"></i>
                                Status Aktivitas
                            </h6>
                        </div>
                        <div class="table-content">
                            <?php
                            // Check borrowing status
                            $borrow_check = $conn->prepare("SELECT COUNT(*) as active FROM peminjaman WHERE id_siswa = ? AND status_pinjam = 'Dipinjam'");
                            $borrow_check->bind_param("i", $id);
                            $borrow_check->execute();
                            $active_loans = $borrow_check->get_result()->fetch_assoc()['active'];
                            
                            $total_loans = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE id_siswa = ?");
                            $total_loans->bind_param("i", $id);
                            $total_loans->execute();
                            $total_borrowed = $total_loans->get_result()->fetch_assoc()['total'];
                            ?>
                            <div class="stat-row">
                                <span class="stat-label">Status:</span>
                                <span class="stat-value">
                                    <?php if ($active_loans > 0): ?>
                                        <span class="status-badge status-warning">Sedang Meminjam</span>
                                    <?php else: ?>
                                        <span class="status-badge status-success">Tersedia</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Sedang Dipinjam:</span>
                                <span class="stat-value text-warning"><?= $active_loans ?> buku</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Total Riwayat:</span>
                                <span class="stat-value text-info"><?= $total_borrowed ?> transaksi</span>
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
            const form = document.getElementById('editStudentForm');
            const nisInput = document.getElementById('nis');
            const namaInput = document.getElementById('nama_siswa');

            // Format NIS input
            nisInput.addEventListener('input', function() {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Format nama input
            namaInput.addEventListener('input', function() {
                // Capitalize first letter of each word
                this.value = this.value.replace(/\w\S*/g, function(txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                });
            });

            // Format kontak input
            const kontakInput = document.getElementById('kontak');
            kontakInput.addEventListener('input', function() {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                const nis = nisInput.value.trim();
                const nama = namaInput.value.trim();
                const kelas = document.getElementById('kelas').value;
                const jurusan = document.getElementById('jurusan').value;

                if (nis.length < 3) {
                    e.preventDefault();
                    alert('NIS minimal 3 digit');
                    nisInput.focus();
                    return;
                }

                if (nama.length < 3) {
                    e.preventDefault();
                    alert('Nama siswa minimal 3 karakter');
                    namaInput.focus();
                    return;
                }

                if (!kelas) {
                    e.preventDefault();
                    alert('Pilih kelas siswa');
                    document.getElementById('kelas').focus();
                    return;
                }

                if (!jurusan) {
                    e.preventDefault();
                    alert('Pilih jurusan siswa');
                    document.getElementById('jurusan').focus();
                    return;
                }
            });
        });
    </script>
</body>
</html>
