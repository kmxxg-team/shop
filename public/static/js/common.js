/**
 * ---------------------------------------------
 * layui后台数据处理
 * ---------------------------------------------
 * Author  dorisnzy@163.com
 * ---------------------------------------------
 * Date 2017-12-20
 * ---------------------------------------------
 */

var module = ['jquery','form', 'upload', 'laydate', 'layer'];
layui.use(module, function(){
    var form      = layui.form
        ,$        = layui.$
        ,upload   = layui.upload
        ,laydate  = layui.laydate
        ,layer    = layui.layer
    ;

    // 分页获取数据 
    var get_list = function(element, current, where){

        var element = element ? element : $('.layui-table');
        var current = current ? current : 1;

        var server_url = element.attr('data-url');
        if (!server_url) return false;

        layer.load(2, {shade: 0.1});

        var o = {};

        o.page = current;
        o      = $.extend(o, where);

        $.get(server_url, o, function(result){
            if (!result.code){
                element.css('text-align', 'center').html('~Oh!暂无数据');
            }

            layer.closeAll();
            element.html(result.data.list);

            layui.use('laypage', function(){
                var laypage  = layui.laypage;
                laypage.render({
                    elem: 'page' 
                    ,count: result.data.count
                    ,curr: current
                    ,limit: result.data.limit || 15
                    ,jump:function(obj, first){
                        current = obj.curr;
                        if (!first) {
                            get_list('', current, where);
                        }
                    }
                });
            });

        });
    }

    // 关键词搜索
    var keyword = function(){
        var form = $('.keyword');
        get_list('', 1, form.serializeObject());
    };

    // 序列化表单对象为json格式
    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

// ----------------- 普通调用 ---------------------
    get_list('', 1, {});
    $('#keyword').on('click', function(){keyword();});
});