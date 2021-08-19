<dlv class="row">
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$blog->name}}</dd>
    <dt class="col-sm-4">Usuario</dt>
    <dd class="col-sm-8">{{$blog->user->username}}</dd>
    <dt class="col-sm-4">Compañia</dt>
    <dd class="col-sm-8">{{$blog->company->name}}</dd>
    <dt class="col-sm-4">Descripción corta:</dt>
    <dd class="col-sm-8">
        @if(is_null($blog->description_short))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$blog->description_short}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Descripción</dt>
    <dd class="col-sm-8">
        @if(is_null($blog->description_short))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$blog->description}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
        @if($blog->status == 'Borrador')
            <span class="badge badge-warning">{{$blog->status}}</span>
        @else
            <span class="badge badge-success">{{$blog->status}}</span>
        @endif
    </dd>
</dl>