<?php

include "functions.php";

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];


/* =========================
   CEK ID PESANAN
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: keranjang.php");
    exit;
}

$pesanan_id = (int)$_GET['id'];


/* =========================
   AMBIL PESANAN
========================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM pesanan
     WHERE id = ?
     AND user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Gagal mengambil pesanan: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "ii", $pesanan_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$pesanan) {
    header("Location: keranjang.php");
    exit;
}


/* =========================
   FUNGSI FORMAT
========================= */

function rupiahStruk($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

function amanStruk($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}


/* =========================
   STATUS PESANAN
========================= */

$statusAsli = trim($pesanan['status'] ?? '');

$statusPembayaran = strtolower($statusAsli);

$statusTampilan = $statusAsli;

if ($statusTampilan === '') {
    $statusTampilan = 'Belum Ada Status';
}


/* =========================
   WARNA STATUS
========================= */

$statusClass = 'status-menunggu';

if ($statusPembayaran === 'pembayaran berhasil') {
    $statusClass = 'status-berhasil';
} elseif ($statusPembayaran === 'pesanan diproses') {
    $statusClass = 'status-proses';
} elseif ($statusPembayaran === 'menunggu konfirmasi') {
    $statusClass = 'status-menunggu';
} elseif ($statusPembayaran === 'dikirim') {
    $statusClass = 'status-dikirim';
} elseif ($statusPembayaran === 'selesai') {
    $statusClass = 'status-selesai';
}


/* =========================
   KETERANGAN STATUS
========================= */

$keteranganStatus = '';

if ($statusPembayaran === 'pembayaran berhasil') {

    $keteranganStatus = 'Pembayaran berhasil. Pesanan kamu sudah tercatat di Saviesta.';

} elseif ($statusPembayaran === 'pesanan diproses') {

    $keteranganStatus = 'Pesanan kamu sedang dipersiapkan oleh pihak Saviesta.';

} elseif ($statusPembayaran === 'menunggu konfirmasi') {

    $keteranganStatus = 'Pesanan kamu sedang menunggu konfirmasi dari admin.';

} elseif ($statusPembayaran === 'dikirim') {

    $keteranganStatus = 'Pesanan kamu sedang dalam perjalanan. Silakan konfirmasi melalui halaman riwayat setelah barang benar-benar diterima.';

} elseif ($statusPembayaran === 'selesai') {

    $keteranganStatus = 'Pesanan sudah selesai. Terima kasih sudah berbelanja di Saviesta!';

} else {

    $keteranganStatus = 'Silakan periksa kembali status pesanan kamu melalui halaman riwayat pembelian.';

}


/* =========================
   AMBIL DETAIL PESANAN
========================= */

$stmtDetail = mysqli_prepare(
    $koneksi,
    "SELECT detail_pesanan.*, buku.judul
     FROM detail_pesanan
     LEFT JOIN buku ON detail_pesanan.buku_id = buku.id
     WHERE detail_pesanan.pesanan_id = ?"
);

if (!$stmtDetail) {
    die("Gagal mengambil detail pesanan: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmtDetail, "i", $pesanan_id);
mysqli_stmt_execute($stmtDetail);

$resultDetail = mysqli_stmt_get_result($stmtDetail);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Struk Pembelian | Saviesta</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 35px 15px;
    background: #f7f5ef;
    color: #293126;
    font-family: Arial, sans-serif;
}

/* CONTAINER */

.container {
    max-width: 760px;
    margin: auto;
}

/* STRUK */

.struk {
    background: #fff;
    border: 1px solid #e8e4d9;
    border-radius: 10px;
    overflow: hidden;
}

/* HEADER */

.header {
    padding: 28px 35px;
    background: #293126;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.brand {
    font-family: Georgia, serif;
    font-size: 29px;
    letter-spacing: 3px;
    margin: 0;
}

.brand-sub {
    margin-top: 5px;
    color: #c4cbbd;
    font-size: 9px;
    letter-spacing: 3px;
}

.receipt-number {
    text-align: right;
    font-size: 11px;
    line-height: 1.8;
    color: #d4dacd;
}

.receipt-number strong {
    display: block;
    color: white;
    font-size: 14px;
}

/* CONTENT */

.content {
    padding: 30px 35px;
}

.title {
    font-family: Georgia, serif;
    font-size: 22px;
    font-weight: normal;
    margin: 0 0 7px;
}

.subtitle {
    color: #92958d;
    font-size: 11px;
    margin-bottom: 22px;
}

/* STATUS */

.status {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: bold;
    margin-bottom: 12px;
}

.status-berhasil {
    background: #e8eee3;
    color: #4c6445;
}

.status-proses {
    background: #e4ebf5;
    color: #45617e;
}

.status-menunggu {
    background: #f1ead8;
    color: #806d3d;
}

.status-dikirim {
    background: #e1ebf5;
    color: #3d6689;
}

.status-selesai {
    background: #dfe9dc;
    color: #42553a;
}

/* KETERANGAN STATUS */

.status-info {
    padding: 13px 15px;
    margin-bottom: 22px;
    border-radius: 8px;
    background: #faf9f5;
    border: 1px solid #efeee8;
    color: #777e70;
    font-size: 11px;
    line-height: 1.7;
}

.status-info strong {
    display: block;
    color: #293126;
    margin-bottom: 4px;
    font-size: 12px;
}

/* INFORMASI */

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.info-item {
    padding: 12px 14px;
    background: #faf9f5;
    border: 1px solid #efeee8;
    border-radius: 6px;
    min-width: 0;
}

.info-label {
    color: #92958d;
    font-size: 9px;
    letter-spacing: 1px;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.info-value {
    font-size: 12px;
    font-weight: bold;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

/* PEMBATAS */

.divider {
    border: 0;
    border-top: 1px solid #eae9e2;
    margin: 25px 0;
}

/* TABEL */

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 430px;
}

th {
    padding: 12px 10px;
    background: #f5f5ef;
    color: #777e70;
    font-size: 10px;
    text-align: left;
    font-weight: normal;
}

td {
    padding: 13px 10px;
    border-bottom: 1px solid #efeee9;
    font-size: 11px;
}

th:not(:first-child),
td:not(:first-child) {
    text-align: right;
}

.nama-buku {
    font-weight: bold;
}

/* TOTAL */

.summary {
    width: 270px;
    max-width: 100%;
    margin-left: auto;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 11px;
}

.summary-row span {
    color: #85877f;
}

.grand-total {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding-top: 15px;
    margin-top: 15px;
    border-top: 1px solid #deded5;
    font-size: 13px;
    font-weight: bold;
}

.grand-total strong {
    font-size: 16px;
}

/* FOOTER */

.footer {
    text-align: center;
    padding: 20px;
    background: #faf9f5;
    border-top: 1px solid #efeee8;
}

.footer h3 {
    font-family: Georgia, serif;
    font-size: 21px;
    font-weight: normal;
    margin: 0 0 8px;
}

.footer p {
    color: #92958d;
    font-size: 10px;
    line-height: 1.7;
    margin: 0;
}

/* BUTTON */

.actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

.btn {
    border: none;
    border-radius: 25px;
    padding: 13px 20px;
    font-size: 10px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: .2s;
}

.btn:hover {
    opacity: .8;
    transform: translateY(-2px);
}

.btn-print {
    background: #293126;
    color: white;
}

.btn-history {
    background: #839276;
    color: white;
}

.btn-home {
    background: #e8e5d9;
    color: #293126;
}

/* RESPONSIVE */

@media(max-width: 550px) {

    body {
        padding: 15px 8px;
    }

    .header {
        padding: 22px;
        align-items: flex-start;
        flex-direction: column;
    }

    .receipt-number {
        text-align: left;
    }

    .content {
        padding: 22px 18px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .summary {
        width: 100%;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        text-align: center;
    }
}

/* CETAK */

@media print {

    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    html,
    body {
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        font-size: 10px;
    }

    .container {
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .struk {
        border: 1px solid #ddd;
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
    }

    .header {
        padding: 16px 22px;
        background: #293126 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .brand {
        font-size: 23px;
    }

    .content {
        padding: 17px 22px;
    }

    .title {
        font-size: 18px;
        margin-bottom: 5px;
    }

    .subtitle {
        margin-bottom: 12px;
    }

    .status {
        margin-bottom: 8px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .status-info {
        padding: 9px 12px;
        margin-bottom: 12px;
    }

    .info-grid {
        gap: 7px;
    }

    .info-item {
        padding: 8px 10px;
        background: #faf9f5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .divider {
        margin: 14px 0;
    }

    th {
        padding: 8px;
        background: #f5f5ef !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    td {
        padding: 8px;
    }

    .summary {
        margin-top: 15px;
    }

    .summary-row {
        margin-bottom: 8px;
    }

    .grand-total {
        padding-top: 9px;
        margin-top: 9px;
    }

    .footer {
        padding: 13px;
        background: #faf9f5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .footer h3 {
        font-size: 17px;
        margin-bottom: 5px;
    }

    .actions {
        display: none !important;
    }

    .struk,
    .header,
    .content,
    .footer,
    tr,
    .info-item,
    .summary {
        break-inside: avoid;
        page-break-inside: avoid;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="struk">

        <!-- HEADER -->

        <div class="header">

            <div>
                <h1 class="brand">SAVIESTA</h1>
                <div class="brand-sub">BOOKSTORE</div>
            </div>

            <div class="receipt-number">

                STRUK PEMBELIAN

                <strong>
                    #SV-<?= str_pad((int)$pesanan['id'], 5, '0', STR_PAD_LEFT); ?>
                </strong>

            </div>

        </div>


        <!-- CONTENT -->

        <div class="content">

            <h2 class="title">
                Terima Kasih atas Pembelian Anda
            </h2>

            <div class="subtitle">
                Berikut adalah bukti transaksi pembelian di Saviesta.
            </div>


            <!-- STATUS ASLI PESANAN -->

            <div class="status <?= amanStruk($statusClass); ?>">
                ✓ <?= amanStruk($statusTampilan); ?>
            </div>


            <div class="status-info">

                <strong>
                    Informasi Pesanan
                </strong>

                <?= amanStruk($keteranganStatus); ?>

                <?php if ($statusPembayaran === 'dikirim'): ?>

                    <br><br>

                    Silakan buka halaman riwayat pembelian untuk
                    mengonfirmasi pesanan setelah barang diterima.

                <?php endif; ?>

            </div>


            <!-- INFORMASI -->

            <div class="info-grid">

                <div class="info-item">

                    <div class="info-label">
                        Tanggal Pembelian
                    </div>

                    <div class="info-value">

                        <?= !empty($pesanan['created_at'])
                            ? date('d M Y, H:i', strtotime($pesanan['created_at']))
                            : '-'; ?>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-label">
                        Nama Penerima
                    </div>

                    <div class="info-value">
                        <?= amanStruk($pesanan['nama']); ?>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-label">
                        Nomor Telepon
                    </div>

                    <div class="info-value">
                        <?= amanStruk($pesanan['telepon']); ?>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-label">
                        Metode Pembayaran
                    </div>

                    <div class="info-value">
                        <?= amanStruk($pesanan['metode']); ?>
                    </div>

                </div>


                <div class="info-item" style="grid-column: 1 / -1;">

                    <div class="info-label">
                        Alamat Pengiriman
                    </div>

                    <div class="info-value">
                        <?= nl2br(amanStruk($pesanan['alamat'])); ?>
                    </div>

                </div>

            </div>


            <hr class="divider">


            <!-- DETAIL BUKU -->

            <h2 class="title">
                Detail Pembelian
            </h2>

            <div class="subtitle">
                Daftar buku yang terdapat dalam pesanan.
            </div>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>Nama Buku</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($resultDetail) > 0): ?>

                        <?php while ($detail = mysqli_fetch_assoc($resultDetail)): ?>

                            <tr>

                                <td class="nama-buku">
                                    <?= amanStruk($detail['judul'] ?? 'Buku'); ?>
                                </td>

                                <td>
                                    <?= (int)$detail['jumlah']; ?>
                                </td>

                                <td>
                                    <?= rupiahStruk($detail['harga']); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= rupiahStruk(
                                            $detail['jumlah'] * $detail['harga']
                                        ); ?>
                                    </strong>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="4" style="text-align:center;">
                                Tidak ada detail pembelian.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <hr class="divider">


            <!-- RINGKASAN -->

            <div class="summary">

                <div class="summary-row">

                    <span>Subtotal</span>

                    <strong>
                        <?= rupiahStruk($pesanan['subtotal']); ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>Ongkos Kirim</span>

                    <strong>
                        <?= rupiahStruk($pesanan['ongkir']); ?>
                    </strong>

                </div>


                <div class="grand-total">

                    <span>Total Pembayaran</span>

                    <strong>
                        <?= rupiahStruk($pesanan['total']); ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- FOOTER -->

        <div class="footer">

            <h3>Terima Kasih!</h3>

            <p>
                Find Your Next Favorite Story.<br>
                © 2026 Saviesta Bookstore
            </p>

        </div>

    </div>


    <!-- BUTTON -->

    <div class="actions">

        <button
            type="button"
            class="btn btn-print"
            onclick="window.print()"
        >
            CETAK / SIMPAN PDF
        </button>


        <a href="keranjang.php" class="btn btn-history">
            RIWAYAT PESANAN
        </a>


        <a href="index.php" class="btn btn-home">
            KEMBALI KE BERANDA
        </a>

    </div>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmtDetail);
?>