<?php

include "functions.php";

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

/* =========================
   FUNGSI
========================= */

function strukTersedia($status) {

    $status = strtolower(trim($status));

    $statusValid = [
        "pembayaran berhasil",
        "pesanan diproses",
        "dikirim",
        "selesai"
    ];

    return in_array($status, $statusValid, true);
}

/* =========================
   PROSES KONFIRMASI BARANG
========================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['konfirmasi_barang'])
) {

    $pesanan_id = filter_input(
        INPUT_POST,
        'pesanan_id',
        FILTER_VALIDATE_INT
    );

    if (!$pesanan_id) {
        header("Location: riwayat.php?konfirmasi=gagal");
        exit;
    }

    /* AMBIL PESANAN MILIK USER */

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, status, foto_bukti_barang
         FROM pesanan
         WHERE id = ? AND user_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Gagal memproses pesanan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $pesanan_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);

    $dataPesanan = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (!$dataPesanan) {
        header("Location: riwayat.php?konfirmasi=gagal");
        exit;
    }

    /* CEK STATUS HARUS DIKIRIM */

    if (strtolower(trim($dataPesanan['status'])) !== 'dikirim') {
        header("Location: riwayat.php?konfirmasi=gagal");
        exit;
    }

    /* CEK FILE */

    if (
        !isset($_FILES['foto_bukti']) ||
        $_FILES['foto_bukti']['error'] !== UPLOAD_ERR_OK
    ) {
        header("Location: riwayat.php?konfirmasi=upload_gagal");
        exit;
    }

    $file = $_FILES['foto_bukti'];

    /* BATAS UKURAN 5 MB */

    if ($file['size'] > 5 * 1024 * 1024) {
        header("Location: riwayat.php?konfirmasi=ukuran");
        exit;
    }

    /* VALIDASI GAMBAR */

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $mime = finfo_file($finfo, $file['tmp_name']);

    finfo_close($finfo);

    $formatValid = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($formatValid[$mime])) {
        header("Location: riwayat.php?konfirmasi=format");
        exit;
    }

    /* BUAT FOLDER */

    $folder = __DIR__ . "/uploads/bukti_barang/";

    if (!is_dir($folder)) {

        if (!mkdir($folder, 0755, true)) {
            header("Location: riwayat.php?konfirmasi=upload_gagal");
            exit;
        }
    }

    /* NAMA FILE */

    $ekstensi = $formatValid[$mime];

    $namaFile = "bukti_pembeli_" .
        $pesanan_id . "_" .
        bin2hex(random_bytes(8)) .
        "." . $ekstensi;

    $tujuan = $folder . $namaFile;

    /* PINDAHKAN FILE */

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        header("Location: riwayat.php?konfirmasi=upload_gagal");
        exit;
    }

    /* SIMPAN FOTO DAN UBAH STATUS */

    $pathDatabase = "uploads/bukti_barang/" . $namaFile;

    $update = mysqli_prepare(
        $koneksi,
        "UPDATE pesanan
         SET foto_bukti_barang = ?,
             status = 'Selesai'
         WHERE id = ?
         AND user_id = ?
         AND status = 'Dikirim'"
    );

    if (!$update) {

        if (file_exists($tujuan)) {
            unlink($tujuan);
        }

        die("Gagal memperbarui pesanan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $update,
        "sii",
        $pathDatabase,
        $pesanan_id,
        $user_id
    );

    $berhasil = mysqli_stmt_execute($update);

    $jumlahBerubah = mysqli_stmt_affected_rows($update);

    mysqli_stmt_close($update);

    if (!$berhasil || $jumlahBerubah < 1) {

        if (file_exists($tujuan)) {
            unlink($tujuan);
        }

        header("Location: riwayat.php?konfirmasi=gagal");
        exit;
    }

    header("Location: riwayat.php?konfirmasi=berhasil");
    exit;
}

/* =========================
   AMBIL RIWAYAT PESANAN
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT *
     FROM pesanan
     WHERE user_id = $user_id
     ORDER BY id DESC"
);

if (!$query) {
    die("Gagal mengambil riwayat pesanan: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Riwayat Pesanan — Saviesta</title>

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
    color: #252a21;
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
    margin-bottom: 22px;
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
    max-width: 1150px;
    margin: auto;
    padding: 60px 0 100px;
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
    margin: 0 0 45px;
    color: #777;
    font-size: 14px;
}

/* ORDER CARD */

.order-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 12px;
    padding: 25px 28px;
    transition: .25s;
}

.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(0,0,0,.07);
}

.order-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 18px;
    border-bottom: 1px solid #e1dbd0;
    gap: 15px;
}

.order-number {
    font-size: 11px;
    color: #777;
    letter-spacing: 1px;
}

.order-id {
    font-weight: bold;
    color: #252a21;
}

/* STATUS */

.status {
    padding: 7px 13px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: .5px;
}

.status.success {
    background: #dfe9dc;
    color: #42553a;
}

.status.pending {
    background: #eee5ce;
    color: #806d3d;
}

.status.cancel {
    background: #f5dddd;
    color: #963e36;
}

/* INFO */

.order-info {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 25px;
    padding: 22px 0;
}

.info-label {
    font-size: 9px;
    letter-spacing: 1.5px;
    color: #888;
    margin-bottom: 7px;
}

.info-value {
    font-size: 13px;
    font-weight: bold;
}

/* BOTTOM */

.order-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 18px;
    border-top: 1px solid #e1dbd0;
    gap: 15px;
}

.order-date {
    color: #888;
    font-size: 11px;
}

.order-total-label {
    color: #777;
    font-size: 11px;
    margin-right: 10px;
}

.order-total {
    font-size: 18px;
    font-weight: bold;
}

/* BUTTON */

.receipt-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.receipt-btn {
    display: inline-block;
    padding: 11px 19px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 11px;
    font-weight: bold;
    transition: .2s;
    border: none;
    cursor: pointer;
}

.receipt-btn:hover {
    transform: translateY(-2px);
    opacity: .85;
}

.view-receipt {
    background: #252a21;
    color: white;
}

.print-receipt {
    background: #e5eadf;
    color: #394833;
}

.photo-proof {
    background: #252a21;
    color: white;
}

/* FORM KONFIRMASI */

.confirm-box {
    margin-top: 20px;
    padding: 20px;
    background: #f2f4ed;
    border: 1px solid #dce3d5;
    border-radius: 10px;
}

.confirm-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
}

.confirm-description {
    color: #6e7666;
    font-size: 12px;
    line-height: 1.7;
    margin-bottom: 15px;
}

.confirm-box input[type=file] {
    display: block;
    width: 100%;
    max-width: 350px;
    margin-bottom: 14px;
    padding: 10px;
    border: 1px solid #d9ddcf;
    border-radius: 7px;
    background: white;
    font-size: 12px;
}

.confirm-box button {
    background: #252a21;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    font-size: 11px;
    font-weight: bold;
    cursor: pointer;
}

.confirm-box button:hover {
    background: #46513e;
}

/* UCAPAN */

.thank-you {
    margin-top: 18px;
    padding: 13px 16px;
    border-radius: 8px;
    background: #f2f4ed;
    color: #52604a;
    font-size: 12px;
    line-height: 1.7;
}

.wait-message {
    margin-top: 18px;
    padding: 13px 16px;
    border-radius: 8px;
    background: #f6f0e2;
    color: #806d3d;
    font-size: 12px;
    line-height: 1.7;
}

/* EMPTY */

.empty {
    background: #fffdf9;
    border: 1px solid #e1dbd0;
    border-radius: 12px;
    padding: 90px 30px;
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
    max-width: 420px;
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

/* FOOTER */

footer {
    background: #252a21;
    color: white;
    padding: 55px 7%;
}

.footer-inner {
    max-width: 1150px;
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

@media(max-width:700px) {

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

    .page-title {
        font-size: 45px;
    }

    .order-info {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .order-top {
        align-items: flex-start;
        gap: 15px;
    }
}

@media(max-width:500px) {

    .container {
        width: 92%;
    }

    .page-title {
        font-size: 40px;
    }

    .order-card {
        padding: 20px;
    }

    .order-bottom {
        display: block;
    }

    .order-total {
        margin-top: 8px;
    }

    .footer-inner {
        display: block;
        text-align: center;
    }

    .footer-text {
        margin-top: 15px;
    }

    .receipt-buttons {
        flex-direction: column;
    }

    .receipt-btn {
        text-align: center;
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

    <a href="riwayat.php">
        Riwayat Pesanan
    </a>

    <?php if(isset($_SESSION['user'])): ?>

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

    <div class="page-label">
        YOUR ORDER HISTORY
    </div>

    <h1 class="page-title">
        Riwayat Pesanan
    </h1>

    <p class="page-description">
        Lihat semua pesanan dan pembelian
        yang pernah kamu lakukan di Saviesta.
    </p>

    <?php if(isset($_GET['konfirmasi'])): ?>

        <?php if($_GET['konfirmasi'] === 'berhasil'): ?>

            <div class="notif success">
                ✓ Terima kasih! Kamu berhasil mengonfirmasi barang sudah diterima.
                Foto bukti telah dikirim ke admin.
            </div>

        <?php elseif($_GET['konfirmasi'] === 'ukuran'): ?>

            <div class="notif error">
                Ukuran foto terlalu besar. Maksimal 5 MB.
            </div>

        <?php elseif($_GET['konfirmasi'] === 'format'): ?>

            <div class="notif error">
                Format foto harus JPG, PNG, atau WEBP.
            </div>

        <?php elseif($_GET['konfirmasi'] === 'upload_gagal'): ?>

            <div class="notif error">
                Foto gagal diunggah. Silakan coba kembali.
            </div>

        <?php else: ?>

            <div class="notif error">
                Konfirmasi gagal. Silakan periksa kembali pesanan dan foto kamu.
            </div>

        <?php endif; ?>

    <?php endif; ?>

    <?php if(mysqli_num_rows($query) == 0): ?>

        <div class="empty">

            <div class="empty-icon">📚</div>

            <h2>Belum ada pesanan.</h2>

            <p>
                Kamu belum melakukan pembelian.
                Yuk cari buku dan mulai cerita baru.
            </p>

            <a href="buku.php">
                Jelajahi Koleksi
            </a>

        </div>

    <?php else: ?>

        <div class="order-list">

            <?php while($pesanan = mysqli_fetch_assoc($query)): ?>

                <?php

                $statusAsli = trim($pesanan['status'] ?? '');
                $status = strtolower($statusAsli);

                $tersedia = strukTersedia($statusAsli);
                $dibatalkan = strpos($status, 'dibatalkan') !== false;

                $fotoBarang = trim($pesanan['foto_bukti_barang'] ?? '');

                ?>

                <div class="order-card">

                    <div class="order-top">

                        <div class="order-number">

                            ORDER #

                            <span class="order-id">
                                <?= (int)$pesanan['id'] ?>
                            </span>

                        </div>

                        <?php if($dibatalkan): ?>

                            <span class="status cancel">
                                ✕ Pesanan Dibatalkan
                            </span>

                        <?php elseif($tersedia): ?>

                            <span class="status success">
                                ✓ <?= aman($statusAsli) ?>
                            </span>

                        <?php else: ?>

                            <span class="status pending">
                                ⏳ <?= aman($statusAsli) ?>
                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="order-info">

                        <div>

                            <div class="info-label">PENERIMA</div>

                            <div class="info-value">
                                <?= aman($pesanan['nama']) ?>
                            </div>

                        </div>

                        <div>

                            <div class="info-label">METODE PEMBAYARAN</div>

                            <div class="info-value">
                                <?= aman($pesanan['metode']) ?>
                            </div>

                        </div>

                        <div>

                            <div class="info-label">TOTAL</div>

                            <div class="info-value">
                                <?= rupiah($pesanan['total']) ?>
                            </div>

                        </div>

                    </div>

                    <div class="order-bottom">

                        <div class="order-date">

                            <?= !empty($pesanan['created_at'])
                                ? date('d M Y, H:i', strtotime($pesanan['created_at']))
                                : '-'; ?>

                        </div>

                        <div>

                            <span class="order-total-label">
                                Total Pembayaran
                            </span>

                            <span class="order-total">
                                <?= rupiah($pesanan['total']) ?>
                            </span>

                        </div>

                    </div>

                    <?php if($dibatalkan): ?>

                        <div class="wait-message">
                            Terima kasih telah mengunjungi Saviesta.
                            Semoga kamu menemukan buku yang sesuai
                            di kesempatan berikutnya.
                        </div>

                    <?php elseif($status === 'dikirim'): ?>

                        <?php if(empty($fotoBarang)): ?>

                            <div class="confirm-box">

                                <div class="confirm-title">
                                    📦 Apakah barang sudah sampai?
                                </div>

                                <div class="confirm-description">
                                    Jika barang sudah kamu terima, silakan unggah
                                    foto barang sebagai bukti penerimaan.
                                    Setelah dikirim, status pesanan akan berubah menjadi Selesai.
                                </div>

                                <form
                                    method="POST"
                                    enctype="multipart/form-data"
                                >

                                    <input
                                        type="hidden"
                                        name="pesanan_id"
                                        value="<?= (int)$pesanan['id'] ?>"
                                    >

                                    <input
                                        type="file"
                                        name="foto_bukti"
                                        accept="image/jpeg,image/png,image/webp"
                                        required
                                    >

                                    <button
                                        type="submit"
                                        name="konfirmasi_barang"
                                        value="1"
                                    >
                                        KONFIRMASI BARANG DITERIMA
                                    </button>

                                </form>

                            </div>

                        <?php endif; ?>

                    <?php elseif($status === 'selesai' && !empty($fotoBarang)): ?>

                        <div class="receipt-buttons">

                            <a
                                href="bukti_barang.php?id=<?= (int)$pesanan['id'] ?>"
                                class="receipt-btn photo-proof"
                            >
                                ▣ LIHAT FOTO BUKTI BARANG
                            </a>

                            <a
                                href="sukses.php?id=<?= (int)$pesanan['id'] ?>"
                                class="receipt-btn view-receipt"
                            >
                                ◉ LIHAT STRUK
                            </a>

                        </div>

                        <div class="thank-you">
                            Terima kasih telah mengonfirmasi barang diterima.
                            Status pesanan kamu sekarang sudah Selesai. 💚
                        </div>

                    <?php elseif($tersedia): ?>

                        <div class="receipt-buttons">

                            <a
                                href="sukses.php?id=<?= (int)$pesanan['id'] ?>"
                                class="receipt-btn view-receipt"
                            >
                                ◉ LIHAT STRUK
                            </a>

                            <a
                                href="sukses.php?id=<?= (int)$pesanan['id'] ?>#cetak"
                                class="receipt-btn print-receipt"
                            >
                                ▤ CETAK STRUK
                            </a>

                        </div>

                        <div class="thank-you">
                            Terima kasih telah berbelanja di Saviesta.
                            Semoga buku pilihanmu menjadi teman cerita
                            yang menyenangkan. 💚
                        </div>

                    <?php else: ?>

                        <div class="wait-message">
                            Terima kasih telah melakukan pemesanan
                            di Saviesta. Pesanan kamu sedang dalam proses
                            konfirmasi. Struk akan tersedia setelah pembayaran
                            dikonfirmasi.
                        </div>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

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

</body>
</html>