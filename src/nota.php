<?php
require 'db.php';

$order_id = (int)($_GET['id'] ?? 0);

// Ambil data order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order tidak ditemukan.");
}

// Ambil data item order (tanpa gambar)
$stmt = $pdo->prepare("
    SELECT oi.*, m.nama, m.kategori, m.harga 
    FROM order_items oi
    LEFT JOIN menu m ON oi.menu_id = m.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Nota Pesanan #<?= $order_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 400px; /* lebih luas dari sebelumnya */
            margin: 20px auto;
            background: repeating-linear-gradient(
                45deg, #fefefe, #fefefe 10px, #f5f5f5 10px, #f5f5f5 20px
            );
            position: relative;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 250px;
            height: 250px;
            background: url('uploads/logo-restoku.png') no-repeat center;
            background-size: contain;
            opacity: 0.05;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        @media print {
            body { background: white; box-shadow: none; }
            button { display: none; }
        }
        .line {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
        }
        h2 {
            font-size: 1.8rem;
        }
        p, span {
            font-size: 1rem;
        }
        .item-name {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .total {
            font-size: 1.3rem;
        }
    </style>
</head>
<body>
    <h2 class="text-center font-extrabold mb-1">RESTOKU</h2>
    <p class="text-center text-sm font-semibold">Jl. Gajah Mada No.123, Indonesia</p>
    <div class="line"></div>

    <p class="font-semibold">No. Order: <?= $order_id ?></p>
    <p class="font-semibold">Nama: <?= htmlspecialchars($order['nama_pembeli']) ?></p>
    <p class="font-semibold">Tanggal: <?= date('d M Y H:i', strtotime($order['created_at'] ?? 'now')) ?></p>
    <div class="line"></div>

    <?php foreach ($items as $item): ?>
        <div class="mb-1">
            <p class="item-name"><?= htmlspecialchars($item['nama'] ?? 'Menu Dihapus') ?></p>
            <div class="flex-between">
                <span><?= $item['qty'] ?> x Rp<?= number_format($item['harga'] ?? 0) ?></span>
                <span>Rp<?= number_format($item['subtotal']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="line"></div>
    <div class="flex-between font-bold total">
        <span>Total</span>
        <span>Rp<?= number_format($order['total']) ?></span>
    </div>
    <div class="line"></div>

    <p class="text-center text-sm mt-4 font-semibold">Terima kasih atas pembelian Anda!</p>

    <button onclick="window.print()" class="mt-4 w-full bg-blue-700 hover:bg-blue-800 text-white py-3 rounded font-bold">
        Cetak Nota
    </button>
</body>
</html>
