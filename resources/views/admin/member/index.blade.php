@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>用户管理</legend>
                <div class="layui-field-box">
                    <div class="layui-col-xs12">
                        <blockquote class="layui-elem-quote layui-quote-nm">
                           <form class="layui-form" action="{{ url('admin/member/index') }}">

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
                                       <label class="layui-form-label">上级ID</label>
                                       <div class="layui-input-inline">
                                           <input type="number" name="pid" value="{{val('pid')}}"  class="layui-input" placeholder="上级ID">
                                       </div>
                                   </div>

                                   <div class="layui-inline">
                                           <label class="layui-form-label">是否代理</label>
                                           <div class="layui-input-inline">
                                               <select name="is_agency">
                                                   <option value="">是否代理</option>
                                                   <option value="1" @if(val('is_agency')==1) selected @endif>是</option>
                                                   <option value="2" @if(val('is_agency')=='2') selected @endif>否</option>
                                               </select>
                                           </div>
                                   </div>
                                   <div class="layui-inline">
                                       <label class="layui-form-label">注册时间</label>
                                       <div class="layui-input-inline">
                                           <input type="text" name='time' class="layui-input" id="time" placeholder="注册时间" value="{{val('time')}}">
                                       </div>
                                   </div>
                               <div style="margin-top: 20px;">
                                   <button class="layui-btn layui-btn-normal">搜索</button>
                               </div>

                            </form>
                        </blockquote>
                    </div>
                    <div style="font-size: 17px;">总人数：{{$num->num}}  &nbsp;&nbsp;&nbsp; 总钻石数：{{$num->sum}}</div>
                    <table class="layui-table">
                        <colgroup>
                            <col width="100">
                            <col width="200">
                            <col width="150">
                            <col width="150">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="200">
                            <col width="100">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>昵称</th>
                            <th>手机号</th>
                            <th>上级ID</th>
                            <th>是否代理</th>
                            <th>钻石</th>
                            <th>胜利次数</th>
                            <th>总次数</th>
                            <th>胜率</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($members as $lev)
                            <tr>
                                <td>{{$lev->id}}</td>
                                <td ><img src="{{$lev->headimgurl}}" alt="" width="50px;" height="50px;">{{$lev->nickname}}</td>
                                <td >@if($lev->phone == '0') 未绑定 @else {{$lev->phone}} @endif</td>
                                <td >@if($lev->pid == '0') 未绑定 @else {{$lev->pid}} @endif</td>
                                <td >@if($lev->is_agency == '0') 否 @else <a href='{{url("admin/agent/index?id={$lev->id}")}}'><b style="color: #1237ff"> 是 </b></a> @endif</td>

                                <td >
                                    <a href="{{url("admin/record/{$lev->id}/member_info")}}"><button class="layui-btn layui-btn-small layui-btn-primary">{{$lev->num}}</button></a>
                                </td>
                                <td >{{$lev->win}}</td>
                                <td >{{$lev->sum}}</td>
                                <td >{{$lev->wins}}%</td>
                                <td >
                                    @if ($lev->is_black == 1)

                                        <a  href="{{ url('admin/member/refuse',[$lev->id])}}" title="可解除该用户的黑名单设置" onclick="return confirm('是否将该用户恢复正常?')"><button class="layui-btn layui-btn-small layui-btn-danger">黑名单</button></a>
                                    @else
                                        <a  href="{{ url('admin/member/pass',[$lev->id])}}" title="可把该账户设置成黑名单" onclick="return confirm('是否将该用户拉黑?')"><button class=" layui-btn layui-btn-small">正常</button></a>
                                    @endif
                                </td>
                                <td >{{date('Y-m-d H:i:s',$lev->create_time)}}</td>
                                    <td>
                                        <a href="{{url("admin/member/{$lev->id}/edit")}}"  title="操作"><button class="layui-btn layui-btn-radius layui-btn-primary">操作</button></a>
                                    </td>
                                </tr>
                        @endforeach
                        <tr>
                            <td colspan="12" class="page">{{$members->appends($input)->links()}}</td>
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
