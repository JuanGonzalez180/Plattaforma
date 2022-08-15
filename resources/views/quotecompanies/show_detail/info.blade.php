<div class="row">
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{$quoteCompany->company->name}}</dd>
    <dt class="col-sm-4">Cotización:</dt>
    <dd class="col-sm-8">{{$quoteCompany->quote->name}}</dd>
    <dt class="col-sm-4">Usuario encargado:</dt>
    <dd class="col-sm-8">{{$quoteCompany->user->fullName()}}</dd>
    <dt class="col-sm-4">Tipo:</dt>
    <dd class="col-sm-8">{{$quoteCompany->type}}</dd>
    <dt class="col-sm-4">Valor cotizado:</dt>
    <dd class="col-sm-8">${{$quoteCompany->price}}</dd>
    <!-- <dd class="col-sm-8">
        <hr>
        <form id="tender_company_forms">
            @csrf
            <input type="hidden" id="id" value="{{$quoteCompany->id}}" name="id"/>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="status">Estado</label>
                    <select name="status" id="status" class="form-control">
                        @foreach($status as $value)
                            <option value="{{ $value }}" {{ old('status', $value) == $quoteCompany->status ? 'selected' : '' }} >{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <button type="submit" class="btn btn-success">Aceptar</button>
                </div>
            </div>
        </form>
    </dd> -->
</div>