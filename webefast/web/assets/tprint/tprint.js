var jprint = {
    record: {},
    detail: {},
    templateobj: '',
    templateid: '',
    is_init:0,
    init: function(param,template_conf) {
        var _self = this;
        _self = $.extend(_self, param);

        setTimeout(function() {
          //  _self.templateobj = $(document.getElementById(_self.templateid).contentWindow.document.body);
            _self.templateobj = $(document.getElementById(_self.templateid).contentWindow.document.documentElement); 
       
            _self.load_group();
            _self.load_row();
            _self.load_column();
            if($.trim(_self.templateobj.html())==''){
                _self.is_init = 1;
                _self.load_template(template_conf);
                _self.is_init = 0;
            }
        },500);
        _self.init_label_control();
    },
    load_template: function(groups) {
        var _self = this;
       
        _self.templateobj.append('<div id="report"></div>');
        var pid = 'report';
        $.each(groups, function(id, group) {
            var cls = '';
            if (typeof group.class !== "undefined") {
                cls = group.class;
            }
            _self.add_group(id, '', group.title,pid);
            if (typeof group.child !== "undefined") {
                if (group.child.type === 'table') {
                   _self.set_group_tip(_self.templateobj.find('#' + id),1);
                    
                    _self.add_table(id, group.child.colume_num);
                    _self.set_attr(id, 'type', 'table');
                  
                } else {
                    _self.set_group_tip(_self.templateobj.find('#' + id));
                    _self.add_row(id);
                }
            } else {
                    _self.set_group_tip(_self.templateobj.find('#' + id));
                    _self.add_row(id);
                }

        });
    },
    get_lable: function() {
        var _self = this;
        var label_id = $('#label_id').val();
        if (label_id === '') {
            alert('选择编辑内容');
            return '';
        }
        return _self.templateobj.find('#' + label_id);
    },
    load_group: function() {
        var _self = this;

        var groups = _self.templateobj.find('.group');
        $.each(groups, function(i, item) {
            var group = $(item);
     
            group.addClass('dev_boder');
            if (group.attr('type') === 'table') {
                _self.set_group_tip(group,1);
                _self.add_edit_tip(group.attr('id'));
                var tid = group.find('table').attr('id');
                 _self.set_index(tid,'row');             
            } else {
                _self.set_group_tip(group);
            }
        });
    },
    load_row: function() {
        var _self = this;
        var rows = _self.templateobj.find('.row');
    
        $.each(rows, function(i, row) {
            var id = $(row).attr('id');
            _self.add_edit_tip(id, '');
            _self.set_index(id,'row');
        });
    },
    set_attr: function(id, key, value) {
                var _self = this;
        _self.templateobj.find('#' + id).attr(key, value);
    },
    load_column: function() {
        var _self = this;
        var columns = _self.templateobj.find('.column');
        $.each(columns, function(i, column) {
            
            var id = $(column).attr('id');
               _self.templateobj.find('#' + id).click(function(){
                _self.edit_column(id);
            });
              _self.set_index(id,'column');
        });
        var td_columns = _self.templateobj.find('.td_column');
        $.each(td_columns, function(i, column) {
            var id = $(column).attr('id');
               _self.templateobj.find('#' + id).click(function(){
                _self.edit_column(id);
            });
              _self.set_index(id,'column');
        });
        
        
    },
    set_group_tip: function(group,is_table) {
        var _self = this;
        var tpl = '<div class="group_row"><div class="group_title">' + group.attr('title') + '</div><div class="group_add"></div></div>';
        group.prepend(tpl);
       if (typeof is_table === "undefined") {
           is_table = 0;
        }
        if(is_table==0){
            var id = group.attr('id');
            group.find('.group_add').click(function() {
                _self.add_row(id, '', '', '内容');
            });
        }else{
            group.find('.group_add').remove();
        }
    },
    init_label_control: function() {
        var _self = this;

        $.each(_self.record, function(key, name) {
            $('#source_record').append('<span class="span2">' + key + '</span>');
        });
        $.each(_self.detail, function(key, name) {
            $('#source_detail').append('<span class="span2">' + key + '</span>');
        });
        BUI.use('bui/tooltip', function(Tooltip) {
            var t = new Tooltip.Tip({
                align: {
                    node: '#show_record'
                },
                alignType: 'bottom-right',
                offset: 10,
                triggerEvent: 'click',
                autoHideType: 'click',
                elCls: 'tips tips-no-icon',
                title: '',
                titleTpl: $('#data_source').html()
            });
            t.render();
            $('#show_record').on('click', function() {
                t.show();
            });
        });
        $('#source_record span').click(function() {
            var name = "{@" + $(this).text() + "}";
            var label = _self.get_lable();
            if (label !== '') {
                label.text(name);
                $('#label_text').val(name);
            }

        });
        $('#source_detail span').click(function() {
            var name = "{#" + $(this).text() + "}";
            var label = _self.get_lable();  
            if (label !== '') {
                label.text(name);
                $('#label_text').val(name);
            }
        });
        var size = 8;
        while (size < 42) {
            $('#label_font_size').append('<option value="' + size + '" style="font-size:' + size + 'px;" >' + size + '</option>');
            size = size + 2;
        }
        $("#label_font_size option[value='12']").attr('selected',true);
        $('#label_font_size').change(function() {
            var size = $(this).val();
            var label = _self.get_lable();
            if (label !== '') {
                label.css('font-size', size + "px");
            }
        });

        $('#label_position li').click(function() {
            var label = _self.get_lable();
            if (label !== '') {
                $('#label_position li').removeClass('active');
                $(this).addClass('active');
                var position = $(this).css('text-align');
        
                label.css('text-align', position);
            }
        });
        $('#label_border').click(function() {
            var label = _self.get_lable();
            if (label !== '') {
                if ($(this).attr('checked')) {
                    label.addClass('column_border');
                } else {
                    label.removeClass('column_border');
                }
            }
        });
        $('#label_width').on('input', function(e) {
            var label = _self.get_lable();
            if (label !== '') {
                _self.set_set_label_width(_self);
            }
        });
             $('#label_height').on('input', function(e) {
            var label = _self.get_lable();
            if (label !== '') {
                  _self.set_set_label_height(_self);
            }
        });
        $('#label_text').on('input', function(e) {
            var label = _self.get_lable();
            if (label !== '') {
              _self.set_label_content(_self);
            }

        });
           $('#text_type').change( function() {
              var label = _self.get_lable();
            if (label !== '') {
               _self.set_label_content(_self);
            }

        }); 
            $('#paper_width').on('input', function(e) {
             var width = $(this).val()+'mm';
            _self.templateobj.find('body').width(width);
            $('#'+_self.templateid).width(width);
         
            

        });   
          $('#paper_height').on('input', function(e) {
          var height = $(this).val()+'mm';
          _self.templateobj.find('body').height(height);
            $('#'+_self.templateid).height(height);

        });
        
     
    },
    set_label_content:function(_self){
        var type = $('#text_type').val();
        var label = _self.get_lable();
              if(type==0){
                    var text = $('#label_text').val();
                    label.text(text);
               }else if(type==1){
                    var text = $('#label_text').val();
                   var label_height = $('#label_height').val();
                     var label_width = $('#label_width').val();
                   var barcode = $('<img src="assets/tprint/picon/barcode.png" type="1" class="barcode"  style="height:'+label_height+'px;width:'+label_width+'px;"  title="'+text+'" >');
                    label.html('');
                    label.append(barcode);
                 
//                    barcode.click(function(){
//                        $(this).parent().click();
//                    });
               }
    },
     set_set_label_width:function(_self){
                var label = _self.get_lable();
                var label_width = $('#label_width').val()+'px';
                
                if(label.parent().hasClass('td_title')||label.parent().hasClass('td_detail')){
                    var id =  label.attr('id');
                     id = id.replace('column_th_',""); 
                     id = id.replace('column_td_',""); 
                      _self.templateobj.find('#column_th_'+id).parent().css('width', label_width);
                      _self.templateobj.find('#column_th_'+id).css('width', label_width);
                      _self.templateobj.find('#column_td_'+id).parent().css('width', label_width);
                      _self.templateobj.find('#column_td_'+id).css('width', label_width);
                }else{
                    label.css('width', label_width);
                }
          
                  if($('#text_type').val()==1){
                     label.find('img').css('wlabel_widthidth', label_width);
                 }
                
     
         
 
     },
     set_set_label_height:function(_self){
                var label = _self.get_lable();
             var label_height = $('#label_height').val()+'px';
                label.css('height', label_height);
                label.css('line-height', label_height);
                 if(!(label.parent().hasClass('td_title')||label.parent().hasClass('td_detail'))){
                    label.parent().css('height', label_height);
                }     
                if($('#text_type').val()==1){
                     label.find('img').css('height',label_height);
                 }
     },
    load_label: function(id) {
        var _self = this;
        $('#label_id').val(id);
        var label = _self.get_lable();
        if (label == '') {
            return;
        }
        _self.templateobj.find('.column_edit').removeClass('column_edit');
        label.addClass('column_edit');

        $('#label_id').val(id);
        var text = label.text();
        var text_type = 0;
        if(label.find('img').length>0){
            var img =  label.find('img');
               text_type = img.attr('type');
               text  = img.attr('title');
            
        }
        $('#label_text').val(text); 
        $('#text_type').find('option[value="' + text_type + '"]').attr('selected', true);   
 
        var label_width = label.css('width');
        label_width = label_width.replace('px', '');
        $('#label_width').val(label_width);
         var label_height = label.parent().css('height');
        label_height = label_height.replace('px', '');
        $('#label_height').val(label_height);
        var label_position = label.css('text-align');
        $('#label_position li.active').removeClass('active');
        label_position = (label_position === '') ? 'center' : label_position;
        $('#label_position li[value="' + label_position + '"]').addClass('active');

        var label_font_size = label.css('font-size');
           label_font_size = label_font_size.replace('px', '');
      $('#label_font_size').find('option[value="' + label_font_size + '"]').attr('selected', true);
          //    $("#label_font_size option[value='"+label_font_size+"']").attr('selected',true);
    },
    show_edit_control: function(title, tpl, callback) {
        var _self = this;
        var itemobj;
        BUI.use('bui/overlay', function(Overlay) {
            itemobj = new Overlay.Dialog({
                title: title,
                width: 500,
                height: 300,
                mask: false,
                buttons: [
                    {
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function() {
                            if (typeof callback == "function") {
                                callback();
                            }
                            this.close();
                            this.remove();
                        }
                    }, {
                        text: '关闭',
                        elCls: 'button',
                        handler: function() {
                            this.close();
                            this.remove();
                        }
                    }
                ],
                bodyContent: tpl
            });
            itemobj.show();

        });
    },
    row_index: 0,
    column_index: 0,
    add_group: function(id, cls, title, pid) {
        var _self = this;
        var p_obj = _self.templateobj;

        if (typeof pid !== "undefined" && pid !== '') {
            p_obj = _self.templateobj.find('#' + pid);
        }

        if (_self.templateobj.find('#' + id).length === 0) {
            var html = '<div id="' + id + '" class="group dev_boder ' + cls + '" title="' + title + '"></div>';

            p_obj.append(html);


        } else {
            return false;
        }
        return true;
    },
    init_group: function(group, pid) {
        var _self = this;

        $.each(group, function(i, obj) {
            _self.add_group(obj.id, obj.cls, obj.title, pid);

        });

    },
    add_group_row: function(id, cls, text, pid) {
        var _self = this;
        var status = _self.add_group(id, cls, text, pid);
        if (status === false) {
            _self.add_row(id, '', '', text);
        }

    },
    add_row: function(pid, id, cls, text) {
        var _self = this;
        if (typeof cls === "undefined" || cls === '') {
            cls = 'row border';
        } else {
            cls = 'row border ' + cls;
        }
        if (typeof id === "undefined" || id === '') {
            id = 'row_' + _self.row_index;
            _self.row_index++;
        }

        var html = '<div id="' + id + '" class="' + cls + '"></div>';
        _self.templateobj.find('#' + pid).append(html);

       if( _self.is_init==1){
         _self.templateobj.find('#' + id).attr('nodel',1);
       }

        _self.add_column(id, text);
        _self.add_edit_tip(id);
    },
    get_column_num: function(id) {
        var _self = this;
        var num = 0;
        if(_self.templateobj.find('#' + id).attr('type')==='table'){
            num = _self.templateobj.find('#' + id + " .td_title").length;
        }else{
            num = _self.templateobj.find('#' + id + " .column").length;
        }

        return num;
    },
    add_edit_tip: function(id, is_row) {
        var _self = this;
        var html = '<div class="row_edit edit_tip"></div>';
        var obj = $(html);
        obj.append('<div class="edit"></div>');
        obj.find('.edit').click(function() {
            var column_num = _self.get_column_num(id);
           var tpl =  '<input id="' + id + '_num" type="text" value="' + column_num + '"  />' ;
            _self.show_edit_control('设置列数量', tpl, function() {
                var new_num = $('#' + id + '_num').val();
                _self.set_column(id, column_num, new_num);
                $('#' + id + '_num').remove();
            });
        });

        var nodel = _self.templateobj.find('#' + id).attr('nodel');
        if (typeof nodel === "undefined"){
            obj.append('<div class="del"></div>');

            obj.find('.del').click(function() {
                var p = _self.templateobj.find('#' + id).parent();
                _self.templateobj.find('#' + id).remove();
            });
        }
        _self.templateobj.find('#' + id).prepend(obj);
        _self.templateobj.find('#' + id).mouseenter(
                function() {
                    $(this).find('.edit_tip').show();
                }
        ).mouseleave(
                function() {
                    $(this).find('.edit_tip').hide();
                }
        );


    },
    add_column: function(pid, text) {
        var _self = this;
        if (typeof text === "undefined") {
            text = '设置内容';
        }
        var id = 'column_' + _self.column_index;
        var html = '<div id="' + id + '" class="column">' + text + '</div>';
        _self.templateobj.find('#' + pid).append(html);
        _self.templateobj.find('#' + id).click(function() {
            _self.edit_column(id);
        });
       var  label_height =  _self.templateobj.find('#' + id).parent().css('height');

            _self.templateobj.find('#' + id).css('height', label_height);
             _self.templateobj.find('#' + id).css('line-height', label_height);

        
        _self.column_index++;
    },
    add_table: function(id, column_num) {
        var _self = this;
          var   tid = 'row_' + _self.row_index;
            _self.row_index++;
        
        var table_html = '<table id="'+tid+'" class="table" cellspacing="0" border="0" cellpadding="0"><tr></tr><tr></tr></table>';
        var table = _self.templateobj.find('#' + id).append(table_html);
        if( _self.is_init==1){
            _self.templateobj.find('#' + id).attr('nodel',1);
        }
        _self.add_edit_tip(id);
        
        for(var i=0;i<5;i++){
          _self.add_td(id);
        }
    },
    add_td: function(tid) {
        var _self = this;

        var table = _self.templateobj.find('#' + tid );
        var id1 = 'column_th_' + _self.column_index;
        var id2 = 'column_td_' + _self.column_index;
        var td1 = '<td  class="td_title"><div class="td_column" id="' + id1 + '">标题</div></td>';
        var td2 = '<td  class="td_detail"><div class="td_column" id="' + id2 + '">明细内容</div></td>';
        table.find('tr').eq(0).append(td1);
        table.find('tr').eq(1).append(td2);
        _self.templateobj.find('#' + id1).click(function() {
            _self.edit_column(id1);
        });
        _self.templateobj.find('#' + id2).click(function() {
            _self.edit_column(id2);
        });
        _self.column_index++;
    },
    edit_column: function(id) {
        var _self = this;
        _self.load_label(id);
    },
    set_column: function(id, num, new_num) {
        var _self = this;
        var is_table = _self.templateobj.find('#' + id + " table");
        if (is_table.length === 0) {
            if (num > new_num) {
                for (var i = num - 1; i >= new_num; i--) {
                    _self.templateobj.find('#' + id + " .column").eq(i).remove();
                }
            } else {
                for (var i = num; i < new_num; i++) {
                    _self.add_column(id);
                }
            }
        } else {
            if (num > new_num) {
                var table = _self.templateobj.find('#' + id + " table");
                for (var i = num - 1; i >= new_num; i--) {
                    table.find('tr').eq(0).find("td").eq(i).remove();
                    table.find('tr').eq(1).find("td").eq(i).remove();
                }
            } else {
                for (var i = num; i < new_num; i++) {
                    _self.add_td(id);

                }
            }
        }

    },
    max:function(m,n){
        if(m>n){
            return m;
        }else{
            return n;
        }
    },
    set_index:function(id,type){
              var _self = this;
        if(type=='row'){
            id = id.replace('table_', '');    
            id = id.replace('row_', ''); 
           var   num = parseInt(id);
             _self.row_index =  _self.max(id,_self.row_index);
              _self.row_index++;
        }else{
             id = id.replace('column_th_', '');    
            id = id.replace('column_td_', ''); 
              id = id.replace('column_', ''); 
            var  num = parseInt(id);
             _self.column_index =  _self.max(id,_self.column_index);
               _self.column_index++;
        }
    },
    get_temp_data: function() {
         var _self = this;
       var html = _self.templateobj.find('body').html();

       var htmlobj =  $("<body></body>").append(html);
        
                htmlobj.find('.row_edit').remove();
                htmlobj.find('.group_row').remove();
                htmlobj.find('.dev_boder').removeClass('dev_boder');
                htmlobj.find('.column_edit').removeClass('column_edit');
        var data = {};
        var detail_body =  htmlobj.find('.table tr').eq(1).html();

         data.detail_body ='<tr>'+detail_body+'</tr>';
         var detail_html = htmlobj.find('.table').html();
         var detail_obj =  $(detail_html);
             detail_obj.find('tr').eq(1).remove();
         var detail_html_r = detail_obj.html()+"<!--detail_list-->";
        data.template_body =  $.trim(htmlobj.html());
        data.template_body  = data.template_body.replace(detail_html,detail_html_r);
        return data;
          
    },
    get_temp_data_clothing: function() {
        var _self = this;
        var html = _self.templateobj.find('body').html();

        var htmlobj =  $("<body></body>").append(html);

        htmlobj.find('.row_edit').remove();
        htmlobj.find('.group_row').remove();
        htmlobj.find('.dev_boder').removeClass('dev_boder');
        htmlobj.find('.column_edit').removeClass('column_edit');
        var data = {};
        var detail_html = htmlobj.find('.table').parent().html();
        var detail_obj =  $(detail_html);
        detail_obj.find('tr').eq(1).remove();
        var detail_html_r = "{#表格}";
        data.template_body =  $.trim(htmlobj.html());
        data.template_body  = data.template_body.replace(detail_html,detail_html_r);
        return data;

    },
    get_temp_html: function() {
         var _self = this;
       var frame = $(document.getElementById(_self.templateid).contentWindow.document.documentElement); 
       var html = $(frame).prop('outerHTML');
            var iframe = $('<iframe id="content" width="0" height="0" src=""></iframe>').appendTo('body');
                document.getElementById('content').contentWindow.document.write(html);
                 htmlobj = $(document.getElementById('content').contentWindow.document.documentElement); 
                htmlobj.find('.row_edit').remove();
                htmlobj.find('.group_row').remove();
                htmlobj.find('.dev_boder').removeClass('dev_boder');
                htmlobj.find('.column_edit').removeClass('column_edit');
                html = htmlobj.prop('outerHTML');

                iframe.remove();
                return html;
          
    }
};




