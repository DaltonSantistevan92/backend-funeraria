<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator,Hash};
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $requestUser = collect($request)->all();
        $validarUsuario = $this->validateUser($requestUser);

        if ($validarUsuario['status']) {
            $user = User::where('email', $requestUser['email'])->first();

            if ($user != null) {
                $hashPassword = Hash::check($requestUser['password'], $user->password);

                if ($this->validarCheckPassword($hashPassword, $user->password)) {
                    $user->rol;
                    //$user->persona;
                    //$menu = $this->permisoCtrl->permisosAppWeb($user->rol->id);
                    $payload = ['user' => $user];
                    $token = JWTAuth::customClaims($payload)->fromUser($user);
                    
                    $response = [ 'status' => true,'message' => "Acceso al Sistema Web", 'token' => $token];
                } else {
                    $response = ['status' => false, 'message' => "Contraseña Incorrecta"];
                }
            } else {
                $response = ['status' => false, 'message' => "No tiene Acceso al Sistema"];
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'No se pudo logear',
                'fails' => [
                    'error_user' => $validarUsuario["error"] ?? "No presenta errores",
                ],
            ];
        }
        return response()->json($response);
    }

    public function crearCuenta(Request $request){
        $requestUser = collect($request)->all();   
        $validarUsuario = $this->validateUser($requestUser);

        if ($validarUsuario['status']) {
            $encriptarPassword = Hash::make($requestUser['password']);

            $existeCorreo = User::where('email', $requestUser['email'])->get()->first();

            if ($existeCorreo) {
                $response = ['status' => false, 'message' => "El correo ya existe"];  
            }else {
                User::create([
                    'rol_id' => 3,//cliente
                    'name' => $requestUser['name'],
                    'email' => $requestUser['email'],
                    'password' => $encriptarPassword
                ]);
    
                $response = ['status' => true, 'message' => "Se registró con exito"];
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'No se pudo crear el usuario',
                'falla' => [
                    'error_usuario' => $validarUsuario['error'] ?? 'No presenta errores',
                ],
            ];
        } 
        return response()->json($response, 200);

    }

    private function validarCheckPassword($hashPassword, $passwordUser)
    {
        if ($hashPassword == $passwordUser) {
            return true;
        } else {
            return false;
        }
    }

    public function validateUser($request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $messages = [
            'email.required' => 'El campo correo es requerido',
            'email.email' => 'El correo no tiene un formato válido',
            'password.required' => 'El campo contraseña es requerido',
        ];
        return $this->validation($request, $rules, $messages);
    }

    public function validation($request, $rules, $messages)
    {
        $response = ['status' => true, 'message' => 'No hubo errores'];

        $validate = Validator::make($request, $rules, $messages);

        if ($validate->fails()) {
            $response = ['status' => false, 'message' => 'Error de validación', 'error' => $validate->errors()];
        }
        return $response;
    }

    public function getRoles(){
        $roles = Rol::all();
        return response()->json($roles);
    }
}
