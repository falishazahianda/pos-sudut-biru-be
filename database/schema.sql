-- =====================================================
-- POS SUDUT BIRU
-- Database Schema
-- Database: sudut_biru_pos
-- DBMS: MySQL / MariaDB
-- Total: 9 Tables
-- =====================================================

CREATE DATABASE IF NOT EXISTS sudut_biru_pos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sudut_biru_pos;
-- =====================================================
-- 1. USERS
-- Akun Admin dan Kasir
-- =====================================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,

    role ENUM('admin', 'kasir')
        NOT NULL DEFAULT 'kasir',

    is_active TINYINT(1) NOT NULL DEFAULT 1,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    session_version INT UNSIGNED NOT NULL DEFAULT 1,

    shift_id INT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;

-- =====================================================
-- 2. PASSWORD RESETS
-- Token pemulihan password Admin dan Kasir
-- =====================================================

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reset_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

) ENGINE=InnoDB;


-- =====================================================
-- 3. SHIFTS
-- Pencatatan dan riwayat shift kasir
-- =====================================================

CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NULL,

    status ENUM('aktif', 'selesai')
        NOT NULL DEFAULT 'aktif',

    INDEX idx_shift_user_tanggal (user_id, tanggal),

    CONSTRAINT fk_shift_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT

) ENGINE=InnoDB;


-- Relasi shift aktif pengguna.
-- Ditambahkan setelah tabel shifts tersedia
-- karena users dan shifts saling berhubungan.

ALTER TABLE users
    ADD CONSTRAINT fk_user_current_shift
        FOREIGN KEY (shift_id)
        REFERENCES shifts(id)
        ON DELETE SET NULL;


-- =====================================================
-- 4. MENUS
-- Master menu Coffee, Non-Coffee, dan Bakery
-- =====================================================

CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_menu VARCHAR(20) NOT NULL UNIQUE,
    nama_menu VARCHAR(100) NOT NULL,

    kategori ENUM('Coffee', 'Non-Coffee', 'Bakery')
        NOT NULL,

    harga INT UNSIGNED NOT NULL,
    foto VARCHAR(255) NULL,

    is_favorit TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,

    allow_sugar_modifier TINYINT(1) NOT NULL DEFAULT 0,
    allow_ice_modifier TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB;


-- =====================================================
-- 5. INVENTORY ITEMS
-- Stok bahan baku dan produk bakery
-- =====================================================

CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_item VARCHAR(100) NOT NULL UNIQUE,

    tipe ENUM('bahan', 'produk') NOT NULL,
    satuan ENUM('g', 'ml', 'pcs') NOT NULL,

    stok DECIMAL(14,3) NOT NULL DEFAULT 0,
    minimum_stok DECIMAL(14,3) NOT NULL DEFAULT 0,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CHECK (stok >= 0),
    CHECK (minimum_stok >= 0)

) ENGINE=InnoDB;


-- =====================================================
-- 6. RECIPES
-- Komposisi bahan berdasarkan SOP setiap menu
-- =====================================================

CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    inventory_item_id INT NOT NULL,

    jumlah_bahan DECIMAL(14,3) NOT NULL,

    modifier_type ENUM('none', 'sugar', 'ice')
        NOT NULL DEFAULT 'none',

    UNIQUE KEY uq_recipe_item (
        menu_id,
        inventory_item_id
    ),

    CONSTRAINT fk_recipe_menu
        FOREIGN KEY (menu_id)
        REFERENCES menus(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_recipe_inventory
        FOREIGN KEY (inventory_item_id)
        REFERENCES inventory_items(id)
        ON DELETE RESTRICT,

    CHECK (jumlah_bahan > 0)

) ENGINE=InnoDB;


-- =====================================================
-- 7. TRANSACTIONS
-- Transaksi pembayaran QRIS dan Tunai
-- =====================================================

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_pesanan VARCHAR(20) NOT NULL UNIQUE,

    shift_id INT NULL,
    user_id INT NOT NULL,
    cashier_name VARCHAR(100) NOT NULL,

    total_harga BIGINT UNSIGNED NOT NULL,

    metode_bayar ENUM('QRIS', 'Tunai') NOT NULL,

    uang_diterima BIGINT UNSIGNED NOT NULL DEFAULT 0,
    kembalian BIGINT UNSIGNED NOT NULL DEFAULT 0,

    status ENUM(
        'menunggu_pembayaran',
        'lunas',
        'batal'
    ) NOT NULL DEFAULT 'menunggu_pembayaran',

    paid_at DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_transaction_status_paid (status, paid_at),

    CONSTRAINT fk_transaction_shift
        FOREIGN KEY (shift_id)
        REFERENCES shifts(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_transaction_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT

) ENGINE=InnoDB;


-- =====================================================
-- 8. TRANSACTION ITEMS
-- Detail pesanan, modifier, dan catatan barista
-- =====================================================

CREATE TABLE transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,

    transaction_id INT NOT NULL,
    menu_id INT NOT NULL,

    nama_menu VARCHAR(100) NOT NULL,

    qty INT UNSIGNED NOT NULL,
    harga_satuan INT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,

    sugar_level ENUM('no', 'less', 'normal') NULL,
    ice_level ENUM('no', 'less', 'normal') NULL,

    catatan_barista VARCHAR(64) NULL,

    CONSTRAINT fk_item_transaction
        FOREIGN KEY (transaction_id)
        REFERENCES transactions(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_transaction_item_menu
        FOREIGN KEY (menu_id)
        REFERENCES menus(id)
        ON DELETE RESTRICT,

    CHECK (qty > 0)

) ENGINE=InnoDB;


-- =====================================================
-- 9. STOCK MOVEMENTS
-- Riwayat restock, penjualan, dan penyesuaian stok
-- =====================================================

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,

    inventory_item_id INT NOT NULL,
    user_id INT NOT NULL,
    transaction_id INT NULL,

    tipe_perubahan ENUM(
        'restock',
        'penjualan',
        'penyesuaian'
    ) NOT NULL,

    jumlah_perubahan DECIMAL(14,3) NOT NULL,
    stok_akhir DECIMAL(14,3) NOT NULL,

    supplier VARCHAR(150) NULL,
    keterangan VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movement_inventory
        FOREIGN KEY (inventory_item_id)
        REFERENCES inventory_items(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_movement_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_movement_transaction
        FOREIGN KEY (transaction_id)
        REFERENCES transactions(id)
        ON DELETE RESTRICT,

    CHECK (stok_akhir >= 0),
    CHECK (jumlah_perubahan <> 0)

) ENGINE=InnoDB;


-- =====================================================
-- END OF DATABASE SCHEMA
-- =====================================================

UPDATE users
SET email = 'azzahrafeylisha@gmail.com'
WHERE username = 'FA1707';