define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'profit/index/index',
                    edit_url: 'profit/index/edit',
                    multi_url: 'profit/index/multi',
                    table: 'profit',
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
                        {field: 'member_reading_charge', title:'会员收益', sortable: true},
                        {field: 'member_reading_num', title:'会员阅读量', sortable: true},
                        {field: 'vip_reading_charge', title:'VIP收益', sortable: true},
                        {field: 'vip_reading_num', title:'VIP阅读量', sortable: true},
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