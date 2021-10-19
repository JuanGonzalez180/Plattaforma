<div class="form-group col-md-12">
    <label for="plan name">Nombre:</label>
    <input type="text" class="form-control" name="name" placeholder="Ingrese el nombre del plan" value="{{ old('name', $plan->name) }}">
    @error('name')
    <span class="text-danger" role="alert">
        <small><b>{{ $message }}</b></small>
    </span>
    @enderror
</div>
<div class="form-group col-md-6">
    <label for="plan width">Ancho:</label>
    <input type="number" class="form-control" name="width" placeholder="Ingrese el nombre del plan" value="{{ old('width', $plan->width) }}">
    @error('width')
    <span class="text-danger" role="alert">
        <small><b>{{ $message }}</b></small>
    </span>
    @enderror
</div>
<div class="form-group col-md-6">
    <label for="plan high">Alto:</label>
    <input type="number" class="form-control" name="high" placeholder="Ingrese el nombre del plan" value="{{ old('high', $plan->high) }}">
    @error('high')
    <span class="text-danger" role="alert">
        <small><b>{{ $message }}</b></small>
    </span>
    @enderror
</div>

<div class="form-group col-md-12">
    <label for="type">Tipo</label>
    <select name="type" id="type" class="form-control">
        @foreach($types as $type)
        <option value="{{ $type }}" {{ old('status', $type) == $type ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
    </select>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>