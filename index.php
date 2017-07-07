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

# Set locale UTF-8 US
setlocale(LC_ALL,'en_US.UTF-8');

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

$host = "localhost";
$database = "zeroside";
$username = "root";
$password = "";

$db = new PDO(
	'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8', 
	$username, $password
);

# Require composer modules
require(__DIR__ . '/vendor/autoload.php');

# Require API modules
require(__DIR__ . '/assets/php/api/URL.php');

# Setup AltoRouter
$router = new AltoRouter();

# Readable human size
function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

# Global user variables
$id = uniqid();

# Mapping homepage
$router->map('GET', '/', function(){
	# Setup Pug
	$pug = new Pug\Pug();	

	# Get file
	$file = file_get_contents(__DIR__ . '/views/homepage.pug');

	# Sending file
	global $id;

	echo $pug->render($file, array(
		"id" => $id
	));
});

# Mapping download
$router->map('GET', '/[i:id]', function( $id ) {
	echo "download: {$id}";
});

# [API] Mapping url checker
$router->map('POST', '/api/check', function(){
	// Use URL class to check disponibility
	$url = new com\zeroside\URL();
	die($url->check($_POST['id']));
});

# [API] Mapping file upload
$router->map('POST', '/api/upload', function(){
	function outputJSON($msg, $status = 'error'){
    	die(json_encode(array(
        	'data' => $msg,
        	'status' => $status
    	)));
	}

	// Making file informations
	$name = $_FILES['SelectedFile']['name'];
	$real = uniqid() . $_FILES['SelectedFile']['name'];
	$size = human_filesize($_FILES['SelectedFile']['size']);
	$extension = pathinfo($_FILES['SelectedFile']['name'], PATHINFO_EXTENSION);
	$downloads = 0;

	// Download URL
	global $url;
	$furl = $_POST['downurl'];
	if(empty($furl)){
		$url = uniqid();
	} else {
		$Checker = new com\zeroside\URL();
		$json = json_decode($Checker->check($furl));

		if($json->code == 200){
			$url = $furl;
		} else {
			$url = uniqid();
		}
	}

	// Download Expiration
	$exp = time() + 24 * 3600;
	if(!empty($_POST['expiration'])){
		switch($_POST['expiration']){
			case 1:
				$exp = time() + 3600;
				break;
			case 24:
				$exp = time() + 24 * 3600;
				break;
			case 48:
				$exp = time() + 48 * 3600;
				break;
			case 168:
				$exp = time() + 168 * 3600;
				break;
			default:
				$exp = time + 24 * 3600;
				break;
		}
	}

	// Check for errors
	if($_FILES['SelectedFile']['error'] > 0){
    	outputJSON('An error ocurred when uploading.');
	}

	// Check for size
	if($_FILES['SelectedFile']['size'] > 10*GB){
		outputJSON('Too large file, 10GB max.');
	}

	// Check if the file exists
	if(file_exists(__DIR__ . '/assets/uploads/' . $real)){
    	outputJSON('File with that name already exists.');
	}

	// Upload file
	if(!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], __DIR__ . '/assets/uploads/' . $real)){
    	outputJSON('Error uploading file - check destination is writeable.');
	}

	global $db;
	global $id;
	$request = $db->prepare("INSERT INTO `files`(`file_name`, `file_path`, `file_url`, `file_ext`, `file_size`, `file_time`, `stat_dl`, `stat_id`) VALUES (:name, :file_path, :url, :ext, :size, :file_time, :stat, :stat_id)");
	$request->execute(array(
		":name" => $name,
		":file_path" => $real,
		":url" => $url,
		":ext" => $extension,
		":size" => $size,
		":file_time" => $exp,
		":stat" => $downloads,
		":stat_id" => $id
	));

	// Success!
	outputJSON("File uploaded!<br><a href='https://www.zeroside.co/s/{$id}'>Check statistics</a><br><a href='https://www.zeroside.co/{$url}'>Download page</a>", 'success');
});

# Match current request url
$match = $router->match();

# Sending response
if( $match && is_callable( $match['target'] ) ) {
    # Matched, send requested page
	call_user_func_array( $match['target'], $match['params'] ); 
} else {
	# Does not match, send 404 page
	header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}

?>