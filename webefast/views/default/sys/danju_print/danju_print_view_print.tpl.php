<?php echo get_webpub('style/css/print.css');?>
<?php echo get_js('print/colResizable-1.3.min.js') ?>

<?php
	$view_print = $response['view_print']['danju_print_content'];
?>

<div id='viewDanju' class='danjuContent' style="width:<?php echo $response['view_print']['template_page_width'];?>mm;">
	<?php echo $view_print;?>
</div>