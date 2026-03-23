<?php
$files = glob('*.php');
foreach($files as $f) {
    if ($f === 'test_syntax.php') continue;
    $out = shell_exec("php -l " . escapeshellarg($f) . " 2>&1");
    if (strpos($out, "No syntax errors") === false) {
        echo trim($out) . "\n";
    }
}
echo "Done checking root dir.\n";
