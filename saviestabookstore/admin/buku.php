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

function rupiahBuku($nilai) {
    return "Rp " . number_format((float)$nilai, 0, ",", ".");
}

function namaKolom($kolom) {
    $nama = str_replace(["_", "-"], " ", $kolom);
    return ucwords($nama);
}

$folderCover = dirname(__DIR__) . "/uploads/cover";
$urlCover = "../uploads/cover/";

if (!is_dir($folderCover)) {
    @mkdir($folderCover, 0775, true);
}

$kolomCover = null;
$pesan = "";
$error = "";
$cari = trim($_GET["cari"] ?? "");
$aksi = $_GET["aksi"] ?? "";
$idEdit = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

$hasilKolom = mysqli_query($koneksi, "SHOW COLUMNS FROM buku");

if (!$hasilKolom) {
    die("Tabel buku tidak ditemukan atau tidak bisa dibaca.");
}

$kolom = [];
$kolomInfo = [];

while ($info = mysqli_fetch_assoc($hasilKolom)) {
    $kolom[] = $info["Field"];
    $kolomInfo[$info["Field"]] = $info;
}

foreach ($kolom as $nama) {
    if (preg_match('/cover|gambar|foto|sampul|image/i', $nama)) {
        $kolomCover = $nama;
        break;
    }
}

if (!$kolom) {
    die("Tabel buku belum memiliki kolom.");
}

$kolomId = in_array("id", $kolom, true) ? "id" : $kolom[0];

$kolomInput = [];

foreach ($kolom as $namaKolom) {
    if (
        $namaKolom === $kolomId ||
        in_array(strtolower($namaKolom), [
            "created_at",
            "updated_at",
            "createdat",
            "updatedat"
        ], true)
    ) {
        continue;
    }

    $extra = strtolower($kolomInfo[$namaKolom]["Extra"] ?? "");

    if (strpos($extra, "auto_increment") !== false) {
        continue;
    }

    if ($kolomCover !== null && $namaKolom === $kolomCover) {
        continue;
    }

    $kolomInput[] = $namaKolom;
}

/* =========================
   UPLOAD COVER
========================= */

function prosesCover($namaInput, $folderCover, &$error) {

    if (
        !isset($_FILES[$namaInput]) ||
        $_FILES[$namaInput]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    $file = $_FILES[$namaInput];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $error = "Upload cover gagal.";
        return false;
    }

    if ($file["size"] > 5 * 1024 * 1024) {
        $error = "Ukuran cover maksimal 5 MB.";
        return false;
    }

    $info = @getimagesize($file["tmp_name"]);

    if ($info === false) {
        $error = "File cover harus berupa gambar yang valid.";
        return false;
    }

    $mime = $info["mime"] ?? "";

    $extMap = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
        "image/gif" => "gif"
    ];

    if (!isset($extMap[$mime])) {
        $error = "Format cover harus JPG, PNG, WEBP, atau GIF.";
        return false;
    }

    if (!is_dir($folderCover) && !mkdir($folderCover, 0775, true)) {
        $error = "Folder uploads/cover tidak bisa dibuat.";
        return false;
    }

    $namaFile = "cover_" . bin2hex(random_bytes(12)) . "." . $extMap[$mime];

    if (!move_uploaded_file($file["tmp_name"], $folderCover . "/" . $namaFile)) {
        $error = "Gambar gagal disimpan.";
        return false;
    }

    return $namaFile;
}

/* =========================
   HAPUS DATA
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hapus_id"])) {

    $idHapus = filter_input(INPUT_POST, "hapus_id", FILTER_VALIDATE_INT);

    if (!$idHapus) {
        $error = "ID buku tidak valid.";
    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM buku WHERE `$kolomId` = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $idHapus);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: buku.php?hapus=berhasil");
                exit;
            }

            $error = "Buku gagal dihapus. Buku mungkin masih terhubung dengan pesanan.";

            mysqli_stmt_close($stmt);

        } else {
            $error = "Query hapus gagal disiapkan.";
        }
    }
}

/* =========================
   TAMBAH / EDIT DATA
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    (isset($_POST["simpan_buku"]) || isset($_POST["update_buku"]))
) {

    $isEdit = isset($_POST["update_buku"]);
    $idForm = filter_input(INPUT_POST, "id_buku", FILTER_VALIDATE_INT);

    $nilai = [];
    $namaKolomValid = [];

    if ($kolomCover !== null) {

        $namaCoverBaru = prosesCover(
            "cover_gambar",
            $folderCover,
            $error
        );

        if ($namaCoverBaru === false) {
            $namaCoverBaru = null;

        } elseif ($namaCoverBaru !== null) {

            $nilai[$kolomCover] = $namaCoverBaru;
            $namaKolomValid[] = $kolomCover;

        } elseif (
            !$isEdit &&
            ($kolomInfo[$kolomCover]["Null"] ?? "YES") === "NO" &&
            ($kolomInfo[$kolomCover]["Default"] ?? null) === null
        ) {
            $error = "Silakan pilih gambar cover buku.";
        }
    }

    foreach ($kolomInput as $namaKolom) {

        $tipe = strtolower($kolomInfo[$namaKolom]["Type"] ?? "");
        $default = $kolomInfo[$namaKolom]["Default"] ?? null;
        $nullAllowed = ($kolomInfo[$namaKolom]["Null"] ?? "NO") === "YES";

        if (
            in_array(strtolower($namaKolom), [
                "created_at",
                "updated_at",
                "createdat",
                "updatedat"
            ], true)
        ) {
            continue;
        }

        if (!array_key_exists($namaKolom, $_POST)) {

            if (
                strpos($tipe, "timestamp") !== false ||
                strpos($tipe, "datetime") !== false ||
                strpos($tipe, "date") === 0
            ) {
                continue;
            }

            if ($default !== null || $nullAllowed) {
                continue;
            }

            $nilai[$namaKolom] = "";
            $namaKolomValid[] = $namaKolom;

            continue;
        }

        $v = trim((string)$_POST[$namaKolom]);

        if ($v === "" && $nullAllowed) {
            $v = null;
        }

        $nilai[$namaKolom] = $v;
        $namaKolomValid[] = $namaKolom;
    }

    if ($error !== "") {

        // Tampilkan pesan error.

    } elseif ($isEdit && !$idForm) {

        $error = "ID buku tidak valid.";

    } elseif (!$nilai && !$isEdit) {

        $error = "Tidak ada kolom yang bisa diisi.";

    } else {

        if ($isEdit) {

            if (!$namaKolomValid) {
                $error = "Tidak ada perubahan data yang disimpan.";
            } else {

                $set = [];

                foreach ($namaKolomValid as $namaKolom) {
                    $set[] = "`$namaKolom` = ?";
                }

                $sql = "UPDATE buku SET " . implode(", ", $set) .
                       " WHERE `$kolomId` = ?";

                $stmt = mysqli_prepare($koneksi, $sql);

                if ($stmt) {

                    $tipeBind = str_repeat("s", count($namaKolomValid)) . "i";

                    $values = array_values($nilai);
                    $values[] = $idForm;

                    mysqli_stmt_bind_param($stmt, $tipeBind, ...$values);

                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: buku.php?ubah=berhasil");
                        exit;
                    }

                    $error = "Perubahan buku gagal disimpan: " . mysqli_stmt_error($stmt);

                    mysqli_stmt_close($stmt);
                }

            }

        } else {

            $fields = array_map(
                function($n) {
                    return "`" . $n . "`";
                },
                $namaKolomValid
            );

            $placeholders = implode(
                ", ",
                array_fill(0, count($namaKolomValid), "?")
            );

            $sql = "INSERT INTO buku (" .
                   implode(", ", $fields) .
                   ") VALUES (" . $placeholders . ")";

            $stmt = mysqli_prepare($koneksi, $sql);

            if ($stmt) {

                $values = array_values($nilai);

                mysqli_stmt_bind_param(
                    $stmt,
                    str_repeat("s", count($values)),
                    ...$values
                );

                if (mysqli_stmt_execute($stmt)) {
                    header("Location: buku.php?tambah=berhasil");
                    exit;
                }

                $error = "Buku gagal ditambahkan: " . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }
}

/* =========================
   NOTIFIKASI
========================= */

if (isset($_GET["tambah"])) {
    $pesan = "Buku berhasil ditambahkan.";
}

if (isset($_GET["ubah"])) {
    $pesan = "Data buku berhasil diperbarui.";
}

if (isset($_GET["hapus"])) {
    $pesan = "Buku berhasil dihapus.";
}

/* =========================
   DATA EDIT
========================= */

$editRow = null;

if ($aksi === "edit" && $idEdit) {

    $stmtEdit = mysqli_prepare(
        $koneksi,
        "SELECT * FROM buku WHERE `$kolomId` = ?"
    );

    if ($stmtEdit) {

        mysqli_stmt_bind_param($stmtEdit, "i", $idEdit);
        mysqli_stmt_execute($stmtEdit);

        $resultEdit = mysqli_stmt_get_result($stmtEdit);
        $editRow = mysqli_fetch_assoc($resultEdit);

        mysqli_stmt_close($stmtEdit);
    }

    if (!$editRow) {
        $error = "Data buku tidak ditemukan.";
        $aksi = "";
    }
}

/* =========================
   TOTAL BUKU
========================= */

$totalBuku = 0;

$hasilTotal = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS jumlah FROM buku"
);

if ($hasilTotal) {
    $rowTotal = mysqli_fetch_assoc($hasilTotal);
    $totalBuku = (int)($rowTotal["jumlah"] ?? 0);
}

/* =========================
   PENCARIAN
========================= */

if ($cari !== "") {

    $searchable = [];

    foreach ($kolom as $namaKolom) {

        $type = strtolower($kolomInfo[$namaKolom]["Type"] ?? "");

        if (
            strpos($type, "char") !== false ||
            strpos($type, "text") !== false ||
            strpos($type, "enum") !== false ||
            strpos($type, "int") !== false
        ) {
            $searchable[] = "`$namaKolom` LIKE ?";
        }
    }

    if ($searchable) {

        $sqlCari = "SELECT * FROM buku WHERE " .
                   implode(" OR ", $searchable) .
                   " ORDER BY `$kolomId` DESC";

        $stmtCari = mysqli_prepare($koneksi, $sqlCari);

        $kataCari = "%" . $cari . "%";
        $typesCari = str_repeat("s", count($searchable));
        $valuesCari = array_fill(0, count($searchable), $kataCari);

        mysqli_stmt_bind_param($stmtCari, $typesCari, ...$valuesCari);
        mysqli_stmt_execute($stmtCari);

        $daftarBuku = mysqli_stmt_get_result($stmtCari);

    } else {

        $daftarBuku = mysqli_query(
            $koneksi,
            "SELECT * FROM buku ORDER BY `$kolomId` DESC"
        );
    }

} else {

    $daftarBuku = mysqli_query(
        $koneksi,
        "SELECT * FROM buku ORDER BY `$kolomId` DESC"
    );
}

$jumlahDitampilkan = $daftarBuku ? mysqli_num_rows($daftarBuku) : 0;

/* =========================
   TOTAL PESAN CONTACT
========================= */

$totalPesan = 0;

$cekTabelPesan = mysqli_query(
    $koneksi,
    "SHOW TABLES LIKE 'pesan_contact'"
);

if ($cekTabelPesan && mysqli_num_rows($cekTabelPesan) > 0) {

    $hasilPesan = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jumlah FROM pesan_contact"
    );

    if ($hasilPesan) {
        $dataPesan = mysqli_fetch_assoc($hasilPesan);
        $totalPesan = (int)($dataPesan["jumlah"] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Data Buku | Saviesta</title>

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

.panel {
    padding: 22px;
    margin-bottom: 22px;
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

.search {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

input,
textarea,
select {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #deded4;
    border-radius: 8px;
    font: 13px Arial, sans-serif;
    background: #fff;
    color: #252a21;
}

.search input {
    width: auto;
    min-width: 220px;
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

.btn-light {
    background: #e9e8df;
    color: #252a21;
}

.btn-danger {
    background: #9b3930;
}

.tablewrap {
    overflow-x: auto;
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 800px;
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
    overflow-wrap: anywhere;
}

.empty {
    text-align: center;
    color: #77796f;
    padding: 30px;
}

.formgrid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 7px;
    color: #55594f;
}

.field.full {
    grid-column: 1 / -1;
}

.formactions {
    display: flex;
    gap: 9px;
    margin-top: 18px;
    flex-wrap: wrap;
}

.actions {
    display: flex;
    gap: 7px;
    align-items: center;
    flex-wrap: wrap;
}

.inline {
    display: inline;
}

.small {
    font-size: 11px;
    color: #77796f;
    margin-top: 10px;
    line-height: 1.5;
}

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

    .cards,
    .formgrid {
        grid-template-columns: 1fr;
    }

    .field.full {
        grid-column: auto;
    }

    .panel {
        padding: 15px;
    }

    .search input {
        width: 100%;
    }
}

</style>
</head>

<body>

<aside class="sidebar">

    <div class="brand">SAVIESTA</div>
    <div class="sub">BOOKSTORE ADMIN PANEL</div>

    <div class="section">MENU UTAMA</div>

    <nav>

        <a href="dashboard.php">▦ Dashboard</a>

        <a class="active" href="buku.php">▤ Data Buku</a>

        <a href="pesanan.php">▣ Data Pesanan</a>

        <a href="users.php">♙ Data Pengguna</a>

        <a href="pesan.php">
            ✉ Data Pesan
    
        </a>

        <div class="section">LAINNYA</div>

        <a href="../index.php" target="_blank">↗ Lihat Website</a>

        <a href="logout.php">⇥ Logout</a>

    </nav>

</aside>

<main class="main">

<header class="top">

    <div>
        <h1>Data Buku</h1>
        <p class="muted">Kelola koleksi buku Saviesta Bookstore.</p>
    </div>

    <div class="admin">
        Admin:
        <strong><?= aman($_SESSION["admin_nama"] ?? "Administrator") ?></strong>
    </div>

</header>

<?php if ($pesan): ?>
    <div class="alert success"><?= aman($pesan) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert danger"><?= aman($error) ?></div>
<?php endif; ?>

<section class="cards">

    <div class="card">
        <div class="icon">📚</div>
        <div class="label">Total Buku</div>
        <div class="number"><?= number_format($totalBuku) ?></div>
    </div>

    <div class="card">
        <div class="icon">🔎</div>
        <div class="label">Hasil Pencarian</div>
        <div class="number"><?= number_format($jumlahDitampilkan) ?></div>
    </div>

    <div class="card">
        <div class="icon">🗂️</div>
        <div class="label">Kolom Data</div>
        <div class="number"><?= number_format(count($kolom)) ?></div>
    </div>

</section>

<?php if ($aksi === "tambah" || $aksi === "edit"): ?>

<section class="panel">

    <div class="panel-head">

        <h2><?= $aksi === "edit" ? "Edit Buku" : "Tambah Buku Baru" ?></h2>

        <a class="btn btn-light" href="buku.php">Kembali</a>

    </div>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="buku.php<?= $aksi === "edit" && $idEdit ? "?aksi=edit&id=" . (int)$idEdit : "" ?>"
    >

        <?php if ($aksi === "edit"): ?>
            <input type="hidden" name="id_buku" value="<?= (int)$idEdit ?>">
        <?php endif; ?>

        <div class="formgrid">

        <?php foreach ($kolomInput as $namaKolom):

            $type = strtolower($kolomInfo[$namaKolom]["Type"] ?? "");
            $val = $editRow[$namaKolom] ?? "";

            $isLong =
                strpos($type, "text") !== false ||
                strpos(strtolower($namaKolom), "deskripsi") !== false ||
                strpos(strtolower($namaKolom), "sinopsis") !== false;

            $isNumber = preg_match('/int|decimal|float|double|numeric/', $type);
            $full = $isLong;

        ?>

            <div class="field <?= $full ? "full" : "" ?>">

                <label for="<?= aman($namaKolom) ?>">
                    <?= aman(namaKolom($namaKolom)) ?>
                    <?= (
                        $kolomInfo[$namaKolom]["Null"] === "NO" &&
                        $kolomInfo[$namaKolom]["Default"] === null
                    ) ? " *" : "" ?>
                </label>

                <?php if ($isLong): ?>

                    <textarea
                        id="<?= aman($namaKolom) ?>"
                        name="<?= aman($namaKolom) ?>"
                        rows="4"
                    ><?= aman($val) ?></textarea>

                <?php else: ?>

                    <input
                        id="<?= aman($namaKolom) ?>"
                        name="<?= aman($namaKolom) ?>"
                        type="<?= $isNumber ? "number" : "text" ?>"
                        <?= $isNumber && strpos($type, "int") === false ? 'step="0.01"' : "" ?>
                        value="<?= aman($val) ?>"
                        <?= (
                            $kolomInfo[$namaKolom]["Null"] === "NO" &&
                            $kolomInfo[$namaKolom]["Default"] === null
                        ) ? "required" : "" ?>
                    >

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

        <?php if ($kolomCover !== null): ?>

            <div class="field full">

                <label for="cover_gambar">
                    Gambar Cover Buku (JPG, PNG, WEBP, GIF — maksimal 5 MB)
                </label>

                <input
                    id="cover_gambar"
                    name="cover_gambar"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    <?= (
                        $aksi === "tambah" &&
                        ($kolomInfo[$kolomCover]["Null"] ?? "YES") === "NO" &&
                        ($kolomInfo[$kolomCover]["Default"] ?? null) === null
                    ) ? "required" : "" ?>
                >

                <?php if ($aksi === "edit" && !empty($editRow[$kolomCover])): ?>

                    <p class="small">
                        Cover saat ini: <?= aman($editRow[$kolomCover]) ?>.
                        Pilih file baru jika ingin menggantinya.
                    </p>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        </div>

        <div class="formactions">

            <button
                class="btn"
                type="submit"
                name="<?= $aksi === "edit" ? "update_buku" : "simpan_buku" ?>"
                value="1"
            >
                <?= $aksi === "edit" ? "Simpan Perubahan" : "Tambah Buku" ?>
            </button>

            <a class="btn btn-light" href="buku.php">Batal</a>

        </div>

        <p class="small">
            Form ini menyesuaikan kolom tabel buku yang ada di database.
            Kolom wajib diisi ditandai dengan tanda *.
        </p>

    </form>

</section>

<?php endif; ?>

<section class="panel">

    <div class="panel-head">

        <h2>Daftar Koleksi Buku</h2>

        <div class="actions">

            <form class="search" method="GET">

                <input
                    type="search"
                    name="cari"
                    placeholder="Cari data buku..."
                    value="<?= aman($cari) ?>"
                >

                <button class="btn" type="submit">Cari</button>

                <?php if ($cari !== ""): ?>
                    <a class="btn btn-light" href="buku.php">Reset</a>
                <?php endif; ?>

            </form>

            <a class="btn" href="buku.php?aksi=tambah">+ Tambah Buku</a>

        </div>

    </div>

    <div class="tablewrap">

        <table>

            <thead>

                <tr>

                    <?php foreach ($kolom as $namaKolom): ?>
                        <th><?= aman(namaKolom($namaKolom)) ?></th>
                    <?php endforeach; ?>

                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($daftarBuku && mysqli_num_rows($daftarBuku) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($daftarBuku)): ?>

                <tr>

                    <?php foreach ($kolom as $namaKolom): ?>

                    <td>

                        <?php

                        $val = $row[$namaKolom] ?? "";
                        $low = strtolower($namaKolom);

                        if (
                            $kolomCover !== null &&
                            $namaKolom === $kolomCover &&
                            $val !== ""
                        ) {

                            $src = $urlCover . rawurlencode(basename((string)$val));

                            echo '<img src="' . aman($src) . '"
                                alt="Cover buku"
                                style="width:52px;height:70px;object-fit:cover;border-radius:5px;border:1px solid #eee"
                                onerror="this.style.display=\'none\'">';

                        } elseif (
                            strpos($low, "harga") !== false ||
                            $low === "price" ||
                            $low === "total"
                        ) {

                            echo aman(rupiahBuku($val));

                        } else {

                            $val = (string)$val;

                            echo aman(
                                mb_strlen($val, "UTF-8") > 70
                                    ? mb_substr($val, 0, 67, "UTF-8") . "..."
                                    : $val
                            );
                        }

                        ?>

                    </td>

                    <?php endforeach; ?>

                    <td>

                        <div class="actions">

                            <a
                                class="btn"
                                href="buku.php?aksi=edit&id=<?= (int)$row[$kolomId] ?>"
                            >
                                Edit
                            </a>

                            <form
                                class="inline"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus buku ini? Tindakan ini tidak bisa dibatalkan.');"
                            >

                                <input
                                    type="hidden"
                                    name="hapus_id"
                                    value="<?= (int)$row[$kolomId] ?>"
                                >

                                <button class="btn btn-danger" type="submit">
                                    Hapus
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="<?= count($kolom) + 1 ?>" class="empty">
                        Belum ada data buku yang ditemukan.
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