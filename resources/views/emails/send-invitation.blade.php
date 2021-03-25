@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola <b>{{ $name }}</b>,<br>
    </div>
    <div style="padding: 10px 0; font-size: 16px; text-align: center; line-height: 1.3;">
        Has recibido esta notificación porque queremos que usted forme parte de nuestro equipo. Por favor, para hacer efectivo el registro, termine de llenar los campos dando clic en el siguiente botón:
    </div>
    <div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="http://platt.incdustry.com/autenticacion/registrar-integrante/{{ $name }}">Registrarse</a></div>
@include('emails.partials.footer')