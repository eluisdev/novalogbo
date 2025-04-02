<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContinentController extends Controller
{
    public function index()
    {
        $continent1 = new \stdClass();
        $continent1->id = 1;
        $continent1->name = "España";

        $continent2 = new \stdClass();
        $continent2->id = 2;
        $continent2->name = "México";

        $continents = [$continent1, $continent2]; // Array de objetos

        return view("admin.continents.index", compact('continents'));
    }

    public function create()
    {
        return view("admin.continents.create");
    }

    public function store(Request $request)
    {
        return view("admin.continents.create");
    }


    public function edit($id)
    {
        $continent = new \stdClass();
        $continent->id = 1;
        $continent->name = "España";

        return view("admin.continents.edit", compact('continent'));
    }

    public function update(Request $request, $id)
    {
        return view("admin.continents.create");
    }


    public function destroy($id)
    {
        return view("admin.continents.create");
    }


    public function show($id)
    {
        return view("admin.continents.create");
    }
}
