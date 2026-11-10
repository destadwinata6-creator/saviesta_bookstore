<?php

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "saviesta"
);

if (!$koneksi) {
    die("Database gagal terhubung");
}

mysqli_set_charset($koneksi, "utf8mb4");