
/**
 * 合并table
 * @param totalCols 结束列
 * @param startCols 起始列
 */
function merge(totalCols,startCols) {

	var totalCols = totalCols != ''?totalCols:0;
	var startCols = startCols != ''?startCols:0;
	var totalRows = $(".tbl").find("tr").length;
	alert(totalRows);
	for ( var i = totalCols-1; i >=startCols ; i--) {
		for ( var j = totalRows-1; j >= 0; j--) {
			startCell = $(".tbl").find("tr").eq(j).find("td").eq(i);
			targetCell = $(".tbl").find("tr").eq(j - 1).find("td").eq(i);
			startCell2 = $(".tbl").find("tr").eq(j).find("td").eq(0);
			targetCell2 = $(".tbl").find("tr").eq(j - 1).find("td").eq(0);
			//alert('i:'+i+":"+'j:'+j);

			if (startCell2.find('.hetitle').text() == targetCell2.find('.hetitle').text() && targetCell2.find('.hetitle').text() != "") {
				targetCell.attr("rowSpan", (startCell.attr("rowSpan")==undefined)?2:(eval(startCell.attr("rowSpan"))+1));
				startCell.remove();
			}

		}
	}
}

function allPrpos ( obj ) { 
	  // 用来保存所有的属性名称和值 
	  var props = "" ; 
	  // 开始遍历 
	  for ( var p in obj ){ // 方法 
	  if ( typeof ( obj[p]) == " function " ){ obj[p]() ; 
	  } else { // p 为属性名称，obj[p]为对应属性的值 
	  props += p + " = " + obj [ p ] + " /t " ; 
	  alert(p);  
      alert(obj[p].goods_code);  
      alert("text:"+obj[p].goods_code+" value:"+obj[p].record_code);  
	  } } // 最后显示所有的属性 
	  //alert ( props ) ;
	}
//字符串长度
function GetLength(str){
	var realLength = 0, len = str.length, charCode = -1;
    for (var i = 0; i < len; i++) {
        charCode = str.charCodeAt(i);
        if (charCode >= 0 && charCode <= 128) realLength += 1;
        else realLength += 2;
    }
    return realLength;
}
//区域联动
function areaChange(parent_id,level,url, callback){
	$.ajax({ type: 'POST', dataType: 'json',
		url: url, data: {parent_id: parent_id},
		success: function(data) {
			var len = data.length;
			var html = '';

			switch(level){
				case -1:
					html = "<option value=''>请选择国家</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#country").html(html);
					$("#province").html("<option value=''>请选择省</option>");
					$("#city").html("<option value=''>请选择市</option>");
					$("#district").html("<option value=''>请选择区/县</option>");
					$("#street").html("<option value=''>请选择街道</option>");
					break;
				case 0:
					html = "<option value=''>请选择省</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#province").html(html);
					$("#city").html("<option value=''>请选择市</option>");
					$("#district").html("<option value=''>请选择区/县</option>");
					$("#street").html("<option value=''>请选择街道</option>");
					break;
				case 1:
					html = "<option value=''>请选择市</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#city").html(html);
					$("#district").html("<option value=''>请选择区/县</option>");
					$("#street").html("<option value=''>请选择街道</option>");
					break;
				case 2:
					html = "<option value=''>请选择区/县</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#district").html(html);
					$("#street").html("<option value=''>请选择街道</option>");
					break;
				case 3:
					html = "<option value=''>请选择街道</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#street").html(html);
					break;
			}

			if(typeof callback == "function"){
				callback();
			}
		}
	});
}
/**
 * 获取grid的某个单元格的DOM（jquery对象）
 * @param grid BUI.grid.grid BUI grid对象
 * @param idFieldValue string 
 * @param colName string 
 * @returns
 */
function ui_getGridCell(grid, idFieldValue, colName) {
	return $(grid.findElement(grid.getItem(idFieldValue))).find('td[data-column-field="'+colName+'"]');
}
//区域联动
function areaChangeByName(name,parent_id,level,url, callback){
    $.ajax({ type: 'POST', dataType: 'json',
        url: url, data: {parent_id: parent_id},
        success: function(data) {
            var len = data.length;
            var html = '';
            switch(level){
                case -1:
                    html = "<option value=''>请选择国家</option>";
                    for (var i = 0; i < len; i++) {
                        html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
                    }
                    $("#country"+name).html(html);
                    $("#province"+name).html("<option value=''>请选择省</option>");
                    $("#city"+name).html("<option value=''>请选择市</option>");
                    $("#district"+name).html("<option value=''>请选择区/县</option>");
                    $("#street"+name).html("<option value=''>请选择街道</option>");
                    break;
                case 0:
                    html = "<option value=''>请选择省</option>";
                    for (var i = 0; i < len; i++) {
                        html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
                    }
                    $("#province"+name).html(html);
                    $("#city"+name).html("<option value=''>请选择市</option>");
                    $("#district"+name).html("<option value=''>请选择区/县</option>");
                    $("#street"+name).html("<option value=''>请选择街道</option>");
                    break;
                case 1:
                    html = "<option value=''>请选择市</option>";
                    for (var i = 0; i < len; i++) {
                        html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
                    }
                    $("#city"+name).html(html);
                    $("#district"+name).html("<option value=''>请选择区/县</option>");
                    $("#street"+name).html("<option value=''>请选择街道</option>");
                    break;
                case 2:
                    html = "<option value=''>请选择区/县</option>";
                    for (var i = 0; i < len; i++) {
                        html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
                    }
                    $("#district"+name).html(html);
                    $("#street"+name).html("<option value=''>请选择街道</option>");
                    break;
                case 3:
                    html = "<option value=''>请选择街道</option>";
                    for (var i = 0; i < len; i++) {
                        html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
                    }
                    $("#street"+name).html(html);
                    break;
            }
            if(typeof callback == "function"){
                callback();
            }
        }
    });
}