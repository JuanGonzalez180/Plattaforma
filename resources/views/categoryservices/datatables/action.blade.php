<a type="button" href="{{ route('categoryservices.edit', $id ) }}" class="btn btn-outline-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
<form method="POST" action="{{ route('categoryservices.destroy', $id) }}" class="d-inline">
    @method('DELETE')
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Deseas Eliminar la Tipo de Proyecto?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
</form>