
```markdown
# Restoku

Restoku adalah sistem manajemen restoran sederhana berbasis PHP dan MySQL.  
Fitur utama: menampilkan menu, keranjang belanja, CRUD menu, login admin, dan cetak nota.

## Fitur
- Menampilkan daftar menu makanan
- CRUD menu makanan (untuk admin)
- Keranjang belanja (cart.php)
- Login admin
- Cetak nota pembelian (nota.php)

## Tech Stack
- PHP
- MySQL
- HTML, CSS

## Struktur Folder
```

restoku/
├─ assets/
│  ├─ uploads/        # folder upload user (tidak di-push ke GitHub)
│  └─ images/         # gambar default seperti foods.jpg
├─ src/               # file PHP utama
│  ├─ cart.php
│  ├─ config.php      # template aman
│  ├─ db.php
│  ├─ index.php
│  ├─ login_admin.php
│  ├─ menu_crud.php
│  └─ nota.php
├─ .gitignore
└─ README.md

````

## Database / Tables

### Tabel: users
| Field       | Tipe        | Keterangan             |
|------------|------------|----------------------|
| id         | INT PK AI   | ID user               |
| username   | VARCHAR(50) | Nama user             |
| password   | VARCHAR(255)| Password (hash)       |
| role       | VARCHAR(10) | admin/user            |

### Tabel: menu
| Field       | Tipe         | Keterangan             |
|------------|-------------|----------------------|
| id         | INT PK AI    | ID menu               |
| name       | VARCHAR(100) | Nama makanan          |
| price      | DECIMAL(10,2)| Harga                 |
| image      | VARCHAR(255) | Nama file gambar      |

### Tabel: cart
| Field       | Tipe        | Keterangan             |
|------------|------------|----------------------|
| id         | INT PK AI   | ID cart               |
| user_id    | INT FK      | ID user               |
| menu_id    | INT FK      | ID menu               |
| quantity   | INT         | Jumlah                |

### Tabel: orders
| Field       | Tipe        | Keterangan             |
|------------|------------|----------------------|
| id         | INT PK AI   | ID order              |
| user_id    | INT FK      | ID user               |
| total      | DECIMAL(10,2)| Total harga          |
| date       | DATETIME    | Waktu order           |

**Contoh query bikin tabel:**
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL
);
````

---

## Cara Jalankan

1. Clone repo:

   ```bash
   git clone https://github.com/username/restoku.git
   ```
2. Masuk folder src:

   ```bash
   cd restoku/src
   ```
3. Edit `config.php` sesuai database lokal: host, dbname, user, password.
4. Jalankan project di localhost (XAMPP/WAMP/MAMP).
5. Pastikan folder `assets/uploads/` writable agar fitur upload berjalan.

---

## Catatan

* Jangan push credential asli atau file uploads pengguna.
* Struktur tabel sudah dijelaskan lengkap, sehingga orang lain bisa bikin database sendiri.

```

---


