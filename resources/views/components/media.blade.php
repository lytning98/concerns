<ul class="media-list">
    <li class="media">
        <div class="media-left">
            <a href="{{$imghref or '#'}}">
                <img class="media-object" src="{{$imgsrc}}" alt="Head" style="border-radius:15px;">
            </a>
        </div>
        <div class="media-body">
            <h4 class="media-heading">
                {{$title}}
                @if(isset($rawHTML))
                    <span style="color:grey">({!! $grey or '' !!})</span>
                @else
                    <span style="color:grey">({{ $grey or ''}})</span>
                @endif
            </h4>
            {{$slot}}
        </div>
        @if(isset($right))
        <div class="media-right">
            {{$right}}
        </div>
        @endif
    </li>
</ul>