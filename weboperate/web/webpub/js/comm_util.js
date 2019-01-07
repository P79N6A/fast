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
				case 0:
					html = "<option value=''>请选择市</option>";
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
