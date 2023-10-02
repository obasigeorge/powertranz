<?php

function my_powertranz_autoloader($class) 
{
    $parts = explode('\\', $class); array_shift($parts);
    $filename = __DIR__ . '/src/' . implode('/', $parts) . '.php';
    if (file_exists($filename)) require_once($filename);
}

spl_autoload_register('my_powertranz_autoloader');