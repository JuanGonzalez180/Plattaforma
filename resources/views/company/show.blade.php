@extends('layout')

@section('title')
Compañia
@endsection

@section('content')
@include('partials.structure.open-main')
<h1>Compañia</h1>
<hr>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">

    <li class="nav-item">
        <a class="nav-link active" id="company-info-tab" data-toggle="pill" href="#company-info" role="tab" aria-controls="company-info" aria-selected="true">
            <i class="fas fa-info-circle"></i>&nbsp;Información
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" id="file-company-tab" data-toggle="pill" href="#file-company" role="tab" aria-controls="file-company" aria-selected="false">
            <i class="far fa-file-alt"></i>&nbsp;Archivos
            <span class="badge badge-light">{{count($company->files)}}</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" id="size-company-tab" data-toggle="pill" href="#size-company" role="tab" aria-controls="size-company" aria-selected="false">
            <i class="fas fa-chart-pie"></i>&nbsp;Tamaño ocupado
            <span class="badge badge-light"></span>
        </a>
    </li>

</ul>
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="company-info" role="tabpanel" aria-labelledby="company-info-tab">

        <dlv class="row">

            @if($company->image)
            <dt class="col-sm-4">Imagen:</dt>
            <dd class="col-sm-8">
                <a href="{{ url('storage/' . $company->image->url ) }}" target="_blank">
                    <img src="{{ url('storage/' . $company->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
                </a>
            </dd>
            @endif

            <dt class="col-sm-4">Nombre:</dt>
            <dd class="col-sm-8">
                {{$company->name}}
            </dd>

            <dt class="col-sm-4">Entidad:</dt>
            <dd class="col-sm-8">{{$company->type_entity->name}}</dd>

            <dt class="col-sm-4">Tipo de entidad:</dt>
            <dd class="col-sm-8">{{$company->type_entity->type->name}}</b></dd>

            <dt class="col-sm-4">NIT:</dt>
            <dd class="col-sm-8"><b>{{$company->nit}}</b></dd>

            @if($company->description)
            <dt class="col-sm-4">Descripción:</dt>
            <dd class="col-sm-8">
                <textarea class="form-control" rows="6" disabled>{{$company->description}}</textarea>
            </dd>
            @endif

            <dt class="col-sm-4">Estado:</dt>
            <dd class="col-sm-8">
                @if($company->status == 'Creado')
                <span class="badge badge-warning">{{$company->status}}</span>
                @elseif($company->status == 'Aprobado')
                <span class="badge badge-success">{{$company->status}}</span>
                @else
                <span class="badge badge-danger">{{$company->status}}</span>
                @endif
            </dd>

            <dt class="col-sm-4">Administrador:</dt>
            <dd class="col-sm-8">{{$company->user->username}}</dd>

            <dt class="col-sm-4">Correo</dt>
            <dd class="col-sm-8">{{$company->user->email}}</dd>

            <dt class="col-sm-4">Pais de la compañia:</dt>
            <dd class="col-sm-8">{{$company->country_code}}</dd>

            <dt class="col-sm-4">Dirección:</dt>
            <dd class="col-sm-8">
                @if(!is_null($company->address) && !is_null($company->address->address))
                {{ $company->address->address }}
                @else
                <span class="badge badge-secondary">Sin dirección</span>
                @endif
            </dd>

            <dt class="col-sm-4">Pagina web:</dt>
            <dd class="col-sm-8">
                @if(is_null($company->web))
                <span class="badge badge-secondary">Sin pagina web</span>
                @else
                {{$company->web}}
                @endif
            </dd>

            <dt class="col-sm-4">Espacio ocupado:</dt>
            <dd class="col-sm-8">
                <span class="badge badge-secondary">{{$company->fileSizeTotal()}} GB</span>
            </dd>

            <dt class="col-sm-4">País principal en el que va a operar:</dt>
            <dd class="col-sm-8">
                @foreach($company->countries as $country)
                {{$country->name}}
                @endforeach
            </dd>
            </dl>

    </div>

    <div class="tab-pane fade" id="file-company" role="tabpanel" aria-labelledby="file-company-tab">
        @if(count($company->files)>0)
        <div class="row">
            <ul class="list-group list-group-flush" style="width: 100%;">
                @foreach($company->files as $file)
                <a class="list-group-item" href="{{ url('storage/'.$file->url)}}" target="_blank">
                    <i class="far fa-file-alt"></i>
                    {{$file->name}}
                </a>
                @endforeach
            </ul>
        </div>
        @else

        <div class="alert alert-light" role="alert">
            No hay archivos por el momento
        </div>

        @endif
    </div>

    <div class="tab-pane fade" id="size-company" role="tabpanel" aria-labelledby="size-company-tab">

        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Categoria</th>
                    <th scope="col">Cantidad de archivos</th>
                    <th scope="col">Tamaño</th>
                    <th scope="col">Detalle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Blogs</th>
                    <td>{{$company->fileCountBlogs()}}</td>
                    <td>{{ round(($company->fileSizeBlogs() / pow(1024, 3)), 3) }}</td>
                    <td>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Proyectos</th>
                    <td>{{$company->fileCountProject()}}</td>
                    <td>{{ round(($company->fileSizeProject() / pow(1024, 3)), 3) }}</td>
                    <td>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Productos</th>
                    <td>{{$company->fileCountProduct()}}</td>
                    <td>{{$company->fileSizeProduct()}}</td>
                    <td>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Portafolio</th>
                    <td>{{$company->fileCountPortfolio()}}</td>
                    <td>{{$company->fileSizePortfolio()}}</td>
                    <td>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Licitaciones</th>
                    <td>{{$company->fileCountTender()}}</td>
                    <td>{{$company->fileSizeTender()}}</td>
                    <td>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

@include('partials.structure.close-main')
@endsection