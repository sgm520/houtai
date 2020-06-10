define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'news/index/index',
                    add_url: 'news/index/add',
                    edit_url: 'news/index/edit',
                    del_url: 'news/index/del',
                    multi_url: 'news/index/multi',
                    table: 'news',
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
                        {field: 'title', title:'标题', sortable: true},
                        {field: 'subtitle', title: '副标题'},
                        {field: 'date', title: '日期'},
                        {field: 'thumb', title:'缩略图', formatter: Controller.api.formatter.thumb, operate: false},
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