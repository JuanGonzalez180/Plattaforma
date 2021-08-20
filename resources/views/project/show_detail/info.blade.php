<dl class="row">
    @if($project->image)
    <dt class="col-sm-4">Imagen:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/' . $project->image->url ) }}" target="_blank">
            <img src="{{ url('storage/' . $project->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
        </a>
    </dd>
    @endif
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{ $project->name }}</dd>
    <dt class="col-sm-4">Encargado:</dt>
    <dd class="col-sm-8">{{ $project->user->username }}</dd>
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{ $project->company->name }}</dd>
    @if($project->description)
    <dt class="col-sm-4">Descripción:</dt>
    <dd class="col-sm-8">
        <textarea class="form-control" rows="6" disabled>{{$project->description}}</textarea>
    </dd>
    @endif
    <dt class="col-sm-4">Dirección:</dt>
    <dd class="col-sm-8">
        @if(!is_null($project->address) && !is_null($project->address->address))
            {{ $project->address->address }}
        @else
            <span class="badge badge-secondary">Sin dirección</span>
        @endif
    </dd>
    @if($project->projectTypeProject)
    <dt class="col-sm-4">Tipo/s de proyecto:</dt>
    <dd class="col-sm-8">
        @foreach($project->projectTypeProject as $type)
            <span class="badge badge-primary">{{$type->name}}</span>
        @endforeach
    </dd>
    @endif
    <dt class="col-sm-4">Metros cuadrados:</dt>
    <dd class="col-sm-8">{{ $project->meters }}</dd>
    <dt class="col-sm-4">Fecha de inicio del proyecto:</dt>
    <dd class="col-sm-8">{{ $project->date_start }}</dd>
    <dt class="col-sm-4">Fecha final del proyecto:</dt>
    <dd class="col-sm-8">{{ $project->date_end }}</dd>
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
        @if($project->status == 'especificaciones-tecnicas')
            <span class="badge badge-warning">{{ $project->status }}</span>
        @else
            <span class="badge badge-danger">{{ $project->status }}</span>
        @endif
    </dd>
    <dt class="col-sm-4">Visible:</dt>
    @if($project->visible == 'Visible')
    <dd class="col-sm-8">
        <span class="badge badge-primary">
            <i class="far fa-eye" data-toggle="tooltip" title="" data-original-title="Visible"></i>{{ $project->visible }}
        </span>
    </dd>
    @else
    <dd class="col-sm-8">
        <span class="badge badge-primary">
            <i class="far fa-eye-slash" data-toggle="tooltip" title="" data-original-title="No visible"></i> {{ $project->visible }}
        </span>
    </dd>
    @endif
</dl>

