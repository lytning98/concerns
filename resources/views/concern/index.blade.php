@extends('common.layout')

@section('title', '我的关注')
@section('nav-concerns', 'nav-current')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @component('components/widget')
                    @slot('title')
                        我的关注
                        <button class="btn btn-default" onclick="window.location.href='{{route('concern-add')}}'">
                            <i class="fa fa-plus"></i>
                        </button>
                    @endslot

                    @forelse($data as $per)
                        @component('components/media')
                            @slot('imgsrc', \App\Tools\Gravatar::getURLbyPerson($per, 60))
                            @slot('title')
                                <a href="{{url('concern/modify/'.$per->id)}}">{{$per->nickname}}</a>
                            @endslot
                            @slot('grey', $per->email)
                            @forelse($per->accounts as $acc)
                                {{$acc->oj}}账号[{{$acc->username}}]
                                @if(!$loop->last)
                                    ，
                                @endif
                            @empty
                                还没有绑定OJ账号
                            @endforelse
                        @endcomponent
                    @empty
                        <div class="alert alert-warning" role="alert">还未关注任何人！</div>
                    @endforelse

                    <hr/>
                    {!! $data->render() !!}
                    <br/>
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components/widget')
                    @slot('title', '统计')
                    共关注了{{count($data)}}人
                @endcomponent

            </div>
        </div>
    </div>
@endsection