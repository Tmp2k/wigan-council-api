<?php

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Europe/London');


$GLOBALS['app']['path'] = realpath(__DIR__).'/';


$folder = str_replace($_SERVER['DOCUMENT_ROOT'],'',$GLOBALS['config']['path'].'public/' );
if($folder == '/') $folder = '';


if (!empty($_SERVER['SERVER_NAME'])) {
    if($_SERVER['HTTPS']) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }
    $GLOBALS['app']['url'] = $protocol.$_SERVER['SERVER_NAME'] .'/'. $folder;
}


//all path constants are written relative to the root of the site defined above.
//all paths contain a terminating slash.


$GLOBALS['app']['includePath'] =  $GLOBALS['app']['path'] . 'includes/';


//get environment variables
$env = json_decode(file_get_contents($GLOBALS['app']['path'] . 'environment.json'));




if(!empty($env->{$_SERVER['SERVER_NAME']})) {
    $GLOBALS['environment'] = (object) array_merge((array) $env->default, (array) $env->{$_SERVER['SERVER_NAME']});
} else {
    $GLOBALS['environment'] = $env->default;
}




if ($GLOBALS['environment']->debug == 1  ) {
    ini_set('display_errors','1');
}else{
    ini_set('display_errors','0');
}



//include libraries
include_once($GLOBALS['app']['includePath'] . 'ASPBrowser.php');
include_once($GLOBALS['app']['includePath'] . 'functions.php');
include_once($GLOBALS['app']['includePath'] . 'simple_html_dom.php');




