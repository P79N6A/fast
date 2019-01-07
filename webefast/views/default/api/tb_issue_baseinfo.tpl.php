<?php echo load_css('bui/imgview.css', true) ?>
<style>
    /*.baseinfo .controls input[type="text"]{width: 150px;}*/
    .baseinfo select{width: 147px;}
    .baseinfo .row{ margin-bottom:5px;}
    .baseinfo{ padding:20px;}
    .baseinfo .main-left{float: left;width: 60%}
    .baseinfo .main-right{float: right;width: 400px;height: 300px;}
    .baseinfo .img-view-controls-wrap .ui-img-view-toolbar{}
</style>
<div class="baseinfo">
    <form  class="form-horizontal" id="form_baseinfo" action="?app_act=api/tb_issue/do_<?php echo $app['scene'] ?>&tab_type=baseinfo" method="post">
        <div class="main-left">
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">销售店铺:</label>
                    <div class="controls">
                        <input type="text" id="shop_name" class="input-normal" value="" data-rules="{required: true}" disabled="disabled" readonly placeholder="销售店铺" />
                        <input type="hidden" id="shop_code" name="shop_code" />
                    </div>
                </div>
                <div class="control-group span8">
                    <label class="control-label span3">宝贝类目：</label>
                    <div class="controls " >
                        <input type="text" id="category_name" class="input-normal" value="" data-rules="{required: true}" placeholder="选择宝贝类目" readonly />
                        <img class="sear_ico" style="margin-right: 10px;" id="_select_item_img" src="assets/img/search.png" />
                        <b style="color:red"> *</b>
                        <input type="hidden" id="category_id" name="category_id" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">宝贝标题：</label>
                    <div class="controls " >
                        <input type="text" id="title" name="title" class="input-normal" value="" style="width:450px;" data-rules="{required: true}" placeholder="宝贝标题" />
                        <b style="color:red;"> *</b>
                        <input type="hidden" id="goods_code" name="goods_code" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span15">
                    <label class="control-label span3">宝贝卖点：
                    </label>
                    <div class="controls " >
                        <input type="text" name="sub_title" id="sub_title" class="input-normal" value="" style="width:450px;" placeholder="宝贝卖点" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">一口价：
                    </label>
                    <div class="controls " >
                        <input type="text" name="price" id="price" class="input-normal" value="" data-rules="{required: true}" placeholder="一口价" /><b style="color:red"> *</b>
                    </div>
                </div>
                <div class="control-group span8">
                    <label class="control-label span3">数量：
                    </label>
                    <div class="controls " >
                        <input type="text" id="quantity" class="input-normal" value="" data-rules="{required: true}" disabled="disabled" readonly placeholder="数量" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">商家编码：
                    </label>
                    <div class="controls " >
                        <input type="text" id="outer_id" class="input-normal" value="" disabled="disabled" disabled="disabled" readonly placeholder="商家编码" />
                    </div>
                </div>
                <div class="control-group span8">
                    <label class="control-label span3">商品条码：
                    </label>
                    <div class="controls " >
                        <input type="text" name="barcode" id="barcode" class="input-normal" value="" placeholder="商品条码" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">定时上架：
                    </label>
                    <div class="controls " >
                        <select name="shelf_time" id="shelf_time" class="input-normal">
                            <option value="stock" selected="selected">进仓库</option>
                            <option value="now">立刻上架</option>
                            <option value="setted">定时上架</option>
                        </select>
                    </div>
                </div>
                <!--                <div class="control-group span8">
                                    <input type="text" name="timing" id="timing" class="calendar calendar-time" value=""/>
                                </div>-->
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">所在地：
                    </label>
                    <div class="controls " >
                        <input type="text" name="location" id="location" class="input-normal" value="" data-rules="{required: true}" placeholder="所在地" />
                        <img class="sear_ico" style="margin-right: 10px;" id="_select_location_img" src="assets/img/search.png" />
                        <b style="color:red"> *</b>
                    </div>
                </div>
                <div class="control-group span8">
                    <label class="control-label span3">运费模板：
                    </label>
                    <div class="controls " >
                        <select name="postage_template" id="postage_template" class="input-normal" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php
                            $temp = '';
                            foreach ($response['postage_template'] as $val) {
                                $temp .="<option value='{$val['template_id']}'>{$val['name']}</option>";
                            }
                            echo $temp;
                            ?>

                        </select>
                        <!--                    <button class="button button-small">编辑</button>
                                            <button class="button button-small">刷新</button>-->
                        <b style="color:red"> *</b>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">物流重量：
                    </label>
                    <div class="controls " >
                        <input type="text" name="weight" id="weight" class="input-normal" value="" placeholder="物流重量/千克" />
                    </div>
                </div>
                <div class="control-group span8">
                    <label class="control-label span3">物流体积：
                    </label>
                    <div class="controls " >
                        <input type="text" name="cubage" id="cubage" class="input-normal" value="" placeholder="物流体积/立方米" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label span3">选择图片：
                    </label>
                    <div class="controls " >
                        <button type="button" id="btn_select_img" class="button button-primary" onclick="_select_img()">选择图片</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-right">
            <div class="clearfix">
                <div id="imgviewWrap"></div>
            </div>
        </div>
        <div style="clear: both;"></div>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        var imgView;
        var imgList = [];
        $(function () {
            $('#shelf_time').on('change', function () {
                if ($(this).val() == 'setted') {

                } else {

                }
            });

            $('#shelf_time').trigger('change');
            //选择类目
            $('#category_name,#_select_item_img').on('click', function () {
                _select_itemcats();
            });
            //选择所在地
            $('#location,#_select_location_img').on('click', function () {
                _select_location();
            });

            //如果编辑，加载数据
            if (scene == 'edit') {
                var baseinfo = <?php echo $response['baseinfo']; ?>;
                $.each(baseinfo, function (key, val) {
                    if (key == 'shelf_time' || key == 'postage_template') {
                        var obj = $("#form_baseinfo select[name='" + key + "']");
                        if (obj != 'undefined') {
                            obj.find('option').removeAttr('selected');
                            obj.find("option[value='" + val + "']").attr('selected', 'selected');
                            if (key == 'shelf_time') {
                                $('#shelf_time').trigger('change');
                            }
                        }
                        return true;
                    }
                    if (key == 'pic_url' && val != null) {
                        $.each(val, function (k, v) {
                            var img_obj = {};
                            img_obj.src = v;
                            img_obj.miniSrc = v;
                            imgList.push(img_obj);
                        });
                        return true;
                    }
                    var obj = $("#form_baseinfo input[id='" + key + "']");
                    if (obj != 'undefined') {
                        obj.val(val);
                    }
                });
            }
        });
        BUI.use('bui/form', function (Form) {
            new Form.HForm({
                srcNode: '#form_baseinfo',
                submitType: 'ajax',
                callback: function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'success');
                        window.location = '?app_act=api/tb_issue/detail&app_scene=edit&shop_code=' + shop_code + '&goods_code=' + $('#goods_code').val() + '&category_id=' + $('#category_id').val();
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }
            }).render();
        });

        //选择宝贝类目
        function _select_itemcats() {
            new ESUI.PopWindow('?app_act=api/tb_issue/select_itemcats', {
                title: "选择宝贝类目",
                width: 600,
                height: 500,
                buttons: [
                    {
                        text: '确定',
                        elCls: 'button button-primary',
                        handler: function () {
                            var item = parent.additem;
                            if (item.leaf == false) {
                                alert('选择类目无效', 'error');
                            } else {
                                $('#category_name').val(item.text);
                                $('#category_id').val(item.id);
                                auto_enter('#category_name');
                                this.close();
                            }
                        }
                    }, {
                        text: '关闭',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ],
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        }

        //选择所在地
        function _select_location() {
            new ESUI.PopWindow('?app_act=api/tb_issue/select_location', {
                title: "选择所在地",
                width: 400,
                height: 500,
                buttons: [
                    {
                        text: '确定',
                        elCls: 'button button-primary',
                        handler: function () {
                            var item = parent.addlocation;
                            if (item.leaf == false) {
                                alert('选择的所在地无效', 'error');
                            } else {
                                $('#location').val(item.record.parent + '/' + item.text);
                                auto_enter('#location');
                                this.close();
                            }
                        }
                    }, {
                        text: '关闭',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ],
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        }

        BUI.use(['bui/imgview'], function (ImgView) {
            imgView = new ImgView.ImgView({
                render: "#imgviewWrap",
                width: 400,
                height: 300,
                imgList: imgList,
                imgNum: 0, // 默认取第几张图片，默认为0，取第一张
                autoRender: false, // 是否自动渲染,默认为false
                selectedchange: function (num, imgSrc) {
                },
                commands: [{
                        cmd: 'zoom',
                        text: '放大'
                    }, {
                        cmd: 'micrify',
                        text: '缩小'
                    }]
            });
            // autoRender如果为true就代表自动渲染。
            imgView.render();

            jQuery(window).resize(function () {
                imgView.set('width', jQuery(window).width());
                imgView.set('height', jQuery(window).height() - 50);
            });
        });

        //选择图片
        function _select_img() {
            new ESUI.PopWindow('?app_act=api/tb_issue/select_img&shop_code=' + shop_code, {
                title: "选择图片",
                width: '60%',
                height: 600,
                buttons: [
                    {
                        text: '确定',
                        elCls: 'button button-primary',
                        handler: function () {
                            imgView.set('imgList', parent.images);
                            add_images(parent.images);
                            this.close();
                        }
                    }, {
                        text: '关闭',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ],
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        }

        function add_images(_data) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('api/tb_issue/save_images'); ?>',
                data: {shop_code: shop_code, goods_code: goods_code, tab_type: 'baseinfo', pic_url: _data},
                success: function (data) {
                    if (data.status != 1) {
                        BUI.Message.Alert('图片更新成功', 'error');
                    } else {
                        BUI.Message.Alert(data.message, 'success');
                    }
                }
            });
        }

        //模拟回车事件，触发文本框规则检查
        function auto_enter(_id) {
            var e = jQuery.Event("keyup");//模拟一个键盘事件
            e.keyCode = 13;//keyCode=13是回车
            $(_id).trigger(e);
        }
    </script>
</div>