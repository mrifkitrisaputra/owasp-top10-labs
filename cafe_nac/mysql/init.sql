-- =============================================
-- NAC CAFE DATABASE INITIALIZATION
-- =============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100),
    `full_name` VARCHAR(100),
    `role` VARCHAR(20) DEFAULT 'guest',
    `avatar` VARCHAR(255) DEFAULT 'default.png',
    `bio` TEXT,
    `staff_notes` TEXT,
    `loyalty_points` INT DEFAULT 100,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Articles table
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `excerpt` TEXT,
    `author_id` INT,
    `category` VARCHAR(50),
    `image` VARCHAR(255),
    `views` INT DEFAULT 0,
    `published` TINYINT(1) DEFAULT 1,
    `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments table
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT,
    `user_id` INT,
    `username` VARCHAR(50),
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menu items table
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2),
    `category` VARCHAR(50),
    `image` VARCHAR(255),
    `available` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Secret menu table (Flag 2 - SQL Injection target)
CREATE TABLE IF NOT EXISTS `secret_menu` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_name` VARCHAR(100) NOT NULL,
    `recipe_code` VARCHAR(255),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings table
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100),
    `email` VARCHAR(100),
    `subject` VARCHAR(255),
    `message` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Loyalty transactions table
CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `points` INT,
    `type` VARCHAR(20),
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INSERT DEFAULT DATA
-- =============================================

-- Users (passwords are MD5 hashed for intentional weakness)
-- admin/4dm1nNAC2024! (not given to players)
-- guest/guest123 (given to players)
-- editor/editor123
-- Employee accounts
INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `bio`, `staff_notes`, `loyalty_points`) VALUES
(1, 'admin', MD5('4dm1nNAC2024!'), 'admin@naccafe.id', 'Administrator', 'admin', 'System Administrator NAC Cafe', 'Master account. Revenue dashboard access. Total pendapatan kotor tahun ini ada di halaman admin.', 500),
(2, 'guest', MD5('guest123'), 'guest@naccafe.id', 'Guest User', 'guest', 'Regular visitor of NAC Cafe', NULL, 100),
(3, 'editor', MD5('editor123'), 'editor@naccafe.id', 'Sarah Wijaya', 'editor', 'Content editor for NAC Cafe news portal. Loves specialty coffee.', 'Editor akses. Bisa publish artikel tapi tidak bisa akses financial.', 250),
(4, 'barista_andi', MD5('kopiHitam99'), 'andi@naccafe.id', 'Andi Pratama', 'staff', 'Head barista with 5 years of experience in specialty coffee.', 'Barista senior. Gaji Rp 5.500.000/bulan. Kontrak sampai 2025.', 180),
(5, 'warehouse_budi', MD5('gudangAman1'), 'budi@naccafe.id', 'Budi Santoso', 'staff', 'Warehouse and supply chain manager.', 'Alamat gudang utama: jalan_merdeka_45_bogor. Stok kopi arabika tinggal 30kg. Perlu order ulang dari supplier minggu depan. Laporan inventori ada di admin panel.', 120),
(6, 'finance_citra', MD5('keuanganRhs'), 'citra@naccafe.id', 'Citra Dewi', 'staff', 'Financial analyst handling cafe accounting.', 'Penanggung jawab laporan keuangan. Encrypted backup di /admin/backup.php. Password enkripsi gunakan API key internal.', 300),
(7, 'deleted_user', MD5('yourAccountDeleted'), 'deleted@naccafe.id', 'User Dihapus', 'disabled', 'Account has been deactivated.', 'Akun ini sudah dinonaktifkan per tanggal 1 Maret 2024.', 0);

-- Site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'NAC Cafe'),
('site_tagline', 'Berita & Cerita Seputar Kopi Nusantara'),
('original_name', 'warung_kopi_nusantara'),
('established_year', '2019'),
('owner_email', 'admin@naccafe.id'),
('contact_phone', '+62 812-3456-7890'),
('address', 'Jl. Sudirman No. 123, Jakarta Selatan'),
('social_instagram', '@naccafe.id'),
('social_twitter', '@naccafe_id'),
('maintenance_mode', '0');

-- Articles
INSERT INTO `articles` (`id`, `title`, `slug`, `content`, `excerpt`, `author_id`, `category`, `image`, `views`, `published_at`) VALUES
(1, 'Selamat Datang di NAC Cafe', 'selamat-datang-di-nac-cafe',
'<p>NAC Cafe hadir sebagai ruang baru bagi para pecinta kopi di Indonesia. Berdiri sejak tahun 2019, kami telah melayani ribuan pelanggan dengan kopi terbaik dari berbagai penjuru Nusantara.</p>
<p>Kami percaya bahwa setiap cangkir kopi memiliki cerita. Dari proses penanaman di dataran tinggi Gayo, hingga penyajian di meja Anda, setiap langkah kami perhatikan dengan seksama.</p>
<p>Kunjungi kami dan rasakan pengalaman ngopi yang berbeda. Tim barista kami yang berpengalaman siap meracik kopi sesuai selera Anda.</p>
<p>Kami juga menyediakan berbagai menu makanan ringan yang cocok menemani waktu ngopi Anda. Dari pastry segar hingga sandwich premium, semuanya disiapkan dengan bahan berkualitas.</p>',
'NAC Cafe hadir sebagai ruang baru bagi para pecinta kopi di Indonesia.', 1, 'Berita', 'welcome.jpg', 1250, '2024-01-15 08:00:00'),

(2, 'Mengenal Kopi Arabika Gayo: Kebanggaan Aceh', 'mengenal-kopi-arabika-gayo',
'<p>Kopi Arabika Gayo berasal dari dataran tinggi Gayo, Aceh. Ditanam pada ketinggian 1.200-1.600 meter di atas permukaan laut, kopi ini memiliki karakter rasa yang unik dan kompleks.</p>
<p>Proses pengolahan yang masih tradisional menggunakan metode wet-hulling atau yang dikenal sebagai "giling basah" memberikan body yang tebal dan aroma earthy yang khas.</p>
<p>Di NAC Cafe, kami menggunakan biji kopi Gayo grade 1 yang dipilih langsung dari petani lokal. Setiap batch kami uji kualitasnya sebelum disajikan kepada pelanggan.</p>
<p>Kami bekerja sama dengan beberapa supplier terpercaya untuk memastikan pasokan biji kopi berkualitas. Detail informasi supplier kami simpan secara internal untuk menjaga kerahasiaan bisnis.</p>
<p>Fun fact: Tim kami pernah mengunjungi langsung perkebunan di Takengon untuk memastikan kualitas biji kopi yang kami gunakan. Perjalanan selama 3 hari itu sangat berkesan!</p>',
'Kopi Arabika Gayo berasal dari dataran tinggi Gayo, Aceh, dengan karakter rasa unik dan kompleks.', 3, 'Edukasi', 'gayo.jpg', 890, '2024-02-10 10:30:00'),

(3, 'Tips Menyeduh Kopi di Rumah dengan V60', 'tips-menyeduh-kopi-v60',
'<p>V60 adalah salah satu metode pour-over yang paling populer di kalangan pecinta kopi. Metode ini menghasilkan kopi dengan clarity tinggi, memperlihatkan karakter asli dari biji kopi.</p>
<p><strong>Yang Anda butuhkan:</strong></p>
<ul>
<li>V60 dripper dan filter paper</li>
<li>Timbangan digital</li>
<li>Gooseneck kettle</li>
<li>15 gram kopi (medium-fine grind)</li>
<li>250ml air (92-96°C)</li>
</ul>
<p><strong>Langkah-langkah:</strong></p>
<ol>
<li>Bilas filter paper dengan air panas</li>
<li>Masukkan kopi, ratakan permukaannya</li>
<li>Blooming: tuang 30ml air, tunggu 30 detik</li>
<li>Tuang sisa air secara perlahan dengan gerakan spiral</li>
<li>Total waktu seduh: 2:30 - 3:00 menit</li>
</ol>
<p>Setiap barista kami menguasai teknik ini. Anda bisa bertanya langsung kepada mereka saat berkunjung ke NAC Cafe.</p>',
'V60 adalah salah satu metode pour-over yang paling populer di kalangan pecinta kopi.', 3, 'Tips', 'v60.jpg', 673, '2024-03-05 14:00:00'),

(4, 'NAC Cafe Raih Penghargaan Kafe Terbaik 2024', 'nac-cafe-raih-penghargaan-2024',
'<p>Dengan bangga kami umumkan bahwa NAC Cafe telah meraih penghargaan "Kafe Terbaik 2024" dalam ajang Indonesian Coffee Awards yang diselenggarakan di Jakarta Convention Center.</p>
<p>Penghargaan ini merupakan hasil kerja keras seluruh tim NAC Cafe, mulai dari barista, staff dapur, hingga tim manajemen. Kami berterima kasih kepada seluruh pelanggan yang telah mendukung kami.</p>
<p>Dalam penjurian, NAC Cafe mendapat nilai tinggi dalam kategori kualitas kopi, pelayanan, dan suasana. Juri juga mengapresiasi program loyalitas pelanggan kami yang inovatif.</p>
<p>"NAC Cafe berhasil menciptakan ekosistem kopi yang lengkap. Dari sourcing hingga serving, semuanya terkelola dengan baik," ujar ketua juri.</p>
<p>Sebagai bentuk perayaan, kami menawarkan program loyalty points double selama bulan ini. Setiap pembelian akan mendapat poin dua kali lipat yang bisa ditukarkan dengan berbagai reward menarik.</p>',
'NAC Cafe telah meraih penghargaan Kafe Terbaik 2024 dalam ajang Indonesian Coffee Awards.', 1, 'Berita', 'award.jpg', 2100, '2024-04-20 09:00:00'),

(5, 'Proses Roasting: Dari Biji Hijau ke Cangkir Anda', 'proses-roasting-kopi',
'<p>Proses roasting adalah tahap krusial dalam perjalanan biji kopi. Di sinilah karakter rasa, aroma, dan body kopi terbentuk. NAC Cafe melakukan in-house roasting untuk menjamin kualitas.</p>
<p><strong>Tingkat Roasting:</strong></p>
<p><em>Light Roast:</em> Warna coklat muda, acidity tinggi, karakter origin menonjol. Cocok untuk single origin.</p>
<p><em>Medium Roast:</em> Keseimbangan antara acidity dan body. Pilihan populer di NAC Cafe.</p>
<p><em>Dark Roast:</em> Body tebal, rasa smoky dan bold. Cocok untuk espresso-based drinks.</p>
<p>Mesin roasting kami menggunakan teknologi terbaru yang bisa dikontrol secara presisi. Data roasting profile kami simpan secara digital untuk konsistensi.</p>
<p>Setiap minggu, tim quality control kami melakukan cupping session untuk memastikan setiap batch memenuhi standar NAC Cafe.</p>',
'Proses roasting adalah tahap krusial dalam perjalanan biji kopi dari biji hijau ke cangkir Anda.', 4, 'Edukasi', 'roasting.jpg', 567, '2024-05-12 11:15:00'),

(6, 'Menu Baru: Koleksi Musim Hujan 2024', 'menu-baru-musim-hujan-2024',
'<p>Memasuki musim hujan, NAC Cafe menghadirkan koleksi menu spesial yang cocok untuk menemani hari-hari Anda. Tiga menu baru yang kami perkenalkan:</p>
<p><strong>1. Jahe Susu Latte</strong> - Perpaduan espresso, susu segar, dan ekstrak jahe merah. Menghangatkan badan di cuaca dingin.</p>
<p><strong>2. Cokelat Rempah</strong> - Hot chocolate dengan sentuhan kayu manis, cengkeh, dan pala. Terinspirasi dari wedang ronde.</p>
<p><strong>3. Matcha Aren</strong> - Matcha premium grade dari Uji, Jepang, dipadukan dengan gula aren asli Banten.</p>
<p>Selain menu baru, kami juga memiliki beberapa resep rahasia yang hanya diketahui oleh barista senior. Menu rahasia ini tidak tercantum di daftar menu reguler dan hanya bisa didapatkan jika Anda tahu cara memesannya.</p>
<p>Penasaran? Coba tanyakan kepada barista kami tentang "menu yang tidak ada di daftar" ;)</p>',
'NAC Cafe menghadirkan koleksi menu spesial musim hujan 2024 yang cocok menemani hari-hari Anda.', 3, 'Menu', 'rainy-season.jpg', 445, '2024-06-01 08:30:00'),

(7, 'Kunjungan ke Perkebunan Kopi Toraja', 'kunjungan-perkebunan-toraja',
'<p>Tim NAC Cafe baru saja menyelesaikan kunjungan ke perkebunan kopi di Toraja, Sulawesi Selatan. Perjalanan ini merupakan bagian dari program kami untuk menjalin hubungan langsung dengan petani kopi.</p>
<p>Kopi Toraja terkenal dengan body yang berat, acidity rendah, dan aroma earthy yang khas. Ditanam di ketinggian 1.400-1.800 mdpl, biji kopi ini memiliki karakter yang sangat berbeda dari kopi Gayo.</p>
<p>Selama kunjungan, kami bertemu dengan Pak Markus, petani kopi generasi ketiga yang telah mengelola kebun seluas 5 hektar. Ia berbagi cerita tentang tantangan dan kebanggaan menjadi petani kopi.</p>
<p>Kami juga mengunjungi unit pengolahan basah (wet mill) milik koperasi setempat. Di sini, biji kopi cherry diolah menjadi green bean yang siap dikirim ke roastery.</p>
<p>NAC Cafe berkomitmen untuk terus mendukung petani lokal melalui direct trade. Kami percaya bahwa kopi yang baik dimulai dari hubungan yang baik dengan petani.</p>',
'Tim NAC Cafe menyelesaikan kunjungan ke perkebunan kopi di Toraja, Sulawesi Selatan.', 4, 'Cerita', 'toraja.jpg', 332, '2024-07-18 10:00:00'),

(8, 'Pentingnya Sistem Keamanan Digital untuk UMKM', 'keamanan-digital-umkm',
'<p>Di era digital ini, keamanan data menjadi hal yang sangat penting, termasuk bagi usaha kecil dan menengah seperti NAC Cafe. Kami ingin berbagi pengalaman kami dalam mengelola keamanan digital.</p>
<p><strong>Langkah-langkah yang kami terapkan:</strong></p>
<ul>
<li>Menggunakan sistem autentikasi yang kuat untuk semua akses internal</li>
<li>Melakukan backup data secara berkala</li>
<li>Memisahkan akses berdasarkan level karyawan</li>
<li>Monitoring aktivitas yang mencurigakan</li>
</ul>
<p>Kami sadar bahwa tidak ada sistem yang sempurna. Oleh karena itu, kami terus melakukan evaluasi dan perbaikan secara berkala.</p>
<p>Bagi UMKM lain, kami sarankan untuk minimal memiliki backup data yang terenkripsi dan sistem autentikasi berlapis. Investasi di keamanan digital adalah investasi jangka panjang.</p>
<p>Jika Anda memiliki pertanyaan seputar keamanan digital, jangan ragu untuk menghubungi kami melalui halaman kontak.</p>',
'Keamanan data menjadi hal penting di era digital, termasuk bagi UMKM seperti NAC Cafe.', 1, 'Tips', 'security.jpg', 289, '2024-08-25 13:45:00');

-- Menu items
INSERT INTO `menu_items` (`name`, `description`, `price`, `category`, `image`, `available`) VALUES
('Espresso', 'Single shot espresso dari biji pilihan. Bold dan intense.', 18000, 'Kopi', 'espresso.jpg', 1),
('Americano', 'Espresso dengan air panas. Clean dan refreshing.', 22000, 'Kopi', 'americano.jpg', 1),
('Cappuccino', 'Espresso, steamed milk, dan foam tebal. Klasik Italia.', 28000, 'Kopi', 'cappuccino.jpg', 1),
('Latte', 'Espresso dengan steamed milk yang lembut. Smooth dan creamy.', 28000, 'Kopi', 'latte.jpg', 1),
('V60 Single Origin', 'Pour over dari biji single origin pilihan hari ini.', 32000, 'Kopi', 'v60-single.jpg', 1),
('Cold Brew', 'Kopi yang diseduh dingin selama 18 jam. Smooth dan sweet.', 30000, 'Kopi', 'cold-brew.jpg', 1),
('Matcha Latte', 'Matcha grade A dari Uji, Jepang dengan susu segar.', 30000, 'Non-Kopi', 'matcha.jpg', 1),
('Cokelat Panas', 'Belgian chocolate dengan susu segar. Rich dan comforting.', 25000, 'Non-Kopi', 'hot-choco.jpg', 1),
('Teh Tarik', 'Teh hitam dengan susu kental manis. Gaya Malaysia.', 20000, 'Non-Kopi', 'teh-tarik.jpg', 1),
('Croissant', 'Butter croissant panggang segar setiap pagi.', 25000, 'Makanan', 'croissant.jpg', 1),
('Sandwich Club', 'Triple decker dengan ayam, telur, dan sayuran segar.', 35000, 'Makanan', 'sandwich.jpg', 1),
('Banana Bread', 'Homemade banana bread dengan walnut. Moist dan flavorful.', 22000, 'Makanan', 'banana-bread.jpg', 1),
('Cheesecake', 'New York style cheesecake dengan berry compote.', 38000, 'Makanan', 'cheesecake.jpg', 1),
('French Fries', 'Kentang goreng crispy dengan bumbu truffle.', 28000, 'Makanan', 'fries.jpg', 1),
('Jahe Susu Latte', 'Espresso, susu segar, dan ekstrak jahe merah. Menu musim hujan.', 32000, 'Spesial', 'jahe-latte.jpg', 1);

-- Secret menu (Flag 2 - hidden table)
INSERT INTO `secret_menu` (`item_name`, `recipe_code`, `notes`) VALUES
('kopi_susu_gula_aren_spesial', 'RCP-001-SECRET', 'Resep rahasia turun temurun. Hanya head barista yang tahu komposisi lengkapnya. Double shot espresso + susu murni + gula aren asli + rempah rahasia.'),
('es_kopi_legendaris', 'RCP-002-SECRET', 'Menu legenda dari era warung kopi. Tidak dijual ke publik.'),
('teh_rempah_nusantara', 'RCP-003-SECRET', 'Campuran 7 rempah asli Indonesia. Formula dirahasiakan.');

-- Default comments
INSERT INTO `comments` (`article_id`, `user_id`, `username`, `content`, `created_at`) VALUES
(1, 3, 'editor', 'Senang sekali bisa menjadi bagian dari NAC Cafe! Mari terus berkarya.', '2024-01-16 09:00:00'),
(1, 2, 'guest', 'Kopinya enak banget! Sudah jadi langganan tetap.', '2024-01-17 14:30:00'),
(2, 4, 'barista_andi', 'Kopi Gayo memang juara. Setiap pagi saya selalu seduh untuk quality check.', '2024-02-11 08:00:00'),
(4, 2, 'guest', 'Selamat NAC Cafe! Well deserved!', '2024-04-21 10:00:00'),
(4, 3, 'editor', 'Terima kasih kepada seluruh pelanggan setia kami!', '2024-04-21 11:30:00'),
(6, 2, 'guest', 'Jahe Susu Latte nya enak banget pas musim hujan gini.', '2024-06-02 16:00:00'),
(3, 2, 'guest', 'Terima kasih tips nya! Saya coba di rumah hasilnya lumayan.', '2024-03-06 20:00:00');

-- Loyalty transactions
INSERT INTO `loyalty_transactions` (`user_id`, `points`, `type`, `description`) VALUES
(2, 100, 'credit', 'Welcome bonus - pendaftaran akun baru'),
(3, 250, 'credit', 'Welcome bonus - staff editor'),
(4, 180, 'credit', 'Welcome bonus - staff barista');
