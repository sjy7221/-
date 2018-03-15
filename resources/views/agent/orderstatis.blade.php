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
            <a href="{{ url('agent/index')}}">首页</a> / 充值统计
        </div>
        <form action="{{ url('agent/orderstatis') }}" class="form-inline" role="form" method="get">
            <div class="form-group">
                <label for="exampleInputName2">玩家ID:</label>
                <input type="text" class="form-control" id="exampleInputName2" name="mid" value="{{val('mid')}}">
                 <button type="submit" class="btn btn-primary">搜索</button>
            </div>
            
               
        </form>
        <table class="table table-bordered">
            <thead>
                <th>玩家ID</th>
                <th>玩家昵称</th>
                <th>充值总额</th>
               {{-- <th>操作</th>--}}
            </thead>
            <tbody>
            @foreach ($data as $lev)
                <tr>
                    <td>{{$lev->mid}}</td>
                    <td>{{$lev->nickname}}</td>
                    <td>{{$lev->num}}</td>
                   {{-- <td></td>--}}
                </tr>
            @endforeach
            </tbody>
            <tr><td colspan="3"> {{$data->links()}}</td></tr>
        </table>

@stop