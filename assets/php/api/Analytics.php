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
        
        if (empty($id)) {
            header('Location: /?r=404');
            exit();
        }
        
        global $db;
        $request = $db->prepare('SELECT * FROM files WHERE stat_id=:id');
        
        $request->execute(array(
            ":id" => $id
        ));
        
        $result = $request->fetch();
        
        if (empty($result)) {
            header('Location: /?r=404');
            exit();
        }
        
        # Setup Pug
        $pug = new \Pug\Pug();
        
        # Get file
        $file = file_get_contents(__DIR__ . '/../../../views/analytics/model.pug');
        
        # Set data
        $data = array(
            "name" => $result["file_name"],
            "downloads" => $result["stat_dl"],
            "views" => $result["views"],
            "url" => $result["file_url"],
            "ratio" => round((($result['stat_dl'] / $result['views']) * 100), 2)
        );
        
        # Sending page
        echo $pug->render($file, $data);
    }
    
}

?>