<?php
require 'db.php';

$KATEGORI = ['Makanan', 'Minuman', 'Tambahan'];
$upload_dir = '../uploads/'; // fix path karena file di src/

// pastikan action selalu ada nilainya
$action = $_GET['action'] ?? '';
$filter = $_GET['kategori'] ?? '';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    if (!in_array($kategori, $KATEGORI)) $kategori = $KATEGORI[0];

    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $new_name = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $new_name);
        $gambar = $new_name;
    }

    if ($nama && $harga > 0) {
        $stmt = $pdo->prepare("INSERT INTO menu (nama, harga, keterangan, kategori, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $harga, $keterangan, $kategori, $gambar]);
    }
    header('Location: menu_crud.php');
    exit;
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    if (!in_array($kategori, $KATEGORI)) $kategori = $KATEGORI[0];

    $stmt = $pdo->prepare("SELECT gambar FROM menu WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch();
    $gambar = $old['gambar'] ?? null;

    if (!empty($_FILES['gambar']['name'])) {
        if ($gambar && file_exists($upload_dir . $gambar)) {
            unlink($upload_dir . $gambar);
        }
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $new_name = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $new_name);
        $gambar = $new_name;
    }

    if ($id && $nama && $harga > 0) {
        $stmt = $pdo->prepare("UPDATE menu SET nama = ?, harga = ?, keterangan = ?, kategori = ?, gambar = ? WHERE id = ?");
        $stmt->execute([$nama, $harga, $keterangan, $kategori, $gambar, $id]);
    }
    header('Location: menu_crud.php');
    exit;
}

// DELETE
if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("SELECT gambar FROM menu WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && $row['gambar'] && file_exists($upload_dir . $row['gambar'])) {
            unlink($upload_dir . $row['gambar']);
        }
        $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: menu_crud.php');
    exit;
}

// Ambil data menu
if ($filter && in_array($filter, $KATEGORI)) {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE kategori = ? ORDER BY created_at DESC");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM menu ORDER BY created_at DESC");
}
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>CRUD Menu - Restoku</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
    background-image: url('../images/foods.jpg'); /* fix path */
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    background-attachment: fixed;
    height: 100vh;
    margin: 0;
}
h1{
    background-color: #d3d3d3;
    border-radius: 10px;
    padding: 20px 0;
    text-align: center;
}
input, select{
    background-color: #d3d3d3;
}
.mb-4{
    width: 350px;
    background-color: #d3d3d3;
    padding: 10px 5px;
    border-radius: 10px;
}
.tabel-div {
  background-color: #d3d3d3;
  width: 100%;
  border-radius:10px;
}
</style>
</head>
<body class="bg-gray-50">
<div class="max-w-5xl mx-auto p-6">

<h1 class="text-2xl font-bold mb-4">CRUD MENU RESTOKU</h1>

<!-- Form tambah menu -->
<form action="?action=create" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
    <input type="text" name="nama" placeholder="Nama menu" required class="p-2 border rounded col-span-2" />
    <input type="number" name="harga" placeholder="Harga" min="1" required class="p-2 border rounded" />
    <select name="kategori" required class="p-2 border rounded">
        <?php foreach ($KATEGORI as $kat): ?>
            <option value="<?= $kat ?>"><?= $kat ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="p-2 border rounded md:col-span-3" />
    <input type="file" name="gambar" accept="image/*" onchange="previewImage(event, 'preview_create')" class="md:col-span-1" />
    <img id="preview_create" src="" alt="" class="max-h-20 mt-2 hidden">
    <div class="flex gap-2">
      <div class="flex items-center gap-2 col-span-1 md:col-start-4">
        <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 whitespace-nowrap">
            Tambah Menu
        </button>
        <a href="index.php" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 whitespace-nowrap">
            Lihat Menu
        </a>
      </div>
    </div>
</form>

<!-- Filter kategori -->
<div class="mb-4">
    <a href="menu_crud.php" class="mr-3 <?= $filter==='' ? 'font-bold' : '' ?>">Semua</a>
    <?php foreach ($KATEGORI as $kat): ?>
        <a href="?kategori=<?= urlencode($kat) ?>" class="mr-3 <?= $filter===$kat ? 'font-bold' : '' ?>"><?= $kat ?></a>
    <?php endforeach; ?>
</div>

<!-- Tabel -->
<table class="tabel-div">
<thead class="bg-gray-100">
<tr >
    <th class="p-3">Nama</th>
    <th class="p-3">Harga</th>
    <th class="p-3">Kategori</th>
    <th class="p-3">Keterangan</th>
    <th class="p-3">Gambar</th>
    <th class="p-3 text-center">Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach ($menus as $menu): ?>
<tr class="border-t">
    <td class="p-3"><?= htmlspecialchars($menu['nama']) ?></td>
    <td class="p-3">Rp <?= number_format($menu['harga']) ?></td>
    <td class="p-3"><?= htmlspecialchars($menu['kategori']) ?></td>
    <td class="p-3"><?= htmlspecialchars($menu['keterangan']) ?></td>
    <td class="p-3">
        <?php if ($menu['gambar']): ?>
            <img src="<?= $upload_dir . $menu['gambar'] ?>" class="max-h-16">
        <?php else: ?>
            <span class="text-gray-500">Tidak ada</span>
        <?php endif; ?>
    </td>
    <td class="p-3 text-center">
        <button 
        onclick="openEdit(
            '<?= (int)$menu['id'] ?>', 
            '<?= htmlspecialchars(addslashes($menu['nama'])) ?>', 
            '<?= (int)$menu['harga'] ?>', 
            '<?= htmlspecialchars(addslashes($menu['keterangan'])) ?>', 
            '<?= htmlspecialchars(addslashes($menu['kategori'])) ?>', 
            '<?= htmlspecialchars(addslashes($menu['gambar'])) ?>'
        )" 
        class="text-blue-600 hover:underline mr-3">
        Edit
        </button>

        <a href="?action=delete&id=<?= (int)$menu['id'] ?>" onclick="return confirm('Hapus menu ini?')" class="text-red-600 hover:underline">Hapus</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Modal edit -->
<div id="editModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-6 rounded shadow w-full max-w-md">
        <h2 class="text-lg font-semibold mb-3">Edit Menu</h2>
        <form action="?action=update" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id" />
            <input type="text" name="nama" id="edit_nama" required class="w-full mb-2 p-2 border rounded" />
            <input type="number" name="harga" id="edit_harga" min="1" required class="w-full mb-2 p-2 border rounded" />
            <select name="kategori" id="edit_kategori" required class="w-full mb-2 p-2 border rounded">
                <?php foreach ($KATEGORI as $kat): ?>
                    <option value="<?= $kat ?>"><?= $kat ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="keterangan" id="edit_keterangan" class="w-full mb-2 p-2 border rounded" />
            <input type="file" name="gambar" accept="image/*" onchange="previewImage(event, 'preview_edit')" />
            <img id="preview_edit" src="" alt="" class="max-h-20 mt-2">
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="closeEdit()" class="px-4 py-2 rounded border">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
function previewImage(event, id) {
    const output = document.getElementById(id);
    output.src = URL.createObjectURL(event.target.files[0]);
    output.classList.remove('hidden');
}

function openEdit(id, nama, harga, keterangan, kategori, gambar) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama || '';
    document.getElementById('edit_harga').value = harga || '';
    document.getElementById('edit_keterangan').value = keterangan || '';
    document.getElementById('edit_kategori').value = kategori || '';
    document.getElementById('preview_edit').src = gambar ? '../uploads/' + gambar : '';
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
</body>
</html>
