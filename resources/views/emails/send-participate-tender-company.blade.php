@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola, la compañia <b>{{ $CompanyName }}</b>
    </div>
    <div style="padding: 10px 0; font-size: 16px; text-align: center; line-height: 1.3;">
        Solicita participar en la licitación <b>{{ $tenderName }}.
    </div>
    <div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{ config('frontend.base_url') . '/' . config('frontend.endpoints.authentication.login') }}">Inicia sesión aquí</a></div>
@include('emails.partials.footer')