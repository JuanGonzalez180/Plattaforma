<a type="button" href="{{ route('category.edit', $id ) }}" class="btn btn-dark btn-sm"> <span class="oi oi-pencil" title="Editar" aria-hidden="true"></span> </a>
<form method="POST" action="{{ route('category.destroy', $id) }}" class="d-inline">
    @method('DELETE')
    @csrf
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseas Eliminar la Categoría?')" data-toggle="tooltip" title='Eliminar'> <i class="oi oi-trash"> </i></button>
</form>