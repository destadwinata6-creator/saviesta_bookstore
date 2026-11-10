<?php

include "functions.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password != $konfirmasi) {

        $error = "Konfirmasi password tidak sama.";

    } else {

        $email_aman = mysqli_real_escape_string($koneksi, $email);
        $nama_aman = mysqli_real_escape_string($koneksi, $nama);

        $cek = mysqli_query(
            $koneksi,
            "SELECT * FROM user
             WHERE email = '$email_aman'
             LIMIT 1"
        );

        if (!$cek) {

            die("ERROR DATABASE: " . mysqli_error($koneksi));

        }

        if (mysqli_num_rows($cek) > 0) {

            $error = "Email sudah terdaftar.";

        } else {

            $password_aman = mysqli_real_escape_string(
                $koneksi,
                $password
            );

            $insert = mysqli_query(
                $koneksi,
                "INSERT INTO user
                (nama, email, password)
                VALUES
                ('$nama_aman', '$email_aman', '$password_aman')"
            );

            if ($insert) {

                $success = "Akun berhasil dibuat. Silakan login.";

            } else {

                $error = "Akun gagal dibuat.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>Register — Saviesta</title>


<style>

/* =====================================================
   RESET
===================================================== */

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

    align-items: center;

    justify-content: space-between;

}


/* LOGO */

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


/* MENU */

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

.menu .register {

    background: #252a21;

    color: white;

    padding: 10px 18px;

    border-radius: 25px;

}


/* =====================================================
   REGISTER PAGE
===================================================== */

.register-page {

    min-height: calc(100vh - 78px);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 80px 20px;

}

.register-container {

    width: 100%;

    max-width: 1050px;

    display: grid;

    grid-template-columns: 1fr 1.05fr;

    gap: 80px;

    align-items: center;

}


/* =====================================================
   LEFT SIDE
===================================================== */

.register-intro {

    padding: 15px;

}

.register-label {

    display: inline-block;

    font-size: 10px;

    letter-spacing: 2.5px;

    color: #777;

    margin-bottom: 20px;

}

.register-intro h1 {

    margin: 0;

    max-width: 500px;

    font: normal 64px/1.02 Georgia, serif;

    letter-spacing: -2px;

}

.register-intro h1 em {

    color: #42553a;

    font-style: italic;

}

.register-intro p {

    max-width: 420px;

    margin-top: 25px;

    color: #777;

    font-size: 14px;

    line-height: 1.9;

}

.register-line {

    width: 70px;

    height: 3px;

    margin-top: 30px;

    background: #c7a46b;

}


/* DECORATION */

.register-decoration {

    display: flex;

    gap: 10px;

    margin-top: 45px;

}

.decor-one {

    width: 70px;

    height: 5px;

    border-radius: 10px;

    background: #42553a;

}

.decor-two {

    width: 35px;

    height: 5px;

    border-radius: 10px;

    background: #c7a46b;

}


/* =====================================================
   REGISTER CARD
===================================================== */

.register-card {

    background: #e9e4d8;

    border-radius: 12px;

    padding: 48px;

    box-shadow:
        0 25px 60px rgba(37,42,33,.08);

}

.card-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 8px;

}

.card-label {

    font-size: 9px;

    letter-spacing: 2px;

    color: #777;

}

.card-number {

    font: normal 18px Georgia, serif;

    color: #c7a46b;

}

.card-title {

    margin: 0 0 30px;

    font: normal 34px Georgia, serif;

}


/* =====================================================
   FORM
===================================================== */

.form-group {

    margin-bottom: 18px;

}

label {

    display: block;

    margin-bottom: 8px;

    font-size: 9px;

    letter-spacing: 1.5px;

    color: #777;

}

input {

    width: 100%;

    padding: 15px 16px;

    border: 1px solid #d2ccc0;

    border-radius: 6px;

    outline: none;

    background: #faf7ef;

    color: #252a21;

    font-size: 13px;

    transition: .2s;

}

input:focus {

    border-color: #42553a;

    box-shadow:
        0 0 0 3px rgba(66,85,58,.08);

}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding: 13px 15px;

    border-radius: 6px;

    margin-bottom: 20px;

    font-size: 12px;

}

.error {

    background: #252a21;

    color: white;

}

.success {

    background: #42553a;

    color: white;

}


/* =====================================================
   REGISTER BUTTON
===================================================== */

.register-btn {

    width: 100%;

    border: none;

    padding: 15px;

    border-radius: 30px;

    background: #252a21;

    color: white;

    font-size: 13px;

    cursor: pointer;

    transition: .25s;

}

.register-btn:hover {

    transform: translateY(-2px);

    opacity: .92;

}


/* =====================================================
   LOGIN LINK
===================================================== */

.login-text {

    text-align: center;

    margin-top: 24px;

    color: #777;

    font-size: 12px;

}

.login-text a {

    color: #252a21;

    font-weight: bold;

    text-decoration: none;

    border-bottom: 1px solid #252a21;

}


/* =====================================================
   FOOTER
===================================================== */

footer {

    background: #252a21;

    color: white;

    padding: 35px 7%;

}

.footer-inner {

    max-width: 1050px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: space-between;

}

.footer-logo {

    font: normal 26px Georgia, serif;

}

.footer-text {

    color: #aaa;

    font-size: 11px;

    line-height: 1.5;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width: 900px) {

    .register-container {

        gap: 40px;

    }

    .register-intro h1 {

        font-size: 52px;

    }

}

@media(max-width: 750px) {

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

    .register-page {

        padding: 60px 20px;

    }

    .register-container {

        grid-template-columns: 1fr;

        gap: 35px;

    }

    .register-intro {

        text-align: center;

        padding: 0;

    }

    .register-intro p {

        margin-left: auto;

        margin-right: auto;

    }

    .register-line {

        margin-left: auto;

        margin-right: auto;

    }

    .register-decoration {

        justify-content: center;

    }

}

@media(max-width: 500px) {

    .menu a:nth-child(5) {

        display: none;

    }

    .register-card {

        padding: 30px 22px;

    }

    .register-intro h1 {

        font-size: 44px;

    }

    .footer-inner {

        display: block;

        text-align: center;

    }

    .footer-text {

        margin-top: 14px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
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


<a href="login.php">
Login
</a>


<a
href="register.php"
class="register"
>

Register

</a>


</div>

</nav>

</header>



<!-- =====================================================
     REGISTER
===================================================== -->

<main class="register-page">


<div class="register-container">


<!-- LEFT -->

<div class="register-intro">


<div class="register-label">

SAVIESTA BOOKSTORE

</div>


<h1>

Begin your <em>story.</em>

</h1>


<p>

Buat akun Saviesta dan jadikan
setiap halaman sebagai bagian dari
perjalanan membaca kamu.

</p>


<div class="register-line">
</div>


<div class="register-decoration">

<div class="decor-one">
</div>

<div class="decor-two">
</div>

</div>


</div>



<!-- RIGHT -->

<div class="register-card">


<div class="card-top">


<div class="card-label">

CREATE ACCOUNT

</div>


<div class="card-number">

02

</div>


</div>


<h2 class="card-title">

Join Saviesta

</h2>


<?php if ($error != ""): ?>

<div class="alert error">

<?= aman($error) ?>

</div>

<?php endif; ?>


<?php if ($success != ""): ?>

<div class="alert success">

<?= aman($success) ?>

</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">


<label>

NAMA LENGKAP

</label>


<input
type="text"
name="nama"
placeholder="Nama kamu"
required
>

</div>



<div class="form-group">


<label>

EMAIL

</label>


<input
type="email"
name="email"
placeholder="nama@email.com"
required
>

</div>



<div class="form-group">


<label>

PASSWORD

</label>


<input
type="password"
name="password"
placeholder="Buat password"
required
>

</div>



<div class="form-group">


<label>

KONFIRMASI PASSWORD

</label>


<input
type="password"
name="konfirmasi"
placeholder="Ulangi password"
required
>

</div>



<button
type="submit"
class="register-btn"
>

Create Account →

</button>


</form>


<div class="login-text">

Sudah punya akun?

<a href="login.php">

Login di sini

</a>

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
<br>

© 2026 Saviesta Bookstore

</div>


</div>


</footer>


</body>

</html>