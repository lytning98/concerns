@extends('common.layout')

@section('title', '首页')
@section('nav-index', 'nav-current')

@if(Auth::guest())
    {{--未登录--}}
    @section('content')
        @component('components/post')
            @slot('title')
                {{env('APP_NAME')}}
            @endslot
            <hr/>
            <div align="center">
                <p>从主流ACM OnlineJudge收集提交/比赛信息</p>
                <p>聚合好友在各大OJ的最近动态</p>
                <br/>
                <p>Submission</p>
                <img src="{{asset('image/effect_ac.png')}}" style="width: 40%; align-self: center;"/>
                <br/>
                <p>Rating</p>
                <img src="{{asset('image/effect_rating.png')}}" style="width: 40%; align-self: center;"/>
                <br/>
                <p>Contest</p>
                <img src="{{asset('image/effect_contest.png')}}" style="width: 50%; align-self: center;"/>
                <hr/>
                <a class="btn btn-default btn-lg" href="{{route('login')}}">登录</a>
                <a class="btn btn-default btn-lg" href="{{route('register')}}">注册</a>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-8">

                    </div>
                    <div class="col-md-4">
                    </div>
                </div>
            </div>

        @endcomponent
    @endsection
@else
    {{--已登录--}}
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    @component('components/widget')
                        @slot('title', '动态')
                        @include('concern.momentlist')
                    @endcomponent
                </div>
                <div class="col-md-4">
                    @component('components/widget')
                        @slot('rawHTML', '<i class="fa fa-bullhorn"></i>')
                        @slot('title', '公告')
                        <p><strong>VJUDGE数据抓取暂未实现</strong></p>
                        <p style="color:grey"><del>吐槽一下poj实在太慢啦</del></p>
                        <p>数据更新速度：30 OJ Accounts / Minute</p>
                        <p>数据库中当前共有 {{$acc_count}} OJ Accounts，</p>
                        <p>数据完全更新一遍需要{{sprintf("%.3f", $acc_count/30)}}分钟</p>
                        <br/>
                        <p>Codeforces 只抓取已结束的CF赛制比赛（即不含Educational Round/Mirror等）每天抓取一次</p>
                        <p>Virtual不计入Friends榜</p>
                    @endcomponent
                </div>
            </div>
        </div>
    @endsection
@endif