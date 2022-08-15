<div class="row">
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$quote->name}}</dd>
    <dt class="col-sm-4">Descripción:</dt>
    <dd class="col-sm-8">
        @if(is_null($quote->description))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="4" disabled>{{$quote->description}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Proyecto:</dt>
    <dd class="col-sm-8">{{$quote->project->name}}</dd>
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{$quote->company->name}}</dd>
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">{{$quote->quotesVersionLast()->status}}</dd>
    <dt class="col-sm-4">Usuario encargado:</dt>
    <dd class="col-sm-8">{{$quote->user->username}}</dd>
</div>