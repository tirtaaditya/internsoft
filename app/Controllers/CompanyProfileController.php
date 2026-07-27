<?php

namespace App\Controllers;

class CompanyProfileController extends BaseController
{
    public function index()
    {
        return view('company_profile/index', [
            'companyName' => 'Internsoft Technology Solutions',
            'domain' => 'Internsoft.my.id',
            'comingSoonFeature' => 'API WhatsApp',
        ]);
    }
}
