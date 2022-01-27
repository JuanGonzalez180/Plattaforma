@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola <b>{{ $name }}</b>,<br>
    </div>
    <div style="padding: 10px 0; font-size: 16px; text-align: center; line-height: 1.3;">
        Se ha generado un código para restablecer su contraseña: {{ $code }}
    </div>
@include('emails.partials.footer')