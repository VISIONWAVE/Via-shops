<?php
session_start();
session_unset();
session_destroy();

// Since logout.php is in /backend/auth/ and register.html is in root,
// go two levels up (../../) then to register.html
header("Location: ../../register.html");
exit();
?>
