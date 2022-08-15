<?php

namespace App\Http\Controllers\WebControllers\company;

use DataTables;
use App\Models\Type;
use App\Models\Company;
use App\Models\Addresses;
use App\Mail\BannedAccount;
use Illuminate\Http\Request;
use App\Mail\UnbannedAccount;
use App\Mail\RejectedAccount;
use App\Models\Notifications;
use App\Mail\ValidatedAccount;
use Illuminate\Validation\Rule;
use App\Models\TendersCompanies;
use App\Models\Tenders;
use App\Models\Quotes;
use App\Models\TendersVersions;
use App\Models\QuotesVersions;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\TemporalInvitationCompany;
use App\Models\TemporalInvitationCompanyQuote;

class CompanyController extends Controller
{
    public function index()
    {
        $types = Type::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return view('company.index2', compact('types'));
    }

    public function getCompanyType($type)
    {
        $companies_a = Company::select('companies.*')
            ->where('companies.status', '=', Company::COMPANY_CREATED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', $type)
            ->orderBy('companies.updated_at', 'desc')
            ->get();

        $companies_b = Company::select('companies.*')
            ->where('companies.status', '<>', Company::COMPANY_CREATED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', $type)
            ->orderBy('companies.updated_at', 'desc')
            ->get();

        $companies = $companies_a->merge($companies_b);

        return view('company.index', compact('companies', 'type'));
    }

    public function edit($id)
    {
        $company = Company::find($id);
        return view('company.edit', compact('company'));
    }

    public function show($id)
    {
        $company = Company::find($id);
        return view('company.show', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name'          => ['required', Rule::unique('companies')->ignore($id)],
            'nit'           => ['required', Rule::unique('companies')->ignore($id)],
            'country_code'  => ['required']
        ];

        $this->validate($request, $rules);


        $company                = Company::find($id);
        $company->name          = $request->name;
        $company->nit           = $request->nit;
        $company->country_code  = $request->country_code;

        $company->save();

        if (!is_null($request->address)) {
            $address    = Addresses::where('addressable_id', $id)
                ->where('addressable_type', Company::class);

            if ($address->exists()) {
                $address->update(['address' => $request->address]);
            } else {
                $address = new Addresses;
                $address->addressable_id    = $id;
                $address->addressable_type  = Company::class;
                $address->address           = $request->address;
                $address->save();
            }
        }

        return redirect()->route('companies-type', ($company->type_entity->type->name == 'Demanda') ? 'Demanda' : 'Oferta')->with([
            'title' => "La compañia fue actulizada con exito",
        ]);
    }

    public function editStatus(Request $request)
    {
        $company = Company::find($request->id);
        $initialState = $company->status;

        // Cambiamos el estado de la compañia
        $company->status = $request->status;

        $company->save();
        // Enviamos mensaje al correo del usuario
        if ($request->status == Company::COMPANY_APPROVED) {

            if ($initialState == Company::COMPANY_CREATED || $initialState == Company::COMPANY_REJECTED) {
                // Invita a la compañia a participar en una licitación/es.
                $this->createTenderCompanyInvitation($company);
                // Invita a la compañia a participar en una licitación/es.
                $this->createQuoteCompanyInvitation($company);
                Mail::to(trim($company->user->email))->send(new ValidatedAccount($company->user));
                $message = "La compañia se ha aprobado con exito y se ha enviado un correo de confirmación(" . $company->user->email . ").";
            } else if ($initialState == Company::COMPANY_BANNED) {
                Mail::to(trim($company->user->email))->send(new UnbannedAccount($company->user));
                $message = "La compañia se desbloqueado y se ha enviado un correo de confirmación(" . $company->user->email . ").";
            }
        }

        if ($request->status == Company::COMPANY_REJECTED) {
            Mail::to(trim($company->user->email))->send(new RejectedAccount($company->user));
            $message = "La compañia no ha sido aprobada y se ha enviado un correo de confirmación(" . $company->user->email . ").";
        }

        if ($request->status == Company::COMPANY_BANNED) {
            Mail::to(trim($company->user->email))->send(new BannedAccount($company->user));
            $message = "La compañia ha sido bloqueada y se ha enviado un correo de confirmación(" . $company->user->email . ").";
        }

        return response()->json(['message' => $message], 200);
    }

    public function createTenderCompanyInvitation($company)
    {
        $query  = TemporalInvitationCompany::where(strtolower('email'), strtolower($company->user->email));

        if ($query->exists()) {
            foreach ($query->get() as $value) {
                $tender = Tenders::find($value->tender_id);
                if ($tender) {
                    if (($tender->tendersVersionLast()->status == TendersVersions::LICITACION_PUBLISH) ? true : false) {
                        $tenderCompany = $this->createTenderCompany($company, $value->tender_id);
                        if ($tenderCompany) {
                            $this->sendNotificationTenderInvitation($tenderCompany, $company->user->id);
                        }
                    }
                }
            }
        }
    }

    public function createQuoteCompanyInvitation($company)
    {
        $query  = TemporalInvitationCompanyQuote::where(strtolower('email'), strtolower($company->user->email));

        if ($query->exists()) {
            foreach ($query->get() as $value) {
                $quote = Quotes::find($value->quote_id);
                if ($quote) {
                    if (($quote->quotesVersionLast()->status == QuotesVersions::QUOTATION_PUBLISH) ? true : false) {
                        $quoteCompany = $this->createQuoteCompany($company, $value->quote_id);
                        if ($quoteCompany) {
                            $this->sendNotificationQuoteInvitation($quoteCompany, $company->user->id);
                        }
                    }
                }
            }
        }
    }

    public function sendNotificationTenderInvitation($tenderCompany, $user_id)
    {
        $notifications = new Notifications();
        $notifications->registerNotificationQuery($tenderCompany, Notifications::NOTIFICATION_TENDERINVITECOMPANIES, [$user_id]);
    }

    public function sendNotificationQuoteInvitation($tenderCompany, $user_id)
    {
        $notifications = new Notifications();
        $notifications->registerNotificationQuery($tenderCompany, Notifications::NOTIFICATION_QUOTEINVITECOMPANIES, [$user_id]);
    }

    public function createTenderCompany($company, $tender_id)
    {
        $tenderCompanies = TendersCompanies::where('tender_id', $tender_id)
            ->where('company_id', $company->id)
            ->exists();

        if ($tenderCompanies) {
            return false;
        } else {
            $fields['tender_id']   = $tender_id;
            $fields['company_id']  = $company->id;
            $fields['user_id']     = $company->user->id;
            $fields['status']      = TendersCompanies::STATUS_PROCESS;

            return TendersCompanies::create($fields);
        }
    }

    public function createQuoteCompany($company, $quotes_id)
    {

        $quotesCompanies = QuotesCompanies::where('quotes_id', $quotes_id)
            ->where('company_id', $company->id)
            ->exists();

        if ($quotesCompanies) {
            return false;
        } else {
            $fields['quotes_id']   = $quotes_id;
            $fields['company_id']  = $company->id;
            $fields['user_id']     = $company->user->id;
            $fields['status']      = QuotesCompanies::STATUS_PROCESS;

            return QuotesCompanies::create($fields);
        }
    }

    public function getTypeCompanies(Request $request)
    {
        $type_id  = $request->type_id;

        $companies_a = Company::select('companies.*')
            ->where('companies.status', '=', Company::COMPANY_CREATED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.id', '=', $type_id)
            ->orderBy('companies.updated_at', 'desc')
            ->get();

        $companies_b = Company::select('companies.*')
            ->where('companies.status', '<>', Company::COMPANY_CREATED)
            ->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.id', '=', $type_id)
            ->orderBy('companies.updated_at', 'desc')
            ->get();

        $companies = $companies_a->merge($companies_b);

        return DataTables::of($companies)
            ->addColumn('type_entity', 'company.datatables.entity')
            ->addColumn('status', 'company.datatables.status')
            ->addColumn('action', 'company.datatables.action')
            ->rawColumns(['actions', 'status', 'type_entity'])
            ->toJson();
    }
    // --------------------------------------------------------------------------------------------------------------------

    public function indexCompanyProject()
    {
        $status = [
            Company::COMPANY_CREATED,
            Company::COMPANY_APPROVED,
            Company::COMPANY_REJECTED,
            Company::COMPANY_BANNED
        ];

        return view('company.projects.index', compact('status'));
    }

    public function indexCompanyVendors()
    {
        $status = [
            Company::COMPANY_CREATED,
            Company::COMPANY_APPROVED,
            Company::COMPANY_REJECTED
        ];

        return view('company.projects.index', compact('status'));
    }
}
