@include('emails.partials.header')
<div style="padding: 10px 0; font-size: 15px; line-height: 1.3;">
    Se informa que el encargado de la licitación <b>{{ $quoteName }}</b> de la compañia <b>{{ $companyName }}</b> ha hecho un anuncio en el muro de consultas.
</div>
<div style="padding: 10px 0; font-size: 15px; line-height: 1.3; font-style: oblique;">
    "<b>{{ $globalMessage }}</b>"
</div>
<br>
<div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{ config('frontend.base_url') . '/' . config('frontend.endpoints.tender.tenderDetail'). '/' .$slugCompany. '/' .config('frontend.endpoints.quote.quote') .'/'.$quoteId  }}">Ver anuncio</a></div>
@include('emails.partials.footer')