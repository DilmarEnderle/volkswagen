<?php
// $link = mysqli_connect("54.39.106.109", "admin", "7ujmMJU&", "gelic_vw");
$link = mysqli_connect("35.199.78.245", "gelicprime.homol", "G9fh4^k42ypAE", "gelic_vw");

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;

mysqli_close($link);
?>
