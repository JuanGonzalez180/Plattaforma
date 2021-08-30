<div class="row">
    @if($portfolio->image)
    <dt class="col-sm-4">Imagen:</dt>
    <dd class="col-sm-8">
        <a href="{{ url('storage/'.$portfolio->image->url)}}" target="_blank">
            <img src="{{ url('storage/' . $portfolio->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 150px;">
        </a>
    </dd>
    @endif
    <dt class="col-sm-4">Nombre:</dt>
    <dd class="col-sm-8">{{$portfolio->name}}</dd>
    <dt class="col-sm-4">Usuario</dt>
    <dd class="col-sm-8">{{$portfolio->user->username}}</dd>
    <dt class="col-sm-4">Compañia</dt>
    <dd class="col-sm-8">{{$portfolio->company->name}}</dd>
    <dt class="col-sm-4">Descripción corta:</dt>
    <dd class="col-sm-8">
        @if(is_null($portfolio->description_short))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$portfolio->description_short}}</textarea>
        @endif
    </dd>
    <dt class="col-sm-4">Descripción</dt>
    <dd class="col-sm-8">
        @if(is_null($portfolio->description))
            <span class="badge badge-secondary">Sin descripción</span>
        @else
            <textarea class="form-control" rows="6" disabled>{{$portfolio->description}}</textarea>
        @endif
    </dd>
    <dd class="col-sm-8">
        <hr>
        <form id="portfolio_form">
            @csrf
            <input type="hidden" id="id" value="{{$portfolio->id}}" name="id"/>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="status">Estado</label>
                    <select name="status" id="status" class="form-control">
                        @foreach($status as $value)
                            <option value="{{ $value }}" {{ old('status', $value) == $portfolio->status ? 'selected' : '' }} >{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <button type="submit" class="btn btn-success">Aceptar</button>
                </div>
            </div>
        </form>
    </dd>
    
</div>