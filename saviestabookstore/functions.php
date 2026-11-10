<?php

session_start();

include "koneksi.php";

function rupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}

function aman($data)
{
    return htmlspecialchars(
        $data,
        ENT_QUOTES,
        'UTF-8'
    );
}

function jumlahCart()
{
    return array_sum($_SESSION['cart'] ?? []);
}

?>