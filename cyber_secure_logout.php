<?php
session_start();
session_unset();
session_destroy();
header("Location: cyber_secure_login.php");
exit();