<dl class="row">
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{ $advertising->Plan->name }}</dd>
    <dt class="col-sm-4">Descripcion:</dt>
    <dd class="col-sm-8">
        <textarea class="form-control" rows="4" disabled>{{ $advertising->Plan->description }}</textarea>
    </dd>
    <dt class="col-sm-4">Dias de publicacion</dt>
    <dd class="col-sm-8">{{ $advertising->Plan->days }} </dd>
    <dt class="col-sm-4">Precio</dt>
    <dd class="col-sm-8"> ${{ $advertising->Plan->price }} </dd>
    <dt class="col-sm-4">Tipo de ubicacion</dt>
    <dd class="col-sm-8">{{$advertising->Plan->type_ubication }}</dd>
</dl>
