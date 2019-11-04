(function (window, $) {
    /**
     * Cigo数据列表插件
     */
    class CigoList {
        /**
         * 构造函数
         * @param options 默认配置项
         */
        constructor(options) {
            /**
             * 列表数据
             * @type {Array}
             */
            this.dataList = [];

            /**
             * 数据url当前值
             * @type {string}
             */
            this.dataSrcUrlCurr = '';

            /**
             * 配置
             * @type {{listItemCls: string, subListFlag: string, hasSubViewCls: string, renderItemViewFunc: CigoList.config.renderItemViewFunc, renderItemEndViewFunc: CigoList.config.renderItemEndViewFunc, pageContainerCls: string, dataSrcUrl: string, argsFunc: (function(): {}), afterRenderFunc: CigoList.config.afterRenderFunc, dataSrcType: string, listView: string, dataListEmpty: CigoList.config.dataListEmpty, dataSrcFunc: (function(): Array)}}
             */
            this.config = {
                listView: '',//列表视图对象，非css类名或id
                listItemCls: 'list-item',//列表项css类,无需加'.'
                subListFlag: 'subList',//数据列表子列表标识
                hasSubViewCls: 'has-sub',//列表项有子集css类,无需加'.'
                dataSrcType: CigoList.CIGO_LIST_DATA_SRC_TYPE.URL,//数据源类型
                dataSrcUrl: '',//数据源Url
                pageContainerCls: '',//分页容器css类,无需加'.'

                /**
                 * 数据源创建函数
                 * @returns {Array}
                 */
                dataSrcFunc: function () {
                    return [];
                },

                /**
                 * 请求参数创建函数
                 * @returns {{}}
                 */
                argsFunc: function () {
                    return {};
                },

                /**
                 * 每条数据对应视图渲染函数
                 * @param listContainer 列表容器
                 * @param itemViewList 列表项视图集合
                 * @param itemKey 数据列表项key
                 * @param dataItem 数据列表项
                 * @param level 树形结构列表项层级
                 * @param hasSubFlag 是否有子项
                 * @param hasSubCls 子项css类
                 * @param itemIndex
                 */
                renderItemViewFunc: function (listContainer, itemViewList, itemKey, dataItem, level, hasSubFlag, hasSubCls, itemIndex) {
                },

                /**
                 * 每条数据对应视图结尾渲染函数
                 * @param listContainer
                 * @param itemViewList
                 * @param itemKey
                 * @param dataItem
                 * @param level
                 * @param hasSubFlag
                 * @param hasSubCls
                 * @param itemIndex
                 */
                renderItemEndViewFunc: function (listContainer, itemViewList, itemKey, dataItem, level, hasSubFlag, hasSubCls, itemIndex) {
                },

                /**
                 * 数据为空
                 * @param listContainer
                 * @param dataList
                 */
                dataListEmpty: function (listContainer, dataList) {
                    if (dataList.length <= 0) {
                        listContainer.append(
                            '<tr class="list-item">' +
                            '   <td colspan="10000000" style="text-align: center;font-weight: 600;padding: 15px 0px;">暂无数据！</td>' +
                            '</tr>'
                        );
                    }
                },

                /**
                 * 数据列表渲染完毕回调函数
                 * @param listContainer 列表容器
                 * @param dataList 数据列表
                 */
                afterRenderFunc: function (listContainer, dataList) {
                }
            };


            //合并传入参数
            if (undefined !== options && '' !== options) {
                this.config = $.extend({}, this.config, options);//合并配置项
                this.dataSrcUrlCurr = this.config.dataSrcUrl;
            }
        }

        /**
         * 获取当前对象数据列表
         * @returns {Array}
         */
        getDataList() {
            return this.dataList;
        }

        /**
         * 设置列表视图
         * @param listView
         */
        setListView(listView = '') {
            this.config.listView = listView;
            return this;
        }

        /**
         * 设置数据源类型
         * @param dataSrcType
         */
        setDataSrcType(dataSrcType = '') {
            this.config.dataSrcType = dataSrcType;
            return this;
        }

        /**
         * 设置Url数据源
         * @param dataSrcUrl
         */
        setDataSrcUrl(dataSrcUrl = '') {
            this.dataSrcUrlCurr = this.config.dataSrcUrl = dataSrcUrl;
            return this;
        }

        /**
         * 设置数据源函数
         * @param {string} dataSrcFunc
         */
        setDataSrcFunc(dataSrcFunc = '') {
            this.config.dataSrcFunc = dataSrcFunc;
            return this;
        }

        /**
         * 设置分页容器
         * @param pageContainer
         */
        setPageContainer(pageContainer = '') {
            this.config.pageContainer = pageContainer;
            return this;
        }

        /**
         * 设置参数生成回调函数
         * @param argsFunc
         */
        setArgsFunc(argsFunc = '') {
            this.config.argsFunc = argsFunc;
            return this;
        }

        /**
         * 设置渲染列表项视图回调函数
         * @param renderItemViewFunc
         */
        setRenderItemViewFunc(renderItemViewFunc = '') {
            this.config.renderItemViewFunc = renderItemViewFunc;
            return this;
        }

        /**
         * 设置列表视图渲染完毕回调函数
         * @param afterRenderFunc
         */
        setAfterRenderFunc(afterRenderFunc = '') {
            this.config.afterRenderFunc = afterRenderFunc;
            return this;
        }

        /**
         * 渲染数据列表
         *
         * @param refreshFlag 刷新标识
         * @param bindEventFlag 绑定事件标识
         * @param manualFlag 手动操作标识
         */
        renderList(refreshFlag, bindEventFlag, manualFlag) {
            switch (this.config.dataSrcType) {
                case CigoList.CIGO_LIST_DATA_SRC_TYPE.Function: {
                    this.dataList = this.config.dataSrcFunc();
                    this.makeDataListView(bindEventFlag, manualFlag);
                }
                    break;
                case CigoList.CIGO_LIST_DATA_SRC_TYPE.URL: {
                    //获取请求参数
                    let argsData = this.config.argsFunc();
                    //Ajax请求列表数据
                    $.post((refreshFlag ? this.config.dataSrcUrl : this.dataSrcUrlCurr), argsData, (data) => {
                        if (data.code !== 1) {
                            cigoLayer.msg(data.msg, {icon: 5});
                            return;
                        }
                        this.dataList = ('dataList' in data.data) ? data.data.dataList : data.data;
                        this.makeDataListView(bindEventFlag, manualFlag, data.data.showPage);
                    });
                }
                    break;
                default:
                    cigoLayer.msg('数据源类型无效！');
                    break;
            }
        }

        /**
         * 创建列表视图
         * @param bindEventFlag
         * @param manualFlag
         * @param showPage
         */
        makeDataListView(bindEventFlag, manualFlag, showPage = '') {
            //加载列表数据
            if (!isArray(this.dataList)) {
                cigoLayer.msg('列表数据错误!');
                this.dataList = [];
                return;
            }
            //清空原列表
            let viewItemList = this.config.listView.find('.' + this.config.listItemCls);
            (viewItemList.length > 0)
                ? viewItemList.remove() : false;

            //根据数据处理列表
            let itemSubViewList = [];
            this.createListItemView(itemSubViewList, this.dataList, 0);
            this.config.listView.append(itemSubViewList.join(''));

            //分页数据
            this.makePaginationView(showPage, bindEventFlag, manualFlag);

            //绑定相关事件
            if (bindEventFlag) {
                //绑定AjaxGet事件
                this.bindAjaxGetEvent();
                //绑定快速编辑事件
                this.bindQuikEditEvetn();
            }

            //处理数据为空
            this.config.dataListEmpty(this.config.listView, this.dataList);

            //列表加载完毕后续处理
            this.config.afterRenderFunc(this.config.listView, this.dataList);

            //if (!bindEventFlag && !manualFlag)
            //cigoLayer.msg('加载完毕！', {icon: 6});
        }

        /**
         * 创建列表项视图
         * @param itemSubViewList
         * @param pDataList
         * @param level
         */
        createListItemView(itemSubViewList, pDataList, level) {
            let itemIndex = 0;
            $.each(pDataList, (itemKey, dataItem) => {
                let hasSubFlag = (this.config.subListFlag in dataItem);
                //创建当前列表项
                this.config.renderItemViewFunc(this.config.listView, itemSubViewList, itemKey, dataItem, level, hasSubFlag, this.config.hasSubViewCls, itemIndex);
                //创建子列表
                if (hasSubFlag) {
                    this.createListItemView(itemSubViewList, dataItem[this.config.subListFlag], level + 1);
                }
                this.config.renderItemEndViewFunc(this.config.listView, itemSubViewList, itemKey, dataItem, level, hasSubFlag, this.config.hasSubViewCls, itemIndex);

                itemIndex++;
            });
        }

        /**
         * 创建分页视图
         * @param showPage
         * @param bindEventFlag
         * @param manualFlag
         */
        makePaginationView(showPage = '', bindEventFlag, manualFlag) {
            if ('' === this.config.pageContainerCls || undefined === this.config.pageContainerCls || !this.config.pageContainerCls) {
                return;
            }

            let _this = this;
            $('.' + this.config.pageContainerCls).each(function () {
                if (undefined === showPage || '' === showPage || !showPage) {
                    $(this).html('');
                } else {
                    $(this).html(showPage);
                    if (bindEventFlag) {
                        //绑定分页点击事件
                        $(this).on('click', '.pagination>.pageItem>a', function () {
                            let target = $(this).attr('href') || $(this).attr('url');
                            if (target === undefined || target === '' || target === '#') {
                                return false;
                            }
                            _this.dataSrcUrlCurr = target;
                            _this.renderList(false, false, manualFlag);
                            return false;
                        });
                    }
                }
            });
        }

        /**
         * 绑定Ajax Get修改事件
         */
        bindAjaxGetEvent(view) {
            let _this = this;
            view == '' || undefined == view
                ? view = this.config.listView
                : false;
            view.on('click', '.ajax-get', function () {
                let itemView = $(this);
                if (itemView.hasClass('confirm')) {
                    let tips = itemView.data('confirm_tips');
                    (tips === undefined || '' === tips)
                        ? tips = '确认要执行该操作吗1?'
                        : false;

                    cigoLayer.confirm(tips, {
                        btn: ['确 定', '取 消'] //可以无限个按钮
                    }, function (index, layero) {
                        cigoLayer.close(index);
                        getRequest(itemView, function (data) {
                            cigoLayer.msg(data.msg, {icon: 6}, function () {
                                _this.renderList(false, false, true);
                            });
                        });
                    }, function (index) {
                        cigoLayer.close(index);
                        cigoLayer.msg('操作已取消!');
                    });
                } else {
                    getRequest(itemView, function (data) {
                        cigoLayer.msg(data.msg, {icon: 6}, function () {
                            _this.renderList(false, false, true);
                        });
                    });
                }
                return false;
            });
        }

        /**
         * 绑定快速编辑事件
         */
        bindQuikEditEvetn(view) {
            let _this = this;
            view == '' || undefined == view
                ? view = this.config.listView
                : false;
            view.on('focusout', '.cigo-edit.quik-edit', function (e) {
                let editor = $(this);
                let oldVal = editor.attr('cigo-edit-val-item-val');
                let newVal = editor.val();
                if (newVal === '') {
                    editor.val(oldVal);
                    return;
                }
                //数据不同才修改
                if (newVal !== oldVal) {
                    let ctrlTarget = editor.attr('cigo-edit-url');
                    let key = editor.attr('cigo-edit-val-item-key');
                    let argsData = {};
                    argsData['id'] = editor.attr('cigo-edit-id');
                    argsData[key] = newVal;

                    $.post(ctrlTarget, argsData, (data) => {
                        if (data.code !== 1) {
                            cigoLayer.msg(data.msg, {icon: 5});
                            return;
                        }
                        editor.attr('cigo-edit-val-item-val', newVal);
                        cigoLayer.msg(data.msg, {icon: 6}, function () {
                            _this.renderList(false, false, true);
                        });
                    });
                }
            });
        }
    }

    /**
     * CigoList 列表数据源类型
     * @type {{Function: string, URL: string}}
     */
    CigoList.CIGO_LIST_DATA_SRC_TYPE = {
        URL: 'url',//数据请求地址
        Function: 'func'//数据创建函数
    };

    //公开Cigo数据列表插件
    window.CigoList = CigoList;
})(window, jQuery);
