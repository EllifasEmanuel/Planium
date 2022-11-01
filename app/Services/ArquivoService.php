<?php

namespace App\Services;

class ArquivoService {

    public function createTableBeneficiarios ($data) {
        ob_start();
        $arquivo_plans = $this->getPlans();
        for($i = 1; $i <= $data ; $i++){
        ?>
            <tr>
                <td scope="row" id=<?= "usuarioCodigo_$i" ?>><?= $i ?></td>
                <td>
                    <div class="col-4">
                        <input type="text" class="form-control" id=<?= "nomeBeneficiario_$i" ?> name=<?= "nome_$i" ?> placeholder="Nome">
                    </div>
                </td>
                <td>
                    <div class="col-2">
                        <input type="number" class="form-control" id=<?= "idadeBeneficiario_$i" ?> name=<?= "idade_$i" ?> placeholder="Idade">
                    </div>
                </td>
                <td>
                    <div class="col-4">
                        <select class="form-select" id=<?= "selectPlanos_$i" ?> name=<?= "planoSelecionado_$i" ?>>
                            <?php
                            foreach($arquivo_plans as $value){ ?>
                                <option value="<?= $value->codigo ?>"><?= $value->nome ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="col-2">
                        <button type="button" class="btn remover-linha" onclick="removerLinha(this)" title="Remover linha"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        <?php
        }
        $html = ob_get_clean();

        return $html;
    }

    public function criarProposta ($data) {        
        $arquivo_prices = $this->getPrices();
        $this->criarArquivoBeneficiarios($data);
        $beneficiarios = $data->dados;
        $quantidadeBeneficiarios = $data->quantidadeBeneficiarios;
        $beneficiariosPlanos = array_count_values(array_column($beneficiarios, 'plano'));
        $arr = array();

        for ($i = 0; $i < $quantidadeBeneficiarios; $i++) {
            foreach ($arquivo_prices as $value) {
                if ($value->codigo == $beneficiarios[$i]['plano']) {
                    if (array_key_exists($value->codigo, $beneficiariosPlanos)) {
                        if($beneficiariosPlanos[$value->codigo] >= $value->minimo_vidas){
                            if (in_array($beneficiarios[$i]['userId'], $arr)) {
                                array_pop($arr);
                            }
                            if($beneficiarios[$i]['idade'] >= 0 && $beneficiarios[$i]['idade'] <= 17){
                                $arr['beneficiario'.$beneficiarios[$i]['userId']] = array(
                                    'codigo_plano' => $beneficiarios[$i]['plano'],
                                    'plano' => $beneficiarios[$i]['nomePlano'],
                                    'codigo_usuario' => $beneficiarios[$i]['userId'],
                                    'nome' => $beneficiarios[$i]['nome'],
                                    'idade' => $beneficiarios[$i]['idade'],
                                    'faixa' => $value->faixa1
                                );
                            } else if($beneficiarios[$i]['idade'] >= 18 && $beneficiarios[$i]['idade'] <= 40){
                                
                                $arr['beneficiario'.$beneficiarios[$i]['userId']] = array(
                                    'codigo_plano' => $beneficiarios[$i]['plano'],
                                    'plano' => $beneficiarios[$i]['nomePlano'],
                                    'codigo_usuario' => $beneficiarios[$i]['userId'],
                                    'nome' => $beneficiarios[$i]['nome'],
                                    'idade' => $beneficiarios[$i]['idade'],
                                    'faixa' => $value->faixa2
                                );
                            } else if($beneficiarios[$i]['idade'] > 40){
                                $arr['beneficiario'.$beneficiarios[$i]['userId']] = array(
                                    'codigo_plano' => $beneficiarios[$i]['plano'],
                                    'plano' => $beneficiarios[$i]['nomePlano'],
                                    'codigo_usuario' => $beneficiarios[$i]['userId'],
                                    'nome' => $beneficiarios[$i]['nome'],
                                    'idade' => $beneficiarios[$i]['idade'],
                                    'faixa' => $value->faixa3
                                );
                            }
                        }
                    }
                }
            }
        }

        $valorTotalProposta = 0;

        foreach($arr as $value){
            $valorTotalProposta += $value['faixa'];
        }

        $arr['valorTotal'] = $valorTotalProposta;

        $caminho = $this->downloadPropostaJson($arr);
        
        return response()->json([
            "error" => false,
            "data" => $caminho
        ]);
    }

    public function validaDados ($data){
        $beneficiarios = $data->dados;
        $quantidadeBeneficiarios = $data->quantidadeBeneficiarios;
        $arquivo_plans = $this->getPlans();
        $codigo_planos = array_column($arquivo_plans,'codigo');
        for ($i = 0; $i < $quantidadeBeneficiarios; $i++) {
            if (empty($beneficiarios[$i]['nome']) || is_null($beneficiarios[$i]['nome'])) {
                return response()->json([
                    "error" => true,
                    "data" => "Linha {$beneficiarios[$i]['userId']} - Nome invalido."
                ]);
            }
            if (empty($beneficiarios[$i]['idade']) || $beneficiarios[$i]['idade'] < 0) {
                return response()->json([
                    "error" => true,
                    "data" => "Linha {$beneficiarios[$i]['userId']} - Idade invalida."
                ]);
            }
            if (!in_array($beneficiarios[$i]['plano'], $codigo_planos)) {
                return response()->json([
                    "error" => true,
                    "data" => "Linha {$beneficiarios[$i]['userId']} - Plano não encontrado."
                ]);
            }
        }

        return $this->criarProposta($data);
    }

    public function getPrices () {
        $arquivo_prices = json_decode(file_get_contents('json/prices.json'));

        return $arquivo_prices;
    }

    public function getPlans () {
        $arquivo_plans = json_decode(file_get_contents('json/plans.json'));

        return $arquivo_plans;
    }

    public function downloadPropostaJson($data) {
        $data = json_encode($data);
        $time = time();
        $caminho = "json/propostas/proposta$time.json";
        file_put_contents($caminho, $data);

        return $caminho;
    }

    public function criarArquivoBeneficiarios($data){
        $data = json_encode($data->dados);
        $time = time();
        $caminho = "json/beneficiarios/beneficiarios$time.json";
        file_put_contents($caminho, $data);
    }

}