<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function index()
    {
        // Obtener todos los usuarios
        $users = User::with('role')->get(); // Cargar la relación 'role'

        // Retornar la vista con los usuarios
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {

        // Obtener todos los roles
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:users',

            // Si el Administrador genera el password
            //'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
        ], [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está en uso.',

            // Si el Administrador genera el password
            //'password.required' => 'La contraseña es obligatoria.',
            //'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            //'password.confirmed' => 'Las contraseñas no coinciden.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        // Generar contraseña aleatoria
        $password = Str::random(10);

        $data = $request->all();
        $data['password'] = Hash::make($password); // Encriptar la contraseña
        $user = User::create($data); // Crear el usuario

        // Enviar credenciales por correo
        Mail::to($user->email)->send(new NewUserCredentialsMail($user, $password));


        return redirect()->route('admin.users.index')->with('message', 'Usuario creado exitosamente.');
    }


    public function edit($id)
    {

        $user = User::find($id);

        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'Usuario no encontrado.');
        }
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ], [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'Usuario no encontrado.');
        }
        $user->update($request->all());

        return redirect()->route('admin.users.index')->with('message', 'Usuario actualizado exitosamente.');
    }


    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'Usuario no encontrado.');
        }
        $user->delete();

        return redirect()->route('users.index')->with('message', 'Usuario eliminado exitosamente.');
    }


    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'Usuario no encontrado.');
        }
        return view('admin.users.show', compact('user'));
    }
}
