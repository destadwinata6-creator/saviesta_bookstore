
<?php

include "functions.php";

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>About — Saviesta</title>

<style>

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    background: #faf7ef;
    color: #252a21;
    font-family: Arial, sans-serif;
}

a {
    color: inherit;
    text-decoration: none;
}

/* HEADER */

header {
    position: sticky;
    top: 0;
    z-index: 100;
    height: 78px;
    padding: 0 7%;
    display: flex;
    align-items: center;
    background: rgba(250,247,239,.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid #ded9cf;
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
    font-size: 13px;
    color: #252a21;
    transition: .2s;
}

.menu a:hover {
    opacity: .55;
}

.welcome {
    font-size: 13px;
}

.register {
    background: #252a21;
    color: white !important;
    padding: 10px 18px;
    border-radius: 25px;
}

/* CONTAINER */

.container {
    width: 88%;
    max-width: 1250px;
    margin: auto;
}

/* ABOUT HERO */

.about-hero {
    min-height: 580px;
    display: grid;
    grid-template-columns: 1.1fr .9fr;
    align-items: center;
    gap: 60px;
    padding: 70px 0;
}

.about-label {
    font-size: 10px;
    color: #777;
    letter-spacing: 2.5px;
    margin-bottom: 22px;
}

.about-hero h1 {
    margin: 0;
    font: normal 67px/1.04 Georgia, serif;
    letter-spacing: -2px;
}

.about-hero h1 em {
    color: #42553a;
    font-style: italic;
}

.about-hero p {
    max-width: 520px;
    margin-top: 28px;
    color: #777;
    font-size: 14px;
    line-height: 1.9;
}

/* HERO IMAGE */

.about-visual {
    display: flex;
    justify-content: center;
    align-items: center;
}

.about-visual img {
    width: 100%;
    max-width: 320px;
    height: 340px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,.12);
    transition: .4s;
}

.about-visual img:hover {
    transform: translateY(-5px);
}

/* STORY */

.story {
    padding: 110px 0;
    border-top: 1px solid #ded9cf;
}

.story-grid {
    display: grid;
    grid-template-columns: .75fr 1.25fr;
    gap: 90px;
}

.story-label {
    font-size: 10px;
    color: #777;
    letter-spacing: 2px;
}

.story-title {
    margin-top: 15px;
    font: normal 45px/1.1 Georgia, serif;
}

.story-text {
    color: #777;
    font-size: 14px;
    line-height: 2;
}

.story-text p {
    margin-top: 0;
    margin-bottom: 25px;
}

/* NUMBERS & SYMBOLS */

.numbers {
    padding: 20px 0 120px;
}

.number-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.number-card {
    background: #e9e4d8;
    padding: 40px;
    border-radius: 8px;
    text-align: center;
    transition: .3s;
}

.number-card:hover {
    transform: translateY(-5px);
}

.number-icon {
    font-size: 35px;
    color: #42553a;
    margin-bottom: 18px;
}

.number-card h3 {
    margin: 0;
    color: #42553a;
    font: normal 38px Georgia, serif;
}

.number-card p {
    margin: 12px 0 0;
    color: #777;
    font-size: 10px;
    letter-spacing: 2px;
}

/* VALUES */

.values {
    padding: 110px 0;
    background: #e9e4d8;
}

.values-header {
    max-width: 600px;
    margin-bottom: 50px;
}

.values-header small {
    color: #777;
    font-size: 10px;
    letter-spacing: 2px;
}

.values-header h2 {
    margin: 14px 0;
    font: normal 45px Georgia, serif;
}

.values-header p {
    color: #777;
    font-size: 14px;
    line-height: 1.8;
}

.value-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
}

.value {
    background: #faf7ef;
    padding: 35px;
    border-radius: 7px;
}

.value-number {
    color: #42553a;
    font: normal 25px Georgia, serif;
}

.value h3 {
    margin: 18px 0 10px;
    font: normal 24px Georgia, serif;
}

.value p {
    color: #777;
    font-size: 13px;
    line-height: 1.8;
}

/* FOOTER */

footer {
    background: #252a21;
    color: white;
    padding: 65px 7%;
}

.footer-inner {
    max-width: 1250px;
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
}

/* RESPONSIVE */

@media(max-width: 850px) {

    .about-hero {
        grid-template-columns: 1fr;
        gap: 35px;
    }

    .about-visual img {
        max-width: 320px;
        height: 330px;
    }

    .story-grid {
        grid-template-columns: 1fr;
        gap: 35px;
    }

    .number-grid,
    .value-grid {
        grid-template-columns: 1fr;
    }

}

@media(max-width: 600px) {

    .container {
        width: 92%;
    }

    header {
        padding: 0 4%;
    }

    .menu {
        gap: 10px;
    }

    .menu a:nth-child(2),
    .menu a:nth-child(3),
    .menu a:nth-child(4) {
        display: none;
    }

    .about-hero h1 {
        font-size: 45px;
    }

    .about-visual img {
        max-width: 270px;
        height: 290px;
    }

    .story-title,
    .values-header h2 {
        font-size: 37px;
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

<!-- HEADER -->

<header>

<nav>

<a href="index.php" class="logo">

<img src="L2.png.png" alt="Saviesta BookStore">

</a>

<div class="menu">

<a href="index.php">Home</a>

<a href="buku.php">Koleksi</a>

<a href="about.php">About</a>

<a href="contact.php">Contact</a>

<a href="keranjang.php">
🛒 <?= jumlahCart() ?>
</a>

<?php if(isset($_SESSION['user'])): ?>

<span class="welcome">
Hi, <?= aman($_SESSION['user']['nama']) ?>
</span>

<a href="logout.php">Logout</a>

<?php else: ?>

<a href="login.php">Login</a>

<a href="register.php" class="register">Register</a>

<?php endif; ?>

</div>

</nav>

</header>

<main>

<div class="container">

<!-- ABOUT HERO -->

<section class="about-hero">

<div>

<div class="about-label">
OUR STORY
</div>

<h1>
A bookstore made
for <em>good stories.</em>
</h1>

<p>
Saviesta hadir sebagai tempat untuk
menemukan buku-buku pilihan yang bisa
menemani hari, perjalanan, dan berbagai
momen dalam kehidupan.
</p>

</div>

<!-- GAMBAR ABOUT -->

<div class="about-visual">

<img src="Sav.jpeg" alt="Saviesta Bookstore">

</div>

</section>

<!-- STORY -->

<section class="story">

<div class="story-grid">

<div>

<div class="story-label">
WHO WE ARE
</div>

<h2 class="story-title">
Tentang Saviesta.
</h2>

</div>

<div class="story-text">

<p>
Saviesta adalah toko buku independen
yang dibuat untuk menghadirkan pengalaman
membaca yang sederhana dan menyenangkan.
</p>

<p>
Kami percaya bahwa menemukan buku yang
tepat bisa menjadi awal dari sebuah perjalanan
baru. Karena itu, setiap koleksi dipilih untuk
memberikan pilihan bacaan yang beragam.
</p>

<p>
Mulai dari cerita fiksi, non-fiksi, hingga
puisi, Saviesta ingin menjadi tempat
di mana pembaca bisa menemukan
cerita favorit berikutnya.
</p>

</div>

</div>

</section>

<!-- NUMBERS -->

<section class="numbers">

<div class="number-grid">

<div class="number-card">

<div class="number-icon">★</div>

<h3>4.9/5</h3>

<p>RATING</p>

</div>

<div class="number-card">

<div class="number-icon">♡</div>

<h3>♥</h3>

<p>LOVE</p>

</div>

<div class="number-card">

<div class="number-icon">✦</div>

<h3>∞</h3>

<p>STORIES</p>

</div>

</div>

</section>

</div>

<!-- VALUES -->

<section class="values">

<div class="container">

<div class="values-header">

<small>
WHAT WE BELIEVE
</small>

<h2>
Simple things matter.
</h2>

<p>
Kami ingin membuat pengalaman mencari
dan menemukan buku terasa lebih personal.
</p>

</div>

<div class="value-grid">

<div class="value">

<div class="value-number">01</div>

<h3>Curated</h3>

<p>
Koleksi dipilih dengan mempertimbangkan
cerita dan pengalaman yang bisa diberikan
kepada pembaca.
</p>

</div>

<div class="value">

<div class="value-number">02</div>

<h3>Personal</h3>

<p>
Setiap pembaca punya selera berbeda.
Kami ingin membantu kamu menemukan
buku yang terasa cocok.
</p>

</div>

<div class="value">

<div class="value-number">03</div>

<h3>Timeless</h3>

<p>
Cerita yang bagus nggak punya batas waktu.
Buku yang tepat akan selalu layak dibaca.
</p>

</div>

</div>

</div>

</section>

</main>

<!-- FOOTER -->

<footer>

<div class="footer-inner">

<div class="footer-logo">
Saviesta Family
</div>

<div class="footer-text">

Find your next favorite story.

<br><br>

© 2026 Saviesta Bookstore

</div>

</div>

</footer>

</body>

</html>