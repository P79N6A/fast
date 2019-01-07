<!DOCTYPE html>
<html>
    <head>
        <title>模版编辑</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="<?php echo $response['css'] ?>" rel="stylesheet" type="text/css" />
        <style>
            #report{
                width: 100%;
                padding-top: <?php echo $response['report_top']; ?>mm;
                margin:0 auto;
                text-align:center;
            }
            .group{
                width: <?php echo $response['data']['paper_width'] - $response['report_left']; ?>mm;
                margin:0 auto;
                height: auto;
                z-index:1;
            }

        </style>
        <?php include get_tpl_path('web_page_top'); ?>
    </head>
    <body style="<?php echo "width:{$response['data']['paper_width']}mm;height:{$response['data']['paper_height']}mm;" ?>">

        <?php echo $response['body']; ?>
    </body>

</html>
