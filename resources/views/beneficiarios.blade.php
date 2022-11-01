@extends('app')
@section('content')
<div class="container">
    <div class="row">
        <div class="area-beneficiarios mt-3">
            <h3 class="title text-center mb-3"><img src="{!! asset('imagens/planium.png') !!}" width="300"></h3>
            <p>Digite a quantidade de beneficiarios:</p>
            <input type="number" class="form-control beneficiarios" id="quantidadeBeneficiarios">
            <button class="button-acao" onclick="criarTabelaBeneficiarios()">Confirmar</button>
        </div>
    </div>
    <div class="mt-3">
        
    <form id="formBeneficiarios" class="area-form">
        <div class="row g-3 col-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Idade</th>
                        <th scope="col">Plano</th>
                        <th scope="col">Ação</th>
                    </tr>
                </thead>
                <tbody class="area-conteudo">

                </tbody>
            </table>
            <div class="text-center">
                <button type="button" class="button-acao mt-3 form-beneficiarios">Criar proposta</button>
            </div>
        </div>
    </form>
    </div>
</div>
@endsection

@section('script')
<script>
    var CSRF_TOKEN = jQuery('meta[name="csrf-token"]').attr('content');

    jQuery(document).on("click", ".form-beneficiarios", function(e) {
        bootbox.confirm({
            message: "Deseja editar o usuário?",
            buttons: {
                confirm: {
                    label: 'Sim',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Não',
                    className: 'btn-danger'
                }
            },
            callback: function(result){ 
                if(result){
                    jQuery('#formBeneficiarios').submit()
                }
            }
        })
    })

    const criarTabelaBeneficiarios = () =>{
        const quantBeneficiarios = document.querySelector("#quantidadeBeneficiarios").value;
        const tabela = document.querySelector(".area-conteudo");
        const areaForm = document.querySelector("#formBeneficiarios")
        tabela.innerHTML = ``;
        
        if(parseInt(quantBeneficiarios) && parseInt(quantBeneficiarios) >= 1){
            jQuery.ajax({
                url: "{{URL::to('planos')}}",
                type: 'GET',
                data: {
                    quantBeneficiarios          
                }
            }).done(function(res){
                areaForm.style.display = "unset"
                tabela.innerHTML += `${res}`;
            })
        }else{
            areaForm.style.display = "none"
            bootbox.alert("Entrada inválida. Por favor, insira números acima de 1.");
        }
    }

    
    jQuery("#formBeneficiarios").submit(function(e){
        e.preventDefault();
        const quantidadeBeneficiarios = document.querySelector("#quantidadeBeneficiarios").value;
        let dados = [];
        for(let i = 1; i <= quantidadeBeneficiarios; i++){
            valores = {
                userId: document.querySelector(`#usuarioCodigo_${i}`).textContent,
                nome: document.querySelector(`#nomeBeneficiario_${i}`).value,
                idade: document.querySelector(`#idadeBeneficiario_${i}`).value,
                plano: document.querySelector(`#selectPlanos_${i}`).value,
                nomePlano: jQuery("#selectPlanos_1 option:selected").text()
            };
            dados.push(valores);
        }
        jQuery.ajax({
            url: "{{URL::to('proposta')}}",
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                dados: dados,
                quantidadeBeneficiarios: quantidadeBeneficiarios
            }
        }).done(function(res){
            if(res.error == true){
                bootbox.alert(res.data);
            }else{
                urlArquivo = window.location.origin+'/'+res.data
                var link = document.createElement("a");
                link.setAttribute('download', name);
                link.href = urlArquivo;
                document.body.appendChild(link);
                link.click();
                link.remove();
            }
        })
    })

    const removerLinha = (e) =>{
        const linhaTabela = e.parentElement.parentElement.parentElement
        const tabela = document.querySelector(".area-conteudo");
        const quantidadeBeneficiarios = document.querySelector("#quantidadeBeneficiarios");
        if(tabela.childElementCount == 1){
            bootbox.alert("Não é possível remover quando possuí apenas uma linha!");
        }else{
            quantidadeBeneficiarios.value = quantidadeBeneficiarios.value - 1;
            linhaTabela.remove();
        }
    }
</script>
@endsection