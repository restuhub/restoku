<?php
session_start();

// require db.php dari root
require 'db.php';

// tombol clear cart (opsional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}

// pastikan action update / checkout tetap aman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['qty'] as $menu_id => $qty) {
            $menu_id = (int)$menu_id;
            $qty = max(0, (int)$qty);
            if ($qty === 0) {
                unset($_SESSION['cart'][$menu_id]);
            } else {
                if (isset($_SESSION['cart'][$menu_id]) && is_array($_SESSION['cart'][$menu_id])) {
                    $_SESSION['cart'][$menu_id]['qty'] = $qty;
                }
            }
        }
    } elseif (isset($_POST['checkout'])) {
        $nama_pembeli = trim($_POST['nama_pembeli'] ?? '');
        if (empty($_SESSION['cart'])) {
            $error = "Keranjang kosong, tidak bisa checkout.";
        } elseif ($nama_pembeli === '') {
            $error = "Nama pembeli wajib diisi.";
        } else {
            $total = 0;
            foreach ($_SESSION['cart'] as $menu_id => $it) {
                $harga = (int)($it['harga'] ?? 0);
                $qty = (int)($it['qty'] ?? 0);
                $total += $harga * $qty;
            }

            $stmt = $pdo->prepare("INSERT INTO orders (nama_pembeli, total) VALUES (?, ?)");
            $stmt->execute([$nama_pembeli, $total]);
            $order_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, qty, subtotal) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $menu_id => $it) {
                $menu_id = (int)$menu_id;
                $harga = (int)($it['harga'] ?? 0);
                $qty = (int)($it['qty'] ?? 0);
                $subtotal = $harga * $qty;
                $stmt->execute([$order_id, $menu_id, $qty, $subtotal]);
            }

            unset($_SESSION['cart']);
            header("Location: nota.php?id=$order_id");
            exit;
        }
    }
}

// normalisasi session cart
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $mid => $itm) {
        $menu_id = (int)$mid;
        if (!is_array($itm)) {
            unset($_SESSION['cart'][$menu_id]);
            continue;
        }
        if (!isset($_SESSION['cart'][$menu_id]['qty'])) {
            $_SESSION['cart'][$menu_id]['qty'] = (int)($itm['qty'] ?? 1);
        }

        $need_fetch = false;
        if (!isset($itm['nama']) || !isset($itm['harga']) || !isset($itm['kategori'])) {
            $need_fetch = true;
        }

        if ($need_fetch) {
            $stmt = $pdo->prepare("SELECT nama, harga, kategori, gambar FROM menu WHERE id = ?");
            $stmt->execute([$menu_id]);
            $m = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($m) {
                $_SESSION['cart'][$menu_id]['id'] = $menu_id;
                $_SESSION['cart'][$menu_id]['nama'] = $m['nama'];
                $_SESSION['cart'][$menu_id]['harga'] = (int)$m['harga'];
                $_SESSION['cart'][$menu_id]['kategori'] = $m['kategori'];
                $_SESSION['cart'][$menu_id]['gambar'] = $m['gambar'];
            } else {
                if (!isset($_SESSION['cart'][$menu_id]['nama'])) $_SESSION['cart'][$menu_id]['nama'] = 'Menu Dihapus';
                if (!isset($_SESSION['cart'][$menu_id]['harga'])) $_SESSION['cart'][$menu_id]['harga'] = 0;
                if (!isset($_SESSION['cart'][$menu_id]['kategori'])) $_SESSION['cart'][$menu_id]['kategori'] = '-';
                if (!isset($_SESSION['cart'][$menu_id]['gambar'])) $_SESSION['cart'][$menu_id]['gambar'] = '';
            }
        }

        if (!isset($_SESSION['cart'][$menu_id]['id'])) {
            $_SESSION['cart'][$menu_id]['id'] = $menu_id;
        }
    }
}

$cart = $_SESSION['cart'] ?? [];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<title>Keranjang - Restoku</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
<div class="max-w-4xl mx-auto">

    <h1 class="text-3xl font-bold mb-6">Keranjang Pesanan</h1>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <p>Keranjang kosong. <a href="index.php" class="text-blue-600 underline">Tambah menu</a></p>
    <?php else: ?>
        <form method="post" class="mb-6">
            <div class="mb-4 flex gap-3">
                <div class="flex-1">
                    <label class="block font-semibold mb-1">Nama Pembeli:</label>
                    <input type="text" name="nama_pembeli" class="w-full border rounded p-2" placeholder="Masukkan nama pembeli" required>
                </div>

                <div class="w-48">
                    <button type="submit" name="clear_cart" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">Kosongkan Keranjang</button>
                </div>
            </div>

            <table class="w-full border-collapse bg-white shadow rounded">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">Gambar</th>
                        <th class="border px-4 py-2">Nama Menu</th>
                        <th class="border px-4 py-2">Kategori</th>
                        <th class="border px-4 py-2 text-right">Harga</th>
                        <th class="border px-4 py-2 text-center">Jumlah</th>
                        <th class="border px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($cart as $menu_id => $item):
                        $nama = htmlspecialchars($item['nama'] ?? 'Menu Dihapus');
                        $kategori = htmlspecialchars($item['kategori'] ?? '-');
                        $gambar = $item['gambar'] ?? '';
                        $harga = (int)($item['harga'] ?? 0);
                        $qty = (int)($item['qty'] ?? 0);
                        $subtotal = $harga * $qty;
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td class="border px-4 py-2">
                            <?php if (!empty($gambar)): ?>
                                <img src="../uploads/<?= htmlspecialchars($gambar) ?>" alt="" class="w-16 h-16 object-cover rounded">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td class="border px-4 py-2"><?= $nama ?></td>
                        <td class="border px-4 py-2"><?= $kategori ?></td>
                        <td class="border px-4 py-2 text-right">Rp <?= number_format($harga) ?></td>
                        <td class="border px-4 py-2 text-center">
                            <input type="number" name="qty[<?= (int)$menu_id ?>]" value="<?= $qty ?>" min="0" class="w-16 p-1 border rounded text-center">
                        </td>
                        <td class="border px-4 py-2 text-right">Rp <?= number_format($subtotal) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="font-bold bg-gray-100">
                        <td colspan="5" class="border px-4 py-2 text-right">Total</td>
                        <td class="border px-4 py-2 text-right">Rp <?= number_format($total) ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4 flex gap-3">
                <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">Update Jumlah</button>
                <button type="submit" name="checkout" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Checkout</button>
                <a href="index.php" class="ml-auto px-4 py-2 rounded border border-gray-400 hover:bg-gray-100 transition">Tambah Menu</a>
            </div>
        </form>
    <?php endif; ?>

</div>
</body>
</html>
