<div class="form-group col-md-6">
    <label for="name">@lang('lang.name')</label>
    <input type="text" class="form-control" name="name" id="name" placeholder="@lang('lang.name')" value="{{ old( 'name', $brand->name ) }}">

    @error('name')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('name') }}</b></small>
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
    @if( $brand->image )
        <img id="preview-image-before-upload" src="{{ url('storage/' . $brand->image->url ) }}" alt="preview image" style="max-height: 250px;">
    @else
        <img id="preview-image-before-upload" src="https://www.riobeauty.co.uk/images/product_image_not_found.gif" alt="preview image" style="max-height: 250px;">
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
}); 
</script>