<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $service1 = new \stdClass();
        $service1->id = 1;
        $service1->name = "España";

        $service2 = new \stdClass();
        $service2->id = 2;
        $service2->name = "México";

        $services = [$service1, $service2]; // Array de objetos

        return view("admin.services.index", compact('services'));
    }

    public function create()
    {
        return view("admin.services.create");
    }

    public function store(Request $request)
    {
        return view("admin.services.create");
    }


    public function edit($id)
    {
        $service = new \stdClass();
        $service->id = 1;
        $service->name = "España";

        return view("admin.services.edit", compact('service'));
    }

    public function update(Request $request, $id)
    {
        return view("admin.services.create");
    }


    public function destroy($id)
    {
        return view("admin.services.create");
    }


    public function show($id)
    {
        return view("admin.services.create");
    }
}
