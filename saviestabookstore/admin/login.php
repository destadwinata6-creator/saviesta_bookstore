<?php
// Simpan di: C:\xampp\htdocs\saviesta\admin\login.php
session_start();
require_once __DIR__ . "/../koneksi_admin.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["masuk"])) {
    $email = trim($_POST["email"] ?? "");
    $passwordInput = $_POST["password"] ?? "";

    if ($email === "" || $passwordInput === "") {
        $error = "Email dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, nama, email, password FROM admin WHERE email = ? LIMIT 1"
        );

        if (!$stmt) {
            $error = "Query login gagal: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $admin = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            // Mendukung password hash maupun password teks biasa untuk data lokal lama.
            $validPassword = false;
            if ($admin) {
                $storedPassword = (string)$admin["password"];
                $validPassword = password_verify($passwordInput, $storedPassword)
                    || hash_equals($storedPassword, $passwordInput);
            }

            if ($admin && $validPassword) {
                session_regenerate_id(true);
                $_SESSION["admin_id"] = (int)$admin["id"];
                $_SESSION["admin_nama"] = $admin["nama"];
                $_SESSION["admin_email"] = $admin["email"];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Email atau password admin salah.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin | Saviesta Bookstore</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f5f3ed;color:#252a21;font-family:Arial,sans-serif;padding:20px}
.card{width:100%;max-width:420px;background:#fff;padding:34px;border-radius:18px;box-shadow:0 12px 40px #252a2112}
.brand{font-size:24px;font-weight:800;letter-spacing:1px}.subtitle{color:#77796f;font-size:13px;margin:8px 0 28px}
h1{font-size:25px;margin:0 0 8px}.desc{font-size:14px;color:#77796f;margin-bottom:24px}
label{display:block;font-size:13px;font-weight:700;margin:16px 0 8px}
input{width:100%;padding:13px 14px;border:1px solid #dddcd4;border-radius:9px;font-size:14px;outline:none}
input:focus{border-color:#626b54}
button{width:100%;border:0;border-radius:9px;background:#252a21;color:white;padding:14px;font-weight:700;font-size:14px;margin-top:22px;cursor:pointer}
button:hover{background:#41483a}.error{background:#fff0ef;color:#a52822;padding:12px;border-radius:8px;font-size:13px;margin-bottom:16px}
.back{display:block;text-align:center;margin-top:20px;color:#555b4b;font-size:13px;text-decoration:none}
</style>
</head>
<body>
<div class="card">
    <div class="brand">SAVIESTA</div>
    <div class="subtitle">BOOKSTORE · ADMIN PANEL</div>
    <h1>Login Admin</h1>
    <p class="desc">Masuk untuk mengelola toko buku.</p>

    <?php if ($error !== ""): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Email Admin</label>
        <input type="email" id="email" name="email" required autocomplete="username"
               value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <button type="submit" name="masuk">Masuk ke Dashboard</button>
    </form>
    <a class="back" href="../index.php">← Kembali ke website</a>
</div>
</body>
</html>
