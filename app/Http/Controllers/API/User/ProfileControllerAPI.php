<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileControllerAPI extends Controller
{
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $dataToUpdate = [
                'name'    => $request->input('name', $user->name),
                'phone'   => $request->input('phone', $user->phone),
                'curso'   => $request->input('curso', $user->curso),
                'n_aluno' => $request->input('n_aluno', $user->n_aluno),
            ];

            // --- LÓGICA DA FOTO DE PERFIL ---
            if ($request->hasFile('image')) {
                // 1. Se o utilizador já tiver uma imagem antiga, apagamos o ficheiro anterior
                if ($user->image && \Storage::disk('public')->exists($user->image)) {
                    \Storage::disk('public')->delete($user->image);
                }
                
                // 2. Guarda a imagem nova na pasta 'storage/app/public/profiles'
                $path = $request->file('image')->store('profiles', 'public');
                $dataToUpdate['image'] = $path;
            }

            // Segurança do Admin para mudar o tipo
            $adminIds = [1, 2]; 
            if (in_array($user->user_type_id, $adminIds) && $request->has('user_type_id')) {
                $dataToUpdate['user_type_id'] = $request->input('user_type_id');
            }

            $user->update($dataToUpdate);

            return response()->json([
                'status' => 'success',
                'message' => 'Perfil atualizado com sucesso!',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}