<?php
include_once '../autoload.php';
use Jazor\Zip\Store\ZipStore;

$base = __DIR__ . DIRECTORY_SEPARATOR;

try {
    $zip = new ZipStore();

    //simple file
    $zip->addFile("{$base}store.txt");

    //file with extra name
    $zip->addFile("{$base}extra.txt", 'files/extra.txt');
    $zip->addFile("{$base}你好.txt", 'files/你好.txt');

    //directory with base name
    $zip->addDirectory("{$base}files", 'extra-files/');

    //raw contents
    $zip->addContents('i am hello world!', 'my name.jpg');

    //$zip->save("{$base}dest.zip");
    $zip->send('a.zip');
}catch (\Exception $e) {
    echo $e->getMessage();
}

