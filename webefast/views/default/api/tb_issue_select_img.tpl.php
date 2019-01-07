<?php echo load_css('select-img.css', true) ?>
<?php echo load_css('pagebar.css', true) ?>
<?php echo load_js('pagebar.js', true) ?>
<div class="select_img">
    <!--    <div class="clearfix">
            <button class="select">&nbsp;</button>
            <button class="send " data-counter="0">&#10004;</button>
        </div>-->
    <div class="pic_search">
        <input type="text" id="pic_title" name="pic_title" class="input-normal" value="" style="width:150px;"  placeholder="按图片标题搜索" />
        <button id="btn_pic_search" class="button button-primary">搜索</button>
    </div>
    <ul id="pic_list">
    </ul>
    <div id="pagination"></div>
</div>
<script>
    var page_size = 1;
    var current_page = 1;
    var pic_load_status = 1;
    $(function () {
        get_images();//加载页面时加载图片

        $('#btn_pic_search').on('click', function () {
            pic_load_status = 1;
            get_images();
        });

    });

    function load_page_click() {
        pic_load_status = 0;
        $('#pagination span').find('a').each(function (i, e) {
            $(e).click(function () {
                get_images();
            });
        });
    }

    function add_pic_click() {
        $('#pic_list').find('li').each(function (i, e) {
            $(e).click(function () {
                $(this).toggleClass('selected');
                if ($('li.selected').length == 0) {
                    $('.select').removeClass('selected');
                } else {
                    $('.select').addClass('selected');
                }
                add_images();
            });
        });
    }

    //获取图片
    function get_images() {
        current_page = $('.current').text() ? $('.current').text() : current_page;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/get_pictures'); ?>',
            data: {shop_code: '<?php echo $request['shop_code'] ?>', current_page: current_page, title: $('#pic_title').val()},
            success: function (data) {
                var node = '';

                $.each(data['pictures'], function (i, val) {
                    node += '<li><img src = "' + val['picture_path'] + '" title = "' + val['title'] + '" /></li>';
                });
                $('#pic_list').html(node);
                add_pic_click(); //添加图片选择事件
                if (pic_load_status == 1) {
                    page_size = Math.ceil(data['pic_count'] / 40);
                    page_size = page_size == 0 ? 1 : page_size;
                    current_page = $('.current').text() ? $('.current').text() : 1;
                    pager_init(); //重新加载分页
                }

                load_page_click(); //添加分页事件，获取图片
            }
        });
    }

    function add_images() {
        var images = [];
        $('.select_img ul').find('.selected').each(function (i, e) {
            var obj = {};
//            obj.title = $(this).find('img').attr('title');
            obj.src = $(this).find('img').attr('src');
            obj.miniSrc = $(this).find('img').attr('src');
            images.push(obj);
        });
        parent.images = images;
    }

    var pager_init = function () {
        Pagination.Init(document.getElementById('pagination'), {
            size: page_size, // pages size
            page: current_page, // selected page
            step: 3   // pages before and after current
        });
    };

//    document.addEventListener('DOMContentLoaded', init, false);

    /*//选择所有图片
     $('.select').click(function () {
     if ($('li.selected').length == 0) {
     $('li').addClass('selected');
     $('.select').addClass('selected');
     } else {
     $('li').removeClass('selected');
     $('.select').removeClass('selected');
     }
     counter();
     add_images();
     });*/

    /*//计数
     function counter() {
     if ($('li.selected').length > 0)
     $('.send').addClass('selected');
     else
     $('.send').removeClass('selected');
     $('.send').attr('data-counter', $('li.selected').length);
     }*/
</script>