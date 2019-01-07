<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>系统提示</title>
        <link href="assets/css/dpl.css" rel="stylesheet">
        <link href="assets/css/bui.css" rel="stylesheet">
    </head>
    <body style="background-color: #bebebe;">
        <div class="doc-content">
            <div style="margin-top:18%; margin-left: 30%; width: 40%;">
                <div class="panel panel-primary">
                    <div class="panel-header clearfix">
                        <h3 class="pull-left"><?php echo isset($app['err_no']) && !empty($app['err_no']) ? $app['err_no'] : '系统提示:' ?></h3>
                    </div>
                    <div class="panel-body">
                        <p><?php echo $app['err_msg'] ?></p>
                    </div>
                </div>
            </div>
        </div>  
    </body>
</html>