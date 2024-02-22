<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adocao;

class AdocaoController extends Controller
{
    public function cadastrarAdocao(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'contact' => 'required|string',
            'observacoes' => 'nullable|string',
            'status' => 'required|in:PENDENTE,NEGADO,APROVADO',
        ]);

        $adocao = Adocao::create($request->all());

        return response()->json(['message' => 'Adoção cadastrada com sucesso', 'adocao' => $adocao], 201);
    }
    public function listarAdocoes(Request $request)
    {
        $query = Adocao::query();

        // Filtragem por nome
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filtragem por email
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        // Filtragem por contato
        if ($request->has('contact')) {
            $query->where('contact', 'like', '%' . $request->input('contact') . '%');
        }

        // Filtragem por status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Obtém os resultados paginados
        $adocoes = $query->paginate(10);

        return response()->json($adocoes);
    }
}