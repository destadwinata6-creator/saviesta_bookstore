<?php
include "functions.php";

$pesan_terkirim = false;
$error_pesan = "";

/* KONEKSI DATABASE */
$db_contact = null;

foreach (['koneksi', 'conn', 'connect', 'db'] as $nama_variabel) {
    if (isset($$nama_variabel) && $$nama_variabel instanceof mysqli) {
        $db_contact = $$nama_variabel;
        break;
    }
}

/* PROSES KIRIM PESAN */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if ($nama === "" || $email === "" || $pesan === "") {
        $error_pesan = "Semua kolom wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_pesan = "Format email tidak valid.";

    } elseif (!$db_contact) {
        $error_pesan = "Koneksi database tidak ditemukan. Periksa functions.php.";

    } else {

        /* BUAT TABEL JIKA BELUM ADA */
        $buat_tabel = "CREATE TABLE IF NOT EXISTS pesan_contact (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            pesan TEXT NOT NULL,
            tanggal_kirim TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if (!$db_contact->query($buat_tabel)) {

            $error_pesan = "Tabel gagal dibuat: " . $db_contact->error;

        } else {

            /* SIMPAN PESAN */
            $stmt = $db_contact->prepare(
                "INSERT INTO pesan_contact (nama, email, pesan)
                 VALUES (?, ?, ?)"
            );

            if (!$stmt) {

                $error_pesan = "Query gagal: " . $db_contact->error;

            } else {

                $stmt->bind_param("sss", $nama, $email, $pesan);

                if ($stmt->execute()) {
                    $pesan_terkirim = true;
                    $_POST = [];
                } else {
                    $error_pesan = "Pesan gagal disimpan: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact — Saviesta</title>

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
}

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

.welcome {
    font-size: 13px;
}

.register {
    background: #252a21;
    color: white !important;
    padding: 10px 18px;
    border-radius: 25px;
}

.container {
    width: 88%;
    max-width: 1250px;
    margin: auto;
}

.contact-hero {
    padding: 100px 0 65px;
    display: grid;
    grid-template-columns: 1fr .7fr;
    gap: 60px;
    align-items: end;
}

.contact-label {
    font-size: 10px;
    color: #777;
    letter-spacing: 2.5px;
    margin-bottom: 20px;
}

.contact-hero h1 {
    margin: 0;
    font: normal 67px/1.03 Georgia, serif;
    letter-spacing: -2px;
}

.contact-hero h1 em {
    color: #42553a;
    font-style: italic;
}

.contact-hero-description {
    color: #777;
    font-size: 14px;
    line-height: 1.9;
    max-width: 430px;
    border-left: 2px solid #42553a;
    padding-left: 20px;
}

.contact-area {
    padding: 30px 0 100px;
}

.contact-grid {
    display: grid;
    grid-template-columns: .75fr 1.25fr;
    gap: 25px;
}

.contact-info {
    background: #42553a;
    color: white;
    border-radius: 10px;
    padding: 42px;
    min-height: 520px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}

.contact-info::before {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50%;
    right: -70px;
    top: -60px;
}

.contact-info::after {
    content: "";
    position: absolute;
    width: 100px;
    height: 100px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 50%;
    right: 20px;
    top: 20px;
}

.contact-info-content {
    position: relative;
    z-index: 2;
}

.contact-info small {
    font-size: 9px;
    letter-spacing: 2px;
    opacity: .65;
}

.contact-info h2 {
    margin: 15px 0 18px;
    font: normal 40px/1.08 Georgia, serif;
}

.contact-info-description {
    max-width: 330px;
    color: rgba(255,255,255,.7);
    font-size: 13px;
    line-height: 1.9;
}

.info-list {
    position: relative;
    z-index: 2;
}

.info-item {
    padding: 17px 0;
    border-top: 1px solid rgba(255,255,255,.17);
}

.info-item span {
    display: block;
    font-size: 9px;
    letter-spacing: 1.5px;
    opacity: .5;
    margin-bottom: 7px;
}

.info-item p {
    margin: 0;
    font-size: 13px;
}

.contact-form {
    background: #e9e4d8;
    border-radius: 10px;
    padding: 42px;
}

.form-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 25px;
    border-bottom: 1px solid #d2ccc0;
    margin-bottom: 30px;
}

.form-heading small {
    color: #777;
    font-size: 9px;
    letter-spacing: 2px;
}

.form-heading h2 {
    margin: 9px 0 0;
    font: normal 38px Georgia, serif;
}

.form-number {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #42553a;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font: normal 17px Georgia, serif;
}

.success {
    background: #42553a;
    color: white;
    padding: 14px 17px;
    border-radius: 5px;
    margin-bottom: 22px;
    font-size: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.field {
    margin-bottom: 21px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 9px;
    color: #777;
    letter-spacing: 1px;
}

input,
textarea {
    width: 100%;
    padding: 15px;
    border: 1px solid #d2ccc0;
    border-radius: 5px;
    background: #faf7ef;
    color: #252a21;
    outline: none;
    font-family: Arial, sans-serif;
    transition: .2s;
}

input::placeholder,
textarea::placeholder {
    color: #aaa;
}

input:focus,
textarea:focus {
    border-color: #42553a;
    box-shadow: 0 0 0 3px rgba(66,85,58,.08);
}

textarea {
    height: 155px;
    resize: vertical;
}

.submit-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 5px;
}

.form-note {
    font-size: 10px;
    color: #888;
}

button {
    border: none;
    padding: 14px 25px;
    border-radius: 30px;
    background: #252a21;
    color: white;
    cursor: pointer;
    font-size: 13px;
    transition: .25s;
}

button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0,0,0,.12);
}

.contact-details {
    padding: 20px 0 110px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.detail {
    padding: 25px;
    border-top: 1px solid #ded9cf;
}

.detail-number {
    color: #42553a;
    font: normal 22px Georgia, serif;
}

.detail h3 {
    margin: 15px 0 8px;
    font: normal 22px Georgia, serif;
}

.detail p {
    margin: 0;
    color: #777;
    font-size: 12px;
    line-height: 1.7;
}

.contact-quote {
    padding: 0 0 110px;
    text-align: center;
}

.contact-quote p {
    max-width: 700px;
    margin: auto;
    color: #42553a;
    font: italic normal 27px/1.5 Georgia, serif;
}

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

@media(max-width: 850px) {
    .contact-hero {
        grid-template-columns: 1fr;
        gap: 25px;
    }

    .contact-grid {
        grid-template-columns: 1fr;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 600px) {
    .container {
        width: 92%;
    }

    .menu {
        gap: 10px;
    }

    .menu a:nth-child(2),
    .menu a:nth-child(3),
    .menu a:nth-child(4) {
        display: none;
    }

    .contact-hero {
        padding-top: 70px;
    }

    .contact-hero h1 {
        font-size: 45px;
    }

    .contact-info,
    .contact-form {
        padding: 30px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .submit-row {
        align-items: flex-start;
        gap: 15px;
        flex-direction: column-reverse;
    }

    .contact-quote p {
        font-size: 22px;
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
<a href="keranjang.php">🛒 <?= jumlahCart() ?></a>

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

<section class="contact-hero">
<div>
<div class="contact-label">CONTACT SAVIESTA</div>
<h1>
Let's make
something <em>clear.</em>
</h1>
</div>

<p class="contact-hero-description">
Punya pertanyaan tentang buku,
pesanan, atau sekadar ingin menyapa?
Kirim pesan ke kami. Tim Saviesta
akan dengan senang hati membantu.
</p>
</section>

<section class="contact-area">
<div class="contact-grid">

<div class="contact-info">
<div class="contact-info-content">
<small>SAVIESTA BOOKSTORE</small>

<h2>
We'd love
to hear from you.
</h2>

<p class="contact-info-description">
Nggak perlu bingung mau mulai dari mana.
Cukup tulis pesan kamu dan ceritakan
apa yang ingin kamu tanyakan.
</p>
</div>

<div class="info-list">
<div class="info-item">
<span>EMAIL</span>
<p>@saviestafamily.com</p>
</div>

<div class="info-item">
<span>PHONE</span>
<p>+62 878 1104 3366</p>
</div>

<div class="info-item">
<span>LOCATION</span>
<p>Jakarta, Indonesia</p>
</div>
</div>
</div>

<div class="contact-form">

<div class="form-heading">
<div>
<small>SEND A MESSAGE</small>
<h2>Say hello.</h2>
</div>

<div class="form-number">01</div>
</div>

<?php if($pesan_terkirim): ?>
<div class="success">
Pesan kamu berhasil dikirim.
Terima kasih sudah menghubungi Saviesta!
</div>
<?php endif; ?>

<?php if($error_pesan): ?>
<div class="success" style="background:#9b3d32;">
<?= htmlspecialchars($error_pesan, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="form-row">

<div class="field">
<label>NAMA</label>
<input
type="text"
name="nama"
placeholder="Nama kamu"
value="<?= htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
required
>
</div>

<div class="field">
<label>EMAIL</label>
<input
type="email"
name="email"
placeholder="Email kamu"
value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
required
>
</div>

</div>

<div class="field">
<label>PESAN</label>
<textarea
name="pesan"
placeholder="Tulis pesan kamu di sini..."
required
><?= htmlspecialchars($_POST['pesan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
</div>

<div class="submit-row">
<div class="form-note">
Kami akan membaca pesanmu dengan senang hati.
</div>

<button type="submit">
Kirim Pesan →
</button>
</div>

</form>
</div>
</div>
</section>

<section class="contact-details">
<div class="details-grid">

<div class="detail">
<div class="detail-number">01</div>
<h3>Questions</h3>
<p>
Tanyakan apa saja tentang
koleksi dan buku yang tersedia.
</p>
</div>

<div class="detail">
<div class="detail-number">02</div>
<h3>Orders</h3>
<p>
Butuh bantuan mengenai pesanan
atau keranjang belanja kamu?
</p>
</div>

<div class="detail">
<div class="detail-number">03</div>
<h3>Feedback</h3>
<p>
Punya ide atau masukan untuk
Saviesta? Kami terbuka untuk itu.
</p>
</div>

</div>
</section>

<section class="contact-quote">
<p>
“Sometimes all it takes
is a simple hello.”
</p>
</section>

</div>
</main>

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