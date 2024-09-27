<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\DisabledColumns;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use stdClass;

class ConfigServicos extends Controller
{
	public function index(Request $request)
	{
		$Modulo = "ConfigServicos";

		//$permUser = Auth::user()->hasPermissionTo("list.ConfigServicos");

		//if (!$permUser) {
		//	return redirect()->route("list.Dashboard", ["id" => "1"]);
		//}

		try {

			$data = Session::all();

			if (!isset($data["ConfigServicos"]) || empty($data["ConfigServicos"])) {
				session(["ConfigServicos" => array("status" => "0", "orderBy" => array("column" => "created_at", "sorting" => "1"), "limit" => "10")]);
				$data = Session::all();
			}

			$Filtros = new Security;
			if ($request->input()) {
				$Limpar = false;
				if ($request->input("limparFiltros") == true) {
					$Limpar = true;
				}

				$arrayFilter = $Filtros->TratamentoDeFiltros($request->input(), $Limpar, ["ConfigServicos"]);
				if ($arrayFilter) {
					session(["ConfigServicos" => $arrayFilter]);
					$data = Session::all();
				}
			}


			$columnsTable = DisabledColumns::whereRouteOfList("list.ConfigServicos")
				->first()
				?->columns;

			$ConfigServicos = DB::table("config_servicos")
				->select(DB::raw("config_servicos.*, DATE_FORMAT(config_servicos.created_at, '%d/%m/%Y - %H:%i:%s') as data_final	
			"));

			if (isset($data["ConfigServicos"]["orderBy"])) {
				$Coluna = $data["ConfigServicos"]["orderBy"]["column"];
				$ConfigServicos =  $ConfigServicos->orderBy("config_servicos.$Coluna", $data["ConfigServicos"]["orderBy"]["sorting"] ? "asc" : "desc");
			} else {
				$ConfigServicos =  $ConfigServicos->orderBy("config_servicos.created_at", "desc");
			}

			//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

			if (isset($data["ConfigServicos"]["descricao"])) {
				$AplicaFiltro = $data["ConfigServicos"]["descricao"];
				$ConfigServicos = $ConfigServicos->Where("config_servicos.descricao",  "like", "%" . $AplicaFiltro . "%");
			}

            if (isset($data["ConfigServicos"]["preco"])) {
				$AplicaFiltro = $data["ConfigServicos"]["preco"];
				$ConfigServicos = $ConfigServicos->Where("config_servicos.preco",  "like", "%" . $AplicaFiltro . "%");
			}

            if (isset($data["ConfigServicos"]["tempo_estimado"])) {
				$AplicaFiltro = $data["ConfigServicos"]["tempo_estimado"];
				$ConfigServicos = $ConfigServicos->Where("config_servicos.tempo_estimado",  "like", "%" . $AplicaFiltro . "%");
			}


			$ConfigServicos = $ConfigServicos->where("config_servicos.deleted", "0");

			$ConfigServicos = $ConfigServicos->paginate(($data["ConfigServicos"]["limit"] ?: 10))
				->appends(["page", "orderBy", "searchBy", "limit"]);

			$Acao = "Acessou a listagem do Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);
			$Registros = $this->Registros();

			return Inertia::render("ConfigServicos/List", [
				"columnsTable" => $columnsTable,
				"ConfigServicos" => $ConfigServicos,

				"Filtros" => $data["ConfigServicos"],
				"Registros" => $Registros,

			]);
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);


			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);
			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}

	public function Registros()
	{

		$mes = date("m");
		$Total = DB::table("config_servicos")
			->where("config_servicos.deleted", "0")
			->count();

		$Ativos = DB::table("config_servicos")
			->where("config_servicos.deleted", "0")
			->where("config_servicos.status", "0")
			->count();

		$Inativos = DB::table("config_servicos")
			->where("config_servicos.deleted", "0")
			->where("config_servicos.status", "1")
			->count();

		$EsseMes = DB::table("config_servicos")
			->where("config_servicos.deleted", "0")
			->whereMonth("config_servicos.created_at", $mes)
			->count();


		$data = new stdClass;
		$data->total = number_format($Total, 0, ",", ".");
		$data->ativo = number_format($Ativos, 0, ",", ".");
		$data->inativo = number_format($Inativos, 0, ",", ".");
		$data->mes = number_format($EsseMes, 0, ",", ".");
		return $data;
	}

	public function create()
	{
		$Modulo = "ConfigServicos";
		//$permUser = Auth::user()->hasPermissionTo("create.ConfigServicos");

		//if (!$permUser) {
		//	return redirect()->route("list.Dashboard", ["id" => "1"]);
		//}
		try {



			$Acao = "Abriu a Tela de Cadastro do Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);

			return Inertia::render("ConfigServicos/Create", []);
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);


			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);
			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}

	public function return_id($id)
	{
		$ConfigServicos = DB::table("config_servicos");
		$ConfigServicos = $ConfigServicos->where("deleted", "0");
		$ConfigServicos = $ConfigServicos->where("token", $id)->first();

		return $ConfigServicos->id;
	}

	public function store(Request $request)
	{
		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("create.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {


			$data = Session::all();




			$save = new stdClass;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT

            $save->descricao = $request->descricao;
            $save->preco = $request->preco;
            $save->tempo_estimado = $request->tempo_estimado;

			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;
			$save->token = md5(date("Y-m-d H:i:s") . rand(0, 999999999));

			$save = collect($save)->toArray();
			DB::table("config_servicos")
				->insert($save);
			$lastId = DB::getPdo()->lastInsertId();

			$Acao = "Inseriu um Novo Registro no Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(2, $Modulo, $Acao, $lastId);

			return redirect()->route("list.ConfigServicos");
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);


			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);
			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}

		return redirect()->route("list.ConfigServicos");
	}




	public function edit($IDConfigServicos)
	{
		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("edit.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$AcaoID = $this->return_id($IDConfigServicos);



			$ConfigServicos = DB::table("config_servicos")
				->where("token", $IDConfigServicos)
				->first();

			$Acao = "Abriu a Tela de Edição do Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao, $AcaoID);

			return Inertia::render("ConfigServicos/Edit", [
				"ConfigServicos" => $ConfigServicos,

			]);
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);

			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);
			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}


	public function update(Request $request, $id)
	{

		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("edit.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		try {


			$AcaoID = $this->return_id($id);



			$save = new stdClass;

			$save->descricao = $request->descricao;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
			$save->preco = $request->preco;
            $save->tempo_estimado = $request->tempo_estimado;
			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;

			$save = collect($save)->toArray();
			DB::table("config_servicos")
				->where("token", $id)
				->update($save);



			$Acao = "Editou um registro no Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(3, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.ConfigServicos");
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);

			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);
			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}





	public function delete($IDConfigServicos)
	{
		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("delete.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$AcaoID = $this->return_id($IDConfigServicos);

			DB::table("config_servicos")
				->where("token", $IDConfigServicos)
				->update([
					"deleted" => "1",
				]);



			$Acao = "Excluiu um registro no Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.ConfigServicos");
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);

			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);

			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}



	public function deleteSelected($IDConfigServicos = null)
	{
		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("delete.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$IDsRecebidos = explode(",", $IDConfigServicos);
			$total = count(array_filter($IDsRecebidos));
			if ($total > 0) {
				foreach ($IDsRecebidos as $id) {
					$AcaoID = $this->return_id($id);
					DB::table("config_servicos")
						->where("token", $id)
						->update([
							"deleted" => "1",
						]);
					$Acao = "Excluiu um registro no Módulo de ConfigServicos";
					$Logs = new logs;
					$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);
				}
			}

			return redirect()->route("list.ConfigServicos");
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);

			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);

			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}

	public function deletarTodos()
	{
		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("delete.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("config_servicos")
				->update([
					"deleted" => "1",
				]);
			$Acao = "Excluiu TODOS os registros no Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.ConfigServicos");
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);

			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);

			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}

	public function RestaurarTodos()
	{
		$Modulo = "ConfigServicos";

		$permUser = Auth::user()->hasPermissionTo("delete.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("config_servicos")
				->update([
					"deleted" => "0",
				]);
			$Acao = "Restaurou TODOS os registros no Módulo de ConfigServicos";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.ConfigServicos");
		} catch (Exception $e) {

			$Error = $e->getMessage();
			$Error = explode("MESSAGE:", $Error);

			$Pagina = $_SERVER["REQUEST_URI"];

			$Erro = $Error[0];
			$Erro_Completo = $e->getMessage();
			$LogsErrors = new logsErrosController;
			$Registra = $LogsErrors->RegistraErro($Pagina, $Modulo, $Erro, $Erro_Completo);

			abort(403, "Erro localizado e enviado ao LOG de Erros");
		}
	}

	public function DadosRelatorio()
	{
		$data = Session::all();

		$ConfigServicos = DB::table("config_servicos")

			->select(DB::raw("config_servicos.*, DATE_FORMAT(config_servicos.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			 
			"))
			->where("config_servicos.deleted", "0");

		//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

		
		if (isset($data["ConfigServicos"]["descricao"])) {
			$AplicaFiltro = $data["ConfigServicos"]["descricao"];
			$ConfigServicos = $ConfigServicos->Where("config_servicos.descricao",  "like", "%" . $AplicaFiltro . "%");
		}

        if (isset($data["ConfigServicos"]["preco"])) {
			$AplicaFiltro = $data["ConfigServicos"]["preco"];
			$ConfigServicos = $ConfigServicos->Where("config_servicos.preco",  "like", "%" . $AplicaFiltro . "%");
		}

        if (isset($data["ConfigServicos"]["tempo_estimado"])) {
			$AplicaFiltro = $data["ConfigServicos"]["tempo_estimado"];
			$ConfigServicos = $ConfigServicos->Where("config_servicos.tempo_estimado",  "like", "%" . $AplicaFiltro . "%");
		}
	

		$ConfigServicos = $ConfigServicos->get();

		$Dadosconfig_servicos = [];
		foreach ($ConfigServicos as $config_servicoss) {
			if ($config_servicoss->status == "0") {
				$config_servicoss->status = "Ativo";
			}
			if ($config_servicoss->status == "1") {
				$config_servicoss->status = "Inativo";
			}
			$Dadosconfig_servicos[] = [
				//MODELO DE CA,MPO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM, EXCLUIR O ID, DELETED E UPDATED_AT
				'descricao' => $config_servicoss->descricao,		
                'preco' => $config_servicoss->preco,	
                'tempo_estimado' => $config_servicoss->tempo_estimado,			
			
			];
		}
		return $Dadosconfig_servicos;
	}

	public function exportarRelatorioExcel()
	{

		$permUser = Auth::user()->hasPermissionTo("create.ConfigServicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		$filePath = "Relatorio_ConfigServicos.xlsx";

		if (Storage::disk("public")->exists($filePath)) {
			Storage::disk("public")->delete($filePath);
			// Arquivo foi deletado com sucesso
		}

		$cabecalhoAba1 = array('descricao', 'preco', 'tempo_estimado', 'status');

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$config_servicos = $this->DadosRelatorio();

		// Define o título da primeira aba
		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->setTitle("ConfigServicos");

		// Adiciona os cabeçalhos da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($cabecalhoAba1, null, "A1");

		// Adiciona os dados da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($config_servicos, null, "A2");

		// Definindo a largura automática das colunas na primeira aba
		foreach ($spreadsheet->getActiveSheet()->getColumnDimensions() as $col) {
			$col->setAutoSize(true);
		}

		// Habilita a funcionalidade de filtro para as células da primeira aba
		$spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());


		// Define o nome do arquivo	
		$nomeArquivo = "Relatorio_ConfigServicos.xlsx";
		// Cria o arquivo
		$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
		$writer->save($nomeArquivo);
		$barra = "'/'";
		$barra = str_replace("'", "", $barra);
		$writer->save(storage_path("app" . $barra . "relatorio" . $barra . $nomeArquivo));

		return redirect()->route("download2.files", ["path" => $nomeArquivo]);
	}
}
