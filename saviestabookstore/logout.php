<?php

include "functions.php";

if (isset($_POST['logout'])) {

    session_unset();

    session_destroy();

    header("Location: index.php");

    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Logout — Saviesta</title>

<style>

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {

    margin: 0;

    min-height: 100vh;

    background: #faf7ef;

    color: #252a21;

    font-family: Arial, sans-serif;

}


/* =====================================================
   HEADER
===================================================== */

header {

    height: 78px;

    padding: 0 7%;

    display: flex;

    align-items: center;

    background: rgba(250,247,239,.95);

    border-bottom: 1px solid #ded9cf;

}

nav {

    width: 100%;

    display: flex;

    align-items: center;

    justify-content: space-between;

}

.logo {

    display: flex;

    align-items: center;

    text-decoration: none;

}

.logo img {

    width: 50px;

    display: block;

}


/* =====================================================
   LOGOUT PAGE
===================================================== */

.logout-page {

    min-height: calc(100vh - 78px);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 70px 20px;

}

.logout-wrapper {

    width: 100%;

    max-width: 720px;

    text-align: center;

}


/* LABEL */

.logout-label {

    font-size: 10px;

    letter-spacing: 2.5px;

    color: #777;

    margin-bottom: 20px;

}


/* TITLE */

.logout-wrapper h1 {

    margin: 0;

    font: normal 60px/1.05 Georgia, serif;

    letter-spacing: -2px;

}

.logout-wrapper h1 em {

    color: #42553a;

    font-style: italic;

}


/* DESCRIPTION */

.logout-description {

    max-width: 500px;

    margin: 25px auto 40px;

    color: #777;

    font-size: 14px;

    line-height: 1.9;

}


/* =====================================================
   CARD
===================================================== */

.logout-card {

    background: #e9e4d8;

    border-radius: 10px;

    padding: 45px;

    max-width: 500px;

    margin: auto;

}


/* ICON */

.logout-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 25px;

    border-radius: 50%;

    background: #42553a;

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font: normal 27px Georgia, serif;

}


/* CARD TEXT */

.logout-card h2 {

    margin: 0;

    font: normal 30px Georgia, serif;

}

.logout-card p {

    color: #777;

    font-size: 13px;

    line-height: 1.7;

    margin: 12px auto 25px;

}


/* BUTTONS */

.buttons {

    display: flex;

    justify-content: center;

    gap: 12px;

}

.btn {

    display: inline-block;

    padding: 13px 22px;

    border-radius: 30px;

    text-decoration: none;

    font-size: 13px;

    cursor: pointer;

    transition: .25s;

}

.btn:hover {

    transform: translateY(-2px);

}


/* CANCEL */

.btn-cancel {

    border: 1px solid #252a21;

    color: #252a21;

    background: transparent;

}


/* LOGOUT */

.btn-logout {

    border: none;

    background: #252a21;

    color: white;

}


/* =====================================================
   FOOTER
===================================================== */

footer {

    background: #252a21;

    color: white;

    padding: 30px 7%;

}

.footer-inner {

    max-width: 1250px;

    margin: auto;

    display: flex;

    justify-content: space-between;

    align-items: center;

}

.footer-logo {

    font: normal 25px Georgia, serif;

}

.footer-text {

    color: #aaa;

    font-size: 11px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width: 600px) {

    .logout-wrapper h1 {

        font-size: 45px;

    }

    .logout-card {

        padding: 30px 20px;

    }

    .buttons {

        flex-direction: column;

    }

    .btn {

        width: 100%;

    }

    .footer-inner {

        display: block;

        text-align: center;

    }

    .footer-text {

        margin-top: 12px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header>

<nav>


<a
href="index.php"
class="logo"
>

<img
src="L2.png.png"
alt="Saviesta BookStore"
>

</a>


</nav>

</header>



<!-- =====================================================
     LOGOUT
===================================================== -->

<main class="logout-page">


<div class="logout-wrapper">


<div class="logout-label">

SAVIESTA BOOKSTORE

</div>


<h1>

Leaving so <em>soon?</em>

</h1>


<p class="logout-description">

Kamu akan keluar dari akun Saviesta.
Tenang, koleksi dan keranjang kamu
tetap menunggu saat kamu kembali.

</p>



<div class="logout-card">


<div class="logout-icon">

↗

</div>


<h2>

Yakin mau keluar?

</h2>


<p>

Sesi akun kamu akan diakhiri dan
kamu akan kembali ke halaman utama.

</p>


<div class="buttons">


<a
href="index.php"
class="btn btn-cancel"
>

Batal

</a>


<form method="POST" style="margin:0;">

<button
type="submit"
name="logout"
class="btn btn-logout"
>

Ya, Keluar

</button>

</form>


</div>


</div>


</div>


</main>



<!-- =====================================================
     FOOTER
===================================================== -->

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