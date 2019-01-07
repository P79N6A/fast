<style type="text/css">
.form-horizontal .control-label {
    display: inline-block;
    float: left;
    line-height: 30px;
    text-align: left;
    width: 160px;
}
form.form-horizontal {
    overflow: hidden;
    padding: 60px 0 10px;
    position: relative;
}
.from-search {text-align: center;}
</style>
<div class="demo-content">
<!-- 搜索页 ================================================== -->
    <div class="row">
      <div class="from-search">
        <h2>查看帐号审核进度</h2>
        <form id="searchForm" class="form-horizontal" tabindex="0" style="outline: none;">
          <div class="row">
            <div class="control-group">
              <label class="control-label">请输入注册时的手机号码：</label>
              <div class="controls">
                <input type="text" name="phone" class="control-text">
              </div>
            </div>
            <div class="form-actions span5">
              <button id="btnSearch" type="submit" class="button button-primary">搜索</button>
            </div>
          </div>
        </form>
      </div>
    </div> 
    <div class="search-grid-container">
      <div id="grid">
    </div>
    <script type="text/javascript">
        var kh_id = "<?php echo $request['kh_id']?>";
        var Grid = BUI.Grid,
          Store = BUI.Data.Store,
          columns = [
            { title: '注册帐号',width: 150,  sortable: false, dataIndex: 'user_code'},
            { title: '公司名称', width: 150, sortable: true, dataIndex: 'company_name'},
            { title: '联系人姓名', width: 150, sortable: false, dataIndex: 'user_name'},
            { title: '联系人手机号码',width: 150, sortable: true,  dataIndex: 'phone'},
            { title: '状态', width: 150,sortable: true,  dataIndex: 'status_name'},
          ];
 
        var store = new Store({
            url : '?app_act=base/custom/do_register_search&kh_id='+kh_id,
            autoLoad:true,
            pageSize:10
          }),
          grid = new Grid.Grid({
            render:'#grid',
            loadMask: true,
            forceFit:true,
            columns : columns,
            store: store,
        //    plugins : [Grid.Plugins.CheckSelection,Grid.Plugins.AutoFit], //勾选插件、自适应宽度插件
            // 底部工具栏
            tbar:{},
            // 顶部工具栏
            bbar : {
              //items 也可以在此配置
              // pagingBar:表明包含分页栏
              //pagingBar:true
            }
          });
 
        grid.render();
 
        //创建表单，表单中的日历，不需要单独初始化
        var form = new BUI.Form.HForm({
          srcNode : '#searchForm'
        }).render();
 
        form.on('beforesubmit',function(ev) {
          //序列化成对象
          var obj = form.serializeToObject();
          obj.start = 0; //返回第一页
          store.load(obj);
          return false;
        });
    </script>
<!-- script end -->
  </div>