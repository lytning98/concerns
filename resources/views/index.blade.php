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
                        @forelse($dataset as $data)
                            @php
                                $nickname = $data['parent']->nickname;
                                $moment = $data['moment'];
                            @endphp
                            @component('components/media')
                                @slot('title', $nickname)
                                @slot('grey', $data['parent']->email)
                                @slot('imgsrc', \App\Tools\Gravatar::getURLbyPerson($data['parent'], 45))
                                @if($moment->event == 'AC')
                                    <span class="label label-success">Accepted</span> {{$nickname}} solved {{$moment->problem}}.
                                @elseif($moment->event == 'Contest')
                                    <span class="label label-info">Contest</span> {{$nickname}} participated in {{$moment->problem}}.
                                @else
                                    <span class="label label-warning">Rating</span> {{$nickname}}'s rating changed : {{$moment->description}} in {{$moment->problem}}
                                @endif
                                <br/>
                                <span style="color:grey">{{$moment->created_at->format("Y-m-d H:i:s")}}</span>
                            @endcomponent
                            <hr />
                            @break($loop->iteration == $ITEM_PER_PAGE)
                        @empty
                            <div class="alert alert-info" role="alert">没有任何动态...尝试多关注几个dalao吧</div>
                        @endforelse
                        {!! $paginator->render() !!}
                        <br/>
                    @endcomponent
                </div>
                <div class="col-md-4">
                    @component('components/widget')
                        @slot('title', '提示')
                        数据抓取周期为半小时。
                    @endcomponent
                </div>
            </div>
        </div>
    @endsection
@endif