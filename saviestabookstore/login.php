<?php
include "functions.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $emailDB = mysqli_real_escape_string($koneksi, $email);

    /*
    ========================================
    CEK LOGIN ADMIN
    ========================================
    */

    $queryAdmin = mysqli_query(
        $koneksi,
        "SELECT * FROM admin
         WHERE email = '$emailDB'
         LIMIT 1"
    );

    if (!$queryAdmin) {
        die("ERROR DATABASE ADMIN: " . mysqli_error($koneksi));
    }

    if (mysqli_num_rows($queryAdmin) > 0) {
        $admin = mysqli_fetch_assoc($queryAdmin);
        $passwordDB = $admin['password'];

        // Mendukung password biasa maupun password hash
        $passwordCocok = password_verify($password, $passwordDB)
            || hash_equals((string)$passwordDB, (string)$password);

        if ($passwordCocok) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_email'] = $admin['email'];

            header("Location: admin/dashboard.php");
            exit;
        } else {
            $error = "Password admin yang kamu masukkan salah.";
        }
    } else {
        /*
        ========================================
        CEK LOGIN PEMBELI
        ========================================
        */

        $queryUser = mysqli_query(
            $koneksi,
            "SELECT * FROM user
             WHERE email = '$emailDB'
             LIMIT 1"
        );

        if (!$queryUser) {
            die("ERROR DATABASE USER: " . mysqli_error($koneksi));
        }

        if (mysqli_num_rows($queryUser) > 0) {
            $user = mysqli_fetch_assoc($queryUser);

            $passwordDB = $user['password'];

            $passwordCocok = password_verify($password, $passwordDB)
                || hash_equals((string)$passwordDB, (string)$password);

            if ($passwordCocok) {
                session_regenerate_id(true);

                $_SESSION['user'] = $user;

                header("Location: index.php");
                exit;
            } else {
                $error = "Password yang kamu masukkan salah.";
            }
        } else {
            $error = "Email belum terdaftar.";
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

<title>Login — Saviesta</title>


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


/* REGISTER */

.menu .register {

    background: #252a21;

    color: white;

    padding: 10px 18px;

    border-radius: 25px;

}


/* =====================================================
   LOGIN PAGE
===================================================== */

.login-page {

    min-height: calc(100vh - 78px);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 80px 20px;

}

.login-container {

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

.login-intro {

    padding: 15px;

}

.login-label {

    display: inline-block;

    font-size: 10px;

    letter-spacing: 2.5px;

    color: #777;

    margin-bottom: 20px;

}

.login-intro h1 {

    margin: 0;

    max-width: 500px;

    font: normal 64px/1.02 Georgia, serif;

    letter-spacing: -2px;

}

.login-intro h1 em {

    color: #42553a;

    font-style: italic;

}

.login-intro p {

    max-width: 420px;

    margin-top: 25px;

    color: #777;

    font-size: 14px;

    line-height: 1.9;

}

.login-line {

    width: 70px;

    height: 3px;

    margin-top: 30px;

    background: #c7a46b;

}


/* =====================================================
   DECORATION
===================================================== */

.login-decoration {

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
   LOGIN CARD
===================================================== */

.login-card {

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

    margin-bottom: 20px;

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
   ERROR
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


/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn {

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

.login-btn:hover {

    transform: translateY(-2px);

    opacity: .92;

}


/* =====================================================
   REGISTER LINK
===================================================== */

.register-text {

    text-align: center;

    margin-top: 24px;

    color: #777;

    font-size: 12px;

}

.register-text a {

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

    .login-container {

        gap: 40px;

    }

    .login-intro h1 {

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

    .login-page {

        padding: 60px 20px;

    }

    .login-container {

        grid-template-columns: 1fr;

        gap: 35px;

    }

    .login-intro {

        text-align: center;

        padding: 0;

    }

    .login-intro p {

        margin-left: auto;

        margin-right: auto;

    }

    .login-line {

        margin-left: auto;

        margin-right: auto;

    }

    .login-decoration {

        justify-content: center;

    }

}

@media(max-width: 500px) {

    .menu a:nth-child(5) {

        display: none;

    }

    .login-card {

        padding: 30px 22px;

    }

    .login-intro h1 {

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
     LOGIN
===================================================== -->

<main class="login-page">


<div class="login-container">


<!-- LEFT -->

<div class="login-intro">


<div class="login-label">

SAVIESTA BOOKSTORE

</div>


<h1>

Welcome <em>back.</em>

</h1>


<p>

Lanjutkan perjalanan membaca kamu
dan temukan cerita berikutnya yang
mungkin akan jadi favorit baru kamu.

</p>


<div class="login-line">
</div>


<div class="login-decoration">

<div class="decor-one">
</div>

<div class="decor-two">
</div>

</div>


</div>



<!-- RIGHT -->

<div class="login-card">


<div class="card-top">


<div class="card-label">

MEMBER LOGIN

</div>


<div class="card-number">

01

</div>


</div>


<h2 class="card-title">

Sign in to Saviesta

</h2>


<?php if ($error != ""): ?>

<div class="alert error">

<?= aman($error) ?>

</div>

<?php endif; ?>


<form method="POST">


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
placeholder="Masukkan password"
required
>


</div>



<button
type="submit"
class="login-btn"
>

Login to Account →

</button>


</form>


<div class="register-text">

Belum punya akun?

<a href="register.php">

Create an account

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