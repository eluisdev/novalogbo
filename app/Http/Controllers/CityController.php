<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $city1 = new \stdClass();
        $city1->id = 1;
        $city1->name = "España";

        $city2 = new \stdClass();
        $city2->id = 2;
        $city2->name = "México";

        $cities = [$city1, $city2];

        return view("admin.cities.index", compact('cities'));
    }

    public function create()
    {
        return view("admin.cities.create");
    }

    public function store(Request $request)
    {
        return view("admin.cities.create");
    }


    public function edit($id)
    {
        $city = new \stdClass();
        $city->id = 1;
        $city->name = "España";

        return view("admin.cities.edit", compact('city'));
    }

    public function update(Request $request, $id)
    {
        return view("admin.cities.create");
    }


    public function destroy($id)
    {
        return view("admin.cities.create");
    }


    public function show($id)
    {
        return view("admin.cities.create");
    }
}
