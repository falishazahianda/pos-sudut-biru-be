USE sudut_biru_pos;

INSERT INTO users (kode_staf, nama_lengkap, username, email, password, role, status, cabang) VALUES
('FA1707', 'Feylisha Azzahra', 'feylisha.admin', 'admin@sudutbiru.id', '123456781010', 'admin', 'aktif', 'Sudut Biru / Pondok Labu'),
('DS1708', 'Dimas Saputra', 'dimas.kasir', 'kasir@sudutbiru.id', '123456781010', 'kasir', 'aktif', 'Sudut Biru / Pondok Labu'),
('AL1709', 'Ayu Lestari', 'ayu.kasir', 'ayu@sudutbiru.id', '123456781010', 'kasir', 'aktif', 'Sudut Biru / Pondok Labu');

INSERT INTO menus (kode_menu, nama_menu, kategori, harga, tipe_stok, jumlah_stok, status_bahan, is_favorit) VALUES
('BR-001', 'Cappuccino', 'Coffee', 28000, 'resep', 0, 'tersedia', 1),
('BR-002', 'Americano', 'Coffee', 22000, 'resep', 0, 'tersedia', 1),
('BR-003', 'Cafe latte', 'Coffee', 30000, 'resep', 0, 'tersedia', 1),
('BR-004', 'Matcha latte', 'Non-Coffee', 32000, 'resep', 0, 'tersedia', 1),
('BR-005', 'Butter croissant', 'Bakery', 20000, 'pcs', 8, 'tersedia', 1),
('BR-006', 'Chocolate', 'Non-Coffee', 30000, 'resep', 0, 'habis', 1);