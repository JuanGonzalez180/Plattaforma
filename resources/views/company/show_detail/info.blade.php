<dlv class="row">



    @if($company->cover_Page())
    <dt class="col-sm-4">Portada de la compañia:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/' . $company->cover_Page()->url ) }}" target="_blank">
            <img src="{{ url('storage/' . $company->cover_Page()->url ) }}" alt="preview image" class="rounded float-left" style="width: 100%;">
        </a>
    </dd>
    @endif

    @if($company->image)
    <dt class="col-sm-4">Logo de la compañia:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/' . $company->image->url ) }}" target="_blank">
            <img src="{{ url('storage/' . $company->image->url ) }}" alt="preview image" class="rounded-circle float-left" style="width: 150px;">
        </a>
    </dd>
    @endif

    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">
        {{$company->name}}
    </dd>

    <dt class="col-sm-4">Entidad:</dt>
    <dd class="col-sm-8">{{$company->type_entity->name}}</dd>

    <dt class="col-sm-4">Tipo de entidad:</dt>
    <dd class="col-sm-8">{{$company->type_entity->type->name}}</b></dd>

    <dt class="col-sm-4">NIT:</dt>
    <dd class="col-sm-8"><b>{{$company->nit}}</b></dd>

    @if($company->description)
    <dt class="col-sm-4">Descripción:</dt>
    <dd class="col-sm-8">
        <textarea class="form-control" rows="6" disabled>{{$company->description}}</textarea>
    </dd>
    @endif

    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
        @if($company->status == 'Creado')
        <span class="badge badge-warning">{{$company->status}}</span>
        @elseif($company->status == 'Aprobado')
        <span class="badge badge-success">{{$company->status}}</span>
        @else
        <span class="badge badge-danger">{{$company->status}}</span>
        @endif
    </dd>

    <dt class="col-sm-4">Nombre Administrador:</dt>

    <dd class="col-sm-8">
        <h6>
            @if($company->user->image)
            <a href="{{ url('storage/' . $company->user->image->url ) }}" target="_blank">
                <img src="{{ url('storage/' . $company->user->image->url ) }}" alt="preview image" class="rounded-circle float-left" style="width: 50px;">
            </a>
            <br>
            &nbsp;
            @endif
            {{$company->user->fullName()}}
        </h6>
        <br>
    </dd>

    <dt class="col-sm-4">Correo</dt>
    <dd class="col-sm-8">{{$company->user->email}}</dd>

    <dt class="col-sm-4">Casa matriz:</dt>
    <dd class="col-sm-8">{{$company->country_code}}</dd>

    <dt class="col-sm-4">Pais de operación</dt>
    <dd class="col-sm-8">
        @forelse ($company->countries as $country)
        <div class="alert alert-secondary" role="alert">
            <div class="row">
                <div class="col">
                    <b>Nombre:</b> {{$country->name}}
                </div>
                <div class="col">
                    <b>Codigo:</b> {{$country->alpha2Code}}
                </div>
            </div>
        </div>
        @empty
        <span class="badge badge-secondary">Sin pais de operación</span>
        @endforelse
    </dd>

    <dt class="col-sm-4">Dirección:</dt>
    <dd class="col-sm-8">
        @if(!is_null($company->address) && !is_null($company->address->address))
        {{ $company->address->address }}
        @else
        <span class="badge badge-secondary">Sin dirección</span>
        @endif
    </dd>

    @if($company->phone)
    <dt class="col-sm-4">Telefono:</dt>
    <dd class="col-sm-8">
        <span class="badge badge-secondary">{{$company->phone['countryCode']}}</span> <b>{{$company->phone['dialCode']}}</b> {{$company->phone['number']}}
    </dd>
    @endif

    @if(count($company->tags)>0)
    <dt class="col-sm-4">Etiquetas:</dt>
    <dd class="col-sm-8">
        @foreach($company->tags as $tag)
        <span class="badge badge-primary">{{$tag->name}}</span>
        @endforeach
        @endif

    <dt class="col-sm-4">Pagina web:</dt>
    <dd class="col-sm-8">
        @if(is_null($company->web))
        <span class="badge badge-secondary">Sin pagina web</span>
        @else
        {{$company->web}}
        @endif
    </dd>
</dlv>