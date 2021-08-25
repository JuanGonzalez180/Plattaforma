@if(count($tender->tenderCompanies)>0)
<table id="myTable" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Compañia</th>
            <th scope="col">Tipo</th>
            <th scope="col">Estado</th>
            <th scope="col">Precio</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tender->tenderCompanies as $company)
            <tr>
                <th scope="row">{{ $loop->iteration }}</th>
                <td>{{$company->company->name}}</td>
                <td>{{$company->type}}</td>
                <td>{{$company->status}}</td>
                <td>${{$company->price}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No hay elementos</td>
            </tr>
        @endforelse
    </tbody>
</table>
@else
<div class="alert alert-light" role="alert">
  No hay compañias licitantes por el momento
</div>
@endif