# OWASP Top 10 Labs

Repositori ini berisi beberapa lab keamanan web yang dirancang untuk mempelajari kerentanan OWASP Top 10 secara praktis. Setiap lab dibuat menyerupai aplikasi nyata, tetapi dengan celah keamanan yang sengaja disediakan untuk keperluan pembelajaran.

## Deskripsi

Repo ini terdiri dari beberapa proyek lab terpisah:

- cafe_nac: lab berbasis portal berita dan kafe dengan berbagai skenario keamanan web.
- library_nac: lab berbasis sistem manajemen perpustakaan yang mencontohkan berbagai celah keamanan.
- news_nac: lab berbasis portal berita yang menekankan pemahaman terhadap serangan web.

Tujuan utama dari repositori ini adalah membantu pengguna belajar secara langsung melalui simulasi aplikasi yang realistis dan mudah dijalankan di lingkungan lokal.

## Instalasi

### Prasyarat

- Docker
- Docker Compose
- Akses ke terminal

### Menjalankan lab

Setiap lab dapat dijalankan secara terpisah.

#### 1. Cafe Lab

```bash
cd cafe_nac
chmod +x setup.sh
sudo ./setup.sh
```

Atau jika ingin menjalankan manual:

```bash
docker compose up -d --build
```

#### 2. Library Lab

```bash
cd library_nac
chmod +x deploy.sh
sudo ./deploy.sh
```

#### 3. News Lab

```bash
cd news_nac
docker compose up -d --build
```

## Akses

Setelah container berjalan, buka aplikasi melalui browser sesuai alamat yang ditampilkan oleh Docker. Untuk beberapa lab, alamat default biasanya berupa localhost atau IP server tempat Docker dijalankan.

## Submit Flag

Untuk submit flag, dapat menggunakan bot Telegram berikut:

- @submitflagteam1 — untuk library_nac
- @submitflagteam2 — untuk news_nac
- @submitflagteam3 — untuk cafe_nac

Setiap web challenge pada repositori ini memiliki 11 flag yang dirancang untuk mencakup berbagai kerentanan OWASP Top 10.

## Catatan

Semua lab ini dibuat untuk tujuan edukasi dan pelatihan keamanan aplikasi. Gunakan dengan bijak dan hanya di lingkungan yang sah.
