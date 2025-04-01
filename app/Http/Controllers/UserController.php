<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class UserController extends Controller
{   
    public function index() 
    {
        return view("admin.users.index");
    }

    public function create() 
    {
        return view("admin.users.create");
    }

    public function store() 
    {
        
        return view("admin.users.create");
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
        return view("admin.users.edit", compact("usuario"));
    }
    
    public function update() 
    {
        
        return view("admin.users.create");
    }

    public function destroy() 
    {
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
