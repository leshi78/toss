#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: leshi
 * Date: 09.09.18
 * Time: 15:01
 */
require __DIR__ . '/vendor/autoload.php';

use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;

if (!file_exists(__DIR__ . '/config.'.gethostname().'.php')) {
    die('Хочу конфиг config.' . gethostname() . '.php');
}
$general = include_once __DIR__ . '/config.' . gethostname() . '.php';

function createCfg($general, $folder)
{
    $cfg = [
        'src' => "{$general['src']}{$folder}/DUMP",
        'jpg_dst' => [],
        'raw_dst' => [],
        'video_dst' => [],
    ];
    foreach ($general['dst'] as $target) {
        $cfg['raw_dst'][] = ["{$target}Photo/RAW/$folder/", null];
        $cfg['video_dst'][] = ["{$target}Video/$folder/", null];
        $preview = [];
        foreach ($general['preview'] as $resolution) {
            $preview[] = ["{$target}Photo/{$folder}/ph{$resolution}/", $resolution];
        }
        $cfg['jpg_dst'][] = ["{$target}Photo/{$folder}/", $preview];
    }
    return [$folder => $cfg];
}

$config = [];
foreach ($general['list'] as $folder) {
    $config = array_merge($config, createCfg($general, $folder));
}
//var_dump($config);die();

class Lister
{
    protected $source;
    protected $dir;
    public function setSoruce($source)
    {
        $this->source = $source;
        $this->dir = opendir($this->source);
        return $this;
    }

    public function getGenerator()
    {
        while ($filename = readdir($this->dir)) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }
            $path = implode(DIRECTORY_SEPARATOR, [$this->source, $filename]);
            if (is_dir($path)) {
                $Lister = new Lister();
                $Lister->setSoruce($path);
                foreach($Lister->getGenerator() as $file) {
                    yield $file;
                }
                continue;
            }
            yield $path;
        }
    }
}

class Toss
{
    protected $raw_type = ['cr2',];
    protected $image_type = ['jpg','jpeg'];
    protected $video_type = ['avi','ts', 'mkv','mov','mpg','dv'];

    protected $config;
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run()
    {
        foreach($this->config as $name => $params) {
	    var_dump($params['src']);
            $this->processDir($params);
        }
    }

    protected function getDate($file)
    {
        $data = @exif_read_data($file);
        $Date = new \DateTime(
            $data["DateTimeOriginal"]
            ?? $data["DateTime"]
            ?? $data["DateTimeDigitized"]
            ?? (isset($data["FileDateTime"]) ? date('Y-m-d H:i:s', $data["FileDateTime"]) : 'now')) ;

        return $Date->format('Y-m-d');
    }

    protected function getType($fileName)
    {
        if (preg_match('/\.([^\.]+)$/i', $fileName, $matches)) {
            $ext = strtolower($matches[1]);
            switch(true) {
                case in_array($ext, $this->image_type):
                    return 'jpg';
                case in_array($ext, $this->raw_type):
                    return 'raw';
                case in_array($ext, $this->video_type):
                    return 'video';
            }
        }
        return false;
    }

    protected function processDir($params)
    {
        $Lister = new Lister();
        $Lister->setSoruce($params['src']);
        foreach($Lister->getGenerator() as $file) {
            $name = basename($file);
            $date = $this->getDate($file);
            $type = $this->getType($name);
            if ($type) {
                $copied = null;

                foreach ($params[$type . '_dst'] as $dst) {
                    $to_file = implode(DIRECTORY_SEPARATOR, [$dst[0], $date, $name]);
                    $res = $this->copyFile($file, $to_file, $dst[1]);
                    if (!$res) {
                        echo "Failed to copy from $file to $to_file\n";
                    }
                    $copied = $res && ($copied || is_null($copied));
                }
                if ($copied === true) {
                    echo "Remove $file\n";
                    unlink($file);
                }
            } else {
                echo "Unknown type for file $file\n";
            }
        }
    }

    public function checkDst($src, $dst)
    {
        $dst_dir = dirname($dst);
        $src_size = filesize($src);

        while(true) {
            if (!file_exists($dst)) {
                return $dst;
            }

            $dst_size = filesize($dst);

            if ($this->isSameFile($src, $dst, $dst_size )) {
                return $src_size == $dst_size ? true : $dst;
            }

            $dst_base = basename($dst);
            preg_match('/^([^~]*)(~(\d+))?\.([^.]+)$/', $dst_base, $matches);
            $dst_base = $matches[1] . '~' . ($matches[3]+1) . '.' . $matches[4];
            $dst = implode(DIRECTORY_SEPARATOR, [$dst_dir, $dst_base]);
        }


    }
    protected function copyFile($src, $dst, $resize)
    {
        $dst_dir = dirname($dst);
        echo "dst dir: $dst_dir\n";
	    @mkdir($dst_dir, 0777, true);
        if ('jpg' == $this->getType($src) && is_numeric($resize)) {
            echo  "Resize to $resize $src => $dst\n";
            return $this->resizeImage($src, $dst, $resize);
        }

        $dst_fixed = $this->checkDst($src, $dst);
        if ($dst_fixed === true) {
            echo "File $dst and $src are identical. No changes.\n";
            $this->createResize($src, $dst, $resize);
        }
        if ($dst_fixed != $dst) {
            echo "File $dst and $src definelly different. Rename to $dst_fixed.\n";
            $dst = $dst_fixed;
        }

        echo "copy $src => $dst \n";
        $result = copy($src, $dst);
        $result = $this->createResize($src, $dst, $resize) && $result;
        return $result;
    }

    protected function createResize($src, $dst, $resize)
    {
        $result = true;

        if (is_array($resize)) {
            $dst_base = basename($dst);
            $dst_date = basename(dirname($dst));
            foreach ($resize as list($dir, $size)) {
                $rdst = implode(DIRECTORY_SEPARATOR, [$dir, $dst_date, $dst_base]);
                $result = $this->copyFile($src, $rdst, $size) && $result;
            }
        }
        return $result;

    }

    protected function isSameFile($src, $dst, $dst_size) {
        if (!file_exists($dst)) {
            return false;
        }
        $sh = fopen($src, 'rb');
        $dh = fopen($dst, 'rb');
        $max_size = 100 * 1024;
        while($dst_size) {
            $sbuf = fread($sh, min($dst_size, $max_size));
            $dbuf = fread($dh, min($dst_size, $max_size));
            $dst_size -= min($dst_size, $max_size);
            if (strcmp($sbuf, $dbuf)) {
                fclose($sh);
                fclose($dh);
                return false;
            }
        }
        fclose($sh);
        fclose($dh);
        return true;
    }

    function resizeImage($file, $dst, $size) {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        $newwidth = $r > 1 ? $size : $size * $r;
        $newheight = $r > 1 ? $size / $r : $size ;

        $from = imagecreatefromjpeg($file);
        $to = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($to, $from, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $result = imagejpeg($to, $dst, 90);
        try{
            $exif = @exif_read_data($file);
            $Date = new \DateTime(
                $exif["DateTimeOriginal"]
                ?? $exif["DateTime"]
                ?? $exif["DateTimeDigitized"]
                ?? (isset($exif["FileDateTime"]) ? date('Y-m-d H:i:s', $exif["FileDateTime"]) : 'now')) ;

            $jpeg = new PelJpeg($dst);
            $newEntry = new \lsolesen\pel\PelEntryTime(PelTag::DATE_TIME,$Date->format('U'),\lsolesen\pel\PelEntryTime::UNIX_TIMESTAMP);

            $newIfd = new \lsolesen\pel\PelIfd(\lsolesen\pel\PelIfd::IFD0);
            $newIfd->addEntry($newEntry);

            $newTiff = new \lsolesen\pel\PelTiff();
            $newTiff->setIfd($newIfd);

            $newExif = new \lsolesen\pel\PelExif();
            $newExif->setTiff($newTiff);
            $jpeg->setExif($newExif);
            $jpeg->saveFile($dst);
        } catch (\Exception $e) {}
        return $result;
    }
}

(new Toss($config))->run();
