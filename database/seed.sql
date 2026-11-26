USE sudut_biru_pos;
-- =====================================================
-- 1. USERS
-- Akun awal Admin dan Kasir
-- Default password testing: 123456781010
-- =====================================================

INSERT INTO users (
    staff_code,
    name,
    username,
    email,
    password_hash,
    role,
    is_active
) VALUES
(
    'FA1707',
    'Feylisha Azzahra',
    'feylisha.admin',
    'admin@sudutbiru.id',
    '$2y$12$kKm6LQVTfi8Jqfv5/tFUVuvRGe.vLXZ47NzdJR/iC/9dKBHdlOkwC',
    'admin',
    1
),
(
    'DS1708',
    'Dimas Saputra',
    'dimas.kasir',
    'kasir@sudutbiru.id',
    '$2y$12$6pWqJpyx37CxcLCTapYzFe1yM8O4pl4rh7Xp5YP5kZmDmOzjYkPFi',
    'kasir',
    1
),
(
    'AL1709',
    'Ayu Lestari',
    'ayu.kasir',
    'ayu@sudutbiru.id',
    '$2y$12$uX1vMwmVwNxGPxGaLPS34u/WVPCImnjoyKKFSAJrfCDuHk9WHq4cK',
    'kasir',
    1
);

-- =====================================================
-- 2. MENUS
-- Daftar menu awal Sudut Biru
-- =====================================================

INSERT INTO menus (
    kode_menu,
    nama_menu,
    kategori,
    harga,
    is_favorit,
    is_active,
    allow_sugar_modifier,
    allow_ice_modifier
) VALUES
('BR-001', 'Cappuccino',      'Coffee',     28000, 1, 1, 1, 1),
('BR-002', 'Americano',       'Coffee',     22000, 1, 1, 1, 1),
('BR-003', 'Cafe latte',      'Coffee',     30000, 1, 1, 1, 1),
('BR-004', 'Matcha latte',    'Non-Coffee', 32000, 1, 1, 1, 1),
('BR-005', 'Butter croissant','Bakery',     20000, 1, 1, 0, 0),
('BR-006', 'Chocolate',       'Non-Coffee', 30000, 1, 1, 1, 1);


-- =====================================================
-- 3. INVENTORY ITEMS
-- Stok awal produk bakery
-- Bahan baku minuman ditambahkan setelah SOP tersedia
-- =====================================================

INSERT INTO inventory_items (
    nama_item,
    tipe,
    satuan,
    stok,
    minimum_stok,
    is_active
) VALUES
('Butter croissant', 'produk', 'pcs', 8, 5, 1);


-- =====================================================
-- 4. RECIPES
-- Satu croissant menggunakan satu pcs stok produk
-- =====================================================

INSERT INTO recipes (
    menu_id,
    inventory_item_id,
    jumlah_bahan,
    modifier_type
)
SELECT
    m.id,
    i.id,
    1,
    'none'
FROM menus m
JOIN inventory_items i
    ON i.nama_item = 'Butter croissant'
WHERE m.kode_menu = 'BR-005';

-- =====================================================

SELECT
    id,
    staff_code,
    name,
    username,
    email,
    role,
    is_active,
    shift_id,
    must_change_password,
    session_version,
    LENGTH(password_hash) AS hash_length
FROM users;

use sudut_biru_pos;
DELETE FROM users
WHERE id = 5;
