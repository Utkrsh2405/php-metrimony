<?php
require "includes/dbconn.php";
$gender_filter_clause = "";
$sql = "SELECT c.* FROM customer c
        INNER JOIN users u ON c.cust_id = u.id
        WHERE (u.account_status = 'active' OR u.account_status IS NULL) AND u.userlevel = 0
        $gender_filter_clause
        ORDER BY c.cust_id DESC LIMIT 12";
$result = mysqli_query($conn, $sql);
if(!$result) {
    echo "Error: ". mysqli_error($conn);
} else {
    echo "Found: ". mysqli_num_rows($result);
}
?>
