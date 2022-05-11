<?php
include_once '../autoload.php';
header('content-type:text/plain');
use Jazor\Zip\Store\ZipStore;
use Jazor\Zip\Store\ZipEntity;


/**
 * @var ZipEntity $entity;
 */

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

    $zip->save("{$base}dest.zip");

//$zip->send('a.zip');
}catch (\Exception $e) {
    echo $e->getMessage();
}

