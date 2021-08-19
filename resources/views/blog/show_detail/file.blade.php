@if(count($blog->files)>0)
    <div class="row">
        <ul class="list-group list-group-flush" style="width: 100%;">
        @foreach($blog->files as $file)
            <a class="list-group-item" href="{{ url('storage/'.$file->url)}}" target="_blank">
                <i class="far fa-file-alt"></i>
                {{$file->name}}
            </a>
        @endforeach
        </ul>
    </div>
@else
    <div class="alert alert-light" role="alert">
        No hay archivos por el momento 
    </div>
@endif