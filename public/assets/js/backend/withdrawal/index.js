define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdrawal/index/index',
                    add_url: 'withdrawal/index/add',
                    edit_url: 'withdrawal/index/edit',
                    del_url: 'withdrawal/index/del',
                    multi_url: 'withdrawal/index/multi',
                    table: 'withdrawal',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title:'id', sortable: true},
                        {field: 'withdrawer', title:'提现人', sortable: true},
                        {field: 'charge', title:'金额', sortable: true},
                        {field: 'state', title:'状态', sortable: true,formatter:function (val) {
                                     if(val ==1){
                                         return "待审核";
                                     }
                                     else if(val ==2){
                                         return  "通过"
                                     }
                                     else if(val ==3){
                                         return  "拒接"
                                     }
                        }},
                        {field: 'withdrawal_time', title: '提现时间'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                thumb: function (value) {
                    return "<img src="+value+" width='200' height='200'>";
                },
            }
        }
    };
    return Controller;
});