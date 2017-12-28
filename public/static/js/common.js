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
            layer.closeAll();
            if (!result.code){
                element.css('text-align', 'center').html('<tr class="layui-bg-gray"><td>~Oh!暂无数据</td><tr>');
            }

            element.html(result.data.list);

            if ($('.dialogue').attr('data-url')) {  
                $('.dialogue').bind('click', function(){
                    show_dialogue(this, current, where);
                });
            }

            layui.use('laypage', function(){
                var laypage  = layui.laypage;
                laypage.render({
                    elem: 'page' 
                    ,count: result.data.count || 0
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

    // 对话询问操作
    var show_dialogue = function(me, current, where){
        var tips = $(me).attr('data-tips');
        tips = tips || '您确定要操作？';
        layer.confirm(tips, {btn:['确定', '取消']},
            function(){
                var server_url = $(me).attr('data-url');
                $.get(server_url, {}, function(result){
                    if (!result.code) {
                        error_msg(result.msg);
                        return false;
                    }
                    success_msg(result.msg);
                    if (current || where) {
                        setTimeout(function(){get_list('', current, where);}, 2000);
                    }
                });
            },
            function(){
                return;
            },
        );
    }

    // 表单提交
    var submit_form = function(){
        form.on('submit(submitForm)', function(data){
            var default_class = data.elem.classList.value;
            var action_url    = data.form.action;
            var default_value = data.elem.innerHTML;

            data.elem.disabled        = true;
            data.elem.innerHTML       = '<i class="layui-icon layui-anim layui-anim-rotate layui-anim-loop">&#xe63d;</i>';
            data.elem.classList.value = default_class + ' layui-disabled';

            $.post(action_url, data.field, function(result){
                data.elem.innerHTML       = default_value;
                data.elem.disabled        = false;
                data.elem.classList.value = default_class

                if (!result.code) {
                    error_msg(result.msg);
                    return false;
                }

                success_msg(result.msg);

                if (result.url) {
                    window.location.href = result.url;
                }
            });
            return false;
        });
    }

    // 弹框获取html页面
    var tan_get_html = function(me){
       $(me).bind('click', function(){
            var html_url = $(this).attr('data-url');
            $.get(html_url, {}, function(result){
                // if (!result.code) {
                //     return false;
                // }

                layer.open({
                    type:1
                    ,content: '<div>sdf</div>'
                    ,area: ['500px', '300px']
                    ,anim: 0
                });
            });
        });
    }

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
    }

    // success msg
    var success_msg = function(msg){
        layer.msg(msg || '操作成功', {icon:6});
    }

    // error msg
    var error_msg = function(msg){
        layer.msg(msg || '操作失败', {icon:5});
    }




// ----------------- 普通调用 ---------------------

    if ($('.layui-table').attr('data-url')) {
        get_list('', 1, {});
    }
    if ($('#keyword')) {        
        $('#keyword').on('click', function(){
            get_list('', 1, $('.keyword').serializeObject());  
        });
    }
    // 表单提交
    submit_form();
    // 弹框获取html
    tan_get_html('.lay-get-html');
});