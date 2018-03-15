@extends('agent.layouts')
@section('content')
    <style>
        .content {
            padding: 0 15px;
        }
        .con {
            width: 100%;
        }
        .con .contents {
            display: inline-block;
            width: 49%;
            background: #eee;
            margin: 5px 0;
            padding: 5px 0 20px;
            border-radius: 10px;
        }
        .con .contents a {
            display: block;
        }
        .con .contents a p {
            text-align: center;
            font-size: 20px;
        }
        .product_img {
            width: 25%;
            margin-left: 35%;
            margin-top: 20px;
        }
    </style>
        <div class="con">
            <div class="contents" style="margin-top: 20px;">

                <a href="{{ url('agent/cashback')}}">
                    <img class="product_img" src="{{asset('agent/img/yeji.png')}}" alt="">
                    <p>我的业绩</p>
                </a>
            </div>
            <div class="contents">
                <a href="{{ url('agent/player')}}">
                     <img class="product_img" src="{{asset('agent/img/wanjia.png')}}" alt="">
                      <p>玩家信息</p>
                 </a>
            </div>
            <div class="contents">
                <a href="{{ url('agent/orderstatis')}}">
                     <img class="product_img" src="{{asset('agent/img/chongzhi2.png')}}" alt="">
                      <p>充值统计</p>
                 </a>
            </div>
            <div class="contents">
                <a href="{{ url('agent/applylist')}}">
                     <img class="product_img" src="{{asset('agent/img/tixian2.png')}}" alt="">
                      <p>提现管理</p>
                 </a>
            </div>
            <div class="contents">
                <a href="{{ url('agent/userinfo')}}">
                     <img class="product_img" src="{{asset('agent/img/geren.png')}}" alt="">
                      <p>个人资料</p>
                 </a>
            </div>
            <div class="contents">
                <a href="{{ url('agent/outlogin')}}">
                     <img class="product_img" src="{{asset('agent/img/out.png')}}" alt="">
                      <p>退出</p>
                 </a>
            </div>
        </div>
@stop