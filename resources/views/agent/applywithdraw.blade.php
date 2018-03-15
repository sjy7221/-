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
            <a href="{{ url('agent/index') }}">首页</a> / 提现管理
        </div>
        <div class="link">
            提现金额：
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
                申请提现
            </button>
            <!-- Modal -->
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">提现申请</h4>
                        </div>
                        <form action="{{ url('agent/apply') }}" class="form-inline" role="form" method="get">

                        <div class="modal-body">
                            <label for="exampleInputName2">提现金额：</label>
                            <input type="number"  class="form-control" required="required" id="exampleInputName2" placeholder="提现金额" name="money">
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

        <table class="table table-bordered">
            <thead>
                <th>申请时间</th>
                <th>提现金额</th>
                <th>提现结果</th>
            </thead>
            <tbody>
            @foreach ($data as $lev)
                <tr>
                    <td>{{date("Y-m-d H:i:s",$lev->creat_time)}}</td>
                    <td>{{$lev->money}}</td>
                    @if ($lev->is_pay == 2)
                        <td>已到账</td>
                    @elseif ($lev->is_pay == 3)
                        <td><a href="javascript:;" onclick="alert('{{$lev->describe}}');">已驳回</a></td>
                    @else
                        <td>等待转账</td>
                    @endif

                </tr>
            @endforeach
            </tbody>
            <tr><td colspan="4"> {{$data->links()}}</td></tr>
        </table>
        <script>
            $("#exampleInputName2").keyup(function () {
                console.log(/^[1-9]\d*$/.test(this.value))
                if(!/^[1-9]\d*$/.test(this.value)){
                    alert("请输入不为零的正整数")
                    this.value=""
                }
                console.log(this.value)
            })

        </script>

@stop