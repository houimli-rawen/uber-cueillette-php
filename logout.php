<?php
session_start();
session_destroy();
header('Location: index.php?error=logged_out');
exit();
?>
