<div class="row">
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$tender->name}}</dd>
    <dt class="col-sm-4">Descripción:</dt>
    <dd class="col-sm-8">
        @if(is_null($tender->description))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="4" disabled>{{$tender->description}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Proyecto:</dt>
    <dd class="col-sm-8">{{$tender->project->name}}</dd>
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{$tender->company->name}}</dd>
    <dt class="col-sm-4">Estado:</dt>
    <dd class="col-sm-8">{{$tender->tendersVersionLast()->status}}</dd>
    <dt class="col-sm-4">Usuario encargado:</dt>
    <dd class="col-sm-8">{{$tender->user->username}}</dd>
    <dt class="col-sm-4">Categorias:</dt>
    <dd class="col-sm-8">
        @forelse ($tender->tenderCategories as $category)
            <span class="badge badge-primary">{{$category->name}}</span>
        @empty
        @endforelse
    </dd>
</div>