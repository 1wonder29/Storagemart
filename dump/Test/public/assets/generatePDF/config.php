<?php
// define database connection
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'josh');
define('DB_PASSWORD', 'ricafort');
define('DB_NAME', 'storagemart');

// attempt to connect
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// check connection
if ($link === false) {
    die("ERROR: Could not connect " . mysqli_connect_error());
}

// set time zone
date_default_timezone_set("Asia/Manila");
?>