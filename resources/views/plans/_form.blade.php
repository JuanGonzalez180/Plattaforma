<div class="form-row">
    @if(!$plan->stripe_plan)
        <div class="form-group col-md-12">
            <label for="plan name">Nombre:</label>
            <input type="text" class="form-control" name="name" placeholder="Ingrese el nombre del plan" value="{{ old('name', $plan->name) }}">

            @error('name')
                <span class="text-danger" role="alert">
                    <small><b>{{ $message }}</b></small>
                </span>
            @enderror
        </div>
        <div class="form-group col-md-12">
            <label for="cost">Costo:</label>
            <input type="text" class="form-control" name="cost" placeholder="Ingrese el costo del plan" value="{{ old('cost', $plan->cost) }}">
            @error('cost')
                <span class="text-danger" role="alert">
                    <small><b>{{ $message }}</b></small>
                </span>
            @enderror
        </div>
        
        <div class="form-group col-md-12">
            <label for="parent_id">Producto</label>
            <select name="product" id="parent_id" class="form-control">
                <option value="">Producto</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ ( old( 'product' ) == $product->stripe_product ) ? 'selected' : '' }} >{{ $product->name }}</option>
                @endforeach
            </select>

            @error('product')
                <span class="text-danger" role="alert">
                    <small><b>{{ $message }}</b></small>
                </span>
            @enderror
        </div>
        
        
        <div class="form-group col-md-12">
            <label for="parent_id">Moneda</label>
            <select name="currency" id="parent_id" class="form-control">
                <option value="">Moneda</option>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->iso }}" {{ ( old( 'currency', $plan->iso ) == $currency->iso ) ? 'selected' : '' }} >{{ $currency->iso }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-6">
            <label for="cost">Cada:</label>
            <input type="text" class="form-control" name="interval_count" placeholder="Cada cuánto?" value="{{ old( 'interval_count', $plan->interval_count ) }}">
        </div>
        <div class="form-group col-md-6">
            <label for="parent_id">&nbsp;</label>
            <select name="interval" id="parent_id" class="form-control">
                <option value="month" {{ ( old( 'interval', $plan->interval ) == "month" ) ? 'selected' : '' }} >Mes</option>
                <option value="week" {{ ( old( 'interval', $plan->interval ) == "week" ) ? 'selected' : '' }} >Semana</option>
                <option value="year" {{ ( old( 'interval', $plan->interval ) == "year" ) ? 'selected' : '' }} >Año</option>
            </select>
        </div>

        <div class="form-group col-md-12">
            <label for="cost">Descripción:</label>
            <input type="text" class="form-control" name="description" placeholder="Ingrese la descripción del plan" value="{{ old( 'description', $plan->description ) }}">
        </div>
    @endif

    <div class="form-group col-md-12">
        <label for="days_trials">Días de prueba:</label>
        <input type="text" class="form-control" name="days_trials" placeholder="Días de pruebas para este plan" value="{{ old( 'days_trials', $plan->days_trials ) }}">
        @error('days_trials')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
</div>
