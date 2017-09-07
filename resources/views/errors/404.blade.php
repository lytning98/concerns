@extends('common.layout')

@section('title', 'Error 404')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                @component('components/widget')
                    @slot('rawHTML', '<i class="fa fa-warning"></i>')
                    @slot('title', '错误')
                    @component('components/alert')
                        @slot('style', 'warning')
                        您访问的页面不存在
                    @endcomponent
                    <div align="center">
                        <a class="btn btn-default" href="{{url('/')}}">返回首页</a>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>
@endsection
