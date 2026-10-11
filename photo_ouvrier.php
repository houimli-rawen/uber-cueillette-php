<?php
require_once 'config.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT photo FROM ouvrier WHERE id_ouvrier=" . $id));
    if ($row && $row['photo']) {
        header('Content-Type: image/jpeg');
        echo $row['photo'];
        exit();
    }
}
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="40" r="22" fill="#556b2f"/><ellipse cx="50" cy="85" rx="30" ry="20" fill="#556b2f"/></svg>';
?>
