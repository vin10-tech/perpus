
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

if ($_POST) {
    $nis = $_POST['nis'];
    $nama_siswa = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $kontak = $_POST['kontak'];
    
    // Check if NIS already exists
    $check_nis = $conn->prepare("SELECT COUNT(*) as count FROM siswa WHERE nis = ?");
    $check_nis->bind_param("s", $nis);
    $check_nis->execute();
    $nis_exists = $check_nis->get_result()->fetch_assoc()['count'] > 0;
    
    if ($nis_exists) {
        $error = 'NIS sudah terdaftar. Silakan gunakan NIS lain.';
    } else {
        $stmt = $conn->prepare("INSERT INTO siswa (nis, nama_siswa, kelas, jurusan, kontak) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nis, $nama_siswa, $kelas, $jurusan, $kontak);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=Siswa berhasil ditambahkan');
            exit();
        } else {
            $error = 'Gagal menambah siswa: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Perpustakaan SMK YMIK Jakarta</title>
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
                            <i class="fas fa-user-plus me-3"></i>
                            Tambah Siswa
                        </h1>
                        <p class="welcome-subtitle">
                            Tambahkan siswa baru ke sistem perpustakaan
                        </p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar me-2"></i>
                                <?= date('d F Y') ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user me-2"></i>
                                <?= htmlspecialchars($_SESSION['nama_petugas']) ?>
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
                                Form Tambah Siswa
                            </h3>
                        </div>
                        
                        <div class="table-content">
                            <form method="POST" id="studentForm">
                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="nis" class="form-label">
                                                <i class="fas fa-id-card me-2"></i>NIS
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nis" name="nis" required 
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
                                                <option value="X">X (Sepuluh)</option>
                                                <option value="XI">XI (Sebelas)</option>
                                                <option value="XII">XII (Duabelas)</option>
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
                                                <option value="RPL">Rekayasa Perangkat Lunak (RPL)</option>
                                                <option value="TKJ">Teknik Komputer dan Jaringan (TKJ)</option>
                                                <option value="MM">Multimedia (MM)</option>
                                                <option value="OTKP">Otomatisasi dan Tata Kelola Perkantoran (OTKP)</option>
                                                <option value="AKL">Akuntansi dan Keuangan Lembaga (AKL)</option>
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
                                           placeholder="Contoh: 081234567890" maxlength="20">
                                    <div class="form-text">Nomor HP atau telepon (opsional)</div>
                                </div>

                                <!-- Form Actions -->
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Siswa
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
                    <!-- Information Panel -->
                    <div class="data-table-container">
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
                                    <h6>Petunjuk Pengisian:</h6>
                                    <ul>
                                        <li>NIS harus unik dan tidak boleh sama dengan siswa lain</li>
                                        <li>Pastikan nama lengkap sesuai dengan data resmi sekolah</li>
                                        <li>Pilih kelas dan jurusan dengan benar</li>
                                        <li>Nomor kontak opsional namun sangat disarankan untuk diisi</li>
                                        <li>Semua field bertanda (*) wajib diisi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Statistics -->
                    <div class="data-table-container mt-3">
                        <div class="table-header">
                            <h6 class="table-title">
                                <i class="fas fa-chart-bar me-2"></i>
                                Statistik Siswa
                            </h6>
                        </div>
                        <div class="table-content">
                            <?php
                            $total_siswa = $conn->query("SELECT COUNT(*) as total FROM siswa")->fetch_assoc()['total'];
                            $siswa_aktif = $conn->query("SELECT COUNT(DISTINCT s.id_siswa) as total FROM siswa s JOIN peminjaman p ON s.id_siswa = p.id_siswa WHERE p.status_pinjam = 'Dipinjam'")->fetch_assoc()['total'];
                            ?>
                            <div class="stat-row">
                                <span class="stat-label">Total Siswa Terdaftar:</span>
                                <span class="stat-value text-primary"><?= number_format($total_siswa) ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Sedang Meminjam:</span>
                                <span class="stat-value text-warning"><?= number_format($siswa_aktif) ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Siswa Tersedia:</span>
                                <span class="stat-value text-success"><?= number_format($total_siswa - $siswa_aktif) ?></span>
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
            const form = document.getElementById('studentForm');
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
