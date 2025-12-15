<?php
// config/db.php - Works with both MySQLi and PDO (for InfinityFree)

$servername = "sql301.infinityfree.com";
$username   = "if0_40691229";
$password   = "SyIBNQUk56";
$dbname     = "if0_40691229_db";

// MySQLi Connection (you already use this)
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("MySQLi Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// PDO Connection (needed for the new register/login pages)
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}
?>
