@extends('common.layout')

@section('title', $per->nickname)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @component('components/widget')
                    @slot('title', "$per->nickname 的最近动态")
                    @include('concern.momentlist')
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components/widget')
                    @slot('title', "资料")
                    <div align="center">
                        <img src="{{\App\Tools\Gravatar::getURLbyEmail($per->email, 75)}}" alt="head_pic" style="border-radius:20px;"/>
                        <h4>{{$per->nickname}}<span style="color:grey">({!! $per->getEmailHTML() !!})</span></h4>
                        <hr/>
                        @forelse($per->accounts as $acc)
                            <h5>{{$acc->username}} @ {{$acc->oj}}</h5>
                        @empty
                            <h5>还没有设置账号</h5>
                        @endforelse
                        <br/>
                        <a class="btn btn-default" href="{{route('concern-modify', ['id'=>$per->id])}}">
                            <span class="glyphicon glyphicon-pencil"><z> 编辑资料</z></span>
                        </a>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
@endsection