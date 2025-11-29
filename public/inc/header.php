<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - CedisPay Savings & Credit Co-operative</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=edit_document" />

    <style>
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: #003366;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 22px;
            font-weight: bold;
            color: #fff;
        }
        .logo-img {
            height: 55px;
            width: auto;
            object-fit: contain;
        }
        nav { display: flex; }
        nav.active { display: block; }
    </style>
</head>

<body>

<header>
    <div class="logo">
        <img src="../../assets/logo.png" alt="CedisPay Logo" class="logo-img">
    </div>

    <i class="fa-solid fa-bars fa-xl"></i>

    <nav>
        <ul>
            <li><a href="./index.php" class="active">Home</a></li>
            <li><a href="./membership-apply.php">Apply</a></li>
            <li><a href="./faqs.html">FAQs</a></li>
            <li><a href="./about.html">About</a></li>
        </ul>
    </nav>

    <div class="phone">
        <a href="tel:+26824042876"><i class="fa-solid fa-phone"></i> Call Us Now: +268 2404 2876</a>
    </div>

    <button class="login-btn"><a href="../../member/login.php">Login</a></button>
</header>
