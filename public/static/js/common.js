/**
 * ---------------------------------------------
 * layui后台数据处理
 * ---------------------------------------------
 * Author  dorisnzy@163.com
 * ---------------------------------------------
 * Date 2017-12-20
 * ---------------------------------------------
 */

// 预加载layui模块
var module = ['jquery','form', 'upload', 'laypage', 'laydate', 'layer'];
layui.use(module, function(){
    var form      = layui.form
        ,$        = layui.$
        ,upload   = layui.upload
        ,laypage  = layui.laypage
        ,laydate  = layui.laydate
        ,layer    = layui.layer
    ;

    // 分页获取数据
    var get_list = function(config){
        var element    = $('.layui-table');
        var server_url = element.attr('data-url');
        var count      = parseInt(element.attr('data-count'));

        if (!server_url || !count)
            return false;

        laypage.render({
            elem: 'page' 
            ,count: count
            ,limit: 15
            ,jump:function(obj, first){
                var current = obj.curr;
                var limit   = obj.limit;
                var param   = {page:current, limit:limit}

                param = $.extend(param, config);

                layer.load(2, {shade: 0.1});

                $.get(server_url, param, function(result){
                    if (!result.code){
                        element.css('text-align', 'center').html('~Oh!暂无数据');
                    }

                    element.attr('data-count', result.data.count);
                    element.html(result.data.list);
                    layer.closeAll();
                    // TODO::设置配置
                    
                });
            }
        });
    };

    // 关键词搜索
    var keyword = function(){
        var form = $('.keyword');
        get_list(form.serializeObject());
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
    get_list({});
    $('#keyword').on('click', function(){keyword();});
});