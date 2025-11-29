<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="logo">
        <img src="../assets/logo.png" alt="CedisPay Logo" class="logo-img">
    </div>

    <i class="fa-solid fa-bars fa-xl"></i>

    <nav>
        <ul>
            <li><a href="./index.html" class="active">Home</a></li>
            <li><a href="./membership-apply.php">Apply</a></li>
            <li><a href="./faqs.html">FAQs</a></li>
            <li><a href="./about.html">About</a></li>
        </ul>
    </nav>

    <div class="phone">
        <a href="tel:+26824042876"><i class="fa-solid fa-phone"></i> Call Us Now: +268 2404 2876</a>
    </div>

    <button class="login-btn"><a href="../member/login.php">Login</a></button>
</header>

<script>
    const menuIcon = document.querySelector('.fa-bars');
    const navMenu = document.querySelector('nav');

    menuIcon.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
</script>
