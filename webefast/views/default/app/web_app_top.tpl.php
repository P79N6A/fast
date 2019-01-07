<div class="top_title"><span class="explain" style="display: none"></span><strong><?php echo $response['title']; ?></strong><span class="menu"></span>
    	<ul class="menu_pop">
        	<i class="triangle"></i>
            <li class="menu_li account"><?php echo  CTX()->get_session('user_name')?></li>
            <li class="menu_li quit"  onclick=" logout()">退出</li>
        </ul>
    </div>








