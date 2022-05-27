@include('emails.partials.header')

    <div style="padding: 10px 0; font-size: 16px; text-align: center; line-height: 1.5;">
    Hola, has recibido una invitación por parte de <b>{{ $companyName }}</b> a la licitación denominada: <b>{{ $tenderName }}</b>. 
    <br>Para acceder a ella, suscribete en:
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