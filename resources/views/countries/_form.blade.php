<div class="form-row">
    <div class="form-group col-md-6">
        <label for="title">@lang('Name')</label>
        <input type="text" class="form-control" name="name" id="name" placeholder="@lang('Name')" value="{{ old('name', $country->name) }}">

        @error('name')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="content">@lang('Code')</label>
        <input type="text" class="form-control" name="alpha2Code" id="alpha2Code" placeholder="@lang('Code')" value="{{ old('alpha2Code', $country->alpha2Code) }}">

        @error('alpha2Code')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
</div>
<a type="button" class="btn btn-danger" href="{{ route('countries.index') }}"><span class="oi oi-x" title="{{ $btnTextCancel }}" aria-hidden="true"></span> {{ $btnTextCancel }}</a>
<button type="submit" class="btn btn-primary"><span class="oi oi-check" title="{{ $btnTextPrimary }}" aria-hidden="true"></span> {{ $btnTextPrimary }}</button>