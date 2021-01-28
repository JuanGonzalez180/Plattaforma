@if ( session('status') )
<div class="alert alert-success alert dismissible fade show" role="alert">
    @switch( session('status') )
        @case('create')
            Tipo de entidad creado satisfactoriamente
            @break

        @case('edit')
            Tipo de entidad editado satisfactoriamente
            @break

        @case('delete')
            Tipo de entidad eliminado satisfactoriamente
            @break
        @default
            {{ session('status') }}
    @endswitch
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif