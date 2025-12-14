
<div class="modern-sidebar">
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' && !strpos($_SERVER['REQUEST_URI'], '/') ? 'active' : '' ?>" href="/">
                    <div class="nav-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="nav-text">Dashboard</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/buku/') !== false ? 'active' : '' ?>" href="/buku/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <span class="nav-text">Manajemen Buku</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/siswa/') !== false ? 'active' : '' ?>" href="/siswa/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="nav-text">Manajemen Siswa</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/peminjaman/') !== false ? 'active' : '' ?>" href="/peminjaman/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <span class="nav-text">Peminjaman</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/pengembalian/') !== false ? 'active' : '' ?>" href="/pengembalian/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <span class="nav-text">Pengembalian</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/denda/') !== false ? 'active' : '' ?>" href="/denda/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <span class="nav-text">Denda</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/laporan/') !== false ? 'active' : '' ?>" href="/laporan/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span class="nav-text">Laporan</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/petugas/') !== false ? 'active' : '' ?>" href="/petugas/index.php">
                    <div class="nav-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span class="nav-text">Manajemen Petugas</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-divider"></div>
        
        <ul class="nav-list nav-secondary">
            <li class="nav-item">
                <a class="nav-link text-muted" href="/help.php">
                    <div class="nav-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <span class="nav-text">Bantuan</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name"><?= $_SESSION['nama_petugas'] ?? 'Admin' ?></div>
                <div class="user-role">Petugas</div>
            </div>
        </div>
    </div>
    <a class="nav-link" href="<?= BASE_URL ?>/index.php">Dashboard</a>
    <a class="nav-link" href="<?= BASE_URL ?>/buku/index.php">Manajemen Buku</a>
    <a class="nav-link" href="<?= BASE_URL ?>/siswa/index.php">Manajemen Siswa</a>
</div>
