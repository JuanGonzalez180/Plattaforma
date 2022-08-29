@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola, la compañia <b>{{ $company_name }}</b>
    </div>
    <div style="padding: 10px 0; font-size: 15px; text-align: center; line-height: 1.3;">
    El administrador de la licitación <b>{{$tender_name}}</b> ha decidido cerrar y eliminar la licitación. por el cual la compañía <b>{{$company_name}}</b> deja de estar participando en dicha licitación.
    </div>
    <div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{ config('frontend.base_url') . '/' . config('frontend.endpoints.authentication.login') }}">Inicia sesión aquí</a></div>
@include('emails.partials.footer')