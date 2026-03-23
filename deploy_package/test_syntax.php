<?php
$files = no_hidden_files('.');
function no_hidden_files($dir) {
    if(!is_dir($dir)) return [];
    $res = [];
    foreach(scandir($dir) as $f) {
        if($f === '.' || $f === '..' || $f[0] === '.') continue;
        $path = $dir.'/'.$f;
        if(is_dir($path)) {
            $res = array_merge($res, no_hidden_files($path));
        } elseif(substr($path, -4) === '.php') {
            $res[] = $path;
        }
    }
    return $res;
}
$broken = [];
foreach($files as $f) {
    $out = shell_exec("php -l \"$f\" 2>&1");
    if(strpos($out, "No syntax errors") === false) {
        echo $out;
    }
}
echo "Done checking.\n";
