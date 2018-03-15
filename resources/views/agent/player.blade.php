@extends('agent.layouts')
@section('content')
        <style>
            .content {
                width: 100%;
                padding: 0px 15px 0px 10px;
            }

            .form-control {
                display: inline-block;
                width: auto;
            }
            .page {
                text-align: center;
            }
        </style>
        <div class="link">
            <a href="{{ url('agent/index')}}">首页</a> / 玩家信息
        </div>
        <div class="link">
            我的房卡：<span style="color: red" id="num">{{$num}}</span>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">房卡充值</h4>
                        </div>
                        <form action="{{ url('agent/chong') }}" class="form-inline" role="form" method="post">
                            <input type="hidden"   name="mid" id="mid" value="">
                            <div class="modal-body">
                                <label for="exampleInputName2">数量:</label>
                                <input type="number"  class="form-control"  placeholder="房卡" name="num">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                                <button type="submit" class="btn btn-primary">确认</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ url('agent/player') }}" class="form-inline" role="form" method="get">

            <div class="form-group">
                <label for="exampleInputName2">玩家ID:</label>
                <input type="text" class="form-control" id="exampleInputName2" name="id" value="{{val('id')}}">
            </div>
            <div class="form-group">
                等级：
                <label class="radio-inline">
                <input type="radio" name="is_agent" checked id="inlineRadio1" value="0" @if(val('is_agent') == '0') checked @endif> 所有玩家
            </label>
                <label class="radio-inline">
                <input type="radio" name="is_agent" id="inlineRadio2" value="1" @if(val('is_agent') == '1') checked @endif> 我的代理
            </label>
                <button type="submit" class="btn btn-primary">搜索</button>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <th>玩家ID</th>
                <th>玩家昵称</th>
                <th>房 卡</th>
                <th>注册时间</th>
                @if($set == 1)
                <th>操作</th>
                @endif
            </thead>
            <tbody>
            @foreach ($data as $lev)
                <tr>
                    <td>{{$lev->id}}</td>
                    <td>{{$lev->nickname}}</td>
                    <td>{{$lev->num}}</td>
                    <td>{{date("Y-m-d H:i:s",$lev->create_time)}}</td>
                    @if($num>0 && $set==1)
                        <td><a href="script:;" onclick="chong({{$lev->id}});" data-toggle="modal" data-target="#myModal">充房卡</a></td>
                    @endif
                 </tr>
            @endforeach
            </tbody>
            <tr><td @if($set == 1)colspan="5" @else colspan="4" @endif> {{$data->appends($input)->links()}}</td></tr>
        </table>
    <script>
        function chong(mid) {
            $("#mid").val(mid);
        }
    </script>

    @stop