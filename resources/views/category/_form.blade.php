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