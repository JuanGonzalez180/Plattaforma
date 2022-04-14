@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola, la compañia <b>{{ $companyName }}</b>
    </div>
    <div style="padding: 10px 0; font-size: 16px; text-align: center; line-height: 1.5;">
        Tu compañía ha sido seleccionada para participar en la licitación <b>{{ $tenderName }}</b>. Para acceder a ella debes suscribirte a plattaforma.
        +<iframe width="700" height="700" src="https://www.youtube.com/embed/M5P1lK8NRN0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>+  
    </div>
    <div style="padding: 10px 0 50px 0; text-align: center;">
        <a 
            style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;"
            href="{{ config('frontend.base_url') . '/' . config('frontend.endpoints.authentication.registerMember') }}"
        >
            Regristrarse
        </a>
    </div>
@include('emails.partials.footer')