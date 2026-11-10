<?php
session_start();

require_once __DIR__ . "/../koneksi_admin.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* =========================
   FUNGSI
========================= */

function ambilAngka($koneksi, $sql) {
    $result = mysqli_query($koneksi, $sql);
    if (!$result) return 0;

    $row = mysqli_fetch_row($result);
    return $row[0] ?? 0;
}

function rupiahAdmin($angka) {
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}

function amanAdmin($data) {
    return htmlspecialchars($data ?? "-", ENT_QUOTES, "UTF-8");
}

/* =========================
   DATA DASHBOARD
========================= */

$totalBuku = ambilAngka($koneksi, "SELECT COUNT(*) FROM buku");
$totalUser = ambilAngka($koneksi, "SELECT COUNT(*) FROM users");
$totalPesanan = ambilAngka($koneksi, "SELECT COUNT(*) FROM pesanan");
$totalPenjualan = ambilAngka($koneksi, "SELECT COALESCE(SUM(total),0) FROM pesanan");

/* =========================
   TOTAL PESAN CONTACT
========================= */

$totalPesan = 0;

$cekTabelPesan = mysqli_query(
    $koneksi,
    "SHOW TABLES LIKE 'pesan_contact'"
);

if ($cekTabelPesan && mysqli_num_rows($cekTabelPesan) > 0) {
    $totalPesan = ambilAngka(
        $koneksi,
        "SELECT COUNT(*) FROM pesan_contact"
    );
}

/* =========================
   PESANAN TERBARU
========================= */

$pesanan = mysqli_query(
    $koneksi,
    "SELECT id, nama, total, status, created_at
     FROM pesanan
     ORDER BY id DESC
     LIMIT 8"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Dashboard Admin | Saviesta</title>

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
    transition: .2s;
}

nav a:hover,
nav a.active {
    background: #41483a;
}

/* MAIN */

.main {
    margin-left: 240px;
    padding: 30px;
    max-width: 1600px;
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
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 26px;
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
    font-size: 25px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

/* PANEL */

.panel {
    margin-bottom: 25px;
}

.panel h2 {
    font-size: 19px;
    margin-bottom: 18px;
}

/* TABLE */

.tablewrap {
    overflow-x: auto;
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 620px;
}

th,
td {
    text-align: left;
    padding: 14px 12px;
    border-bottom: 1px solid #eeeee8;
    font-size: 13px;
}

th {
    background: #f8f7f2;
    color: #66695f;
}

.status {
    display: inline-block;
    background: #f1ead7;
    padding: 6px 9px;
    border-radius: 20px;
    font-size: 11px;
}

.empty {
    text-align: center;
    color: #77796f;
    padding: 25px;
}

/* RESPONSIVE */

@media(max-width: 1050px) {
    .cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width: 700px) {
    .sidebar {
        position: static;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .top {
        align-items: flex-start;
        flex-direction: column;
    }

    .cards {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="brand">SAVIESTA</div>

    <div class="sub">BOOKSTORE ADMIN PANEL</div>

    <div class="section">MENU UTAMA</div>

    <nav>

        <a class="active" href="dashboard.php">
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

        <a href="pesan.php">
            ✉ Data Pesan
            
        </a>

        <div class="section">LAINNYA</div>

        <a href="../index.php" target="_blank">
            ↗ Lihat Website
        </a>

        <a href="logout.php">
            ⇥ Logout
        </a>

    </nav>

</aside>


<!-- MAIN CONTENT -->

<main class="main">

    <header class="top">

        <div>

            <h1>Dashboard</h1>

            <p class="muted">
                Ringkasan Saviesta Bookstore.
            </p>

        </div>

        <div class="admin">

            Admin:

            <strong>
                <?= amanAdmin($_SESSION["admin_nama"] ?? "Administrator") ?>
            </strong>

        </div>

    </header>


    <!-- STATISTIK -->

    <section class="cards">

        <div class="card">

            <div class="icon">📚</div>

            <div class="label">
                Total Buku
            </div>

            <div class="number">
                <?= number_format((int)$totalBuku) ?>
            </div>

        </div>


        <div class="card">

            <div class="icon">👥</div>

            <div class="label">
                Total Pengguna
            </div>

            <div class="number">
                <?= number_format((int)$totalUser) ?>
            </div>

        </div>


        <div class="card">

            <div class="icon">🛍️</div>

            <div class="label">
                Total Pesanan
            </div>

            <div class="number">
                <?= number_format((int)$totalPesanan) ?>
            </div>

        </div>


        <div class="card">

            <div class="icon">💰</div>

            <div class="label">
                Total Penjualan
            </div>

            <div class="number" style="font-size:20px">
                <?= rupiahAdmin($totalPenjualan) ?>
            </div>

        </div>

    </section>


    <!-- PESANAN TERBARU -->

    <section class="panel">

        <h2>Pesanan Terbaru</h2>

        <div class="tablewrap">

            <table>

                <thead>

                    <tr>
                        <th>ID Pesanan</th>
                        <th>Nama Pembeli</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
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
                                    <?= amanAdmin($row["nama"]) ?>
                                </td>

                                <td>
                                    <?= rupiahAdmin($row["total"] ?? 0) ?>
                                </td>

                                <td>

                                    <span class="status">
                                        <?= amanAdmin($row["status"]) ?>
                                    </span>

                                </td>

                                <td>
                                    <?= amanAdmin($row["created_at"]) ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5" class="empty">
                                Belum ada pesanan.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

</body>
</html>