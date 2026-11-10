<?php

include "functions.php";

/* =========================
   KONFIGURASI PEMBAYARAN
========================= */

$nama_bank = "BCA";
$nomor_rekening = "087811043366";
$atas_nama = "SAVIESTA";

$nomor_dana = "087811043366";
$nomor_gopay = "087811043366";
$atas_nama_ewallet = "SAVIESTA";

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    die("ID user tidak ditemukan. Silakan login kembali.");
}

$user_id = (int) $_SESSION['user']['id'];

/* =========================
   CEK KERANJANG
========================= */

if (empty($_SESSION['cart'])) {
    header("Location: keranjang.php");
    exit;
}

/* =========================
   AMBIL DATA BUKU
========================= */

$items = [];
$subtotal = 0;

$ids = implode(
    ',',
    array_map('intval', array_keys($_SESSION['cart']))
);

$data = mysqli_query(
    $koneksi,
    "SELECT * FROM buku WHERE id IN ($ids)"
);

if (!$data) {
    die("Gagal mengambil data buku: " . mysqli_error($koneksi));
}

while ($b = mysqli_fetch_assoc($data)) {

    $jumlah = (int) $_SESSION['cart'][$b['id']];

    $b['jumlah'] = $jumlah;
    $b['total'] = $jumlah * (int) $b['harga'];

    $subtotal += $b['total'];

    $items[] = $b;
}

if (empty($items)) {
    die("Data buku tidak ditemukan.");
}

/* =========================
   ONGKIR DAN TOTAL
========================= */

$ongkir = 15000 ;
$total = $subtotal + $ongkir;

/* =========================
   PROSES CHECKOUT
========================= */

if (isset($_POST['bayar'])) {

    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $metode = trim($_POST['metode'] ?? '');

    /* CEK DATA */

    if (
        $nama === '' ||
        $alamat === '' ||
        $telepon === '' ||
        $metode === ''
    ) {
        die("Semua data checkout wajib diisi.");
    }

    if (!in_array($metode, ['Transfer Bank', 'DANA', 'GoPay'], true)) {
        die("Metode pembayaran tidak valid.");
    }

    $nama_db = mysqli_real_escape_string($koneksi, $nama);
    $alamat_db = mysqli_real_escape_string($koneksi, $alamat);
    $telepon_db = mysqli_real_escape_string($koneksi, $telepon);
    $metode_db = mysqli_real_escape_string($koneksi, $metode);

    /* =========================
       CEK STOK BUKU
    ========================= */

    foreach ($items as $b) {

        $buku_id = (int) $b['id'];
        $jumlah = (int) $b['jumlah'];

        $stok_query = mysqli_query(
            $koneksi,
            "SELECT stok FROM buku
             WHERE id = $buku_id
             LIMIT 1"
        );

        if (!$stok_query) {
            die("Gagal mengecek stok: " . mysqli_error($koneksi));
        }

        $stok_data = mysqli_fetch_assoc($stok_query);

        if (!$stok_data || (int) $stok_data['stok'] < $jumlah) {
            die(
                "Stok buku <b>" . aman($b['judul']) .
                "</b> tidak mencukupi."
            );
        }
    }

    /* =========================
       UPLOAD BUKTI PEMBAYARAN
    ========================= */

    $bukti_pembayaran = null;

    if (in_array($metode, ['Transfer Bank', 'DANA', 'GoPay'], true)) {

        if (
            !isset($_FILES['bukti_pembayaran']) ||
            $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK
        ) {
            die("Silakan upload bukti pembayaran terlebih dahulu.");
        }

        $file = $_FILES['bukti_pembayaran'];

        /* BATAS UKURAN 2 MB */

        if ($file['size'] > 2 * 1024 * 1024) {
            die("Ukuran bukti pembayaran maksimal 2 MB.");
        }

        /* CEK FORMAT GAMBAR */

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!$finfo) {
            die("Gagal memeriksa format gambar.");
        }

        $mime = finfo_file($finfo, $file['tmp_name']);

        finfo_close($finfo);

        $format = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($format[$mime])) {
            die("Bukti pembayaran harus JPG, PNG, atau WEBP.");
        }

        /* FOLDER UPLOAD */

        $folder = __DIR__ . "/uploads/bukti_pembayaran/";

        if (!is_dir($folder)) {

            if (!mkdir($folder, 0755, true)) {
                die("Folder bukti pembayaran gagal dibuat.");
            }
        }

        /* NAMA FILE UNIK */

        $namaFile = "bukti_" .
                    $user_id . "_" .
                    time() . "_" .
                    bin2hex(random_bytes(4)) . "." .
                    $format[$mime];

        $lokasiFile = $folder . $namaFile;

        if (!move_uploaded_file($file['tmp_name'], $lokasiFile)) {
            die("Gagal mengupload bukti pembayaran.");
        }

        $bukti_pembayaran = "uploads/bukti_pembayaran/" . $namaFile;
    }

    /* =========================
       STATUS PESANAN
    ========================= */

    $status_pesanan = "Menunggu Konfirmasi";

    $status_db = mysqli_real_escape_string(
        $koneksi,
        $status_pesanan
    );

    $bukti_db = $bukti_pembayaran
        ? "'" . mysqli_real_escape_string($koneksi, $bukti_pembayaran) . "'"
        : "NULL";

    /* =========================
       SIMPAN PESANAN
    ========================= */

    $query_pesanan = "
        INSERT INTO pesanan
        (
            user_id,
            nama,
            alamat,
            telepon,
            metode,
            subtotal,
            ongkir,
            total,
            status,
            bukti_pembayaran
        )
        VALUES
        (
            '$user_id',
            '$nama_db',
            '$alamat_db',
            '$telepon_db',
            '$metode_db',
            '$subtotal',
            '$ongkir',
            '$total',
            '$status_db',
            $bukti_db
        )
    ";

    $insert_pesanan = mysqli_query(
        $koneksi,
        $query_pesanan
    );

    if (!$insert_pesanan) {

        if ($bukti_pembayaran) {
            @unlink(__DIR__ . "/" . $bukti_pembayaran);
        }

        die(
            "<h3>Gagal menyimpan pesanan.</h3>" .
            mysqli_error($koneksi)
        );
    }

    $pesanan_id = mysqli_insert_id($koneksi);

    /* =========================
       SIMPAN DETAIL PESANAN
    ========================= */

    foreach ($items as $b) {

        $buku_id = (int) $b['id'];
        $jumlah = (int) $b['jumlah'];
        $harga = (int) $b['harga'];

        $detail = mysqli_query(
            $koneksi,
            "INSERT INTO detail_pesanan
            (
                pesanan_id,
                buku_id,
                jumlah,
                harga
            )
            VALUES
            (
                '$pesanan_id',
                '$buku_id',
                '$jumlah',
                '$harga'
            )"
        );

        if (!$detail) {
            die(
                "<h3>Gagal menyimpan detail pesanan.</h3>" .
                mysqli_error($koneksi)
            );
        }

        /* KURANGI STOK */

        $update_stok = mysqli_query(
            $koneksi,
            "UPDATE buku
             SET stok = stok - $jumlah
             WHERE id = $buku_id
             AND stok >= $jumlah"
        );

        if (
            !$update_stok ||
            mysqli_affected_rows($koneksi) !== 1
        ) {
            die("Gagal memperbarui stok buku.");
        }
    }

    /* =========================
       KOSONGKAN KERANJANG
    ========================= */

    $_SESSION['cart'] = [];

    header("Location: sukses.php?id=" . $pesanan_id);
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Checkout — Saviesta</title>

<style>

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

.logo {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo img {
    width: 42px;
    height: 42px;
    object-fit: contain;
}

.menu {
    display: flex;
    align-items: center;
    gap: 23px;
}

.menu a {
    text-decoration: none;
    font-size: 13px;
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

/* CONTAINER */

.container {
    width: 88%;
    max-width: 1250px;
    margin: auto;
    padding: 60px 0 100px;
}

.back {
    display: inline-block;
    color: #777;
    text-decoration: none;
    font-size: 12px;
    margin-bottom: 35px;
}

.page-label {
    font-size: 10px;
    letter-spacing: 2.5px;
    color: #777;
}

.page-title {
    margin: 12px 0 10px;
    font: normal 58px/1 Georgia, serif;
    letter-spacing: -2px;
}

.page-description {
    margin: 0;
    color: #777;
    font-size: 14px;
    line-height: 1.7;
}

/* LAYOUT */

.checkout-layout {
    margin-top: 50px;
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 45px;
    align-items: start;
}

/* FORM */

.form-card {
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 10px;
    padding: 38px;
}

.card-label {
    font-size: 10px;
    letter-spacing: 2px;
    color: #777;
}

.form-card h2 {
    margin: 12px 0 30px;
    font: normal 34px Georgia, serif;
}

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    margin-bottom: 9px;
    font-size: 12px;
    font-weight: bold;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    border: 1px solid #d8d2c7;
    background: #faf7ef;
    border-radius: 6px;
    padding: 14px 15px;
    outline: none;
    font-family: Arial, sans-serif;
    font-size: 13px;
    color: #252a21;
}

.form-group input {
    height: 48px;
}

.form-group textarea {
    height: 120px;
    resize: vertical;
}

.form-group select {
    height: 48px;
    cursor: pointer;
}

/* INFORMASI PEMBAYARAN */

.rekening-box {
    display: none;
    margin-top: 18px;
    padding: 20px;
    background: #f0ece2;
    border: 1px solid #ded6c7;
    border-radius: 8px;
}

.rekening-box h3 {
    margin: 0 0 12px;
    font: normal 23px Georgia, serif;
}

.rekening-box p {
    margin: 8px 0;
    font-size: 13px;
    line-height: 1.6;
}

.nomor-rekening {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 12px;
    padding: 12px;
    background: #fffdf9;
    border: 1px solid #ded6c7;
    border-radius: 6px;
}

.nomor-rekening strong {
    font-size: 16px;
    letter-spacing: 1px;
    overflow-wrap: anywhere;
}

.copy-button {
    flex-shrink: 0;
    border: 0;
    padding: 9px 12px;
    background: #252a21;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 11px;
}

/* UPLOAD BUKTI */

.upload-box {
    margin-top: 22px;
    padding: 18px;
    background: #fffdf9;
    border: 1px dashed #aaa18f;
    border-radius: 8px;
}

.upload-box label {
    display: block;
    margin-bottom: 12px;
    font-size: 13px;
    font-weight: bold;
}

.upload-box input {
    width: 100%;
    font-size: 12px;
}

.upload-info {
    margin-top: 10px;
    color: #777;
    font-size: 11px;
    line-height: 1.6;
}

.payment-note {
    margin-top: 28px;
    padding: 15px;
    background: #f0ece2;
    border-radius: 6px;
    color: #777;
    font-size: 11px;
    line-height: 1.6;
}

/* SUMMARY */

.summary {
    background: #e9e4d8;
    border-radius: 10px;
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

.order-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding-bottom: 20px;
    border-bottom: 1px solid #d4cec1;
}

.order-item {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.order-item-title {
    font: normal 16px Georgia, serif;
}

.order-item-qty {
    margin-top: 4px;
    color: #888;
    font-size: 10px;
}

.order-item-price {
    white-space: nowrap;
    font-size: 12px;
    font-weight: bold;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
    font-size: 13px;
    border-bottom: 1px solid #d4cec1;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    gap: 10px;
}

.summary-total span:first-child {
    color: #777;
    font-size: 13px;
}

.summary-total span:last-child {
    font-size: 20px;
    font-weight: bold;
}

/* BUTTON */

.pay-button {
    width: 100%;
    margin-top: 25px;
    padding: 16px;
    border: 0;
    border-radius: 30px;
    background: #252a21;
    color: white;
    font-size: 12px;
    font-weight: bold;
    cursor: pointer;
}

.pay-button:hover {
    transform: translateY(-2px);
    opacity: .9;
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

@media(max-width:900px) {

    .checkout-layout {
        grid-template-columns: 1fr;
    }

    .summary {
        position: static;
    }
}

@media(max-width:700px) {

    .menu {
        gap: 10px;
    }

    .menu a:nth-child(2),
    .menu a:nth-child(3),
    .menu a:nth-child(4) {
        display: none;
    }

    .page-title {
        font-size: 45px;
    }

    .form-card {
        padding: 28px;
    }
}

@media(max-width:500px) {

    .container {
        width: 92%;
    }

    .page-title {
        font-size: 40px;
    }

    .summary {
        padding: 25px;
    }

    .footer-inner {
        display: block;
        text-align: center;
    }

    .footer-text {
        margin-top: 15px;
    }

    .nomor-rekening {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>

</head>

<body>

<header>

<nav>

<a href="index.php" class="logo">
    <img src="L2.png.png" alt="Saviesta BookStore">
</a>

<div class="menu">

    <a href="index.php">Home</a>
    <a href="buku.php">Koleksi</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>

    <a href="keranjang.php">
        🛒 <?= jumlahCart() ?>
    </a>

    <?php if (isset($_SESSION['user'])): ?>

        <span style="font-size:13px;">
            Hi, <?= aman($_SESSION['user']['nama']) ?>
        </span>

        <a href="logout.php">Logout</a>

    <?php else: ?>

        <a href="login.php">Login</a>
        <a href="register.php" class="register">Register</a>

    <?php endif; ?>

</div>

</nav>

</header>

<div class="container">

<a href="keranjang.php" class="back">
    ← Kembali ke Keranjang
</a>

<div class="page-label">
    SAVIESTA CHECKOUT
</div>

<h1 class="page-title">
    Selesaikan Pesanan.
</h1>

<p class="page-description">
    Isi detail pengiriman di bawah ini
    dan pilih metode pembayaran kamu.
</p>

<div class="checkout-layout">

    <div class="form-card">

        <div class="card-label">
            DELIVERY DETAILS
        </div>

        <h2>Informasi Pengiriman</h2>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label>Nama Penerima</label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Masukkan nama penerima"
                    required
                >

            </div>

            <div class="form-group">

                <label>Alamat Lengkap</label>

                <textarea
                    name="alamat"
                    placeholder="Masukkan alamat lengkap..."
                    required
                ></textarea>

            </div>

            <div class="form-group">

                <label>Nomor Telepon</label>

                <input
                    type="text"
                    name="telepon"
                    placeholder="Contoh: 081234567890"
                    required
                >

            </div>

            <div class="form-group">

                <label>Metode Pembayaran</label>

                <select
                    name="metode"
                    id="metode"
                    required
                >

                    <option value="">
                        Pilih metode pembayaran
                    </option>

                    <option value="Transfer Bank">
                        Transfer Bank
                    </option>

                    <option value="DANA">
                        DANA
                    </option>

                    <option value="GoPay">
                        GoPay
                    </option>

                </select>

            </div>

            <!-- INFORMASI PEMBAYARAN -->

            <div class="rekening-box" id="rekeningBox">

                <h3>Informasi Pembayaran</h3>

                <p>
                    Silakan lakukan pembayaran sesuai total pesanan
                    ke nomor pembayaran berikut.
                </p>

                <p>
                    <strong>Metode:</strong>
                    <span id="namaMetode">Transfer Bank</span>
                </p>

                <p id="bankInfo">
                    <strong>Bank:</strong>
                    <?= aman($nama_bank) ?>
                </p>

                <p>
                    <strong>Atas Nama:</strong>
                    <span id="atasNama">
                        <?= aman($atas_nama) ?>
                    </span>
                </p>

                <div class="nomor-rekening">

                    <strong id="nomorRekening">
                        <?= aman($nomor_rekening) ?>
                    </strong>

                    <button
                        type="button"
                        class="copy-button"
                        onclick="salinRekening()"
                    >
                        SALIN NOMOR
                    </button>

                </div>

                <p
                    id="pesanSalin"
                    style="display:none;color:#42613a;"
                >
                    Nomor berhasil disalin.
                </p>

                <!-- UPLOAD BUKTI PEMBAYARAN -->

                <div class="upload-box">

                    <label for="buktiPembayaran">
                        Upload Bukti Pembayaran
                    </label>

                    <input
                        type="file"
                        name="bukti_pembayaran"
                        id="buktiPembayaran"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="upload-info">
                        Upload foto atau screenshot bukti pembayaran.
                        <br>
                        Format JPG, PNG, atau WEBP.
                        Maksimal 2 MB.
                    </div>

                </div>

            </div>

            <div class="payment-note">

                Pesanan Transfer Bank, DANA, dan GoPay akan
                menunggu konfirmasi admin setelah bukti pembayaran
                berhasil dikirim.

            </div>

            <button
                type="submit"
                name="bayar"
                class="pay-button"
            >
                BAYAR SEKARANG →
            </button>

        </form>

    </div>

    <!-- RINGKASAN PESANAN -->

    <div class="summary">

        <div class="summary-label">
            YOUR ORDER
        </div>

        <h2>Ringkasan Pesanan</h2>

        <div class="order-items">

            <?php foreach ($items as $b): ?>

                <div class="order-item">

                    <div>

                        <div class="order-item-title">
                            <?= aman($b['judul']) ?>
                        </div>

                        <div class="order-item-qty">
                            <?= $b['jumlah'] ?>
                            ×
                            <?= rupiah($b['harga']) ?>
                        </div>

                    </div>

                    <div class="order-item-price">
                        <?= rupiah($b['total']) ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="summary-row">

            <span>Subtotal</span>

            <span>
                <?= rupiah($subtotal) ?>
            </span>

        </div>

        <div class="summary-row">

            <span>Pengiriman</span>

            <span>
                <?= rupiah($ongkir) ?>
            </span>

        </div>

        <div class="summary-total">

            <span>Total Pembayaran</span>

            <span>
                <?= rupiah($total) ?>
            </span>

        </div>

    </div>

</div>

</div>

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

<script>

/* =========================
   TAMPILKAN INFORMASI PEMBAYARAN
========================= */

const metodeSelect = document.getElementById("metode");

const rekeningBox = document.getElementById("rekeningBox");

const buktiPembayaran = document.getElementById("buktiPembayaran");

function tampilkanRekening() {

    const metode = metodeSelect.value;

    const bankInfo = document.getElementById("bankInfo");

    const namaMetode = document.getElementById("namaMetode");

    const nomorRekening = document.getElementById("nomorRekening");

    const atasNama = document.getElementById("atasNama");

    const pesanSalin = document.getElementById("pesanSalin");

    pesanSalin.style.display = "none";

    if (metode === "Transfer Bank") {

        rekeningBox.style.display = "block";

        namaMetode.innerText = "Transfer Bank";

        bankInfo.style.display = "block";

        nomorRekening.innerText = "<?= aman($nomor_rekening) ?>";

        atasNama.innerText = "<?= aman($atas_nama) ?>";

        buktiPembayaran.required = true;

    } else if (metode === "DANA") {

        rekeningBox.style.display = "block";

        namaMetode.innerText = "DANA";

        bankInfo.style.display = "none";

        nomorRekening.innerText = "<?= aman($nomor_dana) ?>";

        atasNama.innerText = "<?= aman($atas_nama_ewallet) ?>";

        buktiPembayaran.required = true;

    } else if (metode === "GoPay") {

        rekeningBox.style.display = "block";

        namaMetode.innerText = "GoPay";

        bankInfo.style.display = "none";

        nomorRekening.innerText = "<?= aman($nomor_gopay) ?>";

        atasNama.innerText = "<?= aman($atas_nama_ewallet) ?>";

        buktiPembayaran.required = true;

    } else {

        rekeningBox.style.display = "none";

        buktiPembayaran.required = false;

        buktiPembayaran.value = "";

    }

}

metodeSelect.addEventListener(
    "change",
    tampilkanRekening
);

tampilkanRekening();

/* =========================
   SALIN NOMOR PEMBAYARAN
========================= */

function salinRekening() {

    const nomor = document
        .getElementById("nomorRekening")
        .innerText
        .trim();

    const pesan = document.getElementById("pesanSalin");

    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(nomor)

            .then(function () {

                pesan.style.display = "block";

            })

            .catch(function () {

                salinManual(nomor, pesan);

            });

    } else {

        salinManual(nomor, pesan);

    }

}

function salinManual(nomor, pesan) {

    const input = document.createElement("textarea");

    input.value = nomor;

    input.style.position = "fixed";

    input.style.opacity = "0";

    document.body.appendChild(input);

    input.focus();

    input.select();

    try {

        document.execCommand("copy");

        pesan.style.display = "block";

    } catch (error) {

        alert(
            "Gagal menyalin. Silakan salin nomor pembayaran secara manual."
        );

    }

    document.body.removeChild(input);

}

</script>

</body>
</html>