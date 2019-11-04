var LODOP_PRINT_DO_STYLE = {
    "PREVIEW": 0,
    "PRINT": 1,
    "DESIGN": 2
};
;(function ($) {
    //定义构造函数
    var lodopPrintCls = function (ele, opt) {
        var instance = this;
        instance.lodopInstance = {};
        instance.defaults = {
            printData: {},
            htmlPrintMargin: {
                Top: '0',
                Left: '15',
                Right: '95%',
                Bottom: '95%',
            },
            pageInfo: {
                intOrient: 1,//打印方向及纸张类型
                // 1---纵向打印，固定纸张；
                // 2---横向打印，固定纸张；
                // 3---纵向打印，宽度固定，高度按打印内容的高度自适应；
                // 0---方向不定，由操作者自行选择或按打印机缺省设置。
                intPageWidth: 241,// 纸张宽，单位为0.1mm 譬如该参数值为45，则表示4.5mm,计量精度是0.1mm。
                intPageHeight: 280,// 固定纸张时该参数是纸张高；高度自适应时该参数是纸张底边的空白高，计量单位与纸张宽一样。
                strPageName: "silversea"
                // 纸张类型名， intPageWidth等于零时本参数才有效，具体名称参见操作系统打印服务属性中的格式定义。
                // 关键字“CreateCustomPage”会在系统内建立一个名称为“LodopCustomPage”自定义纸张类型。
            }
        };
        instance.options = $.extend({}, instance.defaults, opt);

        /**
         * 初始化上传插件
         */
        instance.init = function () {
            instance.lodopInstance = getLodop();
        };

        /**
         * 打印页面
         */
        instance.printHtml = function (createHtmlFunc, doStyle = LODOP_PRINT_DO_STYLE.PREVIEW, initLodopSets = undefined) {
            var htmlContent = new Array();
            if (initLodopSets && undefined !== initLodopSets) {
                initLodopSets();
            } else {
                instance.lodopInstance.SET_SHOW_MODE("LANDSCAPE_DEFROTATED", 1);//横向时转换为正向不需要手动旋转
                instance.lodopInstance.SET_SHOW_MODE("NP_NO_RESULT", true);
            }
            createHtmlFunc(htmlContent, instance.options.printData);

            //instance.lodopInstance.PRINT_INITA(-1, 6, 1100, 1600, "打印定位");
            //instance.lodopInstance.SET_PRINT_PAGESIZE(1, 0, 0, "A4");
            // instance.lodopInstance.SET_PRINT_PAGESIZE(
            //     instance.options.pageInfo.intOrient,
            //     instance.options.pageInfo.intPageWidth,
            //     instance.options.pageInfo.intPageHeight,
            //     instance.options.pageInfo.strPageName,
            // );
            instance.lodopInstance.ADD_PRINT_HTM(
                instance.options.htmlPrintMargin.Top,
                instance.options.htmlPrintMargin.Left,
                instance.options.htmlPrintMargin.Right,
                instance.options.htmlPrintMargin.Bottom,
                htmlContent.join('')
            );

            switch (doStyle) {
                case LODOP_PRINT_DO_STYLE.PRINT:
                    instance.lodopInstance.PRINT();
                    break;
                case LODOP_PRINT_DO_STYLE.DESIGN:
                    instance.lodopInstance.PRINT_DESIGN();
                    break;
                case LODOP_PRINT_DO_STYLE.PREVIEW:
                default:
                    instance.lodopInstance.PREVIEW();
                    break;
            }
        };
    };

    //定义插件
    $.fn.lodopPrint = function (options) {
        //创建实例
        var instance = new lodopPrintCls(this, options);
        //进行初始化操作
        instance.init();
        return instance;
    };
})(jQuery);