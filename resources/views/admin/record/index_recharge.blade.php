@extends('admin.layouts')
@section('content')
    <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
        <legend>充值记录</legend>
        <div class="layui-field-box">
            <div class="layui-col-xs12">
                <blockquote class="layui-elem-quote layui-quote-nm">
                    <form class="layui-form" action="{{ url('admin/record/index_recharge')  }}">
                        <div class="layui-inline">
                            <label class="layui-form-label">充值单号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="sn"  class="layui-input" value="{{val('sn')}}" placeholder="充值单号">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">用户ID</label>
                            <div class="layui-input-inline">
                                <input type="number" name="id"  class="layui-input" value="{{val('id')}}" placeholder="用户ID">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">上一级ID</label>
                            <div class="layui-input-inline">
                                <input type="number" name="agent1"  class="layui-input" value="{{val('agent1')}}" placeholder="代理ID">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">上二级ID</label>
                            <div class="layui-input-inline">
                                <input type="number" name="agent2"  class="layui-input" value="{{val('agent2')}}" placeholder="代理ID">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">充值时间</label>
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input" id="time" name="time" placeholder="充值时间">
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                            <button class="layui-btn layui-btn-normal">搜索</button>
                            <button class="layui-btn layui-btn-warm" value="1" name="excel">导出Excel</button>
                        </div>

                    </form>
                </blockquote>
            </div>
            <div style="font-size: 17px;">总数：{{$num->num}}  &nbsp;&nbsp;&nbsp; 总金额数：{{$num->balance}}  &nbsp;&nbsp;&nbsp;  总一级佣金：{{$num->balance1}} &nbsp;&nbsp;&nbsp;  总二级佣金：{{$num->balance2}} </div>
            <table class="layui-table">
                <colgroup>
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="200">
                </colgroup>
                <thead>
                <tr>
                    <th>充值单号</th>
                    <th>玩家ID</th>
                    <th>上一级ID</th>
                    <th>上二级ID</th>
                    <th>钻石数</th>
                    <th>金额</th>
                    <th>实付金额</th>
                    <th>一级佣金</th>
                    <th>二级佣金</th>
                    <th>充值时间</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data as $v)
                    <tr>
                        <td >{{$v->sn}}</td>
                        <td ><a href="{{url("admin/member/index?id={$v->mid}")}}">{{$v->mid}}</a></td>
                        <td >@if($v->agent1)<a href="{{url("admin/agent/index?id={$v->agent1}")}}">{{$v->agent1}}</a>@endif</td>
                        <td >@if($v->agent2)<a href="{{url("admin/agent/index?id={$v->agent2}")}}">{{$v->agent2}}</a>@endif</td>
                        <td >{{$v->num}}</td>
                        <td >{{$v->balance}}元</td>
                        <td > <b style="color: #ff0604">{{$v->money}}</b>元</td>
                        <td >{{$v->balance1}}</td>
                        <td >{{$v->balance2}}</td>
                        <td >{{date('Y-m-d H:i:s',$v->time)}}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="10" class="page">{{$data->appends($input)->links()}}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </fieldset>
    <script>
        layui.use('laydate', function(){
            var laydate = layui.laydate;
            //日期范围
            laydate.render({
                elem: '#time'
                ,range: '--'
            });
        });
    </script>
@stop
