function cigoFindCharIndexFromStringByNum(srcStr, searchStr, fromIndex, num) {
    if (num <= 0) {
        return -1;
    }
    if (fromIndex > (srcStr.length - 1)) {
        return -1;
    }
    let currIndex = srcStr.indexOf(searchStr, fromIndex);
    if (currIndex <= -1) {
        return -1;
    }

    if (--num == 0) {
        return currIndex;
    } else {
        return cigoFindCharIndexFromStringByNum(srcStr, searchStr, currIndex + 1, num);
    }
}

function show_status_label(status, tipFlags) {
    if (status < tipFlags.length) {
        return tipFlags[status];
    }
    return 'unkown';
}

function show_sex_label(sex, sexFlags) {
    if (sex < sexFlags.length) {
        return sexFlags[sex];
    }
    return 'unkown';
}

function getTitleTab(level, tabItem) {
    let tab = '';
    for (let i = 0; i < level; i++) {
        tab += tabItem;
    }
    return tab;
}

function autoTipAndGo(data, successCallBackFunc, errorCallBackFunc) {
    if (data.code == 1) {
        if (undefined != successCallBackFunc && '' != successCallBackFunc) {
            successCallBackFunc = eval(successCallBackFunc);
            successCallBackFunc(data);
        } else {
            if (data.url) {
                // cigoLayer.msg(data.msg + '<br/><br/>稍后页面将自动跳转~~', {icon: 6});
                cigoLayer.msg(data.msg, {icon: 6});
            } else {
                cigoLayer.msg(data.msg, {icon: 6});
            }
            setTimeout(function () {
                if (data.url) {
                    location.href = data.url;
                } else {
                    location.reload(true);
                }
            }, 1500);
        }
    } else {
        if (undefined != errorCallBackFunc && '' != errorCallBackFunc) {
            errorCallBackFunc = eval(errorCallBackFunc);
            errorCallBackFunc(data);
        } else {
            cigoLayer.msg(data.msg, {icon: 5});
        }
    }
}

function getRequest(ctrlView, successCallBackFunc, errorCallBackFunc) {
    let target = ctrlView.attr('href') || ctrlView.attr('url');
    if (target !== undefined && target !== '' && target !== '#') {
        $.get(target, function (data) {
            autoTipAndGo(data, successCallBackFunc, errorCallBackFunc);
        });
    }
}

function ajaxGet(evt, ctrlView, successCallBackFunc, errorCallBackFunc) {
    (ctrlView === undefined) ? ctrlView = $(this) : false;

    if (ctrlView.hasClass('confirm')) {
        let tips = ctrlView.data('confirm_tips');
        (tips === undefined || '' === tips)
            ? tips = '确认要执行该操作吗?'
            : false;

        cigoLayer.confirm(tips, {
            btn: ['确 定', '取 消'] //可以无限个按钮
        }, function (index, layero) {
            cigoLayer.close(index);

            getRequest(ctrlView, successCallBackFunc, errorCallBackFunc);
        }, function (index) {
            cigoLayer.close(index);
            cigoLayer.msg('操作已取消!');
        });
    } else {
        getRequest(ctrlView, successCallBackFunc, errorCallBackFunc);
    }
    return false;
}

function formPost(evt, ctrlView, successCallBackFunc, errorCallBackFunc, beforeSubmiteFunc) {
    (ctrlView == undefined) ? ctrlView = $(this) : false;
    let target = ctrlView.attr('href') || ctrlView.attr('url');
    if (target !== undefined && target !== '' && target !== '#') {
        let formId;
        if (formId = ctrlView.attr('formId')) {
            let requestParames = "";
            if (undefined != beforeSubmiteFunc && '' != beforeSubmiteFunc) {
                beforeSubmiteFunc = eval(beforeSubmiteFunc);
                requestParames = beforeSubmiteFunc();
            }
            requestParames += $('#' + formId).find('input, select, textarea').serialize();
            $.post(target, requestParames, function (data) {
                autoTipAndGo(data, successCallBackFunc, errorCallBackFunc);
            });
        }
    }
    return false;
}

function isArray(obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
}

function compare(property, ascFlag) {
    return function (a, b) {
        let value1 = a[property];
        let value2 = b[property];
        return ascFlag
            ? (value1 - value2)
            : (value2 - value1);
    }
}

$(function () {//Layer相关
    window.cigoElement = layui.element;
    window.cigoForm = layui.form;
    window.cigoDate = layui.laydate;
    window.cigoLayer = layui.layer;
    window.cigoTable = layui.table;

    //ajax get请求
    $('.ajax-get').click(ajaxGet);
    //表单提交
    $('.form_post').click(formPost);
});


function getRandStr(lenLimit) {
    lenLimit = lenLimit || 8;
    let srcStr = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
    let maxPos = srcStr.length;
    let desStr = '';
    for (let i = 0; i < lenLimit; i++) {
        desStr += srcStr.charAt(Math.floor(Math.random() * maxPos));
    }
    return desStr;
}
