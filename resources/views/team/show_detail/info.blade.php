<dl class="row">
    @if($team->user->mage)
    <dt class="col-sm-4">Imagen:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/' . $team->user->mage->url ) }}" target="_blank">
            <img src="{{ url('storage/' . $team->user->mage->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
        </a>
    </dd>
    @endif
    <dt class="col-sm-4">Nombre de usuario:</dt>
    <dd class="col-sm-8">{{ $team->user->username }}</dd>
    @if($team->user->fullName())
    <dt class="col-sm-4">Nombre completo:</dt>
    <dd class="col-sm-8">{{ $team->user->fullName() }}</dd>
    @endif
    <dt class="col-sm-4">Email:</dt>
    <dd class="col-sm-8">{{ $team->user->email }}</dd>
    
    <dt class="col-sm-4">Administrador:</dt>
    <dd class="col-sm-8">
        @if($team->user->is_admin)
        <span class="badge badge-primary">SI</span>
        @else
        <span class="badge badge-secondary">NO</span>
        @endif
    </dd>

    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{ $team->company->name }}</dd>
    <dt class="col-sm-4">Posición:</dt>
    <dd class="col-sm-8">
        @if($team->position)
            {{$team->position}}
        @else
            <span class="badge badge-secondary">sin definir</span>
        @endif
    </dd>
    @if($team->phone)
    <dt class="col-sm-4">Telefono:</dt>
    <dd class="col-sm-8">{{ $team->phone }}</dd>
    @endif
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
        @if($team->status == 'Aprobado')
        <span class="badge badge-success">{{ $team->status }}</span>
        @else
        <span class="badge badge-secondary">{{ $team->status }}</span>
        @endif
    </dd>


</dl>
