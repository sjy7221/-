@extends('agent.layouts')
@section('content')
        <style>
            .content {
                width: 100%;
                padding: 0px 15px 0px 10px;
            }

            .mes {
                line-height: 1.5;
                border: 1px solid #d7d7d7;
            }

            .mes .header {
                border-bottom: 1px solid #d7d7d7;
                padding: 0 24px;
                line-height: 48px;
            }

            .mes .mes_person {
                font-weight: 500;
                font-size: 1.5em;
            }

            .mes .mes_body {
                padding: 24px;
                font-size: 16px;
            }

            .mes_body .mes_r {
                width: 49%;
                text-align: right;
                display: inline-block;
                padding: 0 8px;
            }

            .mes_body .mes_l {
                width: 49%;
                display: inline-block;
                padding: 0 8px;
            }
        </style>
        <div class="link">
            <a href="{{ url('agent/index')}}">首页</a> / 个人信息 / 基础资料
        </div>
        <div class="mes">
            <div class="header">
                <span class="mes_person">个人信息</span>
                <a href="{{ url('agent/changepw')}}" class="f-fr">修改信息</a>
            </div>
            <div class="mes_body clearfix">
                <div>
                    <span class="mes_r">用户 ID:</span>
                    <span class="mes_l">{{$data->mid}}</span>
                </div>
                <div>
                    <span class="mes_r">昵&nbsp;&nbsp;称:</span>
                    <span class="mes_l">{{$data->nickname}}</span>
                </div>
                <div>
                    <span class="mes_r">佣  &nbsp;&nbsp; 金:</span>
                    <span class="mes_l">{{$data->balance}}</span>
                </div>
                <div>
                    <span class="mes_r">房  &nbsp;&nbsp; 卡:</span>
                    <span class="mes_l">{{$data->num}}</span>
                </div>

                <div>
                    <span class="mes_r">提现方式:</span>
                    <span class="mes_l">@if($data->ti_type == 1)支付宝 @elseif($data->ti_type == 2)银行卡 @endif</span>
                </div>
                <div>
                    <span class="mes_r">提现姓名:</span>
                    <span class="mes_l">{{$data->realname}}</span>
                </div>
                <div>
                    <span class="mes_r">提现账号:</span>
                    <span class="mes_l">{{$data->number}}</span>
                </div>
            </div>

        </div>

@stop