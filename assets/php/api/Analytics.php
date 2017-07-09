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
        global $db, $pug;
        
        if (empty($id)) {
            header('Location: /?r=404');
            exit();
        }
        
        $request = $db->prepare('SELECT * FROM files WHERE stat_id=:id');
        
        $request->execute(array(
            ":id" => $id
        ));
        
        $result = $request->fetch();
        
        if (empty($result)) {
            header('Location: /?r=404');
            exit();
        }
        
        # Calculate ratio (#fix for division by zero)
        if (empty($result["views"]))
        {
            $ratio = 0;
        } else {
            $ratio = round((($result['stat_dl'] / $result['views']) * 100), 2);
        }

        # Set data
        $data = array(
            "name" => $result["file_name"],
            "downloads" => $result["stat_dl"],
            "views" => $result["views"],
            "url" => $result["file_url"],
            "ratio" => $ratio
        );
        
        # Sending page
        echo $pug->render(R . "/views/analytics.model.pug", $data);
    }
    
}

?>