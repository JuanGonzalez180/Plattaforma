
<div class="row">
    @if($product->image)
    <dt class="col-sm-4">Imagen:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/' . $product->image->url ) }}" target="_blank">
            <img src="{{ url('storage/' . $product->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
        </a>
    </dd>
    @endif
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$product->name}}</dd>
    <dt class="col-sm-4">Compañia:</dt>
    <dd class="col-sm-8">{{$product->company->name}}</dd>
    <dt class="col-sm-4">Usuario:</dt>
    <dd class="col-sm-8">{{$product->user->username}}</dd>
    <dt class="col-sm-4">Marca:</dt>
    <dd class="col-sm-8">{{$product->brand->name}}</dd>
    @if($product->description)
    <dt class="col-sm-4">Descripción:</dt>
    <dd class="col-sm-8">
    <textarea class="form-control" rows="4" disabled>{{$product->description}}</textarea>
    </dd>
    @endif
    <dt class="col-sm-4">Tipo:</dt>
    <dd class="col-sm-8">{{$product->type}}</dd>
    <dt class="col-sm-4">Estatus:</dt>
    <dd class="col-sm-8">{{$product->status}}</dd>
    @if(count($product->productCategories)>0)
    <dt class="col-sm-4">Categorias:</dt>
    <dd class="col-sm-8">
    @foreach($product->productCategories as $category)
        <span class="badge badge-primary">{{$category->name}}</span>
    @endforeach
    @endif
    @if(count($product->productCategoryServices)>0)
    <dt class="col-sm-4">Categorias servicio:</dt>
    <dd class="col-sm-8">
    @foreach($product->productCategoryServices as $category)
        <span class="badge badge-primary">{{$category->name}}</span>
    @endforeach
    @endif
    @if(count($product->tags)>0)
    <dt class="col-sm-4">Etiquetas:</dt>
    <dd class="col-sm-8">
    @foreach($product->tags as $tag)
        <span class="badge badge-primary">{{$tag->name}}</span>
    @endforeach
    @endif
</div>