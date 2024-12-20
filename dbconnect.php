<?php
try {
    $pdo = new PDO('mysql:host=192.168.1.252;dbname=gpstracker', 'sqlcapasu', 'mysql-ui2018');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>

