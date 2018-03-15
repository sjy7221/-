@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>代理管理</legend>
                <div class="layui-field-box">
                    <div class="layui-col-xs12">
                        <blockquote class="layui-elem-quote layui-quote-nm">
                           <form class="layui-form" action="{{ url('admin/agent/index') }}">

                                    <div class="layui-inline">
                                        <label class="layui-form-label">ID</label>
                                        <div class="layui-input-inline">
                                            <input type="number" name="id"  class="layui-input" value="{{val('id')}}" placeholder="ID">
                                        </div>
                                    </div>
                                   <div class="layui-inline">
                                       <label class="layui-form-label">昵称</label>
                                       <div class="layui-input-inline">
                                           <input type="text" name="nickname"  class="layui-input" value="{{val('nickname')}}" placeholder="昵称">
                                       </div>
                                   </div>
                                   <div class="layui-inline">
                                       <label class="layui-form-label">手机号</label>
                                       <div class="layui-input-inline">
                                           <input type="number" name="phone"  class="layui-input" value="{{val('phone')}}" placeholder="手机号">
                                       </div>
                                   </div>
                                   <div class="layui-inline">
                                       <label class="layui-form-label">添加时间</label>
                                       <div class="layui-input-inline">
                                           <input type="text" class="layui-input" id="time" placeholder="添加时间" name="time" value="{{val('time')}}">
                                       </div>
                                   </div>
                               <div style="margin-top: 20px;">
                                   <button class="layui-btn layui-btn-normal">搜索</button>
                               </div>
                            </form>
                        </blockquote>
                    </div>
                    <div style="font-size: 17px;">总人数：{{$num->num}}  &nbsp;&nbsp;&nbsp; 总佣金数：{{$num->sum}} </div>
                    <div class="layui-form-mid layui-word-aux">注:以下所有数字均可点击查看详细记录</div>
                    <table class="layui-table">
                        <colgroup>
                            <col width="100">
                            <col width="200">
                            <col width="150">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="200">
                            <col width="100">
                            <col width="200">
                            <col width="200">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>昵称</th>
                            <th>手机号</th>
                            <th>下级人数</th>
                            <th>账户佣金</th>
                            <th>一级佣金</th>
                            <th>二级佣金</th>
                            <th>提现金额</th>
                            <th>真实姓名</th>
                            <th>提现方式</th>
                            <th>提现账号</th>
                            <th>状态</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($members as $lev)
                            <tr>
                                <td>{{$lev->mid}}</td>
                                <td ><a href="{{url("admin/member/index?id={$lev->mid}")}}"><img src="{{$lev->headimgurl}}" alt="" width="50px;" height="50px;">{{$lev->nickname}} </a></td>
                                <td >{{$lev->phone}}</td>
                                <td >@if($lev->x_num)<a href="{{url("admin/member/index?pid={$lev->mid}")}}">{{$lev->x_num}}</a>@endif</td>
                                <td >
                                    <a href="{{url("admin/record/{$lev->mid}/agent_info")}}">{{$lev->balance}}</a>
                                </td>
                                <td>@if($lev->balance1)<a href='{{url("admin/record/index_recharge?agent1=$lev->mid")}}'>{{$lev->balance1}}</a> @else 0.00 @endif</td>
                                <td>@if($lev->balance2)<a href='{{url("admin/record/index_recharge?agent2=$lev->mid")}}'>{{$lev->balance2}}</a> @else 0.00 @endif</td>
                                <td>@if($lev->ti_money)<a href='{{url("admin/record/index_draw?id=$lev->mid")}}'>{{$lev->ti_money}}</a> @else 0.00 @endif</td>
                                <td>{{$lev->realname}}</td>
                                <td>@if($lev->ti_type==1)
                                      支付宝
                                     @elseif($lev->ti_type==2)
                                      银行卡
                                    @endif
                                </td>
                                <td>@if($lev->number){{$lev->number}}@endif</td>
                                <td >
                                    @if ($lev->status == 0)
                                        <a  href='{{ url("admin/agent/{$lev->mid}/1/edit_status")}}' title="可解除该代理的黑名单设置" onclick="return confirm('是否将该代理恢复正常?恢复后该代理能得到佣金')"><button class="layui-btn layui-btn-small layui-btn-danger">黑名单</button></a>
                                    @else
                                        <a  href='{{ url("admin/agent/{$lev->mid}/0/edit_status")}}' title="可把该账户设置成黑名单"  onclick="return confirm('是否将该代理拉黑?拉黑后该代理将不会分佣')"><button class=" layui-btn layui-btn-small">正常</button></a>
                                    @endif
                                </td>
                                <td >{{date('Y-m-d H:i:s',$lev->time)}}</td>
                                    <td>
                                        <a href="{{url("admin/agent/{$lev->mid}/del")}}"   onclick="return confirm('确定将删除代理？删除后下级所有玩家绑定的代理账号将被清空')"  title="删除代理"><button   class="layui-btn layui-btn-radius layui-btn-small layui-btn-primary">删除代理</button></a>
                                        <a href="{{url("admin/agent/{$lev->mid}/reset")}}"   onclick="return confirm('确定重置该代理的密码？重置后密码为8个8')"  title="重置密码"><button   class="layui-btn layui-btn-radius layui-btn-small layui-btn-primary">重置密码</button></a>
                                    </td>
                                </tr>
                        @endforeach
                        <tr>
                            <td colspan="14" class="page">{{$members->appends($input)->links()}}</td>
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
