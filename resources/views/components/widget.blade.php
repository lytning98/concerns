<div class="sidebar">
    <div class="widget">
        <h4 class="title">{!!$rawHTML or ''!!} {{$title or '~'}}</h4>
        <div class="content community" style="word-wrap: break-word">
            {{$slot}}
        </div>
    </div>
</div>