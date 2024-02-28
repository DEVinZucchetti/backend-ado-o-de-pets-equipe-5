<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Adoption;
use App\Models\Client;

class AdoptionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'pet_id' => 'required|exists:pets,id',
                'name' => 'required|string',
                'email' => 'required|email',
                'cpf' => 'required|string',
                'contact' => 'required|string|max:20',
                'observations' => 'nullable|string',
                'status' => 'required|in:PENDENTE,NEGADO,APROVADO',
            ]);

            // Crie um novo registro na tabela 'adoptions'
            $adoption = Adoption::create($request->all());

            // Retorne uma resposta adequada para o sucesso (HTTP 201)
            return response()->json(['message' => 'Adoption created successfully', 'data' => $adoption], 201);
        } catch (\Exception $e) {
            // Em caso de falha, retorne uma resposta apropriada (HTTP 500)
            return response()->json(['message' => 'Failed to create adoption', 'error' => $e->getMessage()], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $query = Adoption::query();

            // Aplicar filtros, se fornecidos na requisição
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->has('email')) {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            }

            if ($request->has('contact')) {
                $query->where('contact', 'like', '%' . $request->input('contact') . '%');
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Obter as adoções filtradas
            $adoptions = $query->get();

            // Retorne uma resposta adequada (HTTP 200)
            return response()->json(['message' => 'Adoptions retrieved successfully', 'data' => $adoptions], 200);
        } catch (\Exception $e) {
            // Em caso de falha, retorne uma resposta apropriada (HTTP 500)
            return response()->json(['message' => 'Failed to retrieve adoptions', 'error' => $e->getMessage()], 500);
        }
    }
    public function realizeAdoption(Request $request, ClientController $clientController)
    {
        try {
            $request->validate([
                'adoption_id' => 'required|exists:adoptions,id',
                'client_name' => 'required|string',
                'client_email' => 'required|email',
                'client_cpf' => 'required|string',
            ]);

            $adoption = Adoption::findOrFail($request->input('adoption_id'));

            $client = $clientController->store($request);

            $adoption->client_id = $client->id;
            $adoption->status = 'APROVADO'; 
            $adoption->save();

            return response()->json(['message' => 'Adoption realized successfully', 'data' => $adoption], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to realize adoption', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $pet = Pet::with("race")->with("specie")->find($id);

        if ($pet->client_id) return $this->error('Dados confidenciais', Response::HTTP_FORBIDDEN);

        if (!$pet) return $this->error('Dado não encontrado', Response::HTTP_NOT_FOUND);

        return $pet;
    }
}
