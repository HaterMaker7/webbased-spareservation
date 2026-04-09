# Web-Based Spa Reservation System

Sistem reservasi spa berbasis web yang dibangun menggunakan PHP native, HTML, CSS, dan JavaScript. Aplikasi ini memungkinkan pengguna untuk melakukan booking layanan spa secara online serta menyediakan fitur admin untuk mengelola data reservasi.

---

## ✨ Fitur Utama

### 👤 User

* Registrasi & Login user
* Booking layanan spa
* Melihat jadwal reservasi

### 🔑 Admin

* Login admin
* Mengelola data reservasi
* Mengelola data layanan spa
* Monitoring aktivitas user

---

## 🔐 Akun Demo

### Admin

* Username: `admin`
* Password: `admin123`

### User

* Username: `john`
* Password: `john123`

---

## ⚙️ Cara Menjalankan Project

1. Clone repository:

```
git clone https://github.com/username/webbased-spareservation.git
```

2. Pindahkan folder ke dalam directory server (contoh: `htdocs` jika menggunakan XAMPP)

3. Buat database baru di MySQL

4. Import file database:

```
spa.sql
```

5. Konfigurasi database:

* Buka file:

```
config/database.php
```

* Sesuaikan dengan konfigurasi database masing-masing:

  * host
  * username
  * password
  * nama database

6. Jalankan project melalui browser:

```
http://localhost/webbased-spareservation
```

---

## ⚠️ Catatan Penting

* File `config/database.php` **harus disesuaikan kembali** dengan konfigurasi database pada masing-masing pengguna.
* Database yang digunakan pada sistem ini sudah disediakan dalam file `spa.sql`. Cukup lakukan import ke MySQL agar sistem dapat berjalan dengan baik.

---

## 🛠️ Teknologi yang Digunakan

* PHP (Native)
* MySQL
* HTML
* CSS
* JavaScript

---

## 👨‍💻 Author

* HaterMaker
