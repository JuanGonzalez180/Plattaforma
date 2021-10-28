<dl class="row">
    <dt class="col-sm-4">Precio:</dt>
    <dd class="col-sm-8"><b>${{ $advertising->payments->price }}</b></dd>
    <dt class="col-sm-4">Tipo</dt>
    <dd class="col-sm-8">{{ $advertising->payments->type }}</dd>
    <dt class="col-sm-4">Estado</dt>
    <dd class="col-sm-8"><span class="badge badge-primary">{{ $advertising->payments->status }}</span></dd>
</dl>