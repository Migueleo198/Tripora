<?php

    function loadFile($dir, $name){
        $url = RUTA_URL."/".$dir."/".$name;
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext === 'css') {
            echo '<link rel="stylesheet" href="'.$url.'">';
        } elseif ($ext === 'js') {
            echo '<script src="'.$url.'"></script>';
        }
    }

    function loadPage($dir, $name){
        $url = RUTA_URL."/".$dir."/".$name;
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext === 'css') {
            echo '<link rel="stylesheet" href="'.$url.'">';
        } elseif ($ext === 'js') {
            echo '<script src="'.$url.'"></script>';
        }
    }
?>
