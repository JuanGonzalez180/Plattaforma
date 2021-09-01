<div class="row">
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{$tenderCompany->company->name}}</dd>
    <dt class="col-sm-4">Licitación:</dt>
    <dd class="col-sm-8">{{$tenderCompany->tender->name}}</dd>
    <dt class="col-sm-4">Usuario:</dt>
    <dd class="col-sm-8">{{$tenderCompany->user->username}}</dd>
    <dt class="col-sm-4">Tipo:</dt>
    <dd class="col-sm-8">{{$tenderCompany->type}}</dd>
    <dt class="col-sm-4">Precio:</dt>
    <dd class="col-sm-8">${{$tenderCompany->price}}</dd>
    <dt class="col-sm-4">Ganador:</dt>
    <dd class="col-sm-8">
    @if($tenderCompany->winner == 'true')
        <span class="badge badge-success">Ganador</span>
    @else
        <span class="badge badge-secondary">No definido</span>
    @endif
    </dd>
    <dd class="col-sm-8">
        <hr>
        <form id="tender_company_forms">
            @csrf
            <input type="hidden" id="id" value="{{$tenderCompany->id}}" name="id"/>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="status">Estado</label>
                    <select name="status" id="status" class="form-control">
                        @foreach($status as $value)
                            <option value="{{ $value }}" {{ old('status', $value) == $tenderCompany->status ? 'selected' : '' }} >{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <button type="submit" class="btn btn-success">Aceptar</button>
                </div>
            </div>
        </form>
    </dd>
</div>