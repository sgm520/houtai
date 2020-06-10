define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdrawal/setconfig/index',
                    edit_url: 'withdrawal/setconfig/edit',
                    multi_url: 'withdrawal/setconfig/multi',
                    table: 'profitsetconfig',
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
                        {field: 'min_amount', title:'最小提金额', sortable: true},
                        {field: 'switch_integer', title:'是否开启整数提现', sortable: true,formatter:function (val) {
                                     if(val ==1){
                                         return "开启";
                                     }
                                     else if(val ==2){
                                         return  "关闭"
                                     }
                        }},
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