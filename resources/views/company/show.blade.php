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
        <a class="nav-link" id="size-company-tab" data-toggle="pill" href="#size-company" role="tab" aria-controls="size-company" aria-selected="false">
            <i class="fas fa-chart-pie"></i>&nbsp;Tamaño ocupado
            <span class="badge badge-light"></span>
        </a>
    </li>
</ul>

<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="company-info" role="tabpanel" aria-labelledby="company-info-tab">
        @include('company.show_detail.info')
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
                @if($company->type_company() == 'Oferta')
                <tr>
                    <th scope="row">Productos</th>
                    <td>{{$company->fileCountProduct()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeProduct())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('products')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Marcas</th>
                    <td>{{$company->fileCountBrands()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeBrands())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('brands')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                @else
                <tr>
                    <th scope="row">Proyectos</th>
                    <td>{{$company->fileCountProject()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeProject())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('projects')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Licitaciones</th>
                    <td>{{$company->fileCountTender()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeTender())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('tenders')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                @endif
                <tr>
                    <th scope="row">Blogs</th>
                    <td>{{$company->fileCountBlogs()}}</td>
                    <td><span class="badge badge-primary">{{ $company->formatSize($company->fileSizeBlogs()) }}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('blogs')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Portafolios</th>
                    <td>{{$company->fileCountPortfolio()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizePortfolio())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('portfolios')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Catalogos</th>
                    <td>{{$company->fileCountCatalogs()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeCatalogs())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('catalogs')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Publicidad</th>
                    <td>{{$company->fileCountAdvertising()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeAdvertising())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('advertisings')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Total</th>
                    <td>{{$company->fileCountTotal()}}</td>
                    <td><span class="badge badge-primary">{{$company->formatSize($company->fileSizeTotal())}}</span></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="fileModalEvent('all')">
                            <i class="fas fa-external-link-square-alt"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal -->
@include('company.show_detail.modal')
<!-- Modal -->
@include('partials.structure.close-main')
<script>
    var table;
    let model = 'all';

    $(document).ready(function() {
        table = $('#file_table').DataTable({
            "serverSide": true,
            "ajax": {
                "url": "{{ route('companies-get-files') }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "data": function(d) {
                    d.company_id = {{$company->id}};
                    d.model = getModel();
                }
            },
            "columns": [{
                    data: 'url'
                },
                {
                    data: 'updated_at'
                }
            ],
            "lengthMenu": [
                [10, 50, 100],
                [10, 50, 100]
            ],
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros por pagina",
                "zeroRecords": "Nothing found - sorry",
                "info": "Mostrando la pagina _PAGE_ de _PAGES_",
                "infoEmpty": "No hay elementos",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "paginate": {
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    });

    function getModel() {
        return model;
    }

    function setModel($model) {
        model = $model;
        return model;
    }

    function fileModalEvent($model) {
        setModel($model);
        table.ajax.reload();
        $('#modal_files').modal();
    }
</script>
@endsection