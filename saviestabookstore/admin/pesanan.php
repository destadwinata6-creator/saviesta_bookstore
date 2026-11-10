<?php

session_start();
require_once __DIR__ . "/../koneksi_admin.php";

/* =========================
   CEK LOGIN ADMIN
========================= */

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* =========================
   FUNGSI
========================= */

function aman($data) {
    return htmlspecialchars(
        (string)$data,
        ENT_QUOTES,
        "UTF-8"
    );
}

function rupiah($angka) {
    return "Rp " . number_format(
        (float)$angka,
        0,
        ",",
        "."
    );
}

/* =========================
   PESAN
========================= */

$pesan = "";
$error = "";

/* =========================
   UBAH STATUS PESANAN
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["ubah_status"])
) {

    $id = filter_input(
        INPUT_POST,
        "id",
        FILTER_VALIDATE_INT
    );

    $status = trim($_POST["status"] ?? "");

    $statusValid = [
        "Menunggu Pembayaran",
        "Menunggu Konfirmasi",
        "Pembayaran Berhasil",
        "Pesanan Diproses",
        "Dikirim",
        "Selesai",
        "Dibatalkan"
    ];

    if (!$id || !in_array($status, $statusValid, true)) {

        $error = "Data status tidak valid.";

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE pesanan SET status = ? WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: pesanan.php?berhasil=1");
                exit;

            } else {

                $error = "Status gagal diperbarui.";

                mysqli_stmt_close($stmt);
            }

        } else {

            $error = "Query gagal diproses.";
        }
    }
}

/* =========================
   NOTIFIKASI
========================= */

if (isset($_GET["berhasil"])) {
    $pesan = "Status pesanan berhasil diperbarui.";
}

/* =========================
   PENCARIAN PESANAN
========================= */

$cari = trim($_GET["cari"] ?? "");

if ($cari !== "") {

    $kata = "%" . $cari . "%";

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT
            id,
            user_id,
            nama,
            alamat,
            telepon,
            metode,
            subtotal,
            ongkir,
            total,
            status,
            bukti_pembayaran,
            created_at
         FROM pesanan
         WHERE nama LIKE ?
         OR CAST(id AS CHAR) LIKE ?
         ORDER BY id DESC"
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $kata,
            $kata
        );

        mysqli_stmt_execute($stmt);

        $pesanan = mysqli_stmt_get_result($stmt);

    } else {

        $pesanan = false;
        $error = "Pencarian pesanan gagal.";
    }

} else {

    $pesanan = mysqli_query(
        $koneksi,
        "SELECT
            id,
            user_id,
            nama,
            alamat,
            telepon,
            metode,
            subtotal,
            ongkir,
            total,
            status,
            bukti_pembayaran,
            created_at
         FROM pesanan
         ORDER BY id DESC"
    );
}

/* =========================
   STATISTIK
========================= */

$totalPesanan = 0;
$totalPendapatan = 0;
$totalBerhasil = 0;

if ($pesanan) {

    $totalPesanan = mysqli_num_rows($pesanan);

    foreach ($pesanan as $item) {

        if (
            stripos(
                $item["status"] ?? "",
                "berhasil"
            ) !== false
        ) {

            $totalBerhasil++;

            $totalPendapatan += (float)$item["total"];
        }
    }

    mysqli_data_seek($pesanan, 0);
}

if (!$pesanan && !$error) {
    $error = "Data pesanan gagal diambil. Periksa struktur tabel pesanan.";
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Data Pesanan | Saviesta</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f3ed;
    color: #252a21;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    width: 240px;
    background: #252a21;
    color: #fff;
    padding: 28px 18px;
    overflow-y: auto;
}

.brand {
    font-size: 23px;
    font-weight: 800;
    letter-spacing: 1px;
}

.sub {
    font-size: 11px;
    color: #c8cabe;
    margin: 8px 0 35px;
}

.section {
    font-size: 10px;
    letter-spacing: 1.5px;
    color: #aeb2a7;
    margin: 20px 12px 10px;
}

nav a {
    display: block;
    color: #eeefe9;
    text-decoration: none;
    padding: 13px 12px;
    border-radius: 8px;
    margin: 5px 0;
    font-size: 14px;
}

nav a:hover,
nav a.active {
    background: #41483a;
}

/* MAIN */

.main {
    margin-left: 240px;
    padding: 30px;
    max-width: 1700px;
}

.top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    margin-bottom: 28px;
}

h1 {
    font-size: 28px;
    margin-bottom: 8px;
}

.muted {
    font-size: 14px;
    color: #77796f;
}

.admin {
    background: #fff;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
}

/* CARDS */

.cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 25px;
}

.card,
.panel {
    background: #fff;
    border-radius: 13px;
    padding: 22px;
    box-shadow: 0 3px 15px #00000008;
}

.icon {
    font-size: 24px;
    margin-bottom: 15px;
}

.label {
    font-size: 13px;
    color: #77796f;
    margin-bottom: 12px;
}

.number {
    font-size: 24px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

/* PANEL */

.panel {
    padding: 22px;
}

.panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.panel h2 {
    font-size: 19px;
}

/* SEARCH */

.search {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

input[type=search] {
    padding: 11px 13px;
    border: 1px solid #deded4;
    border-radius: 8px;
    font-size: 13px;
    min-width: 200px;
}

.btn {
    display: inline-block;
    border: 0;
    border-radius: 8px;
    padding: 11px 15px;
    background: #252a21;
    color: #fff;
    text-decoration: none;
    font-size: 12px;
    cursor: pointer;
}

.btn:hover {
    background: #41483a;
}

/* TABLE */

.tablewrap {
    overflow-x: auto;
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 1200px;
}

th,
td {
    text-align: left;
    padding: 14px 12px;
    border-bottom: 1px solid #eeeee8;
    font-size: 12px;
    vertical-align: middle;
}

th {
    background: #f8f7f2;
    color: #66695f;
    white-space: nowrap;
}

tbody tr:hover {
    background: #faf9f5;
}

/* STATUS */

.status-form {
    display: flex;
    gap: 6px;
    align-items: center;
}

select {
    padding: 8px;
    border: 1px solid #deded4;
    border-radius: 7px;
    background: #fff;
    font-size: 11px;
    max-width: 165px;
}

.save {
    padding: 8px 10px;
    border: 0;
    border-radius: 7px;
    background: #41483a;
    color: #fff;
    cursor: pointer;
    font-size: 11px;
}

/* BADGE */

.badge {
    display: inline-block;
    padding: 7px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: bold;
    background: #eee;
    color: #555;
}

/* BUKTI PEMBAYARAN */

.bukti-btn {
    display: inline-block;
    padding: 9px 12px;
    border: 0;
    border-radius: 7px;
    background: #41483a;
    color: white;
    font-size: 11px;
    cursor: pointer;
    text-decoration: none;
}

.bukti-btn:hover {
    background: #252a21;
}

.no-bukti {
    color: #999;
    font-size: 11px;
}

/* ALERT */

.alert {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 13px;
}

.success {
    background: #e3ecdf;
    color: #365333;
}

.danger {
    background: #f8e2df;
    color: #8b3028;
}

.empty {
    text-align: center;
    color: #77796f;
    padding: 30px;
}

/* POPUP BUKTI */

.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    inset: 0;
    background: rgba(0,0,0,.75);
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.modal-content {
    position: relative;
    background: white;
    padding: 20px;
    border-radius: 12px;
    max-width: 650px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    text-align: center;
}

.modal-content h2 {
    font-size: 20px;
    margin-bottom: 18px;
}

.modal-content img {
    display: block;
    max-width: 100%;
    max-height: 65vh;
    object-fit: contain;
    margin: auto;
    border-radius: 8px;
}

.close {
    position: absolute;
    top: 10px;
    right: 14px;
    border: none;
    background: #252a21;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
}

.download-btn {
    display: inline-block;
    margin-top: 15px;
    background: #252a21;
    color: white;
    text-decoration: none;
    padding: 11px 17px;
    border-radius: 7px;
    font-size: 12px;
}

/* RESPONSIVE */

@media(max-width:1050px) {

    .cards {
        grid-template-columns: 1fr 1fr;
    }

    .main {
        padding: 22px;
    }
}

@media(max-width:700px) {

    .sidebar {
        position: static;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 18px;
    }

    .top {
        align-items: flex-start;
        flex-direction: column;
    }

    .cards {
        grid-template-columns: 1fr;
    }

    .panel {
        padding: 15px;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="brand">
        SAVIESTA
    </div>

    <div class="sub">
        BOOKSTORE ADMIN PANEL
    </div>

    <div class="section">
        MENU UTAMA
    </div>

   <nav>

    <a href="dashboard.php">
        ▦ Dashboard
    </a>

    <a href="buku.php">
        ▤ Data Buku
    </a>

    <a class="active" href="pesanan.php">
        ▣ Data Pesanan
    </a>

    <a href="users.php">
        ♙ Data Pengguna
    </a>

    <a href="pesan.php">
        ✉ Data Pesan
    </a>

    <div class="section">
        LAINNYA
    </div>

    <a href="../index.php" target="_blank">
        ↗ Lihat Website
    </a>

    <a href="logout.php">
        ⇥ Logout
    </a>

</nav>

</aside>

<!-- MAIN -->

<main class="main">

    <header class="top">

        <div>

            <h1>Data Pesanan</h1>

            <p class="muted">
                Kelola pesanan, pembayaran, dan bukti transfer pelanggan.
            </p>

        </div>

        <div class="admin">

            Admin:

            <strong>
                <?= aman($_SESSION["admin_nama"] ?? "Administrator") ?>
            </strong>

        </div>

    </header>

    <?php if ($pesan): ?>

        <div class="alert success">
            <?= aman($pesan) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="alert danger">
            <?= aman($error) ?>
        </div>

    <?php endif; ?>

    <!-- STATISTIK -->

    <section class="cards">

        <div class="card">

            <div class="icon">🛍️</div>

            <div class="label">
                Pesanan Ditampilkan
            </div>

            <div class="number">
                <?= number_format($totalPesanan) ?>
            </div>

        </div>

        <div class="card">

            <div class="icon">✅</div>

            <div class="label">
                Pembayaran Berhasil
            </div>

            <div class="number">
                <?= number_format($totalBerhasil) ?>
            </div>

        </div>

        <div class="card">

            <div class="icon">💰</div>

            <div class="label">
                Total Pembayaran Berhasil
            </div>

            <div class="number" style="font-size:21px">

                <?= rupiah($totalPendapatan) ?>

            </div>

        </div>

    </section>

    <!-- DAFTAR PESANAN -->

    <section class="panel">

        <div class="panel-head">

            <h2>Daftar Pesanan</h2>

            <form class="search" method="GET">

                <input
                    type="search"
                    name="cari"
                    placeholder="Cari nama atau ID pesanan..."
                    value="<?= aman($cari) ?>"
                >

                <button class="btn" type="submit">
                    Cari
                </button>

                <?php if ($cari !== ""): ?>

                    <a class="btn" href="pesanan.php">
                        Reset
                    </a>

                <?php endif; ?>

            </form>

        </div>

        <div class="tablewrap">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Nama Pembeli</th>
                        <th>Metode</th>
                        <th>Subtotal</th>
                        <th>Ongkir</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Bukti Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Ubah Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($pesanan && mysqli_num_rows($pesanan) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($pesanan)): ?>

                    <tr>

                        <td>
                            #<?= (int)$row["id"] ?>
                        </td>

                        <td>
                            <?= aman($row["nama"]) ?>
                        </td>

                        <td>
                            <?= aman($row["metode"]) ?>
                        </td>

                        <td>
                            <?= rupiah($row["subtotal"]) ?>
                        </td>

                        <td>
                            <?= rupiah($row["ongkir"]) ?>
                        </td>

                        <td>
                            <strong>
                                <?= rupiah($row["total"]) ?>
                            </strong>
                        </td>

                        <td>

                            <span class="badge">
                                <?= aman($row["status"]) ?>
                            </span>

                        </td>

                        <!-- BUKTI PEMBAYARAN -->

                        <td>

                            <?php

                            $bukti = trim(
                                $row["bukti_pembayaran"] ?? ""
                            );

                            $buktiUrl = "";

                            if ($bukti !== "") {

                                $namaFile = basename($bukti);

                                $folderBukti = __DIR__ .
                                    "/../uploads/bukti_pembayaran/";

                                $pathBukti = $folderBukti . $namaFile;

                                if (
                                    is_file($pathBukti) &&
                                    is_readable($pathBukti)
                                ) {

                                    $buktiUrl = "../uploads/bukti_pembayaran/" .
                                        rawurlencode($namaFile);
                                }
                            }

                            ?>

                            <?php if ($buktiUrl !== ""): ?>

                                <button
                                    type="button"
                                    class="bukti-btn"
                                    onclick="lihatBukti('<?= aman($buktiUrl) ?>')"
                                >
                                    🖼️ Lihat Bukti
                                </button>

                            <?php elseif ($bukti !== ""): ?>

                                <span class="no-bukti">
                                    File tidak ditemukan
                                </span>

                            <?php else: ?>

                                <span class="no-bukti">
                                    Belum ada bukti
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= aman($row["created_at"]) ?>
                        </td>

                        <!-- UBAH STATUS -->

                        <td>

                            <form
                                class="status-form"
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$row["id"] ?>"
                                >

                                <select name="status" required>

                                    <?php

                                    $opsiStatus = [
                                        "Menunggu Pembayaran",
                                        "Menunggu Konfirmasi",
                                        "Pembayaran Berhasil",
                                        "Pesanan Diproses",
                                        "Dikirim",
                                        "Selesai",
                                        "Dibatalkan"
                                    ];

                                    foreach ($opsiStatus as $opsi):

                                    ?>

                                        <option
                                            value="<?= aman($opsi) ?>"
                                            <?= ($row["status"] === $opsi)
                                                ? "selected"
                                                : "" ?>
                                        >

                                            <?= aman($opsi) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <button
                                    class="save"
                                    type="submit"
                                    name="ubah_status"
                                    value="1"
                                >
                                    Simpan
                                </button>

                            </form>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="10" class="empty">
                            Belum ada pesanan yang ditemukan.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<!-- POPUP BUKTI PEMBAYARAN -->

<div class="modal" id="modalBukti">

    <div class="modal-content">

        <button
            class="close"
            onclick="tutupBukti()"
        >
            ×
        </button>

        <h2>Bukti Pembayaran</h2>

        <img
            src=""
            id="gambarBukti"
            alt="Bukti Pembayaran"
        >

        <a
            href=""
            id="downloadBukti"
            class="download-btn"
            target="_blank"
            rel="noopener"
        >
            Buka Gambar Asli
        </a>

    </div>

</div>

<script>

/* =========================
   LIHAT BUKTI PEMBAYARAN
========================= */

function lihatBukti(gambar) {

    const modal = document.getElementById("modalBukti");

    const img = document.getElementById("gambarBukti");

    const download = document.getElementById("downloadBukti");

    img.src = gambar;

    download.href = gambar;

    modal.style.display = "flex";

    

}

/* =========================
   TUTUP POPUP
========================= */

function tutupBukti() {

    const modal = document.getElementById("modalBukti");

    const img = document.getElementById("gambarBukti");

    modal.style.display = "none";

    img.src = "";

}

/* KLIK LUAR POPUP */

document.getElementById("modalBukti").addEventListener(
    "click",
    function(event) {

        if (event.target === this) {
            tutupBukti();
        }

    }
);

/* TOMBOL ESC */

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {
        tutupBukti();
    }

});

</script>

</body>
</html>