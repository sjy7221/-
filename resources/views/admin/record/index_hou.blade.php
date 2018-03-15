@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>后台充值记录</legend>
                <div class="layui-field-box">
                    <blockquote class="layui-elem-quote layui-quote-nm">
                        <form class="layui-form" action="">

                            <div class="layui-inline">
                                <label class="layui-form-label">时间</label>
                                <div class="layui-input-inline">
                                    <input type="text" name='time' class="layui-input" id="time" placeholder="时间" value="{{val('time')}}">
                                </div>
                            </div>
                            <div style="margin-top: 20px;">
                                <button class="layui-btn layui-btn-normal">搜索</button>
                            </div>
                        </form>
                    </blockquote>
                    <div style="font-size: 18px;">钻石总数：{{$num}}</div>
                    <table class="layui-table">
                        <colgroup>
                            <col width="50">
                            <col width="100">
                            <col width="400">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>玩家ID</th>
                            <th>数量</th>
                            <th>时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $v)
                            <tr>
                                <td><a href='{{url("admin/member/index?id={$v->mid}")}}'>{{$v->mid}}</a></td>
                                <td >{{$v->num}}</td>
                                <td >{{$v->time}}</td>
                        @endforeach
                        <tr>
                            <td colspan="4" class="page">{{$data->appends(['time'=>$time])->links()}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
        </fieldset>
        <script>
            layui.use('laydate', function(){
                var laydate = layui.laydate;
                //执行一个laydate实例
                //日期范围
                laydate.render({
                    elem: '#time'
                    ,range: '--'
                });
            });
        </script>
@stop
