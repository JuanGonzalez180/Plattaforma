<div class="form-row">
    <div class="form-group col-md-6">
        <label for="name">@lang('Name')</label>
        <input type="text" class="form-control" name="name" id="name" placeholder="@lang('Name')" value="{{ old('name', $typeEntity->name) }}">

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

    <div class="form-group col-md-6">
        <label for="status">Estado</label>
        <select name="status" id="type_id" class="form-control">
            <option value="Publicado" {{ old('status', $typeEntity->status) == 'Publicado' ? 'selected' : '' }}>Publicado</option>
            <option value="Borrador" {{ old('status', $typeEntity->status) == 'Borrador' ? 'selected' : '' }}>Borrador</option>
        </select>
    </div>

</div>
<a type="button" class="btn btn-danger" href="{{ route('typesentity.index') }}"><span class="oi oi-x" title="{{ $btnTextCancel }}" aria-hidden="true"></span> {{ $btnTextCancel }}</a>
<button type="submit" class="btn btn-primary"><span class="oi oi-check" title="{{ $btnTextPrimary }}" aria-hidden="true"></span> {{ $btnTextPrimary }}</button>