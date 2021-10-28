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