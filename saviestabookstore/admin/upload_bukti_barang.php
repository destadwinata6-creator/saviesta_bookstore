<?php

session_start();

require_once __DIR__ . "/../koneksi_admin.php";

/* CEK LOGIN ADMIN */

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* CEK REQUEST */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pesanan.php");
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);

if ($order_id <= 0 || !isset($_FILES['foto_bukti'])) {
    header("Location: pesanan.php?pesan=upload_gagal");
    exit;
}

/* CEK PESANAN */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, metode, foto_bukti_barang
     FROM pesanan
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($result);

if (!$pesanan) {
    die("Pesanan tidak ditemukan.");
}

/* FOTO HANYA UNTUK COD */

if (stripos($pesanan['metode'], 'COD') === false) {
    die("Foto bukti barang hanya tersedia untuk pesanan COD.");
}

/* VALIDASI FILE */

$file = $_FILES['foto_bukti'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: pesanan.php?pesan=upload_gagal");
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    die("Ukuran foto maksimal 5 MB.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

$jenisFile = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($jenisFile[$mime])) {
    die("Format foto harus JPG, PNG, atau WEBP.");
}

/* FOLDER UPLOAD */

$folder = __DIR__ . "/../uploads/bukti_barang/";

if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

/* NAMA FILE */

$namaFile = "bukti_" . $order_id . "_" . bin2hex(random_bytes(8))
          . "." . $jenisFile[$mime];

$tujuan = $folder . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
    die("Gagal mengunggah foto.");
}

/* SIMPAN KE DATABASE */

$lokasiFoto = "uploads/bukti_barang/" . $namaFile;

$update = mysqli_prepare(
    $koneksi,
    "UPDATE pesanan
     SET foto_bukti_barang = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $update,
    "si",
    $lokasiFoto,
    $order_id
);

if (!mysqli_stmt_execute($update)) {
    if (file_exists($tujuan)) {
        unlink($tujuan);
    }

    die("Gagal menyimpan foto: " . mysqli_error($koneksi));
}

/* HAPUS FOTO LAMA JIKA ADA */

if (!empty($pesanan['foto_bukti_barang'])) {
    $fotoLama = basename($pesanan['foto_bukti_barang']);
    $pathLama = $folder . $fotoLama;

    if (is_file($pathLama)) {
        unlink($pathLama);
    }
}

header("Location: pesanan.php?pesan=bukti_tersimpan");
exit;

?>