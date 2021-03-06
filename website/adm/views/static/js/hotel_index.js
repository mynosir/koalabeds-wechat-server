$(function() {
    var page = {

        // 页面初始化方法
        init: function(p) {
            var self = this,
                json = {
                    api: config.apiServer + 'hotel/get',
                    type: 'get',
                    data: {
                        actionxm: 'search',
                        page: !p ? 1 : p,
                        size: 10,
                        keyword: $('.propertyName').val()
                    }
                };
            var callback = function(res) {
                // if(res.status == 0) {

                    var idx = 1,
                        list = res['list'],
                        listTpl = '<tr><th>Serial No.</th><th>Property ID</th><th>Property Name</th><th>Property Name(chinese)</th><th>Phone</th><th>Email</th><th>Country</th><th>City</th><th>Address1</th><th>Address2</th><th>Zip</th><th>Check In Time</th><th>Check Out Time</th></tr>',
                        listTpl2 = '<tr><th>Recommend</th><th>Status</th><th>Operation</th></tr>',
                        recommend = ['Not','Waterfall','Recommend'],
                        hotelStatus = ['Unchecked','Approve','Deny'];

                    for(var i in list) {
                        var listid = (res.page-1)*res.size+parseInt(i)+1;
                        if(list[i]['name_cn'] ==undefined){
                          list[i]['name_cn'] = ''
                        }
                        listTpl += '<tr>';
                        listTpl += '<td>' + listid + '</td>';
                        listTpl += '<td>' + list[i]['propertyID'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyName'] + '</td>';
                        listTpl += '<td>' + list[i]['name_cn'] + '</td>';
                        // listTpl += '<td>' + '<img src="'+list[i]['propertyImageThumb']+'">' + '</td>';
                        listTpl += '<td>' + list[i]['propertyPhone'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyEmail'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyCountry'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyCity'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyAddress1'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyAddress2'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyZip'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyCheckInTime'] + '</td>';
                        listTpl += '<td>' + list[i]['propertyCheckOutTime'] + '</td>';
                        listTpl += '</tr>';
                        listTpl2 += '<tr>';
                        listTpl2 += '<td><select data-id='+list[i]['id']+' class="form-control selectRecommend"><option value="0">'+recommend[0]+'</option><option value="1">'+recommend[1]+'</option><option value="2">'+recommend[2]+'</option></select></td>';
                        listTpl2 += '<td><select data-id='+list[i]['id']+' class="form-control selectSection"><option value="0">'+hotelStatus[0]+'</option><option value="1">'+hotelStatus[1]+'</option><option value="2">'+hotelStatus[2]+'</option></select></td>';
                        listTpl2 += '<td><button type="button" class="btn btn-sm btn-primary js_edit" data-id="' + list[i]['id'] + '">Edit</button></td>';
                        listTpl2 += '</tr>';
                    }
                    $('.js_table').html(listTpl)
                    $('.js_table2').html(listTpl2);
                // } else {
                //     alert(res.msg);
                // }

                    for(var i in list){
                      $('.selectSection').eq(i).val(list[i]['status']);
                      $('.selectRecommend').eq(i).val(list[i]['recommend']);
                    }

                    // 处理分页
                    var pageTpl = '',
                        total = parseInt(res.total),//18
                        size = parseInt(res.size),//5
                        page = parseInt(res.page),//4
                        itemNum = Math.ceil(total / size),//4
                        itemStart = 1,
                        itemMax = 1,
                        fisrtItemCls = page==1 ? ' class="disabled"' : '',
                        lastItemCls = page==itemNum ? ' class="disabled"' : '';
                    pageTpl += '<li ' + fisrtItemCls + '><a href="javascript:void(0)" aria-label="Previous" data-page="1" class="js_pageItem"><span aria-hidden="true">&laquo;</span></a></li>';
                    if(page>4) {
                        itemStart = (page + 2) > itemNum ? itemNum - 4 : page - 2;//2
                        itemMax = (page + 2) > itemNum ? itemNum : page + 2;//4
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

        updateConfirmTip: function(id,status) {
          $('#confirmModal').find('.js_sure_update').attr('data-id', id);
            $('#confirmModal').find('.js_sure_update').attr('data-status', status);
            $('#confirmModal').modal('show');
        },

        updateConfirmTip2: function(id,recommend) {
          $('#confirmModal2').find('.js_sure_update2').attr('data-id', id);
            $('#confirmModal2').find('.js_sure_update2').attr('data-recommend', recommend);
            $('#confirmModal2').modal('show');
        },

        updateItem: function(id,status) {
          var json = {
              api: config.apiServer + 'hotel/post',
              type: 'post',
              data: {
                  actionxm: 'updateStatus',
                  id: id,
                  params: {
                    status: status
                  }
              }
          };
          var callback = function(res) {
              $('#confirmModal').modal('hide');
              alert(res.msg);
              window.location.reload();
          };
          json.callback = callback;
          Utils.requestData(json);

        },

        updateRecommend: function(id,recommend) {
          var json = {
              api: config.apiServer + 'hotel/post',
              type: 'post',
              data: {
                  actionxm: 'updateRecommend',
                  id: id,
                  params: {
                    recommend: recommend
                  }
              }
          };
          var callback = function(res) {
              $('#confirmModal2').modal('hide');
              alert(res.msg);
              window.location.reload();
          };
          json.callback = callback;
          Utils.requestData(json);

        }

    };

    $('body').delegate('.js_pageItem', 'click', function(e) {
        var p = $(e.currentTarget).data('page');
        page.init(p);
    });


    $('body').delegate('.js_edit', 'click', function(e) {
        var id = $(e.currentTarget).data('id');
        window.location.href = '/adm/hotel/edit/' + id;
    });

    $('body').delegate('.selectSection', 'change', function(e) {

      var id = $(e.currentTarget).data('id');
      var selectedStatus = e.currentTarget.options.selectedIndex;
      page.updateConfirmTip(id,selectedStatus)
    });

    $('body').delegate('.selectRecommend', 'change', function(e) {

      var id = $(e.currentTarget).data('id');
      var selectedRecommend = e.currentTarget.options.selectedIndex;
      page.updateConfirmTip2(id,selectedRecommend)
    });

    $('body').delegate('.js_sure_update', 'click', function(e) {
      var id = $(e.currentTarget).data('id');
      var status = $(e.currentTarget).data('status');
      page.updateItem(id,status);
    });

    $('body').delegate('.js_sure_update2', 'click', function(e) {
      var id = $(e.currentTarget).data('id');
      var recommend = $(e.currentTarget).data('recommend');
      page.updateRecommend(id,recommend);
    });

    $('#confirmModal').on('hide.bs.modal', function () {
      // 执行一些动作...
      window.location.reload();
    })

    $('#confirmModal2').on('hide.bs.modal', function () {
      // 执行一些动作...
      window.location.reload();
    })

    $('.js_searchFrom').submit(function(e) {
        e.preventDefault();
        // var p = $('.js_page li[class=active] a').data('page');
        page.init();
    });



    page.init();





});
