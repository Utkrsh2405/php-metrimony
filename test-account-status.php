<?php
require "includes/dbconn.php";
$res = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'account_status'");
if(mysqli_num_rows($res) > 0) {
    echo "Column exists. ";
} else {
    echo "Column missing! ";
}

$sql = "SELECT c.* FROM customer c INNER JOIN users u ON c.cust_id = u.id WHERE u.account_status = 'active' AND u.userlevel = 0 ORDER BY c.cust_id DESC LIMIT 12";
$res2 = mysqli_query($conn, $sql);
if(!$res2) {
    echo "Query Error: " . mysqli_error($conn);
} else {
    echo "Results: " . mysqli_num_rows($res2);
}
?>
