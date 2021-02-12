@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola <b>{{ $name }}</b>,<br><br>
        Gracias por registrarse! 
        <br>
    </div>
    @if ($entity->id == 1 )
        <div style="padding: 10px 0; font-size: 16px; text-align: center; line-height: 1.3;">
            Estamos validando la información suministrada, si todos los datos son correctos en menos de <b>48 horas</b> tendrás acceso a Plattaforma
        </div>
    @endif
@include('emails.partials.footer')