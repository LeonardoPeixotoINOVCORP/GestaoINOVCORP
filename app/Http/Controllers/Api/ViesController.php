<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ViesController extends Controller
{
    public function lookup(string $nif): JsonResponse
    {
        // Tenta determinar o país pelo prefixo (ex: PT, ES)
        $pais = 'PT';
        $numero = $nif;

        if (strlen($nif) > 2 && ctype_alpha(substr($nif, 0, 2))) {
            $pais = strtoupper(substr($nif, 0, 2));
            $numero = substr($nif, 2);
        }

        try {
            $response = Http::timeout(5)->get("https://ec.europa.eu/taxation_customs/vies/rest-api/ms/{$pais}/vat/{$numero}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['isValid'] ?? false) {
                    return response()->json([
                        'nome'   => $data['name'] ?? null,
                        'morada' => $data['address'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {

        }

        return response()->json(['nome' => null, 'morada' => null]);
    }
}