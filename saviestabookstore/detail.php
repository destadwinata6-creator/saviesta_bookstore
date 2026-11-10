<?php

include "functions.php";

$id = (int)($_GET['id'] ?? 0);

$query = mysqli_query(
    $koneksi,
    "SELECT buku.*, kategori.nama AS kategori
     FROM buku
     LEFT JOIN kategori
     ON kategori.id = buku.kategori_id
     WHERE buku.id = $id"
);

$b = mysqli_fetch_assoc($query);

if (!$b) {

    header("Location:buku.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= aman($b['judul']) ?> — Saviesta</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    background: #faf6ee;

    color: #252a21;

    font-family: Arial, sans-serif;

}


/* =========================
   HEADER
========================= */

header {

    height: 78px;

    padding: 0 7%;

    display: flex;

    align-items: center;

    border-bottom: 1px solid #ded9cf;

    background: rgba(250,247,239,.95);

    backdrop-filter: blur(10px);

}

nav {

    width: 100%;

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.logo {

    display: flex;

    align-items: center;

    text-decoration: none;

}

.logo img {

    width: 50px;

    height: auto;

    display: block;

}

.menu {

    display: flex;

    align-items: center;

    gap: 23px;

}

.menu a {

    text-decoration: none;

    font-size: 13px;

    color: #252a21;

    transition: .2s;

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


/* =========================
   CONTAINER
========================= */

.container {

    width: 88%;

    max-width: 1200px;

    margin: auto;

    padding: 55px 0 100px;

}


/* =========================
   BACK
========================= */

.back {

    display: inline-block;

    color: #777;

    text-decoration: none;

    font-size: 13px;

    margin-bottom: 35px;

}

.back:hover {

    color: #252a21;

}


/* =========================
   DETAIL
========================= */

.detail {

    display: grid;

    grid-template-columns: 430px 1fr;

    gap: 75px;

    align-items: center;

}


/* =========================
   BOOK COVER
========================= */

.cover-box {

    position: relative;

    width: 100%;

    height: 560px;

    border-radius: 10px;

    overflow: hidden;

    background: #e9e4d8;

    box-shadow: 0 25px 50px rgba(0,0,0,.13);

}

.cover-box img {

    width: 100%;

    height: 100%;

    display: block;

    object-fit: cover;

    object-position: center;

    transition: .4s;

}

.cover-box:hover img {

    transform: scale(1.03);

}


/* =========================
   CATEGORY
========================= */

.category {

    display: inline-block;

    font-size: 10px;

    color: #777;

    letter-spacing: 2px;

    text-transform: uppercase;

    margin-bottom: 15px;

}


/* =========================
   TITLE
========================= */

.title {

    margin: 0;

    font: normal 56px/1.05 Georgia, serif;

    letter-spacing: -2px;

}


/* =========================
   AUTHOR
========================= */

.author {

    margin-top: 15px;

    font-size: 14px;

    color: #777;

}

.author strong {

    color: #252a21;

}


/* =========================
   PRICE
========================= */

.price {

    margin-top: 28px;

    font-size: 22px;

    font-weight: bold;

}


/* =========================
   DESCRIPTION
========================= */

.description {

    margin-top: 25px;

    max-width: 600px;

    color: #777;

    font-size: 14px;

    line-height: 1.9;

}


/* =========================
   STOCK
========================= */

.stock {

    margin-top: 20px;

    font-size: 12px;

    color: #777;

}

.stock.available {

    color: #42553a;

}


/* =========================
   BUTTON
========================= */

.actions {

    margin-top: 30px;

    display: flex;

    gap: 12px;

}

.btn {

    border: 0;

    padding: 15px 25px;

    border-radius: 30px;

    background: #252a21;

    color: white;

    cursor: pointer;

    font-size: 13px;

    transition: .25s;

}

.btn:hover {

    transform: translateY(-2px);

    opacity: .9;

}


/* =========================
   FOOTER
========================= */

footer {

    background: #252a21;

    color: white;

    padding: 55px 7%;

}

.footer-inner {

    max-width: 1200px;

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

    font-size: 12px;

    line-height: 1.6;

}


/* =========================
   RESPONSIVE
========================= */

@media(max-width: 850px) {

    .detail {

        grid-template-columns: 1fr;

        gap: 45px;

    }

    .cover-box {

        max-width: 400px;

        margin: auto;

        height: 500px;

    }

    .title {

        font-size: 45px;

    }

}

@media(max-width: 500px) {

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

    .container {

        width: 92%;

        padding-top: 40px;

    }

    .cover-box {

        height: 430px;

    }

    .title {

        font-size: 38px;

    }

    .footer-inner {

        display: block;

        text-align: center;

    }

    .footer-text {

        margin-top: 15px;

    }

}

</style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<header>

<nav>

<a href="index.php" class="logo">

<img
src="L2.png.png"
alt="Saviesta BookStore"
>

</a>


<div class="menu">

<a href="index.php">
Home
</a>

<a href="buku.php">
Koleksi
</a>

<a href="about.php">
About
</a>

<a href="contact.php">
Contact
</a>

<a href="keranjang.php">
🛒 <?= jumlahCart() ?>
</a>


<?php if(isset($_SESSION['user'])): ?>

<a href="logout.php">
Logout
</a>

<?php else: ?>

<a href="login.php">
Login
</a>

<a
href="register.php"
class="register"
>

Register

</a>

<?php endif; ?>

</div>

</nav>

</header>


<!-- =========================
     CONTENT
========================= -->

<div class="container">


<a
href="buku.php"
class="back"
>

← Kembali ke Koleksi

</a>


<div class="detail">


<!-- =========================
     GAMBAR BUKU
========================= -->

<div class="cover-box">

<img
src="img3/<?= aman($b['cover']) ?>"
alt="<?= aman($b['judul']) ?>"
>

</div>


<!-- =========================
     INFO BUKU
========================= -->

<div>

<div class="category">

<?= aman($b['kategori']) ?>

</div>


<h1 class="title">

<?= aman($b['judul']) ?>

</h1>


<div class="author">

Penulis:
<strong>
<?= aman($b['penulis']) ?>
</strong>

</div>


<div class="price">

<?= rupiah($b['harga']) ?>

</div>


<div class="description">

<?= aman($b['deskripsi']) ?>

</div>


<div
class="stock
<?= $b['stok'] > 0 ? 'available' : '' ?>"
>

<?= $b['stok'] > 0
? "● Stok tersedia — " . $b['stok'] . " buku"
: "● Stok habis"
?>

</div>


<?php if($b['stok'] > 0): ?>

<div class="actions">

<form
method="POST"
action="keranjang.php"
>

<input
type="hidden"
name="id"
value="<?= $b['id'] ?>"
>

<button
type="submit"
name="tambah"
class="btn"
>

Tambah ke Keranjang

</button>

</form>

</div>

<?php endif; ?>


</div>


</div>

</div>


<!-- =========================
     FOOTER
========================= -->

<footer>

<div class="footer-inner">

<div class="footer-logo">

Saviesta family

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