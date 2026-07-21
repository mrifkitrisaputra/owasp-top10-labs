# 📚 Proyek Web Perpustakaan CTF - Ringkasan Lengkap

## 🎯 Deskripsi Proyek

Saya telah membuat website perpustakaan lengkap dengan sistem CTF (Capture The Flag) yang berisi 11 flag tersembunyi dengan tingkat kesulitan yang meningkat. Website ini dibuat agar terlihat seperti website perpustakaan normal tanpa ada indikasi CTF yang jelas.

## ✅ Yang Telah Dibuat

### 1. **Backend (PHP + MySQL)**
- ✅ `config.php` - Konfigurasi database dan koneksi
- ✅ `auth.php` - Sistem autentikasi (login/logout)
- ✅ `api.php` - API endpoints untuk data buku, peminjaman, pencarian
- ✅ `admin.php` - Panel admin untuk logs, archive, dan konfigurasi
- ✅ `hidden.php` - Halaman tersembunyi untuk FLAG 3

### 2. **Frontend (HTML, CSS, JavaScript)**
- ✅ `index.html` - Halaman utama dengan info perpustakaan
- ✅ `login.html` - Halaman login
- ✅ `admin.html` - Panel admin
- ✅ `style.css` - Styling lengkap dan responsive
- ✅ `app.js` - Fungsi utama (search, borrow, dll)
- ✅ `admin.js` - Fungsi admin panel

### 3. **Database (MySQL)**
- ✅ `init.sql` - Database schema lengkap dengan data sample
- ✅ Tabel: users, books, borrowings, archive_books, admin_logs, system_config, book_reviews
- ✅ Sample data untuk semua tabel

### 4. **Docker & Deployment**
- ✅ `docker-compose.yml` - Konfigurasi container
- ✅ `Dockerfile` - Image untuk aplikasi
- ✅ `apache-config.conf` - Konfigurasi Apache
- ✅ `deploy.sh` - Script deployment otomatis
- ✅ `cleanup.sh` - Script pembersihan

### 5. **Dokumentasi**
- ✅ `README.md` - Dokumentasi lengkap dengan solusi semua flag
- ✅ `QUICKSTART.md` - Panduan cepat deployment
- ✅ `HINTS.md` - Sistem hint bertahap untuk setiap flag
- ✅ `TESTING.md` - Checklist testing untuk verifikasi flag

## 🏁 11 Flag yang Tersedia

### Minggu 1-2 (Mudah)
1. **FLAG 1** - SQL Injection: Cari ISBN buku mystery tertua
   - Jawaban: `978-0486284736`

2. **FLAG 2** - Info Disclosure: Nama database
   - Jawaban: `library_ctf`

### Minggu 2-5 (Sedang)
3. **FLAG 3** - Cookie Manipulation: Akses hidden area
   - Jawaban: `true`

4. **FLAG 4** - Session Analysis: Variable session librarian
   - Jawaban: `granted`

5. **FLAG 5** - File Inclusion: Debug parameter vulnerability
   - Jawaban: `config.php`

### Minggu 5-8 (Sulit)
6. **FLAG 6** - Archive System: Lokasi penyimpanan buku archived
   - Jawaban: `BASEMENT-VAULT-A7`

7. **FLAG 7** - Auth Bypass: Username librarian
   - Jawaban: `librarian_admin`

8. **FLAG 8** - Crypto Weakness: Password librarian
   - Jawaban: `library2024`

### Minggu 8-12 (Sangat Sulit)
9. **FLAG 9** - IDOR: IP admin dari log tertentu
   - Jawaban: `192.168.1.100`

10. **FLAG 10** - Privilege Escalation: Master key config
    - Jawaban: `CTF-MASTER-KEY-2024-LIBRARY`

11. **FLAG 11** - Complex Chain: Email user yang pinjam buku author tertua
    - Jawaban: `john@example.com`

## 🚀 Cara Deploy ke Ubuntu Server

### Deploy Otomatis (Rekomendasi):

```bash
# 1. Upload folder web4 ke server
scp -r web4/ user@SERVER_IP:/opt/

# 2. SSH ke server
ssh user@SERVER_IP

# 3. Jalankan script deploy
cd /opt/web4
chmod +x deploy.sh
sudo ./deploy.sh
```

Script akan otomatis:
- Install Docker & Docker Compose
- Build container
- Inisialisasi database
- Start semua service
- Konfigurasi firewall

### Akses Aplikasi:

Setelah deploy:
- Website: `http://SERVER_IP:8080`
- Database: `SERVER_IP:3306`

## 👤 Akun Testing

**User Biasa:**
- Username: `john_doe`
- Password: `password`

**Librarian/Admin:**
- Username: `librarian_admin`
- Password: `library2024`

## 🔒 Keamanan & Karakteristik CTF

### Format Flag yang Unik:
- ✅ TIDAK menggunakan format custom seperti `CTF{blabla}`
- ✅ Menggunakan jawaban logis (ISBN, nama database, IP address, dll)
- ✅ Setiap flag adalah jawaban dari pertanyaan atau hasil eksplorasi

### Vulnerability yang Diimplementasikan:
1. **SQL Injection** - Pencarian tidak di-sanitize
2. **Information Disclosure** - Comment di source code
3. **Cookie Manipulation** - Validasi lemah via cookie
4. **Session Issues** - Session variable bisa dieksploitasi
5. **File Inclusion** - LFI vulnerability via debug parameter
6. **Broken Access Control** - IDOR di admin logs
7. **Weak Authentication** - Username enumeration & weak hash
8. **Privilege Escalation** - Hidden config accessible

### Environment Real:
- ✅ Website terlihat seperti perpustakaan sungguhan
- ✅ Tidak ada indikasi jelas ini adalah CTF
- ✅ Fitur lengkap: browse books, search, borrow, categories, admin panel
- ✅ Design professional dengan CSS modern
- ✅ Responsive untuk mobile

## 📊 Struktur File

```
web4/
├── backend/              # PHP backend files
│   ├── config.php
│   ├── auth.php
│   ├── api.php
│   ├── admin.php
│   └── hidden.php
├── frontend/             # HTML/CSS/JS frontend
│   ├── index.html
│   ├── login.html
│   ├── admin.html
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── app.js
│       └── admin.js
├── database/             # Database setup
│   └── init.sql
├── docker-compose.yml    # Docker orchestration
├── Dockerfile           # Application container
├── apache-config.conf   # Web server config
├── deploy.sh           # Auto deployment
├── cleanup.sh          # Cleanup script
├── README.md           # Full documentation
├── QUICKSTART.md       # Quick start guide
├── HINTS.md           # Progressive hints
└── TESTING.md         # Testing checklist
```

## 🎮 Cara Menggunakan

### Untuk Peserta CTF:

1. Akses website di `http://SERVER_IP:8080`
2. Explore website seperti pengguna normal
3. Cari vulnerability dan flag tersembunyi
4. Flag saling berhubungan - flag sebelumnya membantu menemukan flag berikutnya
5. Total durasi: 2 bulan untuk menemukan semua 11 flag

### Untuk Penyelenggara CTF:

1. Deploy menggunakan `deploy.sh`
2. Verifikasi semua flag dengan `TESTING.md`
3. Siapkan sistem hint menggunakan `HINTS.md`
4. Monitor progress peserta
5. Berikan hint bertahap sesuai jadwal

## 🛠️ Command Berguna

```bash
# Lihat status container
docker-compose ps

# Lihat logs
docker-compose logs -f

# Restart service
docker-compose restart

# Stop semua
docker-compose down

# Reset total (hapus data)
docker-compose down -v
docker-compose up -d

# Akses MySQL
docker exec -it library_mysql mysql -u root -p
# Password: root_password_2024

# Akses shell container
docker exec -it library_webapp bash
```

## 📝 Catatan Penting

### ⚠️ Keamanan:
- Website ini SENGAJA dibuat vulnerable untuk tujuan edukasi
- JANGAN deploy di production atau internet publik tanpa isolasi
- Gunakan hanya di environment CTF yang terisolasi
- Reset setelah setiap sesi CTF

### ✅ Kelebihan Proyek Ini:
- Flag format unik (bukan string custom)
- Tingkat kesulitan progresif
- Flag saling berhubungan
- Environment realistis
- Dokumentasi lengkap
- Easy deployment dengan Docker
- Hint system tersedia

## 📚 Dokumentasi Tambahan

1. **README.md** - Dokumentasi lengkap dengan solusi detail semua flag
2. **QUICKSTART.md** - Panduan deploy cepat
3. **HINTS.md** - Hint bertahap untuk setiap flag dengan jadwal release
4. **TESTING.md** - Checklist testing untuk verifikasi sebelum deploy

## 🎯 Learning Objectives

Peserta akan belajar:
- SQL Injection techniques
- Authentication bypass
- Session & cookie manipulation
- File inclusion vulnerabilities
- IDOR exploitation
- Privilege escalation
- Password cracking
- Complex query construction
- Web application security fundamentals

## 💡 Tips Deployment

1. **Server Requirements:**
   - Ubuntu 20.04+ (atau Linux distro lain)
   - Minimal 2GB RAM
   - Minimal 10GB disk space
   - IP public untuk akses peserta

2. **Network:**
   - Port 8080 harus terbuka
   - Port 3306 optional (untuk debug)
   - Firewall dikonfigurasi otomatis

3. **Monitoring:**
   - Gunakan `docker-compose logs -f` untuk real-time logs
   - Monitor disk space dengan `docker system df`
   - Check resource usage: `docker stats`

## ✨ Kesimpulan

Proyek CTF perpustakaan ini sudah 100% lengkap dan siap deploy. Semua 11 flag sudah diimplementasikan dengan tingkat kesulitan yang meningkat, flag format menggunakan jawaban logis bukan string custom, dan website terlihat seperti aplikasi perpustakaan real tanpa indikasi CTF yang jelas.

**Status:** ✅ READY TO DEPLOY

**Testing:** Silakan jalankan checklist di TESTING.md sebelum launch

**Support:** Semua dokumentasi sudah tersedia untuk penyelenggara maupun peserta

---

**Selamat menjalankan CTF! Good luck to all participants! 🚀**
