@if($status == 'Creado' )
<form method="POST" action="{{ route( 'company.edit.status') }}" class="d-inline form-company-approve">
    @csrf
    <input type="hidden" name="id" value="{{$id}}"/>
    <input type="hidden" name="status" value="Aprobado"/>
    <button type="submit" class="btn btn-success btn-sm" data-toggle="tooltip" title='Abrobar' onclick="return confirm('Deseas aprobar la compañia?')">
        <i class="far fa-thumbs-up"></i>
    </button>
</form>
<form method="POST" action="{{ route( 'company.edit.status') }}" class="d-inline form-company-disapprove">
    @csrf
    <input type="hidden" name="id" value="{{$id}}"/>
    <input type="hidden" name="status" value="Rechazado"/>
    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title='Rechazar' onclick="return confirm('Deseas rechazr la compañia?')">
        <i class="far fa-thumbs-down"></i>
    </button>
</form>
@elseif($status == 'Aprobado' )
    <span class="badge badge-success"><i class="fas fa-check"></i> {{$status}}</span>
@else
    <span class="badge badge-danger"><i class="fas fa-times"></i> {{$status}}</span>
@endif