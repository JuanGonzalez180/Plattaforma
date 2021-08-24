<div class="row">
    @if($portfolio->image)
    <dt class="col-sm-4">Imagen:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/'.$portfolio->image->url)}}" target="_blank">
            <img src="{{ url('storage/' . $portfolio->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
        </a>
    </dd>
    @endif
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$portfolio->name}}</dd>
    <dt class="col-sm-4">Usuario</dt>
    <dd class="col-sm-8">{{$portfolio->user->username}}</dd>
    <dt class="col-sm-4">Compañia</dt>
    <dd class="col-sm-8">{{$portfolio->company->name}}</dd>
    <dt class="col-sm-4">Descripción corta:</dt>
    <dd class="col-sm-8">
        @if(is_null($portfolio->description_short))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$portfolio->description_short}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Descripción</dt>
    <dd class="col-sm-8">
        @if(is_null($portfolio->description))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$portfolio->description}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
        @if($portfolio->status == 'Borrador')
            <span class="badge badge-warning">{{$portfolio->status}}</span>
        @else
            <span class="badge badge-success">{{$portfolio->status}}</span>
        @endif
    </dd>
</div>