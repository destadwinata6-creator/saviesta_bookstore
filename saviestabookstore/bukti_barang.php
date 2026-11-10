<?php

include "functions.php";

/* CEK LOGIN */

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$order_id = (int)($_GET['id'] ?? 0);

/* AMBIL PESANAN */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM pesanan
     WHERE id = ?
     AND user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result);

if (!$pesanan) {
    die("Pesanan tidak ditemukan.");
}

/* CEK METODE COD */

if (stripos($pesanan['metode'], 'COD') === false) {
    die("Halaman ini khusus untuk pesanan COD.");
}

/* CEK STATUS SELESAI */

if (strtolower(trim($pesanan['status'])) !== 'selesai') {
    die("Foto bukti barang tersedia setelah pesanan selesai.");
}

/* FOTO */

$foto = $pesanan['foto_bukti_barang'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Foto Bukti Barang — Saviesta</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 40px 20px;
    background: #faf7ef;
    color: #252a21;
    font-family: Arial, sans-serif;
}

.container {
    max-width: 750px;
    margin: 40px auto;
    padding: 35px;
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 14px;
    text-align: center;
}

.label {
    font-size: 10px;
    letter-spacing: 3px;
    color: #888;
}

h1 {
    font: normal 42px Georgia, serif;
    margin: 15px 0;
}

.description {
    color: #777;
    font-size: 13px;
    line-height: 1.7;
}

.order {
    margin: 25px 0;
    padding: 15px;
    background: #f2f4ed;
    border-radius: 8px;
    font-size: 13px;
}

.photo {
    width: 100%;
    max-height: 550px;
    object-fit: contain;
    border-radius: 10px;
    margin-top: 15px;
    background: #f4f1e9;
}

.no-photo {
    padding: 45px 20px;
    background: #f6f0e2;
    color: #806d3d;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.7;
}

.button {
    display: inline-block;
    margin-top: 25px;
    padding: 13px 25px;
    background: #252a21;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-size: 12px;
}

</style>

</head>

<body>

<div class="container">

    <div class="label">
        SAVIESTA BOOKSTORE
    </div>

    <h1>Foto Bukti Barang</h1>

    <p class="description">
        Berikut adalah foto bukti barang dari pesanan COD kamu.
    </p>

    <div class="order">
        Nomor Pesanan: <b>#<?= (int)$pesanan['id'] ?></b>
        <br><br>
        Penerima: <b><?= aman($pesanan['nama']) ?></b>
    </div>

    <?php if (!empty($foto)): ?>

        <?php
        $namaFoto = basename($foto);
        $pathFoto = "uploads/bukti_barang/" . rawurlencode($namaFoto);
        ?>

        <img
            src="<?= aman($pathFoto) ?>"
            class="photo"
            alt="Foto Bukti Barang"
        >

    <?php else: ?>

        <div class="no-photo">
            Foto bukti barang belum diunggah oleh admin.
            Silakan cek kembali nanti.
        </div>

    <?php endif; ?>

    <a href="riwayat.php" class="button">
        KEMBALI KE RIWAYAT
    </a>

</div>

</body>
</html>