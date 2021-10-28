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
    <dt class="col-sm-12">Planes imagenes</dt>
    <dt class="col-sm-12">
        @if( $advertising->Plan->advertisingPlansImages)

        <ul class="list-group">
            @foreach($advertising->Plan->advertisingPlansImages as $value)
            <li class="list-group-item">
                <div class="row">
                    <div class="col">
                        <b>Nombre:</b> {{ $value->imagesAdvertisingPlans->name }}
                    </div>
                    <div class="col">
                        <b><i class="fas fa-ruler-horizontal"></i> Ancho:</b> {{ $value->imagesAdvertisingPlans->width }}
                    </div>
                    <div class="col">
                        <b><i class="fas fa-ruler-vertical"></i> Largo:</b> {{ $value->imagesAdvertisingPlans->high }}
                    </div>
                    <div class="col">
                        <b><i class="fas fa-mobile-alt"></i> Tipo:</b> {{ $value->imagesAdvertisingPlans->type }}
                    </div>
                </div>
            </li>
            @endforeach
        </ul>

        @else

        <div class="container">
            <div class="badge badge-pill badge-light" role="alert">
                <i class="fas fa-info-circle"></i> no se han seleccionado plan de imagenes para el plan en general
            </div>
        </div>

        @endif
    </dt>
</dl>