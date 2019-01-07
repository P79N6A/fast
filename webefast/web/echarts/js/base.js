/**
 * 
 * @authors Your Name (you@example.org)
 * @date    2017-02-23 09:50:35
 * @version $Id$
 */

//tab切换
function tabFun(tab,content,current){
    var conNum = 0;
    $(tab).click(function(){
        $(this).addClass(current).siblings().removeClass(current);
        conNum =$(this).index();
        $(content).eq(conNum).show().siblings().hide(); 
        return false;
    }); 
}
tabFun(".orderTitle a",".order_state>div","current");