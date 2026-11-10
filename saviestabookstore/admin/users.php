<?php
session_start();
require_once __DIR__ . "/../koneksi_admin.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

function aman($nilai) {
    return htmlspecialchars((string)$nilai, ENT_QUOTES, "UTF-8");
}

$cari = trim($_GET["cari"] ?? "");
$error = "";
$pesan = "";

// Deteksi nama tabel pengguna yang tersedia di database.
$tabelPengguna = null;
$daftarTabel = mysqli_query($koneksi, "SHOW TABLES");
if ($daftarTabel) {
    while ($barisTabel = mysqli_fetch_row($daftarTabel)) {
        if (in_array(strtolower($barisTabel[0]), ["users", "user", "pengguna", "pelanggan"], true)) {
            $tabelPengguna = $barisTabel[0];
            break;
        }
    }
}

if ($tabelPengguna === null) {
    $error = "Tabel pengguna tidak ditemukan. Periksa apakah tabel bernama users, user, pengguna, atau pelanggan ada di database.";
    $kolom = [];
    $hasil = false;
} else {
    $hasilKolom = mysqli_query($koneksi, "SHOW COLUMNS FROM `" . mysqli_real_escape_string($koneksi, $tabelPengguna) . "`");
    $kolom = [];
    if ($hasilKolom) {
        while ($info = mysqli_fetch_assoc($hasilKolom)) {
            $kolom[] = $info["Field"];
        }
    }

    $kolomId = in_array("id", $kolom, true) ? "id" : ($kolom[0] ?? null);
    $kolomCari = [];
    foreach ($kolom as $namaKolom) {
        if (preg_match('/nama|name|email|username|telepon|phone|alamat|address/i', $namaKolom)) {
            $kolomCari[] = "CAST(`$namaKolom` AS CHAR)";
        }
    }

    $sql = "SELECT * FROM `$tabelPengguna`";
    if ($cari !== "" && $kolomCari) {
        $kondisi = [];
        foreach ($kolomCari as $expr) {
            $kondisi[] = "$expr LIKE ?";
        }
        $sql .= " WHERE " . implode(" OR ", $kondisi);
        $sql .= " ORDER BY `" . $kolomId . "` DESC";
        $stmt = mysqli_prepare($koneksi, $sql);
        if ($stmt) {
            $pola = "%" . $cari . "%";
            $tipe = str_repeat("s", count($kolomCari));
            $params = array_fill(0, count($kolomCari), $pola);
            mysqli_stmt_bind_param($stmt, $tipe, ...$params);
            mysqli_stmt_execute($stmt);
            $hasil = mysqli_stmt_get_result($stmt);
        } else {
            $hasil = false;
            $error = "Pencarian data pengguna gagal diproses.";
        }
    } else {
        $sql .= " ORDER BY `" . $kolomId . "` DESC";
        $hasil = mysqli_query($koneksi, $sql);
    }

    if (!$hasil && !$error) {
        $error = "Data pengguna gagal diambil. Periksa struktur tabel database.";
    }
}

$totalPengguna = $hasil ? mysqli_num_rows($hasil) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Pengguna | Saviesta</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f5f3ed;color:#252a21}
.sidebar{position:fixed;inset:0 auto 0 0;width:240px;background:#252a21;color:#fff;padding:28px 18px;overflow-y:auto}
.brand{font-size:23px;font-weight:800;letter-spacing:1px}.sub{font-size:11px;color:#c8cabe;margin:8px 0 35px}
.section{font-size:10px;letter-spacing:1.5px;color:#aeb2a7;margin:20px 12px 10px}
nav a{display:block;color:#eeefe9;text-decoration:none;padding:13px 12px;border-radius:8px;margin:5px 0;font-size:14px}
nav a:hover,nav a.active{background:#41483a}
.main{margin-left:240px;padding:30px;max-width:1700px}
.top{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:28px}
h1{font-size:28px;margin-bottom:8px}.muted{font-size:14px;color:#77796f}
.admin{background:#fff;padding:12px 16px;border-radius:10px;font-size:13px}
.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:25px}
.card,.panel{background:#fff;border-radius:13px;padding:22px;box-shadow:0 3px 15px #00000008}
.icon{font-size:24px;margin-bottom:15px}.label{font-size:13px;color:#77796f;margin-bottom:12px}.number{font-size:24px;font-weight:800;overflow-wrap:anywhere}
.panel{padding:22px}.panel-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:20px;flex-wrap:wrap}.panel h2{font-size:19px}
.search{display:flex;gap:8px;flex-wrap:wrap}input[type=search]{padding:11px 13px;border:1px solid #deded4;border-radius:8px;font-size:13px;min-width:230px}
.btn{display:inline-block;border:0;border-radius:8px;padding:11px 15px;background:#252a21;color:#fff;text-decoration:none;font-size:12px;cursor:pointer}.btn:hover{background:#41483a}
.tablewrap{overflow-x:auto}table{border-collapse:collapse;width:100%;min-width:750px}
th,td{text-align:left;padding:14px 12px;border-bottom:1px solid #eeeee8;font-size:12px;vertical-align:middle}th{background:#f8f7f2;color:#66695f;white-space:nowrap}tbody tr:hover{background:#faf9f5}
.alert{padding:13px 16px;border-radius:8px;margin-bottom:18px;font-size:13px}.danger{background:#f8e2df;color:#8b3028}.empty{text-align:center;color:#77796f;padding:30px}
@media(max-width:1050px){.cards{grid-template-columns:1fr 1fr}.main{padding:22px}}
@media(max-width:700px){.sidebar{position:static;width:100%;height:auto}.main{margin-left:0;padding:18px}.top{align-items:flex-start;flex-direction:column}.cards{grid-template-columns:1fr}.panel{padding:15px}}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="brand">SAVIESTA</div><div class="sub">BOOKSTORE ADMIN PANEL</div>
    <div class="section">MENU UTAMA</div>
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
<main class="main">
    <header class="top">
        <div><h1>Data Pengguna</h1><p class="muted">Lihat dan cari akun pengguna yang terdaftar di Saviesta.</p></div>
        <div class="admin">Admin: <strong><?= aman($_SESSION["admin_nama"] ?? "Administrator") ?></strong></div>
    </header>
    <?php if ($error): ?><div class="alert danger"><?= aman($error) ?></div><?php endif; ?>
    <section class="cards">
        <div class="card"><div class="icon">♙</div><div class="label">Pengguna Ditampilkan</div><div class="number"><?= number_format($totalPengguna) ?></div></div>
        <div class="card"><div class="icon">▤</div><div class="label">Tabel Sumber</div><div class="number" style="font-size:21px"><?= aman($tabelPengguna ?? "Tidak ditemukan") ?></div></div>
        <div class="card"><div class="icon">⌕</div><div class="label">Pencarian</div><div class="number" style="font-size:21px"><?= $cari !== "" ? "Aktif" : "Tidak aktif" ?></div></div>
    </section>
    <section class="panel">
        <div class="panel-head">
            <div><h2>Daftar Pengguna</h2><p class="muted" style="margin-top:7px">Informasi diambil langsung dari tabel pengguna database.</p></div>
            <form method="GET" class="search"><input type="search" name="cari" placeholder="Cari nama, email, username..." value="<?= aman($cari) ?>"><button class="btn" type="submit">Cari</button><?php if ($cari !== ""): ?><a class="btn" href="users.php">Reset</a><?php endif; ?></form>
        </div>
        <div class="tablewrap">
        <?php if ($hasil && $totalPengguna > 0): ?>
            <table><thead><tr><?php foreach ($kolom as $namaKolom): ?><th><?= aman(ucwords(str_replace(["_", "-"], " ", $namaKolom))) ?></th><?php endforeach; ?></tr></thead>
            <tbody><?php while ($baris = mysqli_fetch_assoc($hasil)): ?><tr><?php foreach ($kolom as $namaKolom): ?><td><?php
                $nilai = $baris[$namaKolom] ?? "";
                if (preg_match('/password|token|secret/i', $namaKolom)) {
                    echo "••••••••";
                } else {
                    $teks = (string)$nilai;
                    echo aman(mb_strlen($teks) > 100 ? mb_substr($teks, 0, 100) . "…" : $teks);
                }
            ?></td><?php endforeach; ?></tr><?php endwhile; ?></tbody></table>
        <?php elseif (!$error): ?>
            <div class="empty">Tidak ada data pengguna yang cocok dengan pencarian.</div>
        <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
