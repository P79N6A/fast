var ShowModeList = {
	preview : 'preview',
	design : 'design',
	design_preview : 'design_preview',
	print : 'print'
}
var data = {
	detail : {}
};
var ITEM_ATTRS = [ 'ItemTop', // 上边距
'ItemLeft', // 左边距
'ItemWidth', // 宽度
'ItemHeight', // 高度
'ItemContent', // 内容
'ItemClass', // 对象类别
'ItemClassName', // 对象类别名
'ItemPageType', // 对象类型
'ItemName', // 对象类名
'ItemNameID', // 对象识别序号
'ItemIndex', // 对象物理序号
'ItemFontName', // 字体名称
'ItemFontSize', // 字体大小
'ItemColor', // 字体颜色
'ItemAlign', // 靠齐方式
'Itembold', // 是否粗体
'ItemItalic', // 是否斜体
'ItemUnderline', // 是否下划线
'ItemSelected', // 是否被选择
'ItemPenWidth', // 线条宽度
'ItemPenStyle', // 线条类型
'ItemHorient', // 左右位置
'ItemVorient', // 上下位置
'ItemAngle', // 旋转角度
'ItemStretch', // 图片缩放模式
'ItemReadOnly', // 打印维护内容只读
'ItemPreviewOnly', // 是否仅预览
'ItemPageIndex', // 目标输出页
'ItemNumberStartPage', // 页号起始页
'ItemStartNumberValue', // 页号起始值
'ItemLineSpacing', // 行间距
'ItemLetterSpacing', // 字间距
'ItemGroundColor', // 背景色
'ItemShowBarText', // 显示条码文字
'ItemQRCodeVersion', // QRCode版本号
'ItemTextFrame', // 边框类型
'ItemSpacePatch', // 文本尾是否补空格
'ItemAlignJustify', // 文本两端是否靠齐
'ItemTranscolor', // 图片透明背景色
'ItemTop2Offset', // 次页上边距偏移
'ItemLeft2Offset', // 次页左边距偏移
'ItemTableHeightScope', // 表格高是否含头脚
'ItemLinkedItem', // 关联对象(类名或识别序号)
];
var xlodop = {
	itemAttr : function(attrName, itemName) {
		return LODOP.GET_VALUE(attrName, itemName);
	},
	itemExist : function(name) {
		return LODOP.GET_VALUE('ItemContent', name) == "" ? false : true;
	},

	dataItemAttr : function(name, value) {
		if (data[name] == undefined) {
			data[name] = {};
		}
		if (value == undefined) {
			return data[name];
		}

		data[name] = value;
	},
	dataDetailItemAttr : function(name, value) {
		if (data.detail[name] == undefined) {
			data.detail[name] = {};
		}
		if (value == undefined) {
			return data.detail[name];
		}

		data.detail[name][attrName] = value;
	},

	// 将设计器打印项的属性更新到数据对象
	refreshAllItemAttrs : function() {
		var count = LODOP.GET_VALUE('ItemCount', 0);
		var name;
		for ( var i = 0; i < count; i++) {
			name = itemAttr('ItemName', i);
			if (data.detail[name] == undefined) {
				data.detail[name] = {};
			}
			$.map(ITEM_ATTRS, function(n) {
				console.info(n);
				data.detail[name][n] = itemAttr(n, i);
			});
		}
	},

	refreshItemAttrs : function(name) {
		if (data.detail[name] == undefined) {
			data.detail[name] = {};
		}
		$.map(ITEM_ATTRS, function(n) {
			data.detail[name][n] = xlodop.itemAttr(n, name);
		});
	},

	ADD_PRINT_TEXTA : function(strItemName, Top, Left, Width, Height,
			strContent) {
		ret = LODOP.ADD_PRINT_TEXTA(strItemName, Top, Left, Width, Height,
				strContent);
		xlodop.SET_PRINT_STYLEA(0, 'ReadOnly', 0);
		xlodop.refreshItemAttrs(strItemName);
		
		return ret;
	},

	ADD_PRINT_TEXTA_MULTI : function(strItemName, Top, Left, Width, Height,
			strContent) {
		ret = LODOP.ADD_PRINT_TEXTA(strItemName, Top, Left, Width, Height,
				strContent);
		xlodop.refreshItemAttrs(strItemName);
		
		return ret;
	},

	SET_PRINT_STYLEA_MULTI : function(varItemNameID, strStyleName,
			varStyleValue) {		
		var ret = LODOP.SET_PRINT_STYLEA(varItemNameID, strStyleName,
				varStyleValue);
		xlodop.refreshItemAttrs(varItemNameID);
		
		return ret;
	},

	SET_PRINT_STYLEA : function(varItemNameID, strStyleName, varStyleValue) {
		var ret = LODOP.SET_PRINT_STYLEA(varItemNameID, strStyleName,
				varStyleValue);
		xlodop.refreshItemAttrs(varItemNameID);
		
		return ret;
	},

	setShowMode : function(showMode, opts) {
		opts = $.extend( {copies: 1, printLimit: 1}, opts);
		if (showMode == ShowModeList.preview) {
			LODOP.SET_SHOW_MODE("HIDE_PBUTTIN_PREVIEW", 1);
			LODOP.SET_SHOW_MODE("HIDE_SBUTTIN_PREVIEW", 1);
			LODOP.SET_SHOW_MODE("HIDE_PAPER_BOARD", 0);
			LODOP.SET_SHOW_MODE("HIDE_QBUTTIN_PREVIEW", 1);
			LODOP.SET_SHOW_MODE("BKIMG_IN_PREVIEW", true);
		} else {
			if (showMode == ShowModeList.design) {
				LODOP.SET_SHOW_MODE("HIDE_QBUTTIN_PREVIEW", 1);
				LODOP.SET_SHOW_MODE("SETUP_IN_BROWSE", true);
				LODOP.SET_SHOW_MODE("DESIGN_IN_BROWSE", true);
				LODOP.SET_SHOW_MODE("SETUP_ENABLESS", "11111111011110");
				LODOP.SET_SHOW_MODE("HIDE_ABUTTIN_SETUP", 1);
				LODOP.SET_SHOW_MODE("HIDE_VBUTTIN_SETUP", 1);
				LODOP.SET_SHOW_MODE("HIDE_PBUTTIN_SETUP", 1);
				LODOP.SET_SHOW_MODE("MESSAGE_NOSET_PROPERTY", "");
				LODOP.SET_SHOW_MODE("HIDE_ITEM_LIST", false);
				LODOP.SET_SHOW_MODE("TEXT_SHOW_BORDER", 1);
			} else {
				if (showMode == ShowModeList.design_preview) {
					LODOP.SET_SHOW_MODE("HIDE_PAPER_BOARD", 0);
					LODOP.SET_SHOW_MODE("HIDE_QBUTTIN_PREVIEW", 1);
					LODOP.SET_SHOW_MODE("BKIMG_IN_PREVIEW", true);
				}
			}
		}
		if (showMode == ShowModeList.preview || showMode == ShowModeList.print) {
			if (opts.copies > 1) {
				LODOP.SET_PRINT_COPIES(opts.copies);
			}
		}
		if (opts.printLimit == 0) {
			LODOP.SET_PRINT_MODE("POS_BASEON_PAPER", true);
		}
		LODOP.SET_SHOW_MODE("SKIN_CUSTOM_COLOR", "#EFEFEF");
		LODOP.SET_SHOW_MODE("SHOW_SCALEBAR", true);
	}
};
