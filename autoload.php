<?php
spl_autoload_register(function ($class){
    if(strpos($class, 'Jazor\\Zip\\Store\\') === false) return;
    $name = substr($class, 16);
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $name) . '.php';
    if(!is_file($path)) return;

    include_once $path;

});
