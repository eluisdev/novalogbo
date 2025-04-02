<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IncotermController extends Controller
{
    public function index()
    {
        $incoterms1 = new \stdClass();
        $incoterms1->id = 1;
        $incoterms1->name = "España";

        $incoterms2 = new \stdClass();
        $incoterms2->id = 2;
        $incoterms2->name = "México";

        $incoterms = [$incoterms1, $incoterms2]; // Array de objetos

        return view("admin.incoterms.index", compact('incoterms'));
    }

    public function create()
    {
        return view("admin.incoterms.create");
    }

    public function store(Request $request)
    {
        return view("admin.incoterms.create");
    }


    public function edit($id)
    {
        $incoterm = new \stdClass();
        $incoterm->id = 1;
        $incoterm->name = "España";

        return view("admin.incoterms.edit", compact('incoterm'));
    }

    public function update(Request $request, $id)
    {
        return view("admin.incoterms.create");
    }


    public function destroy($id)
    {
        return view("admin.incoterms.create");
    }


    public function show($id)
    {
        return view("admin.incoterms.create");
    }
}
