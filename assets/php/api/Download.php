<?php

/***********

* ZeroSide *

Open Source & Anonymous File Sharing

************/

namespace com\zeroside;

class Download
{
    
    public function show($id)
    {
        # Defining global variables
        global $db, $pug;
        $request = $db->prepare('SELECT * FROM files WHERE file_url=:id');
        
        $request->execute(array(
            ":id" => $id
        ));
        
        $result = $request->fetch();
        
        if (empty($result)) {
            header('Location: /?r=404');
            exit();
        } else {
            # Testing expiration
            if (time() > $result['file_time']) {
                # Sending page
                echo $pug->render(R . "/views/download.expired.pug", array(
                    "file_name" => $result[0]
                ));
                
                # Remove download from database to prevent access it again
                $request = $db->prepare('DELETE FROM files WHERE file_url=:id');
                $request->execute(array(
                    ":id" => $id
                ));
                
                # Remove in folder now
                unlink(__DIR__ . '/../../uploads/' . $result['file_path']);
            } else {    
                # Sending page
                echo $pug->render(R . "/views/download.model.pug", array(
                    "file_name" => $result[0],
                    "file_size" => $result[4],
                    "temp_url" => base64_encode($id)
                ));
                
                $request = $db->prepare('UPDATE files SET views=:views WHERE file_url=:id');
                $request->execute(array(
                    ":views" => $result['views'] + 1,
                    ":id" => $id
                ));
            }
        }
    }
    
    public function fire($id)
    {
        
        if (empty($id)) {
            header('Location: /?r=500');
            exit();
        } else {
            
            # Defining global variables
            global $db;
            
            $request = $db->prepare('SELECT * FROM files WHERE file_url=:id');
            $request->execute(array(
                ':id' => base64_decode($id)
            ));
            
            $result = $request->fetch();
            
            $filename = __DIR__ . '/../../uploads/' . $result['file_path'];
            $name     = $result['file_name'];
            
            $request = $db->prepare('UPDATE files SET stat_dl=:dl WHERE file_url=:id');
            $request->execute(array(
                ':dl' => $result['stat_dl'] + 1,
                ':id' => base64_decode($id)
            ));
            
            // Starting file sending
            set_time_limit(0);
            session_start();
            
            if (!is_file($filename) || !is_readable($filename)) {
                header('Location: /r=404d');
                exit;
            }
            $size = filesize($filename);
            
            if (ini_get("zlib.output_compression")) {
                ini_set("zlib.output_compression", "Off");
            }
            
            session_write_close();
            
            header("Cache-Control: no-cache, must-revalidate");
            header("Cache-Control: post-check=0,pre-check=0");
            header("Cache-Control: max-age=0");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            header("Content-Type: application/force-download");
            header('Content-Disposition: attachment; filename="' . $name . '"');
            
            header("Content-Length: " . $size);
            
            readfile($filename);
        }
    }
    
}