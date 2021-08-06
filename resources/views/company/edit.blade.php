@extends('layout')

@section('title')
    Editar compañia
@endsection

@section('content')
    @include('partials.structure.open-main')
        <h1>Editar compañia</h1>
        <form method="POST" action="{{ route('companies.update', $company->id) }}" enctype="multipart/form-data">
            <div class="form-row">
                @csrf @method('PATCH')
                
                @include('company._form')
            </div>
            <a type="button" class="btn btn-danger" href="{{ route('companies-type', ($company->type_entity->type->name == 'Demanda')? 'Demanda': 'Oferta') }}" }}"><span class="oi oi-x" title="@lang('lang.cancel')" aria-hidden="true"></span> @lang('lang.cancel')</a>
            <button type="submit" class="btn btn-primary">@lang('lang.save')</button>
        </form>
        <div class="mb-5"></div>
    @include('partials.structure.close-main')
    <script>
    
        function getCountryCode() {
            fetch('https://restcountries.eu/rest/v2/all')
                .then(function(response) {
                    return response.json();
                })
                .then(function(myJson) {
                    let items = "<option value='' selected>Seleccione un pais</option>\n";
                    myJson.forEach(element => {
                        if(element['alpha2Code'] == '{{ $company->country_code }}')
                        {
                            items = items+"<option value='"+element['alpha2Code']+"' selected>"+element['name']+"</option>\n"
                        }
                        else
                        {
                            items = items+"<option value='"+element['alpha2Code']+"'>"+element['name']+"</option>\n"
                        }
                    });
                    document.querySelector('#country_code').innerHTML = items;
                });
        }

        getCountryCode();
        
    </script>
@endsection