<?php

/***********

* ZeroSide *

Open Source & Anonymous File Sharing

************/

# Require composer modules
require(__DIR__ . '/vendor/autoload.php');

# Setup AltoRouter
$router = new AltoRouter();

# Mapping homepage
$router->map('GET', '/', function(){
	# Setup Pug
	$pug = new Pug\Pug();	

	# Get file
	$file = file_get_contents(__DIR__ . '/views/homepage.pug');

	# Sending file
	echo $pug->render($file);
});

# Mapping download
$router->map('GET', '/[i:id]', function( $id ) {
	echo "download: {$id}";
});

# [API] Mapping url checker
$router->map('POST', '/api/check', function(){
	if($_POST['id'] == 2){
		echo json_encode(array(
			"code" => 400,
			"message" => "ID already taken"
		));
	} else {
		echo json_encode(array(
			"code" => 200,
			"message" => "ID available"
		));
	}
});

# [API] Mapping file upload
$router->map('POST', '/api/upload', function(){
	function outputJSON($msg, $status = 'error'){
    	header('Content-Type: application/json');
    	die(json_encode(array(
        	'data' => $msg,
        	'status' => $status
    	)));
	}

	// Check for errors
	if($_FILES['SelectedFile']['error'] > 0){
    	outputJSON('An error ocurred when uploading.');
	}

	// Check if the file exists
	if(file_exists('upload/' . $_FILES['SelectedFile']['name'])){
    	outputJSON('File with that name already exists.');
	}

	// Upload file
	if(!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], __DIR__ . '/assets/uploads/' . uniqid() . $_FILES['SelectedFile']['name'])){
    	outputJSON('Error uploading file - check destination is writeable.');
	}

	// Success!
	outputJSON('File uploaded successfully to "' . 'upload/' . $_FILES['SelectedFile']['name'] . '".', 'success');
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