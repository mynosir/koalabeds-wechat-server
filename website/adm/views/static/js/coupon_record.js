$(function() {
    var page = {
        init: function(p, nickname) {
            var json = {
                api: config.apiServer + 'coupon_record/get',
                type: 'get',
                data: {
                    actionxm: 'getCouponRecord',
                    page: !p ? 1 : p,
                    size: 20,
                    nickname: nickname
                }
            };
            var callback = function(res) {
                // 处理表格数据
                var list = res['list'],
                    show = ['show','hide'],
                    used = ['unused','used'],
                    listTpl = '<tr><th>Serial No.</th><th>Wechat Nickname</th><th>Coupon Over Amount</th><th>Coupon Discount Amount</th><th>Status</th><th>Create Time</th></tr>';
                for(var i in list) {
                    var listid = (res.page-1)*res.size+parseInt(i)+1;
                    listTpl += '<tr>';
                    listTpl += '<td>' + listid + '</td>';
                    // listTpl += '<td>' + list[i]['openid'] + '</td>';
                    listTpl += '<td>' + list[i]['wx_nickname'] + '</td>';
                    listTpl += '<td>' + list[i]['totalAmount'] + '</td>';
                    listTpl += '<td>' + list[i]['discountAmount'] + '</td>';
                    listTpl += '<td>' + used[list[i]['status']] + '</td>';
                    listTpl += '<td>' + list[i]['create_time'] + '</td>';
                    listTpl += '</tr>';
                }
                $('.js_table').html(listTpl);
                // 处理分页
                var pageTpl = '',
                    total = parseInt(res.total),
                    size = parseInt(res.size),
                    page = parseInt(res.page),
                    itemNum = Math.ceil(total / size),
                    itemStart = 1,
                    itemMax = 1,
                    fisrtItemCls = page==1 ? ' class="disabled"' : '',
                    lastItemCls = page==itemNum ? ' class="disabled"' : '';
                pageTpl += '<li ' + fisrtItemCls + '><a href="javascript:void(0)" aria-label="Previous" data-page="1" class="js_pageItem"><span aria-hidden="true">&laquo;</span></a></li>';
                if(page>3) {
                    itemStart = (page + 2) > itemNum ? itemNum - 4 : page - 2;
                    itemMax = (page + 2) > itemNum ? itemNum : page + 2;
                } else {
                    itemMax = itemNum>=5 ? 5 : itemNum;
                }
                for(itemStart; itemStart<=itemMax; itemStart++) {
                    var pageItemCls = itemStart==page ? ' class="active"' : '';
                    pageTpl += '<li ' + pageItemCls + '><a href="javascript:void(0)" data-page="' + itemStart + '" class="js_pageItem">' + itemStart + '</a></li>';
                }
                pageTpl += '<li ' + lastItemCls + '><a href="javascript:void(0)" aria-label="Next" data-page="' + itemNum + '" class="js_pageItem"><span aria-hidden="true">&raquo;</span></a></li>';
                $('.js_page').html(pageTpl);
            };
            json.callback = callback;
            Utils.requestData(json);
        },
        getDetail: function(id) {
            var json = {
                api: config.apiServer + 'coupon/get',
                type: 'get',
                data: {
                    actionxm: 'getDetail',
                    id: !id ? 1 : id
                }
            };
            var callback = function(res) {
                $('.js_id').text(res.id);
                $('.js_update_totalAmount').val(res.totalAmount);
                $('.js_update_discountAmount').val(res.discountAmount);
                $('.js_update_validateDate').val(res.validateDate);
                $('.js_select').val(res.status);
                $('.js_update_sort').val(res.zorder);
            };
            json.callback = callback;
            Utils.requestData(json);
        },
        deleteConfirmTip: function(id) {
            $('#confirmModal').find('.js_sure_delete').attr('data-id', id);
            $('#confirmModal').modal('show');
        },
        deletItem: function(id) {
            var json = {
                api: config.apiServer + 'coupon/post',
                type: 'post',
                data: {
                    actionxm: 'delete',
                    id: id
                }
            };
            var callback = function(res) {
                if(res.status==0) {
                    $('#confirmModal').modal('hide');
                    alert(res.msg);
                    window.location.reload();
                } else {
                    alert(res.msg);
                }
            };
            json.callback = callback;
            Utils.requestData(json);
        }
    };
    page.init();
    $('body').delegate('.js_pageItem', 'click', function(e) {
        var p = $(e.currentTarget).data('page');
        page.init(p);
    });
    $('body').delegate('.js_edit', 'click', function(e) {
        var id = $(e.currentTarget).data('id');
        page.getDetail(id);
    });
    $('body').delegate('.js_delete', 'click', function(e){
        var id = $(e.currentTarget).data('id');
        page.deleteConfirmTip(id);
    });
    $('body').delegate('.js_sure_delete', 'click', function(e){
        var id = $(e.currentTarget).data('id');
        page.deletItem(id);
    });
    $('body').delegate('.js_addPhoto', 'click', function(e) {
        if ($('#addModal').hasClass('in')) {
            $('.js_add_photo').val($('#prevArea').attr('src'));
            $('.js_add_photo_prev').attr('src', $('#prevArea').attr('src'));
        } else {
            $('.js_photo').val($('#prevArea').attr('src'));
            $('.js_photo_prev').attr('src', $('#prevArea').attr('src'));
        }
        $('#uploadModal').modal('hide');
    });
    $('body').delegate('.js_add_saveBtn', 'click', function() {
        var totalAmount = $('.js_add_totalAmount').val(),
            discountAmount = $('.js_add_discountAmount').val(),
            validateDate = $('.js_add_validateDate').val(),
            sort = $('.js_add_sort').val(),
            status = $('.js_add_select').val();
        var json = {
                api: config.apiServer + 'coupon/post',
                type: 'post',
                data: {
                    actionxm: 'addCoupon',
                    params: {
                      totalAmount: totalAmount,
                      discountAmount: discountAmount,
                      validateDate: validateDate,
                      zorder: sort,
                      status: status
                    }
                }
            };
        var callback = function(res) {
            if(res.status==0) {
                alert(res.msg);
                window.location.reload();
            } else {
                alert(res.msg);
            }
        };
        json.callback = callback;
        Utils.requestData(json);
    });
    $('body').delegate('.js_saveBtn', 'click', function() {
        var id = $('.js_id').text(),
            totalAmount = $('.js_update_totalAmount').val(),
            discountAmount = $('.js_update_discountAmount').val(),
            validateDate = $('.js_update_validateDate').val(),
            sort = $('.js_update_sort').val(),
            status = $('.js_select').val();
        var json = {
                api: config.apiServer + 'coupon/post',
                type: 'post',
                data: {
                    actionxm: 'updateCoupon',
                    id: id,
                    params: {
                        totalAmount: totalAmount,
                        discountAmount: discountAmount,
                        validateDate: validateDate,
                        zorder: sort,
                        status: status
                    }
                }
            };
        var callback = function(res) {
            if(res.status==0) {
                alert(res.msg);
                window.location.reload();
            } else {
                alert(res.msg);
            }
        };
        json.callback = callback;
        Utils.requestData(json);
    });
    $('#photo').uploadifive({
        fileTypeDesc: 'uploadfile',
        fileTypeExts: '*.jpg;*.jpeg;*.gif;*.png',
        multi: false,
        buttonText: 'uploadfile',
        height: '25',
        width: '100',
        method: 'post',
        fileObjName: 'uploadfile',
        uploadScript: config.apiServer + 'coupon/post',
        formData: {
            'actionxm': 'upload_photo'
        },
        onUploadComplete: function(file, data, response) {
            result = $.parseJSON(data);
            if(result['status']==0) {
                $('#prevArea').attr('src', result['name']);
            } else {
                alert(result['msg']);
            }
        }
    });
    $('.js_searchFrom').submit(function(e) {
        e.preventDefault();
        // var p = $('.js_page li[class=active] a').data('page');
        var nickname = $('#search_name').val();
        console.log(nickname);
        // console.log(status);
        page.init(1, nickname);
    })
});
