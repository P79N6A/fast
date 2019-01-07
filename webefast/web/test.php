
<?php
//$arr = array('23423a','aab','ggr4');
//$arr1 = array('23423a'=>1,'ggr4'=>5,'aab'=>3,);
//
//sort($arr);
//ksort($arr1);
//
//var_dump($arr,$arr1);die;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>eFAST365</title>
﻿<script type="text/javascript" src="/efast/webpub/js/jquery-1.8.1.min.js"></script>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>eFAST365</title>
﻿<script type="text/javascript" src="/efast/webpub/js/jquery-1.8.1.min.js"></script>

</head>

<body>  
    
    <div id="tt" style="height: 100px; width: 200px;overflow-x: auto;    overflow-y: hidden;  ">
        
        <div style="height: 100px; width: 1000px;"></div>
        
    </div>
    <script>
     $(function(){
        alert( $('#tt').width());
          alert(  $('#tt').get(0).clientWidth);
            alert( $('#tt').get(0).scrollWidth);
              alert($('#tt').get(0).offsetWidth);
 
    
    
    });
    
    </script>
   
    
    </body>
</html>
