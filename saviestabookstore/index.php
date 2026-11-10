<?php
include "functions.php";

/* =========================
   SEARCH
========================= */

$keyword = trim($_GET['keyword'] ?? '');
$search = mysqli_real_escape_string($koneksi, $keyword);

if ($keyword !== '') {
    $sql = "SELECT buku.*, kategori.nama AS kategori
            FROM buku
            LEFT JOIN kategori ON kategori.id = buku.kategori_id
            WHERE buku.judul LIKE '%$search%'
               OR buku.penulis LIKE '%$search%'
               OR kategori.nama LIKE '%$search%'
            ORDER BY buku.id DESC";
} else {
    $sql = "SELECT buku.*, kategori.nama AS kategori
            FROM buku
            LEFT JOIN kategori ON kategori.id = buku.kategori_id
            ORDER BY buku.id DESC
            LIMIT 4";
}

$data = mysqli_query($koneksi, $sql);


/* =========================
   COVER IMAGE
========================= */

function coverSaviesta($nama)
{
    $nama = trim((string)$nama);

    if ($nama === '') {
        return '';
    }

    $namaFile = basename(str_replace('\\', '/', $nama));

    $lokasi = [
        [
            'folder' => __DIR__ . '/img3/',
            'url' => 'img3/'
        ],
        [
            'folder' => __DIR__ . '/uploads/cover/',
            'url' => 'uploads/cover/'
        ]
    ];

    foreach ($lokasi as $item) {
        if (is_file($item['folder'] . $namaFile)) {
            return $item['url'] . rawurlencode($namaFile);
        }
    }

    return '';
}


/* =========================
   HERO BOOKS
========================= */

$sqlHero = "SELECT id, judul, penulis, cover
            FROM buku
            ORDER BY RAND()
            LIMIT 2";

$queryHero = mysqli_query($koneksi, $sqlHero);

$heroBooks = [];

if ($queryHero) {
    while ($rowHero = mysqli_fetch_assoc($queryHero)) {
        $heroBooks[] = $rowHero;
    }
}

$heroBook = $heroBooks[0] ?? null;
$heroBookBack = $heroBooks[1] ?? null;

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Saviesta — Bookstore</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html {
    scroll-behavior: smooth;
}

body {
    background: #faf8f1;
    color: #252a21;
    font-family: Arial, sans-serif;
}

a {
    color: inherit;
    text-decoration: none;
}

:root {
    --cream: #faf8f1;
    --dark: #252a21;
    --green: #42553a;
    --muted: #85847b;
    --line: #e5e0d5;
    --white: #fffdf8;
}

.container {
    width: 88%;
    max-width: 1250px;
    margin: auto;
}


/* =========================
   NAVBAR
========================= */

header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(250,248,241,.96);
    backdrop-filter: blur(15px);
    border-bottom: 1px solid var(--line);
}

nav {
    min-height: 82px;
    width: 86%;
    max-width: 1350px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
}

.logo img {
    width: 54px;
    height: 54px;
    object-fit: contain;
    display: block;
}

.menu {
    display: flex;
    align-items: center;
    gap: 25px;
}

.menu > a {
    font-size: 13px;
    transition: .25s;
}

.menu > a:hover {
    color: var(--green);
}

.welcome {
    font-size: 12px;
    color: #62645b;
}

.register {
    background: var(--dark);
    color: white !important;
    padding: 12px 21px;
    border-radius: 30px;
}

.register:hover {
    background: var(--green);
}

.nav-search {
    display: flex;
    align-items: center;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 30px;
    overflow: hidden;
}

.nav-search input {
    width: 130px;
    padding: 11px 15px;
    border: none;
    outline: none;
    background: transparent;
    font-size: 12px;
}

.nav-search button {
    padding: 8px 13px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 19px;
}


/* =========================
   BUTTON
========================= */

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 25px;
    background: var(--dark);
    color: white;
    border-radius: 30px;
    font-size: 13px;
    transition: .3s;
}

.btn:hover {
    background: var(--green);
    transform: translateY(-3px);
}


/* =========================
   HERO
========================= */

.hero {
    min-height: 680px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 55px;
    align-items: center;
    padding: 75px 0;
}

.hero-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--green);
    font-size: 10px;
    letter-spacing: 3px;
    margin-bottom: 25px;
}

.hero-label::before {
    content: "";
    width: 28px;
    height: 1px;
    background: var(--green);
}

.hero h1 {
    max-width: 650px;
    font: normal 70px/1.06 Georgia, serif;
    letter-spacing: -2.5px;
}

.hero h1 em {
    color: var(--green);
    font-style: italic;
}

.hero-description {
    max-width: 470px;
    margin-top: 25px;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.9;
}

.hero-actions {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-top: 30px;
}

.hero-secondary {
    font-size: 12px;
    border-bottom: 1px solid var(--dark);
    padding-bottom: 5px;
}

.hero-visual {
    position: relative;
    height: 490px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-circle {
    position: absolute;
    width: 410px;
    height: 410px;
    border-radius: 50%;
    background: #e9e5d9;
}

.hero-card {
    position: absolute;
    width: 285px;
    height: 390px;
    right: 25px;
    top: 40px;
    border-radius: 7px;
    overflow: hidden;
    background: var(--green);
    box-shadow: 0 25px 55px rgba(0,0,0,.17);
    transform: rotate(4deg);
    transition: .4s;
    z-index: 2;
}

.hero-card:hover {
    transform: rotate(0deg) translateY(-8px);
}

.hero-card-back {
    position: absolute;
    width: 245px;
    height: 345px;
    left: 35px;
    bottom: 25px;
    border-radius: 7px;
    overflow: hidden;
    background: #c6b99e;
    box-shadow: 0 20px 45px rgba(0,0,0,.13);
    transform: rotate(-8deg);
    z-index: 1;
}

.hero-card img,
.hero-card-back img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.hero-note {
    position: absolute;
    bottom: 5px;
    right: 0;
    z-index: 3;
    background: var(--white);
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    font: italic 15px Georgia, serif;
}


/* =========================
   SECTION
========================= */

.section {
    padding: 90px 0 110px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    margin-bottom: 35px;
}

.section-label {
    color: var(--green);
    font-size: 10px;
    letter-spacing: 3px;
}

.section-title {
    margin-top: 12px;
    font: normal 47px Georgia, serif;
    letter-spacing: -1.5px;
}

.section-link {
    font-size: 12px;
    border-bottom: 1px solid var(--dark);
    padding-bottom: 7px;
    transition: .2s;
}

.section-link:hover {
    color: var(--green);
}


/* =========================
   SEARCH
========================= */

.search-wrapper {
    margin-bottom: 35px;
}

.search-form {
    display: flex;
    gap: 10px;
    max-width: 590px;
}

.search-form input {
    flex: 1;
    min-width: 0;
    padding: 15px 20px;
    border: 1px solid var(--line);
    border-radius: 30px;
    background: var(--white);
    outline: none;
    font-size: 13px;
}

.search-form input:focus {
    border-color: var(--green);
}

.search-form button {
    padding: 0 25px;
    border: none;
    border-radius: 30px;
    background: var(--dark);
    color: white;
    cursor: pointer;
}

.search-form button:hover {
    background: var(--green);
}

.search-info {
    margin-top: 15px;
    color: var(--muted);
    font-size: 13px;
}

.search-info a {
    text-decoration: underline;
    color: var(--dark);
}


/* =========================
   BOOK GRID
========================= */

.book-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 35px 23px;
}

.book-card {
    min-width: 0;
}

.book-cover {
    height: 365px;
    position: relative;
    display: flex;
    align-items: end;
    padding: 22px;
    overflow: hidden;
    border-radius: 5px;
    background: #e7e2d6;
    box-shadow: 0 12px 30px rgba(0,0,0,.06);
    transition: .35s;
}

.book-card:hover .book-cover {
    transform: translateY(-7px);
    box-shadow: 0 22px 40px rgba(0,0,0,.13);
}

.book-cover img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-cover::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0,0,0,.68),
        transparent 65%
    );
}

.cover-content {
    position: relative;
    z-index: 2;
    color: white;
}

.cover-category {
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 7px;
}

.cover-title {
    font: normal 24px/1.15 Georgia, serif;
}

.book-info {
    padding-top: 17px;
}

.book-category {
    font-size: 9px;
    color: var(--muted);
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.book-title {
    margin-top: 8px;
    font: normal 20px Georgia, serif;
}

.book-author {
    margin-top: 6px;
    font-size: 12px;
    color: var(--muted);
}

.book-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.price {
    font-size: 14px;
    font-weight: bold;
}

.plus {
    width: 38px;
    height: 38px;
    border: 1px solid var(--dark);
    border-radius: 50%;
    background: transparent;
    font-size: 20px;
    cursor: pointer;
    transition: .25s;
}

.plus:hover {
    background: var(--dark);
    color: white;
}

.plus:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.stock {
    margin-top: 9px;
    font-size: 10px;
    color: var(--muted);
}

.empty-books {
    grid-column: 1 / -1;
    padding: 65px 20px;
    text-align: center;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 8px;
}

.empty-books h3 {
    font: normal 27px Georgia, serif;
    margin-bottom: 12px;
}

.empty-books p {
    color: var(--muted);
    font-size: 13px;
}


/* =========================
   INSTAGRAM BANNER
========================= */

.recommendation-section {
    padding: 35px 0 70px;
}

.recommendation {
    position: relative;
    min-height: 390px;
    padding: 65px 50px;
    border-radius: 12px;
    overflow: hidden;
    background: #eae6da;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.recommendation::before {
    content: "S";
    position: absolute;
    left: -35px;
    bottom: -180px;
    font: normal 440px Georgia, serif;
    color: rgba(66,85,58,.055);
}

.recommendation::after {
    content: "";
    position: absolute;
    width: 230px;
    height: 230px;
    border: 1px solid rgba(66,85,58,.15);
    border-radius: 50%;
    top: -100px;
    right: -55px;
}

.recommendation-text {
    position: relative;
    z-index: 2;
    max-width: 800px;
}

.recommendation-text small {
    color: var(--green);
    font-size: 10px;
    letter-spacing: 3px;
}

.recommendation-text h2 {
    margin-top: 20px;
    font: normal 43px/1.12 Georgia, serif;
}

.recommendation-text p {
    max-width: 650px;
    margin: 22px auto 0;
    color: #77776e;
    font-size: 14px;
    line-height: 1.9;
}

.recommendation-text .btn {
    margin-top: 28px;
}


/* =========================
   ABOUT
========================= */

.about-section {
    padding: 120px 0 135px;
    text-align: center;
}

.about-label {
    color: var(--green);
    font-size: 10px;
    letter-spacing: 3px;
}

.about-title {
    max-width: 850px;
    margin: 23px auto;
    font: normal 57px/1.12 Georgia, serif;
    letter-spacing: -1.8px;
}

.about-title em {
    color: var(--green);
}

.about-description {
    max-width: 600px;
    margin: auto;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.9;
}

.about-section .btn {
    margin-top: 30px;
}


/* =========================
   FOOTER
========================= */

footer {
    background: var(--dark);
    color: white;
    padding: 60px 7%;
}

.footer-inner {
    max-width: 1250px;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 25px;
}

.footer-logo {
    font: normal 33px Georgia, serif;
}

.footer-tagline {
    margin-top: 9px;
    color: #b7b8ad;
    font-size: 12px;
}

.footer-right {
    color: #b7b8ad;
    font-size: 11px;
    line-height: 2;
    text-align: right;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width: 1050px) {

    .menu {
        gap: 14px;
    }

    .hero h1 {
        font-size: 57px;
    }

    .hero-card {
        width: 245px;
        height: 345px;
    }

    .hero-card-back {
        width: 210px;
        height: 300px;
    }

    .book-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width: 800px) {

    nav {
        width: 92%;
        min-height: 72px;
    }

    .menu {
        gap: 12px;
    }

    .menu > a:nth-child(2),
    .menu > a:nth-child(3),
    .menu > a:nth-child(4) {
        display: none;
    }

    .nav-search {
        display: none;
    }

    .hero {
        grid-template-columns: 1fr;
        padding: 65px 0;
        gap: 35px;
    }

    .hero h1 {
        font-size: 55px;
    }

    .hero-visual {
        height: 420px;
    }

    .hero-card {
        right: 10%;
    }

    .hero-card-back {
        left: 15%;
    }

    .book-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .section {
        padding: 70px 0;
    }

    .about-title {
        font-size: 45px;
    }
}

@media(max-width: 520px) {

    .container {
        width: 92%;
    }

    .hero h1 {
        font-size: 43px;
    }

    .hero-description {
        font-size: 13px;
    }

    .hero-visual {
        height: 340px;
    }

    .hero-circle {
        width: 280px;
        height: 280px;
    }

    .hero-card {
        width: 195px;
        height: 275px;
        right: 5%;
    }

    .hero-card-back {
        width: 165px;
        height: 235px;
        left: 5%;
    }

    .hero-note {
        font-size: 12px;
    }

    .hero-actions {
        flex-wrap: wrap;
    }

    .section-title {
        font-size: 37px;
    }

    .section-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 18px;
    }

    .book-grid {
        grid-template-columns: 1fr;
    }

    .book-cover {
        height: 430px;
    }

    .search-form button {
        padding: 0 17px;
    }

    .recommendation {
        min-height: 350px;
        padding: 45px 22px;
    }

    .recommendation-text h2 {
        font-size: 35px;
    }

    .recommendation-text p {
        font-size: 12px;
    }

    .about-section {
        padding: 90px 0;
    }

    .about-title {
        font-size: 39px;
    }

    .footer-inner {
        flex-direction: column;
        text-align: center;
    }

    .footer-right {
        text-align: center;
    }
}

</style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<header>

<nav>

<a href="index.php" class="logo">
    <img src="L2.png.png" alt="Saviesta Bookstore">
</a>

<div class="menu">

    <a href="index.php">Home</a>
    <a href="buku.php">Koleksi</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>

    <form action="index.php" method="GET" class="nav-search">

        <input
            type="search"
            name="keyword"
            placeholder="Cari buku..."
            value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
        >

        <button type="submit">&#8981;</button>

    </form>

    <a href="keranjang.php">
        🛒 <?= jumlahCart() ?>
    </a>

    <?php if (isset($_SESSION['user'])): ?>

        <span class="welcome">
            Hi, <?= aman($_SESSION['user']['nama']) ?>
        </span>

        <a href="logout.php">Logout</a>

    <?php else: ?>

        <a href="login.php">Login</a>

        <a href="register.php" class="register">
            Register
        </a>

    <?php endif; ?>

</div>

</nav>

</header>


<main>

<div class="container">


<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div class="hero-content">

        <span class="hero-label">
            TOKO BUKU INDEPENDEN
        </span>

        <h1>
            Cerita baik menemukan
            <em>pembacanya.</em>
        </h1>

        <p class="hero-description">
            Kurasi buku fiksi, non-fiksi, dan puisi pilihan
            untuk menemani setiap perjalanan membaca kamu.
            Temukan cerita baru yang mungkin akan menjadi
            bagian favorit dalam hidupmu.
        </p>

        <div class="hero-actions">

            <a href="buku.php" class="btn">
                Jelajahi Koleksi →
            </a>

            <a href="#popular" class="hero-secondary">
                Lihat buku populer
            </a>

        </div>

    </div>


    <div class="hero-visual">

        <div class="hero-circle"></div>

        <?php if ($heroBookBack): ?>

            <?php $gambarBack = coverSaviesta($heroBookBack['cover']); ?>

            <a
                href="detail.php?id=<?= (int)$heroBookBack['id'] ?>"
                class="hero-card-back"
            >

                <?php if ($gambarBack !== ''): ?>
                    <img
                        src="<?= htmlspecialchars($gambarBack, ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= aman($heroBookBack['judul']) ?>"
                    >
                <?php endif; ?>

            </a>

        <?php endif; ?>


        <?php if ($heroBook): ?>

            <?php $gambarHero = coverSaviesta($heroBook['cover']); ?>

            <a
                href="detail.php?id=<?= (int)$heroBook['id'] ?>"
                class="hero-card"
            >

                <?php if ($gambarHero !== ''): ?>
                    <img
                        src="<?= htmlspecialchars($gambarHero, ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= aman($heroBook['judul']) ?>"
                    >
                <?php endif; ?>

            </a>

        <?php else: ?>

            <div class="hero-card"
                 style="display:flex;align-items:center;justify-content:center;color:white;padding:25px;text-align:center;">
                Belum ada buku di koleksi.
            </div>

        <?php endif; ?>


        <div class="hero-note">
            Your next favorite story.
        </div>

    </div>

</section>


<!-- =========================
     POPULAR BOOKS
========================= -->

<section class="section" id="popular">

    <div class="section-header">

        <div>

            <div class="section-label">
                DISCOVER YOUR NEXT READ
            </div>

            <h2 class="section-title">
                Sedang Populer
            </h2>

        </div>

        <a href="buku.php" class="section-link">
            Lihat Semua Koleksi →
        </a>

    </div>


    <div class="search-wrapper">

        <form action="index.php" method="GET" class="search-form">

            <input
                type="search"
                name="keyword"
                placeholder="Cari judul buku, penulis, atau kategori..."
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit">
                Cari
            </button>

        </form>

        <?php if ($keyword !== ''): ?>

            <div class="search-info">

                Hasil pencarian untuk:

                <strong>
                    <?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>
                </strong>

                &nbsp;·&nbsp;

                <a href="index.php">
                    Reset pencarian
                </a>

            </div>

        <?php endif; ?>

    </div>


    <div class="book-grid">

        <?php

        $jumlahBuku = 0;

        if ($data):

            while ($b = mysqli_fetch_assoc($data)):

                $jumlahBuku++;

                $gambarBuku = coverSaviesta($b['cover'] ?? '');

        ?>

            <div class="book-card">

                <a href="detail.php?id=<?= (int)$b['id'] ?>">

                    <div class="book-cover">

                        <?php if ($gambarBuku !== ''): ?>

                            <img
                                src="<?= htmlspecialchars($gambarBuku, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= aman($b['judul']) ?>"
                            >

                        <?php endif; ?>

                        <div class="cover-content">

                            <div class="cover-category">
                                <?= aman($b['kategori'] ?? '') ?>
                            </div>

                            <div class="cover-title">
                                <?= aman($b['judul']) ?>
                            </div>

                        </div>

                    </div>

                </a>


                <div class="book-info">

                    <div class="book-category">
                        <?= aman($b['kategori'] ?? '') ?>
                    </div>

                    <div class="book-title">
                        <?= aman($b['judul']) ?>
                    </div>

                    <div class="book-author">
                        <?= aman($b['penulis']) ?>
                    </div>


                    <div class="book-bottom">

                        <div class="price">
                            <?= rupiah($b['harga']) ?>
                        </div>


                        <?php if ((int)$b['stok'] > 0): ?>

                            <form method="POST" action="keranjang.php">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$b['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="tambah"
                                    class="plus"
                                    aria-label="Tambah ke keranjang"
                                >
                                    +
                                </button>

                            </form>

                        <?php else: ?>

                            <button
                                type="button"
                                class="plus"
                                disabled
                            >
                                +
                            </button>

                        <?php endif; ?>

                    </div>


                    <div class="stock">

                        <?= (int)$b['stok'] > 0
                            ? "● Stok tersedia"
                            : "● Stok habis"
                        ?>

                    </div>

                </div>

            </div>

        <?php

            endwhile;

        endif;

        ?>


        <?php if ($jumlahBuku === 0): ?>

            <div class="empty-books">

                <h3>
                    Buku tidak ditemukan.
                </h3>

                <p>
                    Coba gunakan kata kunci lain untuk menemukan buku favoritmu.
                </p>

                <a href="index.php" class="btn">
                    Lihat Semua Buku
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =========================
     INSTAGRAM
========================= -->

<section class="recommendation-section">

    <div class="recommendation">

        <div class="recommendation-text">

            <small>
                FOLLOW OUR INSTAGRAM
            </small>

            <h2>
                Let's Connect<br>
                On Instagram.
            </h2>

            <p>
                Ikuti Instagram Saviesta untuk mendapatkan
                informasi buku terbaru, rekomendasi bacaan,
                koleksi favorit, dan promo menarik.
                Jadilah bagian dari perjalanan membaca bersama Saviesta.
            </p>

            <a
                href="https://www.instagram.com/saviesta_official/"
                target="_blank"
                rel="noopener noreferrer"
                class="btn"
            >
                Visit Instagram ↗
            </a>

        </div>

    </div>

</section>


<!-- =========================
     ABOUT
========================= -->

<section class="about-section">

    <div class="about-label">
        ABOUT SAVIESTA
    </div>

    <h2 class="about-title">
        Tempat untuk menemukan
        <em>cerita yang tepat.</em>
    </h2>

    <p class="about-description">
        Saviesta menghadirkan pilihan buku untuk menemani
        waktu santai, perjalanan, dan berbagai cerita
        dalam kehidupan kamu. Karena setiap buku memiliki
        cerita, dan setiap pembaca memiliki perjalanan.
    </p>

    <a href="about.php" class="btn">
        Tentang Saviesta →
    </a>

</section>


</div>

</main>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div class="footer-inner">

        <div>

            <div class="footer-logo">
                Saviesta Family
            </div>

            <div class="footer-tagline">
                
            </div>

        </div>

        <div class="footer-right">

           Find your next favorite story.
            <br>

            © 2026 Saviesta Bookstore

        </div>

    </div>

</footer>


</body>
</html>