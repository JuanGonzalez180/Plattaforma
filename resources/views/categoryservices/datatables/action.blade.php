<a type="button" href="{{ route('categoryservices.edit', $id ) }}" class="btn btn-dark btn-sm"><i class="fas fa-pencil-alt"></i></a>
<form method="POST" action="{{ route('categoryservices.destroy', $id) }}" class="d-inline">
    @method('DELETE')
    @csrf
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar la Tipo de Proyecto?')" data-toggle="tooltip" title='Eliminar'><i class="fas fa-trash-alt"></i></button>
</form>