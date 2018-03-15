@extends('agent.layouts')
@section('content')
        <style>
            .content {
                width: 100%;
                padding: 0px 15px 0px 10px;
            }
        </style>
        <div class="link">
            <a href="{{ url('agent/index')}}">首页</a> / 业绩查询
        </div>
        <table class="table table-bordered">
            <thead>
                <th>月份</th>
                <th>返利总额</th>
                <th>一级返利</th>
                <th>二级返利</th>
                {{--<th>操作</th>--}}
            </thead>
            <tbody>
            @foreach ($data as $lev)
                <tr>
                    <td>{{$lev->month}}</td>
                    <td>{{$lev->b1+$lev->b2}}元</td>
                    <td>{{$lev->b1}}元</td>
                    <td>{{$lev->b2}}元</td>
                    {{--<td></td>--}}
                </tr>
            @endforeach
            </tbody>
            <tr><td colspan="9"> {{$data->links()}}</td></tr>

        </table>

@stop