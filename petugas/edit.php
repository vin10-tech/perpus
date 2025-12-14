
<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;
$message = '';

// Get petugas data
$stmt = $conn->prepare("SELECT * FROM petugas WHERE id_petugas = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$petugas = $result->fetch_assoc();

if (!$petugas) {
    header('Location: index.php?error=Petugas tidak ditemukan');
    exit();
}

if ($_POST) {
    $username = $_POST['username'];
    $nama_petugas = $_POST['nama_petugas'];
    $password = $_POST['password'];
    
    // Check if username already exists (except current user)
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM petugas WHERE username = ? AND id_petugas != ?");
    $check_stmt->bind_param("si", $username, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $count = $check_result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = '<div class="alert alert-danger">Username sudah digunakan oleh petugas lain!</div>';
    } else {
        if (!empty($password)) {
            // Update with new password
            $stmt = $conn->prepare("UPDATE petugas SET username=?, password=MD5(?), nama_petugas=? WHERE id_petugas=?");
            $stmt->bind_param("sssi", $username, $password, $nama_petugas, $id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE petugas SET username=?, nama_petugas=? WHERE id_petugas=?");
            $stmt->bind_param("ssi", $username, $nama_petugas, $id);
        }
        
        if ($stmt->execute()) {
            header('Location: index.php?success=Petugas berhasil diupdate');
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
    <title>Edit Petugas - Perpustakaan SMK YMIK Jakarta</title>
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
                            Edit Petugas
                        </h1>
                        <p class="welcome-subtitle">
                            Edit informasi petugas <?= htmlspecialchars($petugas['nama_petugas']) ?>
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
                                <i class="fas fa-user-edit me-2"></i>
                                Form Edit Petugas
                            </h3>
                        </div>
                        <div class="p-4">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($petugas['username']) ?>" required>
                                    <?php if ($petugas['username'] == 'admin'): ?>
                                        <div class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Hati-hati mengubah username admin
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nama_petugas" class="form-label">Nama Petugas *</label>
                                    <input type="text" class="form-control" id="nama_petugas" name="nama_petugas" value="<?= htmlspecialchars($petugas['nama_petugas']) ?>" required>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>Update Petugas
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
                                Informasi Petugas
                            </h3>
                        </div>
                        <div class="p-4">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>ID Petugas:</strong></td>
                                    <td><?= $petugas['id_petugas'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Username Saat Ini:</strong></td>
                                    <td><?= htmlspecialchars($petugas['username']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Saat Ini:</strong></td>
                                    <td><?= htmlspecialchars($petugas['nama_petugas']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>
                                        <?php if ($petugas['username'] == 'admin'): ?>
                                            <span class="badge bg-danger">Administrator</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Petugas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php if ($petugas['username'] == 'admin'): ?>
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">Peringatan!</h6>
                                <p class="mb-0 small">
                                    Ini adalah akun administrator utama. Berhati-hatilah saat mengubah data ini.
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
