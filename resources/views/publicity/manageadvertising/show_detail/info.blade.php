<dl class="row">
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{ $advertising->name }}</dd>
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{ $advertising->company() }}</dd>
    <dt class="col-sm-4">Tipo:</dt>
    <dd class="col-sm-8"> <span class="badge badge-primary">{{ ($advertising->type_publicity_detail())['type'] }}</span> | <b>{{ ($advertising->type_publicity_detail())['name'] }}</b></dd>
    <dt class="col-sm-4">Fecha de inicio:</dt>
    <dd class="col-sm-8">{{ $advertising->start_date }} </dd>
</dl>
<hr>

<div class="form-group col-md-12">
    <label for="type_id">Estado</label>
    @if($advertising->payments->status == $status_payment[0])

    <div class="alert alert-primary" role="alert">
        <i class="fas fa-info-circle"></i> El pago sigue pendiente, debe realizar el pago para aprobar o rechazar la publicidad.
    </div>

    @elseif($advertising->payments->status == $status_payment[2])

    <div class="alert alert-danger" role="alert">
        <i class="fas fa-times"></i> El pago ha sido rechazado
    </div>

    @elseif($advertising->payments->status == $status_payment[1] && $advertising->status == $status[0])

    <form method="POST" action="{{ route('adver-status-update') }}" class="d-inline form-advertising-status">
        @csrf
        <input type="hidden" id="id" value="{{$advertising->id}}" name="id" />
        <div class="form-group">
            <div class="form-row">
                <div class="form-group col-md-8">
                    <select name="status" id="status" class="form-control">
                        @foreach($status as $value)
                        <option value="{{ $value }}" {{ old('status', $value) == $advertising->status ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <button type="submit" class="btn btn-success">Aceptar</button>
                </div>
            </div>
        </div>
    </form>

    @elseif($advertising->payments->status == $status_payment[1] && $advertising->status == $status[1])

    <div class="alert alert-success" role="alert">
        <i class="fas fa-check"></i> La publidad ha sido aprobada.
    </div>

    @elseif($advertising->payments->status == $status_payment[1] && $advertising->status == $status[2])
    <div class="alert alert-primary" role="alert">
        <i class="fas fa-info-circle"></i> La publidad ha sido rechazada.
    </div>
    @endif


</div>