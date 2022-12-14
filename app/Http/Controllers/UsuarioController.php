<?php

namespace App\Http\Controllers;

use App\Models\Estabelecimento;
use App\Models\Resgate;
use App\Models\Usuario;
use CustomConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function login(Request $request)
    {
        $rules = array("DS_LOGIN_USIG" => "required", "DS_SENHA_USIG" => "required");
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        } else {
            try {
                $usuario = Usuario::where('DS_LOGIN_USIG', $request->DS_LOGIN_USIG)->first();
                $est = $usuario ? Estabelecimento::where('PK_ESTABELECIMENTO_ETIG', $usuario->FK_ESTABELECIMENTO_USIG)->first() : null;
    
                if (!$usuario || !Hash::check($request->DS_SENHA_USIG, $usuario->DS_SENHA_USIG)) {
    
                    return response(
                        [
                            "success" => false,
                            "status_code"  => 404,
                            "message" => "Usuário não encontrado."
                        ]
                    );
                }
    
                CustomConnection::criarNovaConexaoComBanco($est);
    
                $token = $usuario->createToken('my-app-token')->plainTextToken;
    
                $result = ["usuario" => $usuario, "estabelecimento" => $est, "token" => $token];
    
                return response()->json(["status_code" => 200, "success" => true, "data" => $result]);
            } catch (\Throwable $th) {
                return response(
                    [
                        "success" => false,
                        "status_code" => 500,
                        "message" => $th->getMessage()
                    ],
                    500
                );
            }
        }
    }


    public function registar(Request $request)
    {
        $rules = array(
            "FK_ESTABELECIMENTO_USIG" => "required",
            "DS_NOME_USIG" => "required",
            "DS_EMAIL_USIG" => "required",
            "DS_TELEFONE_USIG" => "required",
            "DS_CELULAR_USIG" => "required",
            "DS_LOGIN_USIG" => "required",
            "DS_SENHA_USIG" => "required",
        );
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        } else {

            try {
                $usuarioResult = Usuario::where('DS_LOGIN_USIG', $request->DS_LOGIN_USIG)->first();

                if ($usuarioResult) {
                    return response(
                        [
                            "success" => false,
                            "status_code" => 500,
                            "message" => "Este usuário existe em nossos registros. Por favor, escolha outro!"
                        ],
                        500
                    );
                }

                $usuario = new Usuario();
    
                $usuario->FK_ESTABELECIMENTO_USIG = $request->FK_ESTABELECIMENTO_USIG;
                $usuario->DS_NOME_USIG = $request->DS_NOME_USIG;
                $usuario->DS_EMAIL_USIG = $request->DS_EMAIL_USIG;
                $usuario->DS_TELEFONE_USIG = $request->DS_TELEFONE_USIG;
                $usuario->DS_CELULAR_USIG = $request->DS_CELULAR_USIG;
                $usuario->DS_LOGIN_USIG = $request->DS_LOGIN_USIG;
                $usuario->DS_SENHA_USIG = Hash::make($request->DS_SENHA_USIG);
                $usuario->NR_PONTOS_USIG = 0;
                $usuario->DT_CADASTRO_USIG = date('Y-m-d H:i:s');
    
                $result = $usuario->save();
    
                if ($result) {
    
                    $token = $usuario->createToken('my-app-token')->plainTextToken;
    
                    $result = ["usuario" => $usuario, "token" => $token];
    
                    return response()->json(["status_code" => 200, "success" => true, "data" => $result]);
                } else {
                    return response(
                        [
                            "status_code" => 500,
                            "success" => false,
                            "message" => "Não foi possível cadastrar o usuário."
                        ],
                        500
                    );
                }
            } catch (\Throwable $th) {
                return response(
                    [
                        "success" => false,
                        "status_code" => 500,
                        "message" => $th->getMessage()
                    ],
                    500
                );
            }
        }
    }

    public function obter($idUsuario = null)
    {
        try {
            if ($idUsuario) {
                $result = Usuario::where('PK_USUARIO_USIG', $idUsuario)->first();
            } else {
                $result = Usuario::all();
            }
    
            if ($result) {
                return response(
                    [
                        "success" => true,
                        "status_code" => 200,
                        "data" => $result
                    ],
                    200
                );
            } else {
                return response(
                    [
                        "success" => false,
                        "status_code" => 500,
                        "message" => "Nada encontrado"
                    ],
                    500
                );
            }
        } catch (\Throwable $th) {
            return response(
                [
                    "success" => false,
                    "status_code" => 500,
                    "message" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function putPontosUsuario(Request $request)
    {
        $rules = array(
            "PK_USUARIO_USIG" => "required",
            "FK_ESTABELECIMENTO_USIG" => "required",
            "NR_PONTOS_USIG" => "required",
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        } else {

            try {
                $result = Usuario::where("PK_USUARIO_USIG", $request->PK_USUARIO_USIG)->where("FK_ESTABELECIMENTO_USIG", $request->FK_ESTABELECIMENTO_USIG)->update(["NR_PONTOS_USIG" => $request->NR_PONTOS_USIG]);

                if ($result) {
                    return response(
                        [
                            "status_code" => 200,
                            "success" => true,
                            "message" => "Pontos atualizados com sucesso."
                        ],
                        200
                    );
                } else {
                    return response(
                        [
                            "status_code" => 500,
                            "success" => false,
                            "message" => "Não foi possível atualizar os pontos."
                        ],
                        500
                    );
                }
            } catch (\Throwable $th) {
                return response(
                    [
                        "success" => false,
                        "status_code" => 500,
                        "message" => $th->getMessage()
                    ],
                    500
                );
            }
        }
    }
}
