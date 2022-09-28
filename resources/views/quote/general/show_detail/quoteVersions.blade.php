<div class="accordion" id="accordionExample">
<<<<<<< HEAD
@foreach($quote->quotesVersion->sortBy([ ['updated_at', 'desc'] ]) as $key=>$version)
    @if($loop->iteration == 1)
    <div class="card">
      <div class="card-header" id="heading{{$loop->iteration}}">
        <h2 class="mb-0">
          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$loop->iteration}}" aria-expanded="true" aria-controls="collapse{{$loop->iteration}}">
            Version #{{count($quote->quotesVersion)-$key}}
          </button>
        </h2>
      </div>    
      <div id="collapse{{$loop->iteration}}" class="collapse show" aria-labelledby="heading{{$loop->iteration}}" data-parent="#accordionExample">
        <div class="card-body row">
            <dt class="col-sm-4">Adenda:</dt>
            <dd class="col-sm-8">{{$version->adenda}}</dd>
            <dt class="col-sm-4">Precio:</dt>
            <dd class="col-sm-8">{{$version->price}}</dd>
            <dt class="col-sm-4">Estado:</dt>
            <dd class="col-sm-8">{{$version->status}}</dd>
            <dt class="col-sm-4">Fecha cierre:</dt>
            <dd class="col-sm-8">{{$version->date}}</dd>
            <dt class="col-sm-4">Hora:</dt>
            <dd class="col-sm-8">{{$version->hour}}</dd>
            <dt class="col-sm-4">Etiquetas:</dt>
            <dd class="col-sm-8">
            @forelse ($version->tags as $tag)
                <span class="badge badge-primary">{{$tag->name}}</span>
            @empty
                Sin etiquetas
            @endforelse
            </dd>
                @if(count($version->files)>0)
                    <dt class="col-sm-4">Archivos:</dt>
                    <dd class="col-sm-12">
                        @foreach($version->files as $file)
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('storage/'.$file->url)}}" target="_blank" style="margin-top: 2px;">
                                <i class="far fa-file-alt"></i>
                                {{$file->name}}
                            </a>
                        @endforeach
                    </dd>
                @endif
            </div>
        </div>
=======
  @foreach($quote->quotesVersion->sortBy([ ['updated_at', 'desc'] ]) as $key=>$version)
  @if($loop->iteration == 1)
  <div class="card">
    <div class="card-header" id="heading{{$loop->iteration}}">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$loop->iteration}}" aria-expanded="true" aria-controls="collapse{{$loop->iteration}}">
          Version #{{count($quote->quotesVersion)-$key}}
        </button>
      </h2>
    </div>
    <div id="collapse{{$loop->iteration}}" class="collapse show" aria-labelledby="heading{{$loop->iteration}}" data-parent="#accordionExample">
      <div class="card-body row">
        <dt class="col-sm-4">Adenda:</dt>
        <dd class="col-sm-8">{{$version->adenda}}</dd>
        <dt class="col-sm-4">Precio:</dt>
        <dd class="col-sm-8">{{$version->price}}</dd>
        <dt class="col-sm-4">Estado:</dt>
        <dd class="col-sm-8">{{$version->status}}</dd>
        <dt class="col-sm-4">Fecha de creación de la licitación:</dt>
        <dd class="col-sm-8">{{$version->quotes->created_at->isoFormat('YYYY-MM-DD')}}</dd>
        <dt class="col-sm-4">Fecha de creación de la adenda:</dt>
        <dd class="col-sm-8">{{$version->created_at->isoFormat('YYYY-MM-DD')}}</dd>
        <dt class="col-sm-4">Fecha de cierre:</dt>
        <dd class="col-sm-8">{{$version->date}}</dd>
        <dt class="col-sm-4">Hora:</dt>
        <dd class="col-sm-8">{{$version->hour}}</dd>
        <dt class="col-sm-4">Etiquetas:</dt>
        <dd class="col-sm-8">
          @forelse ($version->tags as $tag)
          <span class="badge badge-primary">{{$tag->name}}</span>
          @empty
          Sin etiquetas
          @endforelse
        </dd>
        @if(count($version->files)>0)
        <dt class="col-sm-4">Archivos:</dt>
        <dd class="col-sm-12">
          @foreach($version->files as $file)
          <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('storage/'.$file->url)}}" target="_blank" style="margin-top: 2px;">
            <i class="far fa-file-alt"></i>
            {{$file->name}}
          </a>
          @endforeach
        </dd>
        @endif
>>>>>>> cristianfajardo
      </div>
    </div>
  </div>
</div>
@else
<div class="card">
  <div class="card-header" id="heading{{$loop->iteration}}">
    <h2 class="mb-0">
      <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse{{$loop->iteration}}" aria-expanded="false" aria-controls="collapse{{$loop->iteration}}">
        Version #{{count($quote->quotesVersion)-$key}}
      </button>
    </h2>
  </div>
  <div id="collapse{{$loop->iteration}}" class="collapse" aria-labelledby="heading{{$loop->iteration}}" data-parent="#accordionExample">
    <div class="card-body row">
      <dt class="col-sm-4">Adenda:</dt>
      <dd class="col-sm-8">{{$version->adenda}}</dd>
      <dt class="col-sm-4">Precio:</dt>
      <dd class="col-sm-8">{{$version->price}}</dd>
      <dt class="col-sm-4">Estado:</dt>
      <dd class="col-sm-8">{{$version->status}}</dd>
      <dt class="col-sm-4">Fecha de creación de la licitación:</dt>
      <dd class="col-sm-8">{{$version->quotes->created_at->isoFormat('YYYY-MM-DD')}}</dd>
      <dt class="col-sm-4">Fecha de creación de la adenda:</dt>
      <dd class="col-sm-8">{{$version->created_at->isoFormat('YYYY-MM-DD')}}</dd>
      <dt class="col-sm-4">Fecha de cierre:</dt>
      <dd class="col-sm-8">{{$version->date}}</dd>
      <dt class="col-sm-4">Hora:</dt>
      <dd class="col-sm-8">{{$version->hour}}</dd>
      <dt class="col-sm-4">Etiquetas:</dt>
      <dd class="col-sm-8">
        @forelse ($version->tags as $tag)
        <span class="badge badge-primary">{{$tag->name}}</span>
        @empty
        Sin etiquetas
        @endforelse
      </dd>
      @if(count($version->files)>0)
      <dt class="col-sm-4">Archivos:</dt>
      <dd class="col-sm-12">
        @foreach($version->files as $file)
        <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('storage/'.$file->url)}}" target="_blank" style="margin-top: 2px;">
          <i class="far fa-file-alt"></i>
          {{$file->name}}
        </a>
        @endforeach
      </dd>
      @endif
    </div>
  </div>
</div>
@endif
@endforeach
</div>