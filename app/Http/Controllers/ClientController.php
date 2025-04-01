<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index() 
    {
        return view("clients.index");
    }

    public function create() 
    {
        return view("clients.create");
    }

    public function store() 
    {
        
        return view("clients.create");
    }

    public function edit() 
    {
        $usuario = (object)[
            'id' => "1",
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'telefono' => '987654321',
            "password" => "12612lkalsfa",
            'username' => 'juanperez',
            'role' => 'admin'
        ];
        return view("clients.edit", compact("usuario"));
    }
    
    public function update() 
    {
        
        return view("clients.create");
    }

    public function destroy() 
    {
        return redirect()->route('clients.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
