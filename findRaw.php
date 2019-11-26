<?php

if (!file_exists(__DIR__ . '/config.'.gethostname().'.php')) {
    die('Хочу конфиг config.' . gethostname() . '.php');
}
$general = include_once __DIR__ . '/config.' . gethostname() . '.php';


function findRaw($source, $dst, &$list)
{
    $dir = opendir($source);
    while ($filename = readdir($dir)) {
        if (preg_match('/(IMG_[\d]+)/i', $filename, $match)) {
            $res = isset($list[$match[1]]) ? 'YES' : 'no';
//            echo "check $source/RAW/$filename   {$match[1]} $res\n";
            if (isset($list[$match[1]])) {
                $ext = '';
                $i = 1;
                do {
                    $toFile = "{$dst}/{$match[1]}{$ext}.CR2";
                    $ext = '-' . $i++;
                } while (file_exists($toFile));
                echo "copy $source/$filename\n";
                copy("{$source}/{$filename}", $toFile);
            }
        }
    }

    closedir($dir);

}
foreach ($general['list'] as $top) {
    $source = "{$general['src']}/{$top}/FIND";
    $dest = "{$general['src']}/{$top}/FIND/RAW";
    mkdir($dest);
    $dir = opendir($source);
    $list = [];
    while ($filename = readdir($dir)) {
        if (in_array($filename, ['.', '..'])) {
            continue;
        }
        if (preg_match('/(IMG_[\d]+)/i', $filename, $match)) {
            $list[] = $match[1];
        }
    }

    closedir($dir);
    if (!empty($list)) {
        $list = array_flip($list);

        $source = "{$general['src']}/RAW/{$top}";
        $dir = opendir($source);
        $dirs = [];
        while ($filename = readdir($dir)) {
            $dirs[] = $filename;
        }
        closedir($dir);
        foreach ($dirs as $filename) {
            echo "{$source}/$filename\n";
            findRaw("{$source}/$filename", $dest, $list);
        }
        var_dump($dirs);
    }
}
