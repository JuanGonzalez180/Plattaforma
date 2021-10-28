<div class="form-group col-md-6">
    <label for="name">@lang('lang.name')</label>
    <input type="text" class="form-control" name="name" id="name" placeholder="@lang('lang.name')" value="{{ old( 'name', $category->name ) }}">

    @error('name')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('name') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-6">
    <label for="parent_id">@lang('lang.parentCategory')</label>

    <select name="parent_id" id="parent_id" class="form-control">
        <option value="" selected>@lang('lang.parentCategory')</option>
        @foreach ($categoryOptions as $option)
            @if ( $option->id != old( 'id', $category->id ) ) 
                <option value="{{ $option->id }}" {{ ( old( 'parent_id', $category->parent_id ) == $option->id ) ? 'selected' : '' }}>{{ $option->name }}</option>
            @endif
        @endforeach
    </select>
    @error('parent_id')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('parent_id') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-12">
    <label for="description">@lang('lang.description')</label>
    <textarea type="text" class="form-control" name="description" id="description" placeholder="@lang('lang.description')">{{ old('description', $category->description ) }}</textarea>
    @error('description')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('description') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-6">
    <label for="type_id">Estado</label>
    <select name="status" id="status" class="form-control">
        <option value="">Estado</option>
        @foreach ($status as $value)
            <option value="{{ $value }}" {{ old('status', $category->status) == $value ? 'selected' : '' }}>{{$value}}</option>
        @endforeach
    </select>
    @error('status')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('status') }}</b></small>
        </span>
    @enderror
</div>

<div class="col-md-12">
    <div class="form-group">
        <label>Selecciona una imagen</label><br>
        <input type="file" name="image" placeholder="Selecciona la imagen" id="image">

        @error('image')
            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col-md-12 mb-2">
    @if( $category->image )
        <img id="preview-image-before-upload" src="{{ url('storage/' . $category->image->url ) }}" alt="preview image" style="max-height: 250px;">
    @else
        <img id="preview-image-before-upload" src="https://www.riobeauty.co.uk/images/product_image_not_found.gif" alt="preview image" style="max-height: 250px;">
    @endif
</div>

<div class="col-md-12">
    <div class="form-group">
        <label>Selecciona un icono</label><br>
        <input type="file" name="icon" placeholder="Selecciona la imagen" id="icon">

        @error('icon')
            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col-md-12 mb-2">
    @if( $category->icon )
        <img id="preview-icon-before-upload" src="{{ url('storage/' . $category->icon->url ) }}" alt="preview image" style="max-height: 250px;">
    @else
        <img id="preview-icon-before-upload" src="https://www.riobeauty.co.uk/images/product_image_not_found.gif" alt="preview image" style="max-height: 250px;">
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function (e) {
   $('#image').change(function(){
    let reader = new FileReader();
    reader.onload = (e) => { 
      $('#preview-image-before-upload').attr('src', e.target.result); 
    }
    reader.readAsDataURL(this.files[0]); 
   });

   $('#icon').change(function(){
    let reader = new FileReader();
    reader.onload = (e) => { 
      $('#preview-icon-before-upload').attr('src', e.target.result); 
    }
    reader.readAsDataURL(this.files[0]); 
   });
}); 
</script>