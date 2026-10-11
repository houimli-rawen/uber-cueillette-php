<?php
function isLoggedIn() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        return true;
    }
    return false;
}

function requireLogin($type) {
    if (!isLoggedIn()) {
        header('Location: index.php?error=login_required');
        exit();
    }
    if ($type && $_SESSION['user_type'] !== $type) {
        header('Location: index.php?error=unauthorized');
        exit();
    }
}

function sanitize($conn, $val) {
    return mysqli_real_escape_string($conn, trim($val));
}
?>
