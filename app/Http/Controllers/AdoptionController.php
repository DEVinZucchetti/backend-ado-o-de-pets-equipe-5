<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Adoption;
use App\Models\Client;
use App\Models\People;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\SolicitationDocument;
use App\Mail\SendDocuments;
use Illuminate\Support\Facades\Mail;
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
    public function realizeAdoption(Request $request)
    {
        DB::beginTransaction();

        $data = $request->all();

        $request->validate([
            'adoption_id' => 'required|integer',
        ]);

        $adoption = Adoption::find($data['adoption_id']);

        if (!$adoption) return $this->error('Dado não encontrado', Response::HTTP_NOT_FOUND);
        $adoption->update(['status' => 'APROVADO']);
        $adoption->save();

        $people = People::create([
            'name' => $adoption->name,
            'email' => $adoption->email,
            'cpf' => $adoption->cpf,
            'contact' => $adoption->contact,
        ]);

        $client = Client::create([
            'people_id' => $people->id,
            'bonus' => true,
        ]);

        $pet = Pet::find($adoption->pet_id);
        $pet->update(['client_id' => $client->id]);
        $pet->save();

        $solicitation = SolicitationDocument::create([
            'client_id' => $client->id
        ]);

        Mail::to($people->email, $people->name)
            ->send(new SendDocuments($people->name, $solicitation->id));

        DB::commit();

        return $client;
    }

    public function show($id)
    {
        $pet = Pet::with("race")->with("specie")->find($id);

        if ($pet->client_id) return $this->error('Dados confidenciais', Response::HTTP_FORBIDDEN);

        if (!$pet) return $this->error('Dado não encontrado', Response::HTTP_NOT_FOUND);

        return $pet;
    }

    public function upload(Request $request)
    {

        $file = $request->file('file');
        $description =  $request->input('description');
        $key =  $request->input('key');
        $id =  $request->input('id');

        $slugName = Str::of($description)->slug();
        $fileName = $slugName . '.' . $file->extension();

        $pathBucket = Storage::disk('s3')->put('documentos', $file);
        $fullPathFile = Storage::disk('s3')->url($pathBucket);

        $file = File::create(
            [
                'name' => $fileName,
                'size' => $file->getSize(),
                'mime' => $file->extension(),
                'url' => $fullPathFile
            ]
        );

        return $fullPathFile;

        $solicitation = SolicitationDocument::find($id);

        if(!$solicitation) return $this->error('Dado não encontrado', Response::HTTP_NOT_FOUND);

        $solicitation->update([$key => $file->id]);

        return ['message' => 'Arquivo criado com sucesso'];
    }

    public function getAdoptionDocuments($adoptionId)
    {
        try {
            // Assumindo que você está usando o modelo SolicitationDocument para armazenar os uploads
            $solicitation = SolicitationDocument::where('adoption_id', $adoptionId)->first();
    
            if (!$solicitation) {
                return response()->json(['error' => 'Solicitação de documento não encontrada.'], Response::HTTP_NOT_FOUND);
            }
    
            // Recupera informações sobre os documentos
            $documents = [
                'document1' => File::find($solicitation->cpf),
                'document2' => File::find($solicitation->rg),
                'document3' => File::find($solicitation->document_address),
                'document4' => File::find($solicitation->term_adoption),
            ];
    
            return response()->json(['documents' => $documents]);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
}
