<div class="form-group col-md-12">
    <label for="name">@lang('lang.name')</label>
    <input type="text" class="form-control" name="name" id="name" placeholder="@lang('lang.name')" value="{{ old( 'name', $company->name ) }}">
    @error('name')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('name') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-12">
    <label for="description">@lang('lang.description')</label>
    <textarea class="form-control" name="description" id="description" placeholder="@lang('lang.description')" rows="10" >
        {{ old( 'description', $company->description ) }}
    </textarea>
    @error('description')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('description') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-6">
    <label for="nit">Identificación de la empresa</label>
    <input type="text" class="form-control" name="nit" id="nit" placeholder="Identificación de la empresa" value="{{ old( 'nit', $company->nit ) }}">
    @error('nit')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('nit') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-6">
    <label for="parent_id">Pais de la empresa</label>
    <select name="country_code" id="country_code" class="form-control">
    </select>
    @error('country_code')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('country_code') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-12">
    <label for="web">Pagina web</label>
    <input type="text" class="form-control" name="web" id="web" placeholder="Pagina web" value="{{ old( 'web', $company->web ) }}">
    @error('web')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('web') }}</b></small>
        </span>
    @enderror
</div>

<div class="form-group col-md-12">
    <label for="address">Direccion</label>
    <input type="text" class="form-control" name="address" id="address" placeholder="Dirección" value="{{ old( 'web', (isset($company->address->address))? $company->address->address : '' ) }}">
    @error('web')
        <span class="text-danger" role="alert">
            <small><b>{{ $errors->getBag('default')->first('address') }}</b></small>
        </span>
    @enderror
</div>