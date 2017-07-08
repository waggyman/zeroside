<?php

/***********

* ZeroSide *

Open Source & Anonymous File Sharing

************/

namespace com\zeroside;

class Upload {

    public function upload(){

        global $db, $id;

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

	    // Download URL
        $url = null;
	    $furl = $_POST['downurl'];
	    if(empty($furl)){
	    	$url = uniqid();
	    } else {
	    	$Checker = new URL();
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
	    		case 0.01:
	    			$exp = time() + 60;
	    			break;
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
	    if(!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], __DIR__ . '/../../uploads/' . $real)){
        	outputJSON('Error uploading file - check destination is writeable.');
	    }

	    $request = $db->prepare("INSERT INTO `files`(`file_name`, `file_path`, `file_url`, `file_ext`, `file_size`, `file_time`, `stat_dl`, `views`, `stat_id`) VALUES (:name, :file_path, :url, :ext, :size, :file_time, :stat, :views, :stat_id)");
        
        $dbres = $request->execute(array(
	    	":name" => $name,
	    	":file_path" => $real,
	    	":url" => $url,
	    	":ext" => $extension,
	    	":size" => $size,
	    	":file_time" => $exp,
	    	":stat" => 0,
	    	":views" => 0,
	    	":stat_id" => $id
	    ));

        if(!$dbres){
            outputJSON('Error database');
        }

	    // Success!
	    outputJSON("File uploaded!<br><a href='https://www.zeroside.co/s/{$id}'>Check statistics</a><br><a href='https://www.zeroside.co/{$url}'>Download page</a>", 'success');
        
    }

}

?>