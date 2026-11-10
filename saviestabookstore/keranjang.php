<?php

include "functions.php";

/* =========================
   USER KONFIRMASI PESANAN SELESAI
========================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['pesanan_selesai'])
) {

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = (int)$_SESSION['user']['id'];
    $pesanan_id = (int)($_POST['pesanan_id'] ?? 0);

    if ($pesanan_id <= 0) {
        header("Location: keranjang.php?selesai=gagal");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE pesanan
         SET status = 'Selesai'
         WHERE id = ?
         AND user_id = ?
         AND status = 'Dikirim'"
    );

    if (!$stmt) {
        header("Location: keranjang.php?selesai=gagal");
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $pesanan_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $berhasil = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    if ($berhasil > 0) {
        header("Location: keranjang.php?selesai=berhasil");
        exit;
    }

    header("Location: keranjang.php?selesai=gagal");
    exit;
}


/* =========================
   FUNGSI CEK STOK
========================= */

function ambilStokBuku($koneksi, $id) {

    $id = (int)$id;

    $query = mysqli_query(
        $koneksi,
        "SELECT stok FROM buku WHERE id = $id LIMIT 1"
    );

    if (!$query || mysqli_num_rows($query) == 0) {
        return null;
    }

    $data = mysqli_fetch_assoc($query);

    return max(0, (int)$data['stok']);
}


/* =========================
   TAMBAH KE KERANJANG
========================= */

if (isset($_POST['tambah'])) {

    $id = (int)($_POST['id'] ?? 0);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $stok = ambilStokBuku($koneksi, $id);
    $jumlahSekarang = (int)($_SESSION['cart'][$id] ?? 0);

    if (
        $stok !== null &&
        $stok > 0 &&
        $jumlahSekarang < $stok
    ) {
        $_SESSION['cart'][$id] = $jumlahSekarang + 1;
    }

    header("Location: keranjang.php");
    exit;
}


/* =========================
   KURANG JUMLAH
========================= */

if (isset($_POST['kurang'])) {

    $id = (int)($_POST['id'] ?? 0);

    if (isset($_SESSION['cart'][$id])) {

        $_SESSION['cart'][$id]--;

        if ($_SESSION['cart'][$id] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    }

    header("Location: keranjang.php");
    exit;
}


/* =========================
   TAMBAH JUMLAH
   SESUAI STOK DATABASE
========================= */

if (isset($_POST['tambah_jumlah'])) {

    $id = (int)($_POST['id'] ?? 0);

    if (isset($_SESSION['cart'][$id])) {

        $stok = ambilStokBuku($koneksi, $id);
        $jumlahSekarang = (int)$_SESSION['cart'][$id];

        if (
            $stok !== null &&
            $stok > 0 &&
            $jumlahSekarang < $stok
        ) {
            $_SESSION['cart'][$id]++;
        }
    }

    header("Location: keranjang.php");
    exit;
}


/* =========================
   HAPUS BUKU
========================= */

if (isset($_POST['hapus'])) {

    $id = (int)($_POST['id'] ?? 0);

    unset($_SESSION['cart'][$id]);

    header("Location: keranjang.php");
    exit;
}


/* =========================
   DATA KERANJANG
========================= */

$cart = $_SESSION['cart'] ?? [];

$books = [];

$total = 0;

$totalItem = 0;

if (!empty($cart)) {

    $ids = implode(
        ',',
        array_map('intval', array_keys($cart))
    );

    $query = mysqli_query(
        $koneksi,
        "SELECT buku.*, kategori.nama AS kategori
         FROM buku
         LEFT JOIN kategori
         ON kategori.id = buku.kategori_id
         WHERE buku.id IN ($ids)"
    );

    if ($query) {

        while ($b = mysqli_fetch_assoc($query)) {

            $idBuku = (int)$b['id'];
            $stok = max(0, (int)$b['stok']);
            $jumlah = (int)($cart[$idBuku] ?? 0);

            if ($stok <= 0) {
                unset($_SESSION['cart'][$idBuku]);
                continue;
            }

            if ($jumlah > $stok) {
                $jumlah = $stok;
                $_SESSION['cart'][$idBuku] = $stok;
            }

            if ($jumlah <= 0) {
                unset($_SESSION['cart'][$idBuku]);
                continue;
            }

            $subtotal = (float)$b['harga'] * $jumlah;

            $total += $subtotal;

            $totalItem += $jumlah;

            $books[] = [
                'data' => $b,
                'jumlah' => $jumlah,
                'stok' => $stok,
                'subtotal' => $subtotal
            ];
        }
    }
}


/* =========================
   HISTORY PEMBELIAN
========================= */

$history = [];

if (isset($_SESSION['user'])) {

    $user_id = (int)$_SESSION['user']['id'];

    $history_query = mysqli_query(
        $koneksi,
        "SELECT *
         FROM pesanan
         WHERE user_id = $user_id
         ORDER BY created_at DESC"
    );

    if ($history_query) {

        while ($pesanan = mysqli_fetch_assoc($history_query)) {
            $history[] = $pesanan;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Keranjang — Saviesta</title>

<style>

/* RESET */

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    background: #faf7ef;
    color: #252a21;
    font-family: Arial, sans-serif;
}

a {
    color: inherit;
}


/* HEADER */

header {
    position: sticky;
    top: 0;
    z-index: 100;
    height: 78px;
    padding: 0 7%;
    display: flex;
    align-items: center;
    background: rgba(250,247,239,.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid #ded9cf;
}

nav {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
}


/* LOGO */

.logo {
    display: flex;
    align-items: center;
    text-decoration: none;
    flex-shrink: 0;
}

.logo img {
    width: 42px;
    height: 42px;
    object-fit: contain;
    display: block;
}


/* MENU */

.menu {
    display: flex;
    align-items: center;
    gap: 23px;
}

.menu a {
    text-decoration: none;
    font-size: 13px;
    color: #252a21;
    transition: .2s;
    white-space: nowrap;
}

.menu a:hover {
    opacity: .55;
}

.register {
    background: #252a21;
    color: white !important;
    padding: 10px 18px;
    border-radius: 25px;
}


/* NOTIFIKASI */

.notif {
    padding: 15px 18px;
    margin-bottom: 20px;
    border-radius: 9px;
    font-size: 13px;
    line-height: 1.6;
}

.notif.success {
    background: #dfe9dc;
    color: #42553a;
}

.notif.error {
    background: #f5dddd;
    color: #963e36;
}


/* CONTAINER */

.container {
    width: 88%;
    max-width: 1250px;
    margin: auto;
    padding: 60px 0 100px;
}


/* PAGE HEADER */

.page-top {
    margin-bottom: 45px;
}

.page-label {
    font-size: 10px;
    letter-spacing: 2.5px;
    color: #777;
}

.page-title {
    margin: 12px 0 0;
    font: normal 58px/1 Georgia, serif;
    letter-spacing: -2px;
}

.page-description {
    margin: 12px 0 0;
    color: #777;
    font-size: 14px;
}


/* CART LAYOUT */

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 45px;
    align-items: start;
}

.cart-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}


/* CART ITEM */

.cart-item {
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 12px;
    padding: 22px;
    display: grid;
    grid-template-columns: 105px 1fr auto;
    gap: 22px;
    align-items: center;
    transition: .25s;
}

.cart-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(0,0,0,.07);
}


/* BOOK IMAGE */

.cart-cover {
    width: 105px;
    height: 140px;
    border-radius: 7px;
    overflow: hidden;
    background: #e9e4d8;
    box-shadow: 0 10px 20px rgba(0,0,0,.08);
}

.cart-cover img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    object-position: center;
    transition: .3s;
}

.cart-item:hover .cart-cover img {
    transform: scale(1.04);
}


/* INFO */

.cart-info {
    min-width: 0;
}

.cart-category {
    font-size: 9px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.cart-title {
    margin-top: 8px;
    font: normal 25px Georgia, serif;
    line-height: 1.2;
}

.cart-author {
    margin-top: 6px;
    font-size: 12px;
    color: #777;
}

.cart-price {
    margin-top: 16px;
    font-size: 14px;
    font-weight: bold;
}


/* QUANTITY */

.quantity {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-top: 12px;
}

.quantity form {
    margin: 0;
}

.quantity button {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid #cfc8bc;
    background: transparent;
    cursor: pointer;
    transition: .2s;
}

.quantity button:hover {
    background: #252a21;
    color: white;
    border-color: #252a21;
}

.quantity span {
    min-width: 25px;
    text-align: center;
    font-size: 13px;
}


/* RIGHT */

.cart-right {
    text-align: right;
}

.subtotal-label {
    font-size: 9px;
    color: #888;
    letter-spacing: 1.2px;
    text-transform: uppercase;
}

.subtotal {
    margin-top: 6px;
    font-size: 15px;
    font-weight: bold;
}


/* DELETE */

.delete {
    margin-top: 18px;
    border: 0;
    background: transparent;
    color: #888;
    font-size: 11px;
    cursor: pointer;
    text-decoration: underline;
}

.delete:hover {
    color: #252a21;
}


/* SUMMARY */

.summary {
    background: #e9e4d8;
    border-radius: 12px;
    padding: 32px;
    position: sticky;
    top: 105px;
}

.summary-label {
    font-size: 10px;
    letter-spacing: 2px;
    color: #777;
}

.summary h2 {
    margin: 12px 0 25px;
    font: normal 35px Georgia, serif;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 13px 0;
    border-bottom: 1px solid #d4cec1;
    font-size: 13px;
}

.summary-row:last-of-type {
    border-bottom: 0;
}

.summary-total {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.summary-total span:first-child {
    font-size: 13px;
    color: #777;
}

.summary-total span:last-child {
    font-size: 20px;
    font-weight: bold;
}


/* CHECKOUT */

.checkout {
    display: block;
    margin-top: 25px;
    padding: 15px;
    text-align: center;
    border-radius: 30px;
    background: #252a21;
    color: white;
    text-decoration: none;
    font-size: 13px;
    transition: .25s;
}

.checkout:hover {
    transform: translateY(-2px);
    opacity: .9;
}


/* CONTINUE */

.continue {
    display: block;
    text-align: center;
    margin-top: 15px;
    font-size: 12px;
    text-decoration: none;
    color: #777;
}

.continue:hover {
    color: #252a21;
}


/* EMPTY */

.empty {
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 12px;
    padding: 100px 30px;
    text-align: center;
}

.empty-icon {
    font-size: 45px;
    margin-bottom: 15px;
}

.empty h2 {
    margin: 0;
    font: normal 34px Georgia, serif;
}

.empty p {
    max-width: 400px;
    margin: 12px auto 25px;
    color: #777;
    font-size: 13px;
    line-height: 1.7;
}

.empty a {
    display: inline-block;
    padding: 13px 23px;
    background: #252a21;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-size: 12px;
}


/* HISTORY */

.history {
    margin-top: 85px;
}

.history-head {
    margin-bottom: 25px;
}

.history-label {
    font-size: 10px;
    letter-spacing: 2.5px;
    color: #777;
}

.history-title {
    margin: 10px 0 5px;
    font: normal 38px Georgia, serif;
}

.history-desc {
    margin: 0;
    font-size: 13px;
    color: #777;
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.history-card {
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 12px;
    padding: 22px 25px;
    display: grid;
    grid-template-columns: .8fr 1fr 1.2fr 1fr minmax(230px, auto);
    gap: 20px;
    align-items: center;
    transition: .25s;
}

.history-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(0,0,0,.06);
}

.history-small {
    font-size: 9px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1.4px;
    margin-bottom: 6px;
}

.history-value {
    font-size: 13px;
    font-weight: 600;
}

.history-id {
    font: normal 20px Georgia, serif;
}

.history-total {
    font-size: 15px;
    font-weight: bold;
}

.status {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 20px;
    background: #eee9df;
    font-size: 10px;
    color: #555;
    white-space: nowrap;
}


/* TOMBOL STRUK */

.history-actions {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: center;
    gap: 10px;
}

.receipt-button {
    display: inline-block;
    padding: 11px 16px;
    border-radius: 25px;
    background: #252a21;
    color: #fff;
    text-decoration: none;
    text-align: center;
    font-size: 10px;
    letter-spacing: .5px;
    white-space: nowrap;
    transition: .2s;
}

.receipt-button:hover {
    background: #454b3e;
    transform: translateY(-2px);
}


/* PERINGATAN BARANG SUDAH SAMPAI */

.peringatan-selesai {
    background: #fff4d8;
    border: 1px solid #e8d49e;
    color: #72591d;
    padding: 12px 14px;
    border-radius: 8px;
    font-size: 11px;
    line-height: 1.6;
    margin-bottom: 10px;
    width: 100%;
}

.peringatan-selesai strong {
    display: block;
    margin-bottom: 4px;
    font-size: 12px;
}


/* TOMBOL SELESAI */

.finish-button {
    display: block;
    width: 100%;
    padding: 12px 16px;
    border-radius: 25px;
    background: #252a21;
    color: white;
    border: none;
    font-size: 11px;
    font-weight: bold;
    cursor: pointer;
    transition: .2s;
    white-space: nowrap;
}

.finish-button:hover {
    background: #46513e;
    transform: translateY(-2px);
}


/* HISTORY EMPTY */

.history-empty {
    background: #fffdf9;
    border: 1px dashed #d8d1c5;
    border-radius: 12px;
    padding: 45px 25px;
    text-align: center;
    color: #777;
    font-size: 13px;
}


/* LOGIN HISTORY */

.login-history {
    background: #e9e4d8;
    border-radius: 12px;
    padding: 35px;
    text-align: center;
}

.login-history h3 {
    margin: 0 0 8px;
    font: normal 28px Georgia, serif;
}

.login-history p {
    margin: 0 auto 20px;
    max-width: 430px;
    color: #777;
    font-size: 13px;
    line-height: 1.7;
}

.login-history a {
    display: inline-block;
    background: #252a21;
    color: white;
    text-decoration: none;
    padding: 12px 22px;
    border-radius: 25px;
    font-size: 12px;
}


/* FOOTER */

footer {
    background: #252a21;
    color: white;
    padding: 55px 7%;
}

.footer-inner {
    max-width: 1250px;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-logo {
    font: normal 30px Georgia, serif;
}

.footer-text {
    color: #aaa;
    font-size: 11px;
    line-height: 1.7;
}


/* RESPONSIVE */

@media(max-width: 1100px) {

    .history-card {
        grid-template-columns: 1fr 1fr;
    }

    .history-actions {
        grid-column: 1 / -1;
        max-width: 300px;
    }

}


@media(max-width: 900px) {

    .cart-layout {
        grid-template-columns: 1fr;
    }

    .summary {
        position: static;
    }

    .history-card {
        grid-template-columns: 1fr 1fr;
    }

    .history-actions {
        grid-column: 1 / -1;
        max-width: 300px;
    }

}


@media(max-width: 700px) {

    header {
        padding: 0 5%;
    }

    .menu {
        gap: 10px;
    }

    .menu a:nth-child(2),
    .menu a:nth-child(3),
    .menu a:nth-child(4) {
        display: none;
    }

    .cart-item {
        grid-template-columns: 85px 1fr;
    }

    .cart-cover {
        width: 85px;
        height: 120px;
    }

    .cart-right {
        grid-column: 2;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .history-card {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .history-actions {
        grid-column: auto;
        max-width: 100%;
    }

}


@media(max-width: 500px) {

    .container {
        width: 92%;
    }

    .page-title {
        font-size: 43px;
    }

    .cart-item {
        padding: 16px;
        gap: 15px;
    }

    .cart-title {
        font-size: 20px;
    }

    .summary {
        padding: 25px;
    }

    .history {
        margin-top: 60px;
    }

    .history-title {
        font-size: 32px;
    }

    .history-card {
        padding: 20px;
    }

    .footer-inner {
        display: block;
        text-align: center;
    }

    .footer-text {
        margin-top: 15px;
    }

}

</style>

</head>

<body>


<!-- =========================
     HEADER
========================= -->

<header>

<nav>

<a href="index.php" class="logo">

    <img
        src="L2.png.png"
        alt="Saviesta BookStore"
    >

</a>


<div class="menu">

    <a href="index.php">Home</a>

    <a href="buku.php">Koleksi</a>

    <a href="about.php">About</a>

    <a href="contact.php">Contact</a>

    <a href="keranjang.php">
        🛒 <?= jumlahCart() ?>
    </a>


    <?php if(isset($_SESSION['user'])): ?>

        <span style="font-size:13px;">
            Hi, <?= aman($_SESSION['user']['nama']) ?>
        </span>

        <a href="logout.php">
            Logout
        </a>

    <?php else: ?>

        <a href="login.php">
            Login
        </a>

        <a href="register.php" class="register">
            Register
        </a>

    <?php endif; ?>

</div>

</nav>

</header>


<!-- =========================
     MAIN
========================= -->

<div class="container">


<div class="page-top">

    <div class="page-label">
        YOUR READING BAG
    </div>

    <h1 class="page-title">
        Keranjang
    </h1>

    <p class="page-description">
        Buku pilihan kamu siap menemani
        cerita berikutnya.
    </p>

</div>


<?php if(empty($books)): ?>


<!-- EMPTY -->

<div class="empty">

    <div class="empty-icon">
        📚
    </div>

    <h2>
        Keranjang kamu masih kosong.
    </h2>

    <p>
        Belum ada buku yang masuk ke keranjang.
        Yuk cari cerita baru yang cocok buat kamu.
    </p>

    <a href="buku.php">
        Jelajahi Koleksi
    </a>

</div>


<?php else: ?>


<!-- CART -->

<div class="cart-layout">


<div class="cart-list">


<?php foreach($books as $item): ?>

<?php
$b = $item['data'];
$jumlah = $item['jumlah'];
?>


<div class="cart-item">


<div class="cart-cover">

    <img
        src="img3/<?= aman($b['cover']) ?>"
        alt="<?= aman($b['judul']) ?>"
    >

</div>


<div class="cart-info">

    <div class="cart-category">
        <?= aman($b['kategori']) ?>
    </div>

    <div class="cart-title">
        <?= aman($b['judul']) ?>
    </div>

    <div class="cart-author">
        <?= aman($b['penulis']) ?>
    </div>

    <div class="cart-price">
        <?= rupiah($b['harga']) ?>
    </div>


    <div class="quantity">


        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= $b['id'] ?>"
            >

            <button type="submit" name="kurang">
                −
            </button>

        </form>


        <span>
            <?= $jumlah ?>
        </span>


        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= $b['id'] ?>"
            >

            <button type="submit" name="tambah_jumlah">
                +
            </button>

        </form>


    </div>

</div>


<div class="cart-right">


    <div>

        <div class="subtotal-label">
            Subtotal
        </div>

        <div class="subtotal">
            <?= rupiah($item['subtotal']) ?>
        </div>

    </div>


    <form method="POST">

        <input
            type="hidden"
            name="id"
            value="<?= $b['id'] ?>"
        >

        <button
            type="submit"
            name="hapus"
            class="delete"
        >
            Hapus
        </button>

    </form>


</div>


</div>


<?php endforeach; ?>


</div>


<!-- SUMMARY -->

<div class="summary">


    <div class="summary-label">
        ORDER SUMMARY
    </div>

    <h2>
        Ringkasan
    </h2>


    <div class="summary-row">

        <span>Jumlah Buku</span>

        <span><?= $totalItem ?></span>

    </div>


    <div class="summary-row">

        <span>Subtotal</span>

        <span><?= rupiah($total) ?></span>

    </div>


    <div class="summary-row">

        <span>Pengiriman</span>

        <span>15.000</span>

    </div>


    <div class="summary-total">

        <span>Total</span>

        <span><?= rupiah($total) ?></span>

    </div>


    <!-- CEK LOGIN SEBELUM CHECKOUT -->

    <?php if (isset($_SESSION['user'])): ?>

        <a href="checkout.php" class="checkout">
            Lanjut ke Checkout
        </a>

    <?php else: ?>

        <a
            href="login.php"
            class="checkout"
            onclick="alert('Silakan login terlebih dahulu untuk melanjutkan checkout!');"
        >
            Login Dulu untuk Checkout
        </a>

    <?php endif; ?>


    <a href="buku.php" class="continue">
        ← Lanjut Belanja
    </a>


</div>


</div>


<?php endif; ?>


<!-- =========================
     HISTORY PEMBELIAN
========================= -->

<div class="history">


<div class="history-head">

    <div class="history-label">
        YOUR ORDER HISTORY
    </div>

    <h2 class="history-title">
        Riwayat Pembelian
    </h2>

    <p class="history-desc">
        Lihat kembali pesanan dan pembelian buku kamu di Saviesta.
    </p>

</div>


<!-- NOTIFIKASI SELESAI -->

<?php if(isset($_GET['selesai'])): ?>

    <?php if($_GET['selesai'] === 'berhasil'): ?>

        <div class="notif success">
            ✓ Pesanan berhasil diselesaikan.
            Terima kasih sudah berbelanja di Saviesta!
        </div>

    <?php else: ?>

        <div class="notif error">
            Pesanan gagal diperbarui.
            Silakan coba lagi.
        </div>

    <?php endif; ?>

<?php endif; ?>


<?php if(!isset($_SESSION['user'])): ?>


<div class="login-history">

    <h3>
        Belum Login?
    </h3>

    <p>
        Login dulu untuk melihat riwayat pembelian buku
        yang pernah kamu pesan.
    </p>

    <a href="login.php">
        Login Sekarang
    </a>

</div>


<?php elseif(empty($history)): ?>


<div class="history-empty">

    📖

    <br><br>

    Belum ada riwayat pembelian.

    <br>

    Yuk temukan buku favorit kamu dan mulai belanja.

</div>


<?php else: ?>


<div class="history-list">


<?php foreach($history as $order): ?>

<?php
$statusOrder = strtolower(trim($order['status'] ?? ''));
?>


<div class="history-card">


    <div>

        <div class="history-small">
            Pesanan
        </div>

        <div class="history-id">
            #<?= (int)$order['id'] ?>
        </div>

    </div>


    <div>

        <div class="history-small">
            Tanggal
        </div>

        <div class="history-value">

            <?= !empty($order['created_at'])
                ? date('d M Y', strtotime($order['created_at']))
                : '-' ?>

        </div>

    </div>


    <div>

        <div class="history-small">
            Total Pembayaran
        </div>

        <div class="history-total">
            <?= rupiah($order['total']) ?>
        </div>

    </div>


    <div>

        <div class="history-small">
            Status
        </div>

        <div class="status">
            <?= aman($order['status']) ?>
        </div>

    </div>


    <!-- AKSI PESANAN -->

    <div class="history-actions">


        <?php if($statusOrder === 'dikirim'): ?>

            <!-- PERINGATAN JIKA BARANG SUDAH SAMPAI -->

            <div class="peringatan-selesai">

                <strong>
                    📦 Pesanan Sedang Dikirim
                </strong>

                Jika barang sudah sampai dan kamu
                sudah menerimanya, silakan klik
                tombol SELESAI di bawah ini.

            </div>


            <!-- TOMBOL KONFIRMASI SELESAI -->

            <form
                method="POST"
                onsubmit="return confirm('Apakah barang sudah sampai dan kamu terima? Klik OK untuk menyelesaikan pesanan.');"
            >

                <input
                    type="hidden"
                    name="pesanan_id"
                    value="<?= (int)$order['id'] ?>"
                >

                <button
                    type="submit"
                    name="pesanan_selesai"
                    value="1"
                    class="finish-button"
                >
                    ✓ SELESAI
                </button>

            </form>


        <?php elseif($statusOrder === 'selesai'): ?>

            <span
                class="status"
                style="background:#dfe9dc;color:#42553a;"
            >
                ✓ PESANAN SELESAI
            </span>


        <?php else: ?>

            <a
                href="sukses.php?id=<?= (int)$order['id'] ?>"
                class="receipt-button"
            >
                LIHAT STRUK
            </a>

        <?php endif; ?>


    </div>


</div>


<?php endforeach; ?>


</div>


<?php endif; ?>


</div>


</div>


<!-- =========================
     FOOTER
========================= -->

<footer>

<div class="footer-inner">


    <div class="footer-logo">
        Saviesta Family
    </div>


    <div class="footer-text">

        Find your next favorite story.

        <br>

        © 2026 Saviesta Bookstore

    </div>


</div>

</footer>


</body>
</html>