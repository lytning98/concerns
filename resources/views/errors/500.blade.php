@extends('common.layout')

@section('title', 'Error 500')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                @component('components/widget')
                    @slot('rawHTML', '<i class="fa fa-warning"></i>')
                    @slot('title', '错误')
                    @component('components/alert')
                        @slot('style', 'danger')
                        服务器端程序出现了一些问题，尝试重试以解决问题
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
