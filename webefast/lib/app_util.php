<?php

function app_css($css, $v = '0.1') {
    $css_arr = explode(',', $css);

    foreach ($css_arr as $_css) {
        $path_css = "app/css/" . $_css . "?v=" . $v;
        echo '<link href="' . $path_css . '" rel="stylesheet" type="text/css">';
    }
}

function app_js($js, $v = '0.1') {
    $js_arr = explode(',', $js);
    foreach ($js_arr as $_js) {
        $path_js = "app/js/" . $_js . "?v=" . $v;
        $v = '0.1';
        echo '<script src="' . $path_js . '"></script>';
    }
}

function app_img($img, $v) {
    echo $path_img = "app/images/" . $img . "?v=" . $v;
}

function app_tpl($tpl){
 $response = &CTX()->response;
 $request = &CTX()->request;
 include   ROOT_PATH.CTX()->app_name."/views/default/app/{$tpl}.tpl.php"; 
}