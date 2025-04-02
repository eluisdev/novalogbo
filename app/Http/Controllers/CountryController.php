<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $country1 = new \stdClass();
        $country1->id = 1;
        $country1->name = "España";

        $country2 = new \stdClass();
        $country2->id = 2;
        $country2->name = "México";

        $countries = [$country1, $country2]; // Array de objetos

        return view("admin.countries.index", compact('countries'));
    }

    public function create()
    {
        return view("admin.countries.create");
    }

    public function store(Request $request)
    {
        return view("admin.countries.create");
    }


    public function edit($id)
    {
        $country = new \stdClass();
        $country->id = 1;
        $country->name = "España";

        return view("admin.countries.edit", compact('country'));
    }

    public function update(Request $request, $id)
    {
        return view("admin.countries.create");
    }


    public function destroy($id)
    {
        return view("admin.countries.create");
    }


    public function show($id)
    {
        return view("admin.countries.create");
    }
}
