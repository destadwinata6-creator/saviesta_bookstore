<?php

include "functions.php";

/* =========================
   SEARCH
========================= */

$q = $_GET['q'] ?? '';
$q = mysqli_real_escape_string($koneksi, $q);


/* =========================
   FILTER KATEGORI
========================= */

$kategori = (int)($_GET['kategori'] ?? 0);


/* =========================
   SORT
========================= */

$sort = $_GET['sort'] ?? 'terbaru';

switch ($sort) {

    case 'murah':
        $order = "buku.harga ASC";
        break;

    case 'mahal':
        $order = "buku.harga DESC";
        break;

    case 'az':
        $order = "buku.judul ASC";
        break;

    default:
        $order = "buku.id DESC";
}


/* =========================
   QUERY
========================= */

$sql = "
SELECT
    buku.*,
    kategori.nama AS nama_kategori
FROM buku
LEFT JOIN kategori
ON kategori.id = buku.kategori_id
WHERE 1=1
";

if ($q != '') {

    $sql .= "
    AND (
        buku.judul LIKE '%$q%'
        OR buku.penulis LIKE '%$q%'
    )
    ";

}

if ($kategori > 0) {

    $sql .= "
    AND buku.kategori_id = $kategori
    ";

}

$sql .= " ORDER BY $order";

$data = mysqli_query($koneksi, $sql);


/* =========================
   KATEGORI
========================= */

$kategoriData = mysqli_query(
    $koneksi,
    "SELECT * FROM kategori ORDER BY nama ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Koleksi — Saviesta</title>

<style>

/* =========================
   RESET
========================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f8f5ee;
    color: #242820;
    font-family: Arial, sans-serif;
}

a {
    color: inherit;
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
    background: #f8f5ee;
}

nav {

    width: 100%;

    display: flex;
    align-items: center;
    justify-content: space-between;
}


/* =========================
   LOGO
========================= */

.logo {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.logo img {
    width: 50px;
    height: 50px;
    object-fit: contain;
    display: block;
}

.logo-text {
    font-family: Georgia, serif;
    font-size: 21px;
    font-weight: bold;
    color: #242820;
}


/* =========================
   MENU
========================= */

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
    color: #6f7c61;
}

.register {

    background: #242820;
    color: white !important;

    padding: 10px 18px;
    border-radius: 25px;
}


/* =========================
   CONTAINER
========================= */

.container {

    width: 88%;
    max-width: 1250px;

    margin: auto;

    padding: 50px 0 90px;
}


/* =========================
   TOP
========================= */

.back {

    color: #777;
    text-decoration: none;
    font-size: 13px;
}

.top {

    margin-top: 35px;

    display: flex;
    justify-content: space-between;
    align-items: end;
}

.top h1 {

    margin: 0;

    font: normal 58px Georgia;

    letter-spacing: -2px;
}

.top p {

    color: #777;
    font-size: 14px;

    margin-bottom: 3px;
}


/* =========================
   SEARCH
========================= */

.search-wrapper {

    margin-top: 35px;

    display: flex;
    gap: 12px;
}

.search {

    flex: 1;
    position: relative;
}

.search input {

    width: 100%;
    height: 54px;

    padding: 0 22px;

    border-radius: 30px;

    border: 1px solid #d8d2c7;

    background: #fffdf9;

    outline: none;

    font-size: 14px;
}

.search input:focus {

    border-color: #242820;
}

.search-btn {

    height: 54px;

    padding: 0 27px;

    border: 0;

    border-radius: 30px;

    background: #242820;

    color: white;

    cursor: pointer;

    font-weight: bold;
}


/* =========================
   FILTER
========================= */

.filter-bar {

    margin-top: 25px;

    padding-bottom: 25px;

    border-bottom: 1px solid #ded9cf;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}

.categories {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;
}

.categories a {

    text-decoration: none;

    padding: 9px 17px;

    border-radius: 30px;

    border: 1px solid #d2ccc1;

    font-size: 12px;

    transition: .2s;
}

.categories a:hover {

    background: #242820;
    color: white;
}

.categories .active {

    background: #242820;
    color: white;

    border-color: #242820;
}

.sort {

    border: 1px solid #d2ccc1;

    background: #fffdf9;

    border-radius: 25px;

    padding: 10px 15px;

    outline: none;
}


/* =========================
   RESULT
========================= */

.result {

    margin: 25px 0;

    color: #777;

    font-size: 13px;
}


/* =========================
   BOOK GRID
========================= */

.book-grid {

    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 38px 24px;
}


/* =========================
   CARD
========================= */

.book-card {

    min-width: 0;
}


/* =========================
   COVER
========================= */

.cover {

    height: 380px;

    border-radius: 5px;

    position: relative;

    overflow: hidden;

    display: flex;

    align-items: end;

    padding: 25px;

    color: white;

    background-size: cover;
    background-position: center;

    box-shadow:
    0 15px 30px rgba(0,0,0,.08);

    transition: .3s;
}

.book-card:hover .cover {

    transform: translateY(-7px);

    box-shadow:
    0 20px 40px rgba(0,0,0,.13);
}


/* =========================
   GRADIENT
========================= */

.cover::after {

    content: "";

    position: absolute;

    inset: 0;

    background:
    linear-gradient(
        to top,
        rgba(0,0,0,.7),
        rgba(0,0,0,0) 65%
    );
}


/* =========================
   COVER TEXT
========================= */

.cover-content {

    position: relative;

    z-index: 2;
}

.cover-category {

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 1.5px;

    opacity: .85;
}

.cover-title {

    margin-top: 8px;

    font: 25px Georgia;

    line-height: 1.1;
}


/* =========================
   WISHLIST
========================= */

.wishlist {

    position: absolute;

    z-index: 5;

    right: 15px;
    top: 15px;

    width: 38px;
    height: 38px;

    border-radius: 50%;

    border: 0;

    background: rgba(255,255,255,.9);

    font-size: 18px;

    cursor: pointer;
}


/* =========================
   INFO
========================= */

.info {

    padding-top: 15px;
}

.category-name {

    font-size: 10px;

    text-transform: uppercase;

    color: #89857c;

    letter-spacing: 1.2px;
}

.title {

    margin-top: 7px;

    font: 20px Georgia;

    line-height: 1.2;
}

.author {

    margin-top: 5px;

    font-size: 13px;

    color: #777;
}


/* =========================
   BOTTOM
========================= */

.bottom {

    margin-top: 15px;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.price {

    font-weight: bold;

    font-size: 15px;
}


/* =========================
   ADD
========================= */

.add-form {
    margin: 0;
}

.add {

    border: 1px solid #242820;

    background: transparent;

    width: 38px;
    height: 38px;

    border-radius: 50%;

    cursor: pointer;

    font-size: 20px;

    transition: .2s;
}

.add:hover {

    background: #242820;

    color: white;
}


/* =========================
   STOCK
========================= */

.stock {

    margin-top: 8px;

    font-size: 11px;

    color: #888;
}


/* =========================
   DETAIL
========================= */

.detail {

    display: block;

    text-align: center;

    margin-top: 14px;

    padding: 10px;

    border: 1px solid #d5cfc4;

    border-radius: 4px;

    text-decoration: none;

    font-size: 12px;

    transition: .2s;
}

.detail:hover {

    background: #242820;

    color: white;
}


/* =========================
   EMPTY
========================= */

.empty {

    grid-column: 1 / -1;

    padding: 90px 20px;

    text-align: center;

    background: #fffdf9;

    border: 1px solid #e1dbd0;
}

.empty h2 {

    font: 30px Georgia;
}

.empty p {

    color: #777;
}

.empty a {

    display: inline-block;

    margin-top: 10px;

    padding: 12px 22px;

    background: #242820;

    color: white;

    text-decoration: none;

    border-radius: 25px;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width: 1050px) {

    .book-grid {

        grid-template-columns:
        repeat(3, 1fr);
    }
}

@media(max-width: 800px) {

    .menu {
        gap: 10px;
    }

    .menu a:nth-child(2),
    .menu a:nth-child(3),
    .menu a:nth-child(4) {
        display: none;
    }

    .book-grid {

        grid-template-columns:
        repeat(2, 1fr);
    }

    .filter-bar {

        align-items: flex-start;

        flex-direction: column;
    }
}

@media(max-width: 500px) {

    .container {
        width: 92%;
    }

    .top h1 {
        font-size: 42px;
    }

    .search-wrapper {
        flex-direction: column;
    }

    .search-btn {
        width: 100%;
    }

    .book-grid {
        grid-template-columns: 1fr;
    }

    .cover {
        height: 450px;
    }
}

/* INFO STOK BUKU */
.stock {
    margin-top: 8px;
    font-size: 12px;
    color: #777;
}
.stock.habis {
    color: #b65d50;
    font-weight: bold;
}

/* DETAIL */
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
alt="Saviesta"
>

<span class="logo-text">

</span>

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
     MAIN
========================= -->

<div class="container">


<a
href="index.php"
class="back"
>

← Kembali ke Home

</a>


<div class="top">

<div>

<h1>
Koleksi Buku
</h1>

<p>
Temukan buku yang cocok
untuk cerita kamu.
</p>

</div>

</div>



<!-- =========================
     SEARCH
========================= -->

<form
method="GET"
class="search-wrapper"
>

<div class="search">

<input

type="text"

name="q"

placeholder="Cari judul buku atau penulis..."

value="<?= aman($q) ?>"

>

</div>


<button
type="submit"
class="search-btn"
>

Search

</button>

</form>



<!-- =========================
     FILTER
========================= -->

<div class="filter-bar">


<div class="categories">


<a
href="buku.php"
class="<?= $kategori == 0 ? 'active' : '' ?>"
>

All Books

</a>


<?php while($kat = mysqli_fetch_assoc($kategoriData)): ?>

<a

href="buku.php?kategori=<?= $kat['id'] ?>"

class="<?= $kategori == $kat['id'] ? 'active' : '' ?>"

>

<?= aman($kat['nama']) ?>

</a>

<?php endwhile; ?>


</div>


<select
class="sort"
onchange="location.href=this.value;"
>

<option value="buku.php">
Sort by
</option>

<option value="buku.php?sort=terbaru">
Terbaru
</option>

<option value="buku.php?sort=murah">
Harga Terendah
</option>

<option value="buku.php?sort=mahal">
Harga Tertinggi
</option>

<option value="buku.php?sort=az">
A - Z
</option>

</select>


</div>



<!-- =========================
     RESULT
========================= -->

<div class="result">

<?php

$count = mysqli_num_rows($data);

if($q != '') {

    echo "$count buku ditemukan untuk "
       . "<b>" . aman($q) . "</b>";

} else {

    echo "$count buku tersedia di Saviesta";

}

?>

</div>



<!-- =========================
     BOOKS
========================= -->

<div class="book-grid">


<?php if($count == 0): ?>

<div class="empty">

<h2>
Yah, bukunya nggak ketemu 😭
</h2>

<p>
Coba cari judul atau penulis lainnya.
</p>

<a href="buku.php">
Lihat Semua Buku
</a>

</div>

<?php endif; ?>



<?php while($b = mysqli_fetch_assoc($data)): ?>


<div class="book-card">


<!-- COVER -->

<?php
// Ambil nilai cover dari kolom gambar buku.
// Mendukung upload baru di uploads/cover/ dan cover lama di img3/.
$cover = '';
foreach (['cover', 'gambar', 'foto', 'sampul', 'image'] as $kolomCover) {
    if (isset($b[$kolomCover]) && trim((string)$b[$kolomCover]) !== '') {
        $cover = trim((string)$b[$kolomCover]);
        break;
    }
}

$coverUrl = '';
$coverIsImage = false;

if ($cover !== '' && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $cover)) {
    // Jika database menyimpan path lengkap relatif, gunakan path tersebut.
    if (strpos($cover, '/') !== false || strpos($cover, '\\\\') !== false) {
        $coverUrl = str_replace('\\\\', '/', $cover);
        $coverUrl = ltrim($coverUrl, '/');
    } else {
        // File hasil upload admin disimpan di uploads/cover/.
        $pathUpload = __DIR__ . '/uploads/cover/' . basename($cover);
        $pathLama = __DIR__ . '/img3/' . basename($cover);

        if (is_file($pathUpload)) {
            $coverUrl = 'uploads/cover/' . rawurlencode(basename($cover));
        } elseif (is_file($pathLama)) {
            $coverUrl = 'img3/' . rawurlencode(basename($cover));
        } else {
            // Default ke folder upload untuk file yang baru ditambahkan.
            $coverUrl = 'uploads/cover/' . rawurlencode(basename($cover));
        }
    }

    $coverIsImage = true;
}

// Kalau isi kolom bukan nama file gambar, perlakukan sebagai warna lama.
$coverColor = (!$coverIsImage && preg_match('/^#[0-9a-f]{3,8}$/i', $cover))
    ? $cover
    : '#42553A';
?>

<div
class="cover"
<?php if ($coverIsImage): ?>
    style="background-image: url('<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>');"
<?php else: ?>
    style="background: <?= htmlspecialchars($coverColor, ENT_QUOTES, 'UTF-8') ?>;"
<?php endif; ?>
>


<button
class="wishlist"
type="button"
onclick="
this.innerHTML =
this.innerHTML === '♡'
? '♥'
: '♡';
"
>

♡

</button>


<div class="cover-content">

<div class="cover-category">

<?= aman($b['nama_kategori']) ?>

</div>


<div class="cover-title">

<?= aman($b['judul']) ?>

</div>

</div>

</div>



<!-- INFO -->

<div class="info">


<div class="category-name">

<?= aman($b['nama_kategori']) ?>

</div>


<div class="title">

<?= aman($b['judul']) ?>

</div>


<div class="author">

<?= aman($b['penulis']) ?>

</div>


<div class="bottom">


<div class="price">

<?= rupiah($b['harga']) ?>

</div>


<?php if ((int)$b['stok'] > 0): ?>

<form method="POST" action="keranjang.php" class="add-form">
    <input type="hidden" name="id" value="<?= $b['id'] ?>">

    <button
        type="submit"
        name="tambah"
        class="add"
        title="Tambah ke keranjang"
    >
        +
    </button>
</form>

<?php else: ?>
    <span class="stock habis">Habis</span>
<?php endif; ?>


</div>


<div class="stock">

<?= $b['stok'] > 0
? "● Stok tersedia"
: "● Stok habis"
?>

</div>


<div class="stock <?= (int)$b['stok'] <= 0 ? 'habis' : '' ?>">
    <?php if ((int)$b['stok'] > 0): ?>
        Stok tersisa: <?= (int)$b['stok'] ?> buku
    <?php else: ?>
        Stok habis
    <?php endif; ?>
</div>

<a

href="detail.php?id=<?= $b['id'] ?>"

class="detail"

>

Lihat Detail

</a>


</div>

</div>


<?php endwhile; ?>


</div>


</div>

</body>

</html>