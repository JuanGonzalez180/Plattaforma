<div class="form-row">
    <div class="form-group col-md-6">
        <label for="name">@lang('lang.name')</label>
        <input type="text" class="form-control" name="name" id="name" placeholder="@lang('lang.name')" value="{{ old('name', $typeEntity->name) }}">

        @error('name')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="type_id">@lang('Type')</label>
        <select name="type_id" id="type_id" class="form-control">
            <option value="" selected>@lang('Type')</option>
            @foreach ($typeOptions as $option)
                <option value="{{ $option->id }}" {{ old('type_id', $typeEntity->type_id) == $option->id ? 'selected' : '' }} >{{ $option->name }}</option>
            @endforeach
        </select>
        @error('type_id')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
</div>
<button type="submit" class="btn btn-primary">{{ $btnText }}</button>