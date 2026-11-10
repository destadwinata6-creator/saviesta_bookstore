<?php
session_start();

// Proses logout jika tombol diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logout Admin — Saviesta</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f5ee;
            font-family: Arial, sans-serif;
            color: #242820;
            padding: 20px;
        }

        .logout-card {
            width: 100%;
            max-width: 450px;
            padding: 45px 35px;
            text-align: center;
            background: #fffdf9;
            border: 1px solid #ded9cf;
            border-radius: 12px;
            box-shadow: 0 15px 45px rgba(36, 40, 32, 0.08);
        }

        .logo {
            font-family: Georgia, serif;
            font-size: 29px;
            font-weight: bold;
            color: #34452f;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #888579;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 35px;
        }

        .icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: #edf0e8;
            color: #42553a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
        }

        h1 {
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: normal;
            margin-bottom: 14px;
        }

        .description {
            color: #77766e;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-logout {
            background: #34452f;
            color: white;
            border: 1px solid #34452f;
        }

        .btn-logout:hover {
            background: #263523;
        }

        .btn-cancel {
            background: transparent;
            color: #34452f;
            border: 1px solid #d2d0c5;
        }

        .btn-cancel:hover {
            background: #f0eee6;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e8e3d9;
            font-size: 11px;
            color: #99958a;
        }

        @media (max-width: 480px) {
            .logout-card {
                padding: 35px 22px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

    <div class="logout-card">

        <div class="logo">Saviesta</div>

        <div class="subtitle">Bookstore Admin</div>

        <div class="icon">↗</div>

        <h1>Keluar dari Admin?</h1>

        <p class="description">
            Apakah kamu yakin ingin mengakhiri sesi admin?
            Pastikan pekerjaanmu sudah selesai sebelum keluar.
        </p>

        <div class="buttons">

            <form method="POST" action="logout.php">
                <button type="submit" class="btn btn-logout">
                    Ya, Logout
                </button>
            </form>

            <a href="dashboard.php" class="btn btn-cancel">
                Batal, Kembali ke Dashboard
            </a>

        </div>

        <div class="footer">
            &copy; <?= date('Y') ?> Saviesta Bookstore
            <br><br>
            A peaceful place for every story.
        </div>

    </div>

</body>
</html>