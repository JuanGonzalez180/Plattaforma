@include('emails.partials.header')
<div style="padding: 10px 0; font-size: 15px; line-height: 1.3;">
    Se informa que la compañia <b>{{ $companyName }}</b>, ha realizado una pregunta bajo la licitación <b>{{ $tenderName }}</b> :
</div>
<div style="padding: 10px 0; font-size: 15px; line-height: 1.3; font-style: oblique;">
    "<b>{{ $question }}</b>"
</div>
<div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{ config('frontend.base_url') . '/' . config('frontend.endpoints.tender.tenderDetail'). '/' .$slugCompany. '/' .config('frontend.endpoints.tender.tender') .'/'.$tenderId  }}">Responder</a></div>
@include('emails.partials.footer')