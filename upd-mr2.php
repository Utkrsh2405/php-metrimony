<?php
$content = file_get_contents("matchright.php");
$pattern = '/\$sql\s*=\s*"SELECT c\.\* FROM customer c\s*INNER JOIN users u ON c\.cust_id = u\.id\s*WHERE u\.account_status = \'active\' AND u\.userlevel = 0\s*ORDER BY c\.profilecreationdate DESC";/s';

$replace = "\$gender_filter = \"\";
     if(isset(\$_SESSION['id'])) {
         \$logged_user_id = intval(\$_SESSION['id']);
         \$my_gender_sql = \"SELECT sex FROM customer WHERE cust_id = \".\$logged_user_id;
         if(function_exists('mysqlexec')) {
             \$my_gender_result = mysqlexec(\$my_gender_sql);
         } else {
             global \$conn;
             \$my_gender_result = mysqli_query(\$conn, \$my_gender_sql);
         }
         
         if(\$my_gender_result && mysqli_num_rows(\$my_gender_result) > 0) {
             \$my_gender_row = mysqli_fetch_assoc(\$my_gender_result);
             \$my_user_gender = \$my_gender_row['sex'];
             \$my_opposite_gender = (strtolower(\$my_user_gender) == 'male') ? 'Female' : 'Male';
             \$gender_filter = \" AND LOWER(TRIM(c.sex)) = LOWER('\$my_opposite_gender')\";
         }
     }

     \$sql=\"SELECT c.* FROM customer c
           INNER JOIN users u ON c.cust_id = u.id
           WHERE u.account_status = 'active' AND u.userlevel = 0 \$gender_filter
           ORDER BY c.profilecreationdate DESC\";";

$new_content = preg_replace($pattern, $replace, $content);
if ($new_content !== $content && $new_content !== null) {
    file_put_contents('matchright.php', $new_content);
    echo "Updated matchright.php via preg_replace\n";
} else {
    echo "Pattern not found in matchright.php\n";
}
?>
