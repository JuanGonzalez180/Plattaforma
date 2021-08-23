<div class="row">
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{$tenderCompany->company->name}}</dd>
    <dt class="col-sm-4">Licitación:</dt>
    <dd class="col-sm-8">{{$tenderCompany->tender->name}}</dd>
    <dt class="col-sm-4">Usuario:</dt>
    <dd class="col-sm-8">{{$tenderCompany->user->username}}</dd>
    <dt class="col-sm-4">Tipo:</dt>
    <dd class="col-sm-8">{{$tenderCompany->type}}</dd>
    <dt class="col-sm-4">Precio:</dt>
    <dd class="col-sm-8">${{$tenderCompany->price}}</dd>
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">
    @if($tenderCompany->winner == 'true')
        <span class="badge badge-success">Ganador</span>
    @else
        <span class="badge badge-secondary">No definido</span>
    @endif
    </dd>
</div>