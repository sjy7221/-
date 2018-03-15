<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>游戏后台管理系统</title>
    <link rel="stylesheet" href="{{url('layui/css/layui.css')}}">
    <script src="{{url("layui/layui.js")}}"></script>
    <style>
        .pagination:after {
            content: " ";
            display: block;
            clear: both;
            height: 0;
        }
        .pagination{
            zoom: 1;
            text-align: center;
            display: inline-block;
        }
        .page{
            text-align: center;
        }
        .pagination li{
            float: left;
            text-align: center;

            border: 1px solid #d7d7d7;
        }
        .pagination li a,.pagination li span{
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
        }
        .pagination .active{
            background-color: #009688;
        }
    </style>
</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo">荆门游戏管理后台</div>

        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">

                <a href="javascript:;">
                    <i class="layui-icon">&#xe612;</i> {{session('name')}}
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="{{url('admin/set/admin')}}">修改密码</a></dd>
                    <dd><a href="{{url('admin/outlogin')}}" onclick="return confirm('确定要退出吗？');">退出登入</a></dd>
                </dl>
            </li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="layui-nav layui-nav-tree"  lay-filter="test" id="list">
                <li class="layui-nav-item">
                    <a href="{{url('admin')}}"><i class="layui-icon" style="font-size: 15px; color: #fbfbfb;">&#xe68e;</i>&nbsp;&nbsp;&nbsp;&nbsp;首页</a>
                </li>
                <li class="layui-nav-item">
                    <a class="" href="javascript:;"><i class="layui-icon" style="font-size: 13px; color: #fbfbfb;">&#xe613;</i>&nbsp;&nbsp;&nbsp;&nbsp;用户管理</a>
                    <dl class="layui-nav-child">
                        <dd ><a href="{{url('admin/member/index')}}">玩家管理</a></dd>
                        <dd><a href="{{url('admin/agent/index')}}" id="aa">代理管理</a></dd>
                    </dl>
                </li>
                <li class="layui-nav-item">
                    <a href="javascript:;"><i class="layui-icon" style="font-size: 18px; color: #fbfbfb;">&#xe60a;</i>&nbsp;&nbsp;&nbsp;&nbsp;记录管理</a>
                    <dl class="layui-nav-child">
                        <dd><a href="{{url('admin/record/index_recharge')}}">充值记录</a></dd>
                        <dd><a href="{{url('admin/record/index_draw')}}">提现管理</a></dd>
                        <dd><a href="{{url('admin/record/index_hou')}}">后台充值</a></dd>
                    </dl>
                </li>
                <li class="layui-nav-item">
                    <a href="javascript:;"><i class="layui-icon" style="font-size: 15px; color: #fbfbfb;">&#xe609;</i>&nbsp;&nbsp;&nbsp;&nbsp;游戏管理</a>
                    <dl class="layui-nav-child">
                        <dd><a href="{{url('admin/goods/index')}}">充值设置</a></dd>
                        <dd><a href="{{url('admin/notice/notice')}}">公告设置</a></dd>
                        <dd><a href="{{url('admin/notice/inform')}}">通知设置</a></dd>
                        <dd><a href="{{url('admin/notice/method')}}">玩法管理</a></dd>
                        <dd><a href="{{url('admin/notice/feedback')}}">意见反馈</a></dd>
                    </dl>
                </li>
                <li class="layui-nav-item"><a href="{{url('admin/set/index')}}"><i class="layui-icon" style="font-size: 15px; color: #fbfbfb;">&#xe614;</i>&nbsp;&nbsp;&nbsp;&nbsp;系统设置</a></li>
                <li class="layui-nav-item"><a href="{{url('admin/system/log')}}"><i class="layui-icon" style="font-size: 15px; color: #fbfbfb;">&#xe629;</i>&nbsp;&nbsp;&nbsp;&nbsp;操作日志</a></li>
            </ul>
        </div>
    </div>

    <div class="layui-body">
        @yield('content')
    </div>

    <div class="layui-footer">
        <!-- 底部固定区域 -->
        © 荆门游戏管理后台
    </div>
</div>

<script>
    //JavaScript代码区域
    layui.use(['element','form','layer'], function(){
        var $ = layui.$;
        var element = layui.element;
        var form = layui.form;
         form.render();
         $("#list  a").click(function () {
             var index = $(this).attr('href');
             localStorage.setItem("list",index);
         })
        var url = localStorage.getItem("list");
         if(url == ''){
             url = window.location.href;
         }
        var pp =  $("a[href='"+url+"']").parent()[0].tagName;
        $("a[href='"+url+"']").parent().addClass('layui-this');
        if(pp=='DD'){
               $("a[href='"+url+"']").parent().parent().parent().addClass('layui-nav-itemed');
        }
        @if (count($errors) > 0)
              layer.msg("{{$errors->first()}}");
        @endif
        @if (session('success')==1)
            layer.msg("{{session('msg')}}");
        @endif
    });
</script>
</body>
</html>