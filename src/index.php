<?php
session_start();

// require db.php dari root karena sekarang index.php ada di src/
require 'db.php';

// Tambah ke keranjang
if (isset($_POST['menu_id'])) {
    $menu_id = (int) $_POST['menu_id'];

    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->execute([$menu_id]);
    $menu = $stmt->fetch();

    if ($menu) {
        if (isset($_SESSION['cart'][$menu_id])) {
            $_SESSION['cart'][$menu_id]['qty']++;
        } else {
            $_SESSION['cart'][$menu_id] = [
                'id' => $menu['id'],
                'nama' => $menu['nama'],
                'harga' => $menu['harga'],
                'qty' => 1,
                'gambar' => $menu['gambar'] ?? '',
                'kategori' => $menu['kategori'] ?? ''
            ];
        }
    }
    header("Location: index.php");
    exit;
}

// Ambil menu berdasarkan kategori
$kategori_list = ['Makanan', 'Minuman', 'Tambahan'];
$menus_by_kategori = [];

foreach ($kategori_list as $kat) {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE kategori = ? ORDER BY id DESC");
    $stmt->execute([$kat]);
    $menus_by_kategori[$kat] = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>POS Kasir - Restoku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-image: url('../images/bg.jpg'); /* path fix */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gray-50 p-6">
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">POS Kasir - Restoku</h1>
        <div class="flex space-x-2">
            <a href="cart.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Lihat Keranjang</a>
            <a href="login_admin.php" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 transition">💀</a>
            <a href="menu_crud.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">+</a>
        </div>
    </div>

    <!-- Menu per kategori -->
    <?php foreach ($menus_by_kategori as $kategori => $menus): ?>
        <h2 class="text-2xl font-semibold mb-4 mt-8"><?= htmlspecialchars($kategori) ?></h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <?php foreach ($menus as $menu): ?>
            <div class="bg-white p-4 rounded shadow flex flex-col">
                <?php if (!empty($menu['gambar'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($menu['gambar']) ?>" 
                         alt="<?= htmlspecialchars($menu['nama']) ?>" 
                         class="w-full h-32 object-cover rounded mb-3">
                <?php else: ?>
                    <div class="w-full h-32 bg-gray-200 flex items-center justify-center rounded mb-3">No Image</div>
                <?php endif; ?>

                <h3 class="font-semibold"><?= htmlspecialchars($menu['nama']) ?></h3>
                <p class="text-gray-600 text-sm mb-1">Rp <?= number_format($menu['harga']) ?></p>
                <p class="text-gray-500 text-xs italic"><?= htmlspecialchars($menu['kategori']) ?></p>

                <form method="post" class="mt-auto">
                    <input type="hidden" name="menu_id" value="<?= (int)$menu['id'] ?>">
                    <button type="submit" class="w-full bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">Tambah</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</div>
</body>
</html>
