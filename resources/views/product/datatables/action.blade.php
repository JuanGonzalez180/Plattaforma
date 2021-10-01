<div class="btn-group" role="group">
    <div class="btn-group" role="group">
        <a type="button" href="{{ route('productos.show', $id) }}" class="btn btn-success btn-sm"> <span class="oi oi-eye" title="Ver" aria-hidden="true"></span> </a>
    </div>
    <button id="btnGroupDrop1" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span>
    </button>
    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
        <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('remark.class.id', ['product',$id] ) }}">
            Reseñas
            <span class="badge badge-primary"></span>
        </a>
    </div>
</div>