<?php
$content = file_get_contents('functions.php');
$pattern = '/function searchid\([^}]+\$sql="SELECT \* FROM customer WHERE cust_id=\$profid";\s*\}/s';
$replace = "function searchid(){\n        if (\$_SERVER['REQUEST_METHOD'] == 'POST') {\n                \$profid=\$_POST['profid'];\n                \$sql=\"SELECT * FROM customer WHERE cust_id=\$profid\";\n                \$result = mysqlexec(\$sql);\n                return \$result;\n        }\n}";
$new_content = preg_replace($pattern, $replace, $content);
file_put_contents('functions.php', $new_content);
if ($content !== $new_content) echo "Success\n"; else echo "Failed\n";
?>
