<?php
$content = file_get_contents('view_profile.php');
$search = '$phone_code=$row[\'phone_code\'] ?? \'91\';';
$replace = $search . "\n\nif (\$is_exclusive_profile_var && !\$user_is_subscribed && !\$is_own_profile) {\n    \$mobile = 'xxxxxxxxxx';\n    \$phone_code = '';\n    \$email = 'Locked';\n}";
$content = str_replace($search, $replace, $content);
file_put_contents('view_profile.php', $content);
echo "Patched\n";
?>
