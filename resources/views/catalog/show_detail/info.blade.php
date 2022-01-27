<div class="row">
    @if($catalog->image)
    <dt class="col-sm-4">Imagen:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/'.$catalog->image->url)}}" target="_blank">
            <img src="{{ url('storage/' . $catalog->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
        </a>
    </dd>
    @endif
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$catalog->name}}</dd>
    <dt class="col-sm-4">Usuario</dt>
    <dd class="col-sm-8">{{$catalog->user->username}}</dd>
    <dt class="col-sm-4">Compañia</dt>
    <dd class="col-sm-8">{{$catalog->company->name}}</dd>
    <dt class="col-sm-4">Descripción corta:</dt>
    <dd class="col-sm-8">
        @if(is_null($catalog->description_short))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$catalog->description_short}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Descripción</dt>
    <dd class="col-sm-8">
        @if(is_null($catalog->description))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$catalog->description}}</textarea>
        @endif
    </dd>

    @if(count($catalog->tags)>0)
    <dt class="col-sm-4">Etiquetas:</dt>
    <dd class="col-sm-8">
    @foreach($catalog->tags as $tag)
        <span class="badge badge-primary">{{$tag->name}}</span>
    @endforeach
    </dd>
    @endif

    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
        @if($catalog->status == 'Publicado')
        <span class="badge badge-success">{{$catalog->status}}</span>
        @else
        <span class="badge badge-warning">{{$catalog->status}}</span>
        @endif
    </dd>

</div>