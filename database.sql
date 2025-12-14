
-- Database: perpustakaan_smk_ymik
CREATE DATABASE IF NOT EXISTS perpustakaan_smk_ymik;
USE perpustakaan_smk_ymik;

-- Table: petugas
CREATE TABLE petugas (
    id_petugas INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_petugas VARCHAR(100) NOT NULL
);

-- Table: siswa
CREATE TABLE siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20) UNIQUE NOT NULL,
    nama_siswa VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,
    kontak VARCHAR(20)
);

-- Table: buku
CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    stok INT DEFAULT 0
);

-- Table: peminjaman
CREATE TABLE peminjaman (
    id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    id_petugas INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    status_pinjam ENUM('Dipinjam', 'Dikembalikan') DEFAULT 'Dipinjam',
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa),
    FOREIGN KEY (id_petugas) REFERENCES petugas(id_petugas)
);

-- Table: detail_pinjam
CREATE TABLE detail_pinjam (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pinjam INT NOT NULL,
    id_buku INT NOT NULL,
    jumlah INT DEFAULT 1,
    FOREIGN KEY (id_pinjam) REFERENCES peminjaman(id_pinjam) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
);

-- Table: pengembalian
CREATE TABLE pengembalian (
    id_kembali INT AUTO_INCREMENT PRIMARY KEY,
    id_pinjam INT NOT NULL,
    tanggal_kembali DATE NOT NULL,
    telat INT DEFAULT 0,
    denda INT DEFAULT 0,
    status_denda ENUM('Belum Lunas', 'Lunas') DEFAULT 'Lunas',
    FOREIGN KEY (id_pinjam) REFERENCES peminjaman(id_pinjam)
);

-- Insert default admin
INSERT INTO petugas (username, password, nama_petugas) VALUES 
('admin', MD5('admin123'), 'Administrator');

-- Sample data
INSERT INTO siswa (nis, nama_siswa, kelas, jurusan, kontak) VALUES
('2024001', 'Ahmad Rizki', 'XII', 'RPL', '081234567890'),
('2024002', 'Siti Nurhaliza', 'XI', 'TKJ', '081234567891'),
('2024003', 'Budi Santoso', 'X', 'MM', '081234567892');

INSERT INTO buku (judul, isbn, pengarang, penerbit, tahun_terbit, kategori, stok) VALUES
('Pemrograman PHP Dasar', '978-123-456-789-0', 'John Doe', 'Informatika', 2023, 'Teknologi', 5),
('Database MySQL', '978-123-456-789-1', 'Jane Smith', 'Andi', 2022, 'Teknologi', 3),
('Jaringan Komputer', '978-123-456-789-2', 'Bob Wilson', 'Elex Media', 2023, 'Teknologi', 4);
