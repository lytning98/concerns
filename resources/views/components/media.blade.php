<ul class="media-list">
    <li class="media">
        <div class="media-left">
            <a href="#">
                <img class="media-object" src="{{$imgsrc}}" alt="Head" style="border-radius:15px;">
            </a>
        </div>
        <div class="media-body">
            <h4 class="media-heading">{{$title}}<span style="color:grey">({{$grey}})</span></h4>
            {{$slot}}
        </div>
    </li>
</ul>