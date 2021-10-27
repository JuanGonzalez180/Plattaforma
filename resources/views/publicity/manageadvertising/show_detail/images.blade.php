@forelse( $advertising->advertisingPlansPaidImages as $plan)
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $plan->advertisingPlansImages->imagesAdvertisingPlans->name }}</h5>
        <hr>
        <div class="container">
            <div class="row">
                <div class="col">
                    <b><i class="fas fa-ruler-horizontal"></i> Ancho:</b> {{ $plan->advertisingPlansImages->imagesAdvertisingPlans->width }}
                </div>
                <div class="col">
                    <b><i class="fas fa-ruler-vertical"></i> Largo:</b> {{ $plan->advertisingPlansImages->imagesAdvertisingPlans->high }}
                </div>
                <div class="col">
                    <b><i class="fas fa-mobile-alt"></i> Tipo:</b> {{ $plan->advertisingPlansImages->imagesAdvertisingPlans->type }}
                </div>
            </div>
            <div class="row">
                @if($plan->image)
                <br>
                <dt class="col-sm-8"><i class="fas fa-image"></i> Imagen:</dt>
                <dd class="col-sm-8">
                    <a href="{{ url('storage/' . $plan->image->url ) }}" target="_blank">
                        <img src="{{ url('storage/' . $plan->image->url ) }}" alt="preview image" class="rounded float-left" style="width: 300px;">
                    </a>
                </dd>
                @endif
            </div>
        </div>

    </div>
</div>
<br>
@empty
<div class="container">
    <div class="alert alert-primary" role="alert">
    <i class="fas fa-info-circle"></i> El usuario no ha subido imagenes
    </div>
</div>
@endforelse