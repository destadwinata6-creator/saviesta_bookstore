<?php
session_start();

require_once __DIR__ . "/../koneksi_admin.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

function amanPesan($data) {
    return htmlspecialchars($data ?? "", ENT_QUOTES, "UTF-8");
}

/* =========================
   HAPUS PESAN
========================= */

if (isset($_POST["hapus_pesan"])) {

    $id = (int) ($_POST["id"] ?? 0);

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM pesan_contact WHERE id = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: pesan.php");
    exit;
}

/* =========================
   AMBIL DATA PESAN
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM pesan_contact ORDER BY tanggal_kirim DESC"
);

if (!$query) {
    die("Gagal mengambil data pesan: " . amanPesan(mysqli_error($koneksi)));
}

$totalPesan = mysqli_num_rows($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Pesan | Saviesta Admin</title>

<style>

/* =========================
   RESET
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f3ed;
    color: #252a21;
    min-height: 100vh;
}


/* =========================
   SIDEBAR
========================= */

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 260px;
    padding: 30px 20px;
    background: #252a21;
    color: white;
    overflow-y: auto;
    z-index: 100;
}

.brand {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.sub {
    color: #bfc3b6;
    font-size: 11px;
    margin-top: 8px;
    margin-bottom: 42px;
}

.section {
    color: #aeb2a7;
    font-size: 10px;
    letter-spacing: 2px;
    margin: 25px 13px 12px;
}

nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #eeefe9;
    text-decoration: none;
    padding: 14px 13px;
    margin: 5px 0;
    border-radius: 9px;
    font-size: 14px;
    transition: .2s;
}

nav a:hover {
    background: #343b30;
}

nav a.active {
    background: #41483a;
    font-weight: bold;
}


/* =========================
   MAIN CONTENT
========================= */

.main {
    margin-left: 260px;
    padding: 35px;
    width: calc(100% - 260px);
    max-width: 1800px;
}


/* =========================
   HEADER
========================= */

.top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 32px;
}

.page-title {
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 9px;
}

.muted {
    color: #858579;
    font-size: 13px;
    line-height: 1.6;
}

.admin {
    background: white;
    padding: 14px 20px;
    border-radius: 10px;
    font-size: 13px;
    box-shadow: 0 3px 12px rgba(0,0,0,.03);
    white-space: nowrap;
}

.admin strong {
    color: #252a21;
}


/* =========================
   TOTAL CARD
========================= */

.card {
    background: white;
    border-radius: 15px;
    padding: 27px 30px;
    margin-bottom: 28px;
    box-shadow: 0 4px 18px rgba(0,0,0,.035);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.card-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card-label {
    color: #858579;
    font-size: 13px;
}

.card-number {
    font-size: 34px;
    font-weight: 700;
    color: #252a21;
}

.card-icon {
    width: 58px;
    height: 58px;
    border-radius: 14px;
    background: #eef0e8;
    color: #41483a;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 25px;
}


/* =========================
   PANEL
========================= */

.panel {
    background: white;
    border-radius: 15px;
    padding: 28px;
    box-shadow: 0 4px 18px rgba(0,0,0,.035);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 24px;
}

.panel-title {
    font-size: 20px;
    font-weight: 700;
}

.panel-subtitle {
    color: #929286;
    font-size: 12px;
    margin-top: 7px;
}

.total-badge {
    background: #eef0e8;
    color: #41483a;
    padding: 9px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
    white-space: nowrap;
}


/* =========================
   TABLE
========================= */

.tablewrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #eeeee8;
    border-radius: 10px;
}

table {
    width: 100%;
    min-width: 1000px;
    border-collapse: collapse;
    table-layout: fixed;
}


/* LEBAR KOLOM */

th:nth-child(1),
td:nth-child(1) {
    width: 60px;
    text-align: center;
}

th:nth-child(2),
td:nth-child(2) {
    width: 165px;
}

th:nth-child(3),
td:nth-child(3) {
    width: 215px;
}

th:nth-child(4),
td:nth-child(4) {
    width: auto;
}

th:nth-child(5),
td:nth-child(5) {
    width: 185px;
}

th:nth-child(6),
td:nth-child(6) {
    width: 100px;
    text-align: center;
}


/* HEADER TABEL */

thead th {
    background: #f7f6f1;
    color: #6f7168;
    font-size: 12px;
    font-weight: 700;
    padding: 18px 14px;
    text-align: left;
    border-bottom: 1px solid #eae9e2;
    white-space: nowrap;
}

thead th:first-child {
    text-align: center;
}


/* ISI TABEL */

tbody td {
    padding: 19px 14px;
    font-size: 13px;
    line-height: 1.6;
    border-bottom: 1px solid #efeee9;
    vertical-align: top;
}

tbody tr:last-child td {
    border-bottom: none;
}

tbody tr {
    transition: background .2s;
}

tbody tr:hover {
    background: #fcfbf8;
}


/* =========================
   NOMOR
========================= */

.nomor {
    color: #858579;
    font-size: 12px;
    text-align: center;
}


/* =========================
   NAMA
========================= */

.nama {
    font-weight: 700;
    color: #252a21;
    overflow-wrap: anywhere;
}


/* =========================
   EMAIL
========================= */

.email {
    color: #64734d;
    overflow-wrap: anywhere;
}


/* =========================
   ISI PESAN
========================= */

.pesan {
    color: #55594e;
    text-align: left !important;
    vertical-align: top !important;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: normal;
    line-height: 1.7;
    padding: 19px 14px !important;
    min-width: 0;
}

tbody td.pesan {
    text-align: left !important;
}

tbody td.pesan * {
    text-align: left !important;
}


/* =========================
   TANGGAL
========================= */

.tanggal {
    color: #858579;
    font-size: 12px;
    white-space: nowrap;
}


/* =========================
   TOMBOL HAPUS
========================= */

.aksi {
    text-align: center;
}

.aksi form {
    display: flex;
    justify-content: center;
}

.btn-hapus {
    border: none;
    background: #b34743;
    color: white;
    padding: 10px 17px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

.btn-hapus:hover {
    background: #873632;
    transform: translateY(-2px);
}


/* =========================
   EMPTY STATE
========================= */

.empty {
    padding: 55px 20px !important;
    text-align: center;
    color: #929286;
    font-size: 13px;
}

.empty-icon {
    font-size: 30px;
    display: block;
    margin-bottom: 12px;
}


/* =========================
   FOOTER INFO
========================= */

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 18px;
    color: #929286;
    font-size: 11px;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width: 1100px) {

    .sidebar {
        width: 230px;
    }

    .main {
        margin-left: 230px;
        width: calc(100% - 230px);
        padding: 25px;
    }

    .top {
        align-items: flex-start;
        flex-direction: column;
    }

}

@media(max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        padding: 22px;
    }

    .sub {
        margin-bottom: 20px;
    }

    .main {
        margin-left: 0;
        width: 100%;
        padding: 20px;
    }

    .page-title {
        font-size: 25px;
    }

    .panel {
        padding: 18px;
    }

    .card {
        padding: 22px;
    }

    .panel-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .table-footer {
        align-items: flex-start;
        flex-direction: column;
    }

}

</style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

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

        <a href="pesanan.php">
            ▣ Data Pesanan
        </a>

        <a href="users.php">
            ♙ Data Pengguna
        </a>

        <a href="pesan.php" class="active">
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


<!-- =========================
     MAIN
========================= -->

<main class="main">


    <!-- HEADER -->

    <header class="top">

        <div>

            <h1 class="page-title">
                Data Pesan
            </h1>

            <p class="muted">
                Kelola pesan yang dikirim pengunjung melalui halaman Contact.
            </p>

        </div>

        <div class="admin">

            Admin:

            <strong>
                <?= amanPesan($_SESSION["admin_nama"] ?? "Administrator") ?>
            </strong>

        </div>

    </header>


    <!-- TOTAL PESAN -->

    <section class="card">

        <div class="card-info">

            <div class="card-label">
                Total Pesan Masuk
            </div>

            <div class="card-number">
                <?= number_format($totalPesan) ?>
            </div>

        </div>

        <div class="card-icon">
            ✉
        </div>

    </section>


    <!-- DAFTAR PESAN -->

    <section class="panel">

        <div class="panel-header">

            <div>

                <h2 class="panel-title">
                    Daftar Pesan Masuk
                </h2>

                <p class="panel-subtitle">
                    Semua pesan yang dikirim melalui halaman Contact Saviesta.
                </p>

            </div>

            <div class="total-badge">
                <?= number_format($totalPesan) ?> PESAN
            </div>

        </div>


        <div class="tablewrap">

            <table>

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Isi Pesan</th>
                        <th>Tanggal Kirim</th>
                        <th>Aksi</th>
                    </tr>

                </thead>


                <tbody>

                <?php if ($totalPesan > 0): ?>

                    <?php
                    $no = 1;

                    while ($row = mysqli_fetch_assoc($query)):

                        // Rapikan spasi dan enter berlebih dari isi pesan.
                        $isiPesan = trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                (string)($row["pesan"] ?? "")
                            )
                        );
                    ?>

                    <tr>

                        <td class="nomor">
                            <?= $no++ ?>
                        </td>

                        <td class="nama">
                            <?= amanPesan($row["nama"]) ?>
                        </td>

                        <td class="email">
                            <?= amanPesan($row["email"]) ?>
                        </td>

                        <td class="pesan">
                            <?= amanPesan($isiPesan) ?>
                        </td>

                        <td class="tanggal">
                            <?= amanPesan($row["tanggal_kirim"]) ?>
                        </td>

                        <td class="aksi">

                            <form
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus pesan ini?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$row["id"] ?>"
                                >

                                <button
                                    type="submit"
                                    name="hapus_pesan"
                                    class="btn-hapus"
                                >
                                    Hapus
                                </button>

                            </form>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6" class="empty">

                            <span class="empty-icon">
                                ✉
                            </span>

                            Belum ada pesan masuk.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <div class="table-footer">

            <span>
                Data pesan pengunjung Saviesta Bookstore.
            </span>

            <span>
                Total: <?= number_format($totalPesan) ?> pesan
            </span>

        </div>

    </section>

</main>

</body>
</html>