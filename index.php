<?php

/***********

* ZeroSide *

Open Source & Anonymous File Sharing

************/

# Define units
define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);

# Define path
define('R', __DIR__);

# Set locale UTF-8 US
setlocale(LC_ALL, 'en_US.UTF-8');

# Getting configuration
$config = parse_ini_file(R . '/config.ini');

# Enable debug mode
if ($config['debug'])
{
    error_reporting(E_ALL);
}

# Init DB (MySQL)
#-----------------
# Table Blueprint:
#-----------------
# file_name | TEXT -> Default file name
# file_path | TEXT -> Real file name in upload folder
# file_url  | TEXT -> File custom URL
# file_ext  | TEXT -> File extension
# file_size | TEXT -> Human readable file size (with B/KB/MB/GB extension)
# file_time | INT  -> Time where is file is expired
# stat_dl   | INT  -> Number of downloads
# stat_id   | INT  -> Identifier to access stats page
#-----------------

$db = new \PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=utf8', $config['username'], $config['password']);

# Require composer modules
require(__DIR__ . '/vendor/autoload.php');

# Require API modules
require(__DIR__ . '/assets/php/api/URL.php');
require(__DIR__ . '/assets/php/api/Upload.php');
require(__DIR__ . '/assets/php/api/Download.php');
require(__DIR__ . '/assets/php/api/Analytics.php');

# Setup AltoRouter
$router = new AltoRouter();

# Setup Pug (#fix for better memory and views management)
$pug = new \Pug\Pug(array(
    'cache' => R . '/assets/cache',
    'basedir' => R . '/views'
));

# Readable human size
function human_filesize($bytes, $decimals = 2)
{
    $size   = array(
        'B',
        'kB',
        'MB',
        'GB',
        'TB',
        'PB',
        'EB',
        'ZB',
        'YB'
    );
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

# Global user variables
$id = uniqid();

# [GET] Mapping homepage
$router->map('GET', '/', function()
{   
    # Getting global variables
    global $pug;
    global $id;
    
    # Sending file
    echo $pug->render(R . "/views/homepage.pug", array(
        "id" => $id
    ));
});

# [GET] Mapping download
$router->map('GET', '/[a:id]', function($id)
{
    $download = new com\zeroside\Download;
    $download->show($id);
});

# [GET] Mapping analytics
$router->map('GET', '/s/[a:id]', function($id)
{
    $stats = new com\zeroside\Analytics;
    $stats->show($id);
});

# [API] Mapping analytics JSON
$router->map('GET', '/api/stat/[a:id]', function($id){
    $stats = new com\zeroside\Analytics;
    die($stats->api($id));
});

# [API] Mapping download base64
$router->map('POST', '/api/download', function()
{
    $download = new com\zeroside\Download;
    $download->fire($_POST['download_uid']);
});

# [API] Mapping url checker
$router->map('POST', '/api/check', function()
{
    // Use URL class to check disponibility
    $url = new com\zeroside\URL();
    die($url->check($_POST['id']));
});

# [API] Mapping file upload
$router->map('POST', '/api/upload', function()
{
    $upload = new com\zeroside\Upload;
    $upload->upload();
});

# Match current request url
$match = $router->match();

# Sending response
if ($match && is_callable($match['target'])) {
    # Matched, send requested page
    call_user_func_array($match['target'], $match['params']);
} else {
    # Does not match, send 404 page
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}

?>