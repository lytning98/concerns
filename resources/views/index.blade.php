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

            mottos here

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
                        @slot('title', '公告')
                        数据抓取周期为半小时。
                    @endcomponent
                </div>
            </div>
        </div>
    @endsection
@endif