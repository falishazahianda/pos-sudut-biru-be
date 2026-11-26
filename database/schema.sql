CREATE DATABASE IF NOT EXISTS sudut_biru_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sudut_biru_pos;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_staf VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir',
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    cabang VARCHAR(100) DEFAULT 'Sudut Biru / Pondok Labu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
);

CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NULL,
    status ENUM('aktif', 'selesai') NOT NULL DEFAULT 'aktif',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_menu VARCHAR(20) NOT NULL UNIQUE,
    nama_menu VARCHAR(100) NOT NULL,
    kategori ENUM('Coffee', 'Non-Coffee', 'Bakery') NOT NULL,
    harga INT NOT NULL,
    foto VARCHAR(255) NULL,
    tipe_stok ENUM('resep', 'pcs') NOT NULL DEFAULT 'resep',
    jumlah_stok INT NOT NULL DEFAULT 0,
    status_bahan ENUM('tersedia', 'habis') NOT NULL DEFAULT 'tersedia',
    is_favorit TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE inventory_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    user_id INT NOT NULL,
    tipe_perubahan ENUM('restock', 'penjualan', 'penyesuaian') NOT NULL,
    jumlah_perubahan INT NOT NULL,
    stok_akhir INT NOT NULL,
    keterangan VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_pesanan VARCHAR(20) NOT NULL UNIQUE,
    shift_id INT NULL,
    user_id INT NOT NULL,
    total_harga INT NOT NULL,
    metode_bayar ENUM('QRIS', 'Tunai') NOT NULL,
    uang_diterima INT NOT NULL DEFAULT 0,
    kembalian INT NOT NULL DEFAULT 0,
    status ENUM('menunggu_pembayaran', 'lunas', 'batal') NOT NULL DEFAULT 'lunas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    menu_id INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan INT NOT NULL,
    catatan_barista VARCHAR(64) NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);