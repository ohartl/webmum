<?php
require_once 'include/php/default.inc.php';

session_destroy();
header("Location: ".FRONTEND_BASE_PATH);
?>