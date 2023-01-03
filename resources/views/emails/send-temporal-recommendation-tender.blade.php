@include('emails.partials.header')
    <div style="padding: 10px 0; font-size: 24px; text-align: center; line-height: 40px;">
        Hola, <b>{{ $companyName }}</b> hay una licitación nueva que te podría interesar.
    </div>
    <div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #2168F5; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{{ config('frontend.base_url') . '/' . config('frontend.endpoints.tender.tenderDetail').'/'.$slugCompany.'/'.config('frontend.endpoints.tender.tender').'/'.$tenderId}}">Ir a licitación</a></div>
@include('emails.partials.footer')