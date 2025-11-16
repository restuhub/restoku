<?php
session_start();
require 'db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $err = "Username dan password harus diisi.";
    } else {
        // Ambil user admin berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Bandingkan plain text (TIDAK AMAN untuk production)
        if ($admin && $password === $admin['password']) {
            // login sukses
            $_SESSION['admin_logged_in'] = true;
            // bisa simpan username juga: $_SESSION['admin_username'] = $username;
            header("Location: menu_crud.php");
            exit;
        } else {
            $err = "Username atau password salah!";
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{
         background-image: url('foods.jpg');
         background-repeat: no-repeat;
         background-size: cover;
         background-position: center center;
         background-attachment: fixed;
         height: 100vh;
         margin: 0;
    }
  </style>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow w-96">
        <h2 class="text-2xl font-bold mb-4">Login Admin</h2>
        <?php if($err): ?>
            <p class="text-red-500 mb-2"><?= htmlspecialchars($err) ?></p>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <input type="text" name="username" placeholder="Username" class="w-full border px-3 py-2 rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full border px-3 py-2 rounded" required>
            <button type="submit" class="w-full bg-gray-800 text-white px-3 py-2 rounded hover:bg-gray-900">Login</button>
        </form>
    </div>
</body>
</html>
