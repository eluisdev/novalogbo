<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Cost;
use App\Models\Country;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Incoterm;
use App\Models\Continent;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    //

    public function index()
    {
        if (Auth::user()->role_id === 1) {
            $quotations = Quotation::all();
        }
        else {
            $quotations = Quotation::where('users_id', Auth::id())->get();
        }

        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        return view('quotations.create', [
            'continents' => Continent::where('is_active', true)->get(),
            'incoterms' => Incoterm::where('is_active', true)->get(),
            'services' => Service::where('is_active', true)->get(),
            'costs' => Cost::where('is_active', true)->get(),
            'customers' => Customer::where('active', true)->get(),
        ]);
    }

    public function getCountries(Continent $continent)
    {
        return $continent->countries()->where('is_active', true)->get();
    }

    public function getCities(Country $country)
    {
        return $country->cities()->where('is_active', true)->get();
    }




}
