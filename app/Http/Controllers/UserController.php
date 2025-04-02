<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\UserCredentials;
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
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:users',
            'role_id' => 'required|exists:roles,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        // Generar base del username (ej: "jperez")
        $baseUsername = strtolower(
            substr($request['name'], 0, 1) . // Primera letra del nombre
                str_replace(' ', '', $request['surname']) // Apellido sin espacios
        );

        // Verificar si el username existe y agregar número
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Generar contraseña segura
        $password = Str::random(10) . rand(0, 9) . '!';

        // Crear usuario
        $user = User::create([
            'name' => $request['name'],
            'surname' => $request['surname'],
            'username' => $username,
            'email' => $request['email'],
            'password' => Hash::make($password),
            'phone' => $request['phone'],
            'force_password_change' => true,
            'role_id' => $request['role_id']
        ]);

        // Enviar credenciales por correo
        Mail::to($user->email)->send(new UserCredentials($user, $password));


        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }


    public function edit($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
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
            'password' => 'nullable|string|min:8|confirmed'
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
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }
        $data = $request->except('password');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }


    public function destroy($id)
    {   

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
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
