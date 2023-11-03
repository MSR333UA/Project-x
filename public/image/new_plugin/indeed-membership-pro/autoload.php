<?php
spl_autoload_register('indeedIhcAutoloader');
function indeedIhcAutoloader($fullClassName='')
{
    if (strpos($fullClassName, "Indeed\Ihc\Db")!==FALSE){
        $path = IHC_PATH . 'classes/Db/';
    } else if (strpos($fullClassName, "Indeed\Ihc\PaymentGateways")!==FALSE){
        $path = IHC_PATH . 'classes/PaymentGateways/';
    } else if (strpos($fullClassName, "Indeed\Ihc\Services")!==FALSE){
        $path = IHC_PATH . 'classes/services/';
    } else if (strpos($fullClassName, "Indeed\Ihc")!==FALSE){
        $path = IHC_PATH . 'classes/';
    }
    if (empty($path)) return;

    $classNameParts = explode('\\', $fullClassName);
    if (!$classNameParts) return;
    $lastElement = count($classNameParts) - 1;
    if (empty($classNameParts[$lastElement])) return;
    $fullPath = $path . $classNameParts[$lastElement] . '.php';

    if (!file_exists($fullPath)) return;
    include $fullPath;
}
