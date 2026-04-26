<?php
$HOST = 'localhost';
$USERNAME = 'root';
$PASSWORD = '';
$DBNAME = 'chess_board';

try {
    $conn = new PDO(
        "mysql:host=$HOST;dbname=$DBNAME;charset=utf8",
        $USERNAME,
        $PASSWORD
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>