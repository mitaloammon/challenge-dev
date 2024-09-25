<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\DisabledColumns;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Requests\StoreHistoricosPost;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use stdClass;

class HistoricosController extends Controller
{
	public function index(Request $request)
	{
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("list.Historicos");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$data = Session::all();

			if (!isset($data["Historico"]) || empty($data["Historico"])) {
				session(["Historico" => array("status" => "0", "orderBy" => array("column" => "created_at", "sorting" => "1"), "limit" => "10")]);
				$data = Session::all();
			}

			$Filtros = new Security;
			if ($request->input()) {
				$Limpar = false;
				if ($request->input("limparFiltros") == true) {
					$Limpar = true;
				}

				$arrayFilter = $Filtros->TratamentoDeFiltros($request->input(), $Limpar, ["Historico"]);
				if ($arrayFilter) {
					session(["Historico" => $arrayFilter]);
					$data = Session::all();
				}
			}


			$columnsTable = DisabledColumns::whereRouteOfList("list.Historicos")
				->first()
				?->columns;

			$Historico = DB::table("Historico")

				->select(DB::raw("Historico.*, DATE_FORMAT(Historico.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			
			"));

			if (isset($data["Historico"]["orderBy"])) {
				$Coluna = $data["Historico"]["orderBy"]["column"];
				$Historico =  $Historico->orderBy("Historico.$Coluna", $data["Historico"]["orderBy"]["sorting"] ? "asc" : "desc");
			} else {
				$Historico =  $Historico->orderBy("Historico.created_at", "desc");
			}

			//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

			if (isset($data["Historico"]["data"])) {
				$AplicaFiltro = $data["Historico"]["data"];
				$Historico = $Historico->Where("Historico.data",  "like", "%" . $AplicaFiltro . "%");
			}
			
			if (isset($data["Historico"]["observacoes"])) {
				$AplicaFiltro = $data["Historico"]["observacoes"];
				$Historico = $Historico->Where("Historico.observacoes",  "like", "%" . $AplicaFiltro . "%");
			}

			if (isset($data["Historico"]["status"])) {
				$AplicaFiltro = $data["Historico"]["status"];
				$Historico = $Historico->Where("Historico.status",  "like", "%" . $AplicaFiltro . "%");
			}


			$Historico = $Historico->where("Historico.deleted", "0");

			$Historico = $Historico->paginate(($data["Historico"]["limit"] ?: 10))
				->appends(["page", "orderBy", "searchBy", "limit"]);

			$Acao = "Acessou a listagem do Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);
			$Registros = $this->Registros();

			return Inertia::render("Historico/List", [
				"columnsTable" => $columnsTable,
				"Historico" => $Historico,

				"Filtros" => $data["Historico"],
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
		$Total = DB::table("Historico")
			->where("Historico.deleted", "0")
			->count();

		$Ativos = DB::table("Historico")
			->where("Historico.deleted", "0")
			->where("Historico.status", "0")
			->count();

		$Inativos = DB::table("Historico")
			->where("Historico.deleted", "0")
			->where("Historico.status", "1")
			->count();

		$EsseMes = DB::table("Historico")
			->where("Historico.deleted", "0")
			->whereMonth("Historico.created_at", $mes)
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
		$Modulo = "Historico";
		$permUser = Auth::user()->hasPermissionTo("create.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}
		try {

			$Acao = "Abriu a Tela de Cadastro do Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);

			return Inertia::render("Historico/Create", []);
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
		$Historico = DB::table("Historico");
		$Historico = $Historico->where("deleted", "0");
		$Historico = $Historico->where("token", $id)->first();

		return $Historico->id;
	}

	public function store(Request $request)
	{
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("create.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {


			$data = Session::all();

			$save = new stdClass;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
			$save->data = $request->data;
			$save->observacoes = $request->observacoes;
			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;
			$save->token = md5(date("Y-m-d H:i:s") . rand(0, 999999999));

			$save = collect($save)->toArray();
			DB::table("Historico")
				->insert($save);
			$lastId = DB::getPdo()->lastInsertId();

			$Acao = "Inseriu um Novo Registro no Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(2, $Modulo, $Acao, $lastId);

			return redirect()->route("list.Historicos");
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

		return redirect()->route("list.Historicos");
	}




	public function edit($IDHistorico)
	{
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("edit.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$AcaoID = $this->return_id($IDHistorico);



			$Historico = DB::table("Historico")
				->where("token", $IDHistorico)
				->first();

			$Acao = "Abriu a Tela de Edição do Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao, $AcaoID);

			return Inertia::render("Historico/Edit", [
				"Historico" => $Historico,

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

		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("edit.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		try {


			$AcaoID = $this->return_id($id);



			$save = new stdClass;

			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
			$save->data = $request->data;
			$save->observacoes = $request->observacoes;
			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;

			$save = collect($save)->toArray();
			DB::table("Historico")
				->where("token", $id)
				->update($save);



			$Acao = "Editou um registro no Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(3, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.Historicos");
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





	public function delete($IDHistorico)
	{
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("delete.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$AcaoID = $this->return_id($IDHistorico);

			DB::table("Historico")
				->where("token", $IDHistorico)
				->update([
					"deleted" => "1",
				]);



			$Acao = "Excluiu um registro no Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.Historicos");
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



	public function deleteSelected($IDHistorico = null)
	{
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("delete.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$IDsRecebidos = explode(",", $IDHistorico);
			$total = count(array_filter($IDsRecebidos));
			if ($total > 0) {
				foreach ($IDsRecebidos as $id) {
					$AcaoID = $this->return_id($id);
					DB::table("Historico")
						->where("token", $id)
						->update([
							"deleted" => "1",
						]);
					$Acao = "Excluiu um registro no Módulo de Historico";
					$Logs = new logs;
					$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);
				}
			}

			return redirect()->route("list.Historicos");
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
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("delete.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("Historico")
				->update([
					"deleted" => "1",
				]);
			$Acao = "Excluiu TODOS os registros no Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.Historicos");
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
		$Modulo = "Historico";

		$permUser = Auth::user()->hasPermissionTo("delete.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("Historico")
				->update([
					"deleted" => "0",
				]);
			$Acao = "Restaurou TODOS os registros no Módulo de Historico";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.Historicos");
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

		$Historico = DB::table("Historico")

			->select(DB::raw("Historico.*, DATE_FORMAT(Historico.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			 
			"))
			->where("Historico.deleted", "0");

		//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT


		if (isset($data["Historico"]["data"])) {
			$AplicaFiltro = $data["Historico"]["data"];
			$Historico = $Historico->Where("Historico.data",  "like", "%" . $AplicaFiltro . "%");
		}
		if (isset($data["Historico"]["observacoes"])) {
			$AplicaFiltro = $data["Historico"]["observacoes"];
			$Historico = $Historico->Where("Historico.observacoes",  "like", "%" . $AplicaFiltro . "%");
		}
	

		$Historico = $Historico->get();

		$DadosHistorico = [];
		foreach ($Historico as $Historicos) {
			if ($Historicos->status == "0") {
				$Historicos->status = "Ativo";
			}
			if ($Historicos->status == "1") {
				$Historicos->status = "Inativo";
			}
			$DadosHistorico[] = [
				//MODELO DE CAMPO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM, EXCLUIR O ID, DELETED E UPDATED_AT		
				'data' => $Historicos->data,				
				'observacoes' => $Historicos->observacoes,				
			
			];
		}
		return $DadosHistorico;
	}

	public function exportarRelatorioExcel()
	{

		$permUser = Auth::user()->hasPermissionTo("create.Historico");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		$filePath = "Relatorio_Historico.xlsx";

		if (Storage::disk("public")->exists($filePath)) {
			Storage::disk("public")->delete($filePath);
			// Arquivo foi deletado com sucesso
		}

		$cabecalhoAba1 = array('data', 'observacoes', 'status');

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$Historico = $this->DadosRelatorio();

		// Define o título da primeira aba
		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->setTitle("Historico");

		// Adiciona os cabeçalhos da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($cabecalhoAba1, null, "A1");

		// Adiciona os dados da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($Historico, null, "A2");

		// Definindo a largura automática das colunas na primeira aba
		foreach ($spreadsheet->getActiveSheet()->getColumnDimensions() as $col) {
			$col->setAutoSize(true);
		}

		// Habilita a funcionalidade de filtro para as células da primeira aba
		$spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());


		// Define o nome do arquivo	
		$nomeArquivo = "Relatorio_Historico.xlsx";
		// Cria o arquivo
		$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
		$writer->save($nomeArquivo);
		$barra = "'/'";
		$barra = str_replace("'", "", $barra);
		$writer->save(storage_path("app" . $barra . "relatorio" . $barra . $nomeArquivo));

		return redirect()->route("download2.files", ["path" => $nomeArquivo]);
	}
}
