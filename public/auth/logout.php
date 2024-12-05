<?php
session_start();
session_destroy();
header("Location: /week10/public/index.php");
exit();
