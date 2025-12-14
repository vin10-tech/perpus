
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$message = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama_petugas = $_POST['nama_petugas'];
    
    // Check if username already exists
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM petugas WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $count = $check_result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = '<div class="alert alert-danger">Username sudah digunakan!</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO petugas (username, password, nama_petugas) VALUES (?, MD5(?), ?)");
        $stmt->bind_param("sss", $username, $password, $nama_petugas);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=Petugas berhasil ditambahkan');
            exit();
        } else {
            $message = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Petugas - Perpustakaan SMK YMIK Jakarta</title>
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
                            Tambah Petugas
                        </h1>
                        <p class="welcome-subtitle">
                            Tambahkan petugas baru untuk sistem perpustakaan
                        </p>
                    </div>
                    <div class="welcome-actions">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <?= $message ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-user-plus me-2"></i>
                                Form Tambah Petugas
                            </h3>
                        </div>
                        <div class="p-4">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                    <div class="form-text">Username harus unik dan tidak boleh sama dengan yang sudah ada</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">Gunakan password yang kuat untuk keamanan</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nama_petugas" class="form-label">Nama Petugas *</label>
                                    <input type="text" class="form-control" id="nama_petugas" name="nama_petugas" required>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>Simpan Petugas
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="data-table-container">
                        <div class="table-header">
                            <h3 class="table-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Petunjuk:</h6>
                                <ul class="mb-0 small">
                                    <li>Username harus unik</li>
                                    <li>Password minimal 6 karakter</li>
                                    <li>Nama petugas akan ditampilkan di sistem</li>
                                    <li>Petugas baru akan mendapat akses penuh ke sistem</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">Keamanan:</h6>
                                <ul class="mb-0 small">
                                    <li>Jangan bagikan password kepada siapa pun</li>
                                    <li>Gunakan kombinasi huruf dan angka</li>
                                    <li>Ganti password secara berkala</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
