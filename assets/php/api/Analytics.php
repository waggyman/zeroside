<?php

/***********

* ZeroSide *

Open Source & Anonymous File Sharing

************/

namespace com\zeroside;

class Analytics
{
    
    public function show($id)
    {

        # Defining global variables
        global $pug;
        
        $result = json_decode($this->api($id));

        switch($result->code){
            case 403:
                header('Location: /?r=403');
                exit();
                break;
            case 404:
                header('Location: /?r=404');
                exit();
                break;
            case 200:
                # Set data
                $data = array(
                    "name" => $result->name,
                    "downloads" => $result->downloads,
                    "views" => $result->views,
                    "url" => $result->url,
                    "ratio" => $result->ratio
                );
        
                # Sending page
                echo $pug->render(R . "/views/analytics.model.pug", $data);
        }
    }

    public function api($id)
    {

        global $db;

        if (empty($id)){
            return json_encode(array(
                "code" => 403,
                "message" => "No stat id in request"
            ));
        }

        $request = $db->prepare('SELECT * FROM files WHERE stat_id=:id');
        
        $request->execute(array(
            ":id" => $id
        ));
        
        $result = $request->fetch();
        
        if (empty($result)) {
            return json_encode(array(
                "code" => 404,
                "message" => "No analytics for this id"
            ));
        }

        # Calculate ratio (#fix for division by zero)
        if (empty($result["views"]))
        {
            $ratio = 0;
        } else {
            $ratio = round((($result['stat_dl'] / $result['views']) * 100), 2);
        }

        return json_encode(array(
            "code" => 200,
            "message" => "success",
            "downloads" => $result['stat_dl'],
            "views" => $result['views'],
            "ratio" => $ratio,
            "url" => $result['file_url'],
            "name" => $result["file_name"]
        ));
    }
    
}

?>