<?php
/**
 * (c) 2019-10-07 Remco van der Velde remco@priya.software
 *
 *
 *
 */

echo 'Please specify a target directory where the latest Priya will be installed.' . PHP_EOL;
$target = readline('Directory: ');

if(substr($target, 0, -1) != DIRECTORY_SEPARATOR){
    $target .= DIRECTORY_SEPARATOR;
}
$dir_data = $target . 'Data' . DIRECTORY_SEPARATOR;
$dir_backup = $dir_data . 'Backup' . DIRECTORY_SEPARATOR . 'Priya' . DIRECTORY_SEPARATOR . date('Ymd Hi00') . DIRECTORY_SEPARATOR;
$dir_priya = $target . 'Vendor' . DIRECTORY_SEPARATOR . 'Priya' . DIRECTORY_SEPARATOR;

if(!file_exists($dir_priya)){
    mkdir($dir_priya, 0740, true);
}

if(!file_exists($dir_backup)){
    mkdir($dir_backup, 0740, true);
}

if(extension_loaded('zip') == false){
    echo 'installing PHP-ZIP module...';
    shell_exec('apt install php-zip -y');

    if(extension_loaded('zip') == false){
        echo 'unable to load zip extension, try manually with apt install php-zip';
        exit;
    }

}

$cwd = getcwd();
chdir($dir_backup);

/**
 * need to create a release/latest branch in github
 */

shell_exec('wget https://github.com/like-it/Priya/archive/master.zip');
chdir($cwd);

$scan = scandir($dir_backup);

foreach($scan as $file){
    if(in_array($file, ['.', '..'])){
        continue;
    }
    $url = $dir_backup . $file;
    break;
}

echo 'Extracting zip archive...' . PHP_EOL;

zip_extract($url, $dir_priya);

echo 'Setting up Priya...' . PHP_EOL;

$file_priya = $dir_priya . 'Application' . DIRECTORY_SEPARATOR . 'Priya.php';

if(file_exists($file_priya)){
    shell_exec('php ' . $file_priya . ' bin');
    shell_exec('priya version');
}

function zip_extract($url, $target_dir){
    $zip = new ZipArchive();
    $zip->open($url);

    $errorList = [];
    $dirList = [];
    $fileList = [];
    $first = false;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $node = new stdClass();
        $isDir = false;
        $node->name = $zip->getNameIndex($i);
        if(substr($node->name, -1) == '/'){
            $node->type = 'Dir';
        } else {
            $node->type = 'File';
        }
        $node->index = $i;
        $node->url = $target_dir . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $node->name);

        if($first == false && $node->type == 'Dir'){
            $first = $node;
        }
        if($node->type == 'Dir'){
            $dirList[] = $node;
        } else {
            $fileList[] = $node;
        }
    }
    if(file_exists($target_dir)){
        shell_exec('rm ' . $target_dir . ' -rf');
    }
    foreach($dirList as $node){
        $explode = explode($first->name, $node->url, 2);
        $node->url = implode('', $explode);
        $test = mkdir($node->url, 0740, true);
        if($test === false){
            $errorList[] = $node->url;
        }
    }
    foreach($fileList as $node){
        $stats = $zip->statIndex($node->index);
        $explode = explode($first->name, $node->url, 2);
        $node->url = implode('', $explode);
        $test = file_write($node->url, $zip->getFromIndex($node->index));
        if($test === false){
            $errorList[] = $node->url;
        }
        touch($node->url, $stats['mtime']);
    }
    if(empty($errorList)){
        return true;
    }
    return false;
}

function file_write($url, $data){
    $url = (string) $url;
    $data = (string) $data;
    $fwrite = 0;
    $resource = @fopen($url, 'w');
    if($resource === false){
        return $resource;
    }
    //change to //flock exec see lock / unlock
    $lock = flock($resource, LOCK_EX);
    for ($written = 0; $written < strlen($data); $written += $fwrite) {
        $fwrite = fwrite($resource, substr($data, $written));
        if ($fwrite === false) {
            break;
        }
    }
    if(!empty($resource)){
        flock($resource, LOCK_UN);
        fclose($resource);
    }
    if($written != strlen($data)){
        throw new Exception('File.write failed, written != strlen data....');
        return false;
    } else {
        return $written;
    }
}