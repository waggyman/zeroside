<?php

/***********

* ZeroSide *

Open Source & Anonymous File Sharing

************/

namespace com\zeroside;

class URL
{
    
    public function check($url)
    {
        # Response blueprint
        #-------------------
        # Type: JSON
        # code => 200 (available) or 400 (taken)
        # message => "url available" or "url taken"
        #-------------------
        
        global $db;
        
        if (empty($url) || !ctype_alnum($url)) {
            
            return json_encode(array(
                "code" => 400,
                "message" => "This URL is already taken"
            ));
            die();
            
        } else {
            $request = $db->prepare("SELECT file_url FROM files WHERE file_url = (:user_type)");
            
            $request->execute(array(
                ":user_type" => $url
            ));
            
            $result = $request->fetchAll(\PDO::FETCH_OBJ);
            
            if (!empty($result)) {
                return json_encode(array(
                    "code" => 400,
                    "message" => "This URL is already taken"
                ));
                die();
            } else {
                return json_encode(array(
                    "code" => 200,
                    "message" => "This URL is available!"
                ));
                die();
            }
        }
    }
    
}

?>