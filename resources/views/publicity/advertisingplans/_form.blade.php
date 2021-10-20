<div class="form-row">

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
        <label for="type_ubication">Tipo</label>
        <select name="type_ubication" id="type_ubication" class="form-control">
            @foreach($type_ubications as $ubication)
            <option value="{{ $ubication }}" {{ old('status', $ubication) == $ubication ? 'selected' : '' }}>{{ $ubication }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group col-md-12">
        <label for="cost">Descripción:</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old( 'description', $plan->description ) }}</textarea>
        @error('description')
        <span class="text-danger" role="alert">
            <small><b>{{ $message }}</b></small>
        </span>
        @enderror
    </div>
    <div class="form-group col-md-12">
        <label for="days_trials">Días de exposición:</label>
        <input type="number" class="form-control" name="days" placeholder="Días de pruebas para este plan" value="{{ old( 'days_trials', $plan->days ) }}">
        @error('days')
        <span class="text-danger" role="alert">
            <small><b>{{ $message }}</b></small>
        </span>
        @enderror
    </div>
    <div class="form-group col-md-12">
        <label for="price">Costo:</label>
        <input type="number" class="form-control" name="price" placeholder="Ingrese el costo del plan" value="{{ old('price', $plan->price) }}">
        @error('price')
        <span class="text-danger" role="alert">
            <small><b>{{ $message }}</b></small>
        </span>
        @enderror
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Selecciona una imagen</label>
            <div>Sube una imagen de como se veria la publicidad</div>
            <input type="file" name="image" placeholder="Selecciona la imagen" id="image">

            @error('image')
            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-12 mb-2">
        @if( $plan->image )
        <img id="preview-image-before-upload" src="{{ url('storage/' . $plan->image->url ) }}" alt="preview image" style="max-height: 250px;">
        @else
        <img id="preview-image-before-upload" src="https://www.riobeauty.co.uk/images/product_image_not_found.gif" alt="preview image" style="max-height: 250px;">
        @endif
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(e) {
        $('#image').change(function() {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#preview-image-before-upload').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        });
    });
</script>