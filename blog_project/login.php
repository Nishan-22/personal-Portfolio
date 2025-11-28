<?php
// Redirect legacy/short login URL to the actual auth path
header("Location: auth/login.php");
exit;
