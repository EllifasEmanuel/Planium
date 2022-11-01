<?php

namespace App\Http\Controllers;

use App\Services\ArquivoService;
use Illuminate\Http\Request;

class ArquivoController extends Controller
{
    public ArquivoService $service;

    public function __construct()
    {
        $this->service = app(ArquivoService::class);
    }
    public function index () 
    {
        return view('beneficiarios');
    }

    public function criarTabela (Request $request) 
    {
        return $this->service->createTableBeneficiarios($request->quantBeneficiarios);
    }

    public function criarProposta (Request $request) 
    {
        return $this->service->validaDados($request);
    }
}
