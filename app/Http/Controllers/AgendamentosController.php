<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\DisabledColumns;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Requests\StoreAgendamentosPost;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use stdClass;
 
class AgendamentosController extends Controller
{
	public function index(Request $request)
	{
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("list.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$data = Session::all();

			if (!isset($data["AgendamentosController"]) || empty($data["AgendamentosController"])) {
				session(["AgendamentosController" => array("status" => "0", "orderBy" => array("column" => "created_at", "sorting" => "1"), "limit" => "10")]);
				$data = Session::all();
			}

			$Filtros = new Security;
			if ($request->input()) {
				$Limpar = false;
				if ($request->input("limparFiltros") == true) {
					$Limpar = true;
				}

				$arrayFilter = $Filtros->TratamentoDeFiltros($request->input(), $Limpar, ["AgendamentosController"]);
				if ($arrayFilter) {
					session(["AgendamentosController" => $arrayFilter]);
					$data = Session::all();
				}
			}


			$columnsTable = DisabledColumns::whereRouteOfList("list.AgendamentosController")
				->first()
				?->columns;

			$AgendamentosController = DB::table("Agendamentos")

				->select(DB::raw("Agendamentos.*, DATE_FORMAT(Agendamentos.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			
			"));

			if (isset($data["AgendamentosController"]["orderBy"])) {
				$Coluna = $data["AgendamentosController"]["orderBy"]["column"];
				$AgendamentosController =  $AgendamentosController->orderBy("Agendamentos.$Coluna", $data["AgendamentosController"]["orderBy"]["sorting"] ? "asc" : "desc");
			} else {
				$AgendamentosController =  $AgendamentosController->orderBy("Agendamentos.created_at", "desc");
			}

			//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

            
			if (isset($data["AgendamentosController"]["nome"])) {
				$AplicaFiltro = $data["AgendamentosController"]["nome"];
				$AgendamentosController = $AgendamentosController->Where("Agendamentos.nome",  "like", "%" . $AplicaFiltro . "%");
			}

			if (isset($data["AgendamentosController"]["data_agendamento"])) {
				$AplicaFiltro = $data["AgendamentosController"]["data_agendamento"];
				$AgendamentosController = $AgendamentosController->Where("Agendamentos.data_agendamento",  "like", "%" . $AplicaFiltro . "%");
			}

			if (isset($data["AgendamentosController"]["hora_agendamento"])) {
				$AplicaFiltro = $data["AgendamentosController"]["hora_agendamento"];
				$AgendamentosController = $AgendamentosController->Where("Agendamentos.hora_agendamento",  "like", "%" . $AplicaFiltro . "%");
			}

			$AgendamentosController = $AgendamentosController->where("Agendamentos.deleted", "0");

			$AgendamentosController = $AgendamentosController->paginate(($data["AgendamentosController"]["limit"] ?: 10))
				->appends(["page", "orderBy", "searchBy", "limit"]);

			$Acao = "Acessou a listagem do Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);
			$Registros = $this->Registros();

			return Inertia::render("AgendamentosController/List", [
				"columnsTable" => $columnsTable,
				"AgendamentosController" => $AgendamentosController,

				"Filtros" => $data["AgendamentosController"],
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
		$Total = DB::table("Agendamentos")
			->where("Agendamentos.deleted", "0")
			->count();

		$Ativos = DB::table("Agendamentos")
			->where("Agendamentos.deleted", "0")
			->where("Agendamentos.status", "0")
			->count();

		$Inativos = DB::table("Agendamentos")
			->where("Agendamentos.deleted", "0")
			->where("Agendamentos.status", "1")
			->count();

		$EsseMes = DB::table("Agendamentos")
			->where("Agendamentos.deleted", "0")
			->whereMonth("Agendamentos.created_at", $mes)
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
		$Modulo = "AgendamentosController";
		$permUser = Auth::user()->hasPermissionTo("create.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}
		try {



			$Acao = "Abriu a Tela de Cadastro do Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);

			return Inertia::render("AgendamentosController/Create", []);
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
		$AgendamentosController = DB::table("Agendamentos");
		$AgendamentosController = $AgendamentosController->where("deleted", "0");
		$AgendamentosController = $AgendamentosController->where("token", $id)->first();

		return $AgendamentosController->id;
	}

	public function store(Request $request)
	{
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("create.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {


			$data = Session::all();

			$save = new stdClass;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
			$save->nome = $request->nome;
			$save->data_agendamento = $request->data_agendamento;
			$save->hora_agendamento = $request->hora_agendamento;
			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;
			$save->token = md5(date("Y-m-d H:i:s") . rand(0, 999999999));

			$save = collect($save)->toArray();
			DB::table("Agendamentos")
				->insert($save);
			$lastId = DB::getPdo()->lastInsertId();

			$Acao = "Inseriu um Novo Registro no Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(2, $Modulo, $Acao, $lastId);

			return redirect()->route("list.AgendamentosController");
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

		return redirect()->route("list.AgendamentosController");
	}




	public function edit($IDAgendamentosController)
	{
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("edit.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$AcaoID = $this->return_id($IDAgendamentosController);



			$AgendamentosController = DB::table("Agendamentos")
				->where("token", $IDAgendamentosController)
				->first();

			$Acao = "Abriu a Tela de Edição do Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao, $AcaoID);

			return Inertia::render("AgendamentosController/Edit", [
				"AgendamentosController" => $AgendamentosController,

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

		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("edit.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		try {


			$AcaoID = $this->return_id($id);



			$save = new stdClass;

			$save->nome = $request->nome;
            $save->data_agendamento = $request->data_agendamento;
			$save->hora_agendamento = $request->hora_agendamento;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
            
			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;

			$save = collect($save)->toArray();
			DB::table("Agendamentos")
				->where("token", $id)
				->update($save);



			$Acao = "Editou um registro no Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(3, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.AgendamentosController");
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





	public function delete($IDAgendamentosController)
	{
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("delete.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$AcaoID = $this->return_id($IDAgendamentosController);

			DB::table("Agendamentos")
				->where("token", $IDAgendamentosController)
				->update([
					"deleted" => "1",
				]);



			$Acao = "Excluiu um registro no Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.AgendamentosController");
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



	public function deleteSelected($IDAgendamentosController = null)
	{
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("delete.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$IDsRecebidos = explode(",", $IDAgendamentosController);
			$total = count(array_filter($IDsRecebidos));
			if ($total > 0) {
				foreach ($IDsRecebidos as $id) {
					$AcaoID = $this->return_id($id);
					DB::table("Agendamentos")
						->where("token", $id)
						->update([
							"deleted" => "1",
						]);
					$Acao = "Excluiu um registro no Módulo de AgendamentosController";
					$Logs = new logs;
					$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);
				}
			}

			return redirect()->route("list.AgendamentosController");
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
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("delete.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("Agendamentos")
				->update([
					"deleted" => "1",
				]);
			$Acao = "Excluiu TODOS os registros no Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.AgendamentosController");
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
		$Modulo = "AgendamentosController";

		$permUser = Auth::user()->hasPermissionTo("delete.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("Agendamentos")
				->update([
					"deleted" => "0",
				]);
			$Acao = "Restaurou TODOS os registros no Módulo de AgendamentosController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.AgendamentosController");
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

		$AgendamentosController = DB::table("Agendamentos")

			->select(DB::raw("Agendamentos.*, DATE_FORMAT(Agendamentos.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			 
			"))
			->where("Agendamentos.deleted", "0");

		//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

		
		if (isset($data["AgendamentosController"]["nome"])) {
			$AplicaFiltro = $data["AgendamentosController"]["nome"];
			$AgendamentosController = $AgendamentosController->Where("Agendamentos.nome",  "like", "%" . $AplicaFiltro . "%");
		}
		if (isset($data["AgendamentosController"]["data_agendamento"])) {
			$AplicaFiltro = $data["AgendamentosController"]["data_agendamento"];
			$AgendamentosController = $AgendamentosController->Where("Agendamentos.data_agendamento",  "like", "%" . $AplicaFiltro . "%");
		}
		if (isset($data["AgendamentosController"]["hora_agendamento"])) {
			$AplicaFiltro = $data["AgendamentosController"]["hora_agendamento"];
			$AgendamentosController = $AgendamentosController->Where("Agendamentos.hora_agendamento",  "like", "%" . $AplicaFiltro . "%");
		}
	

		$AgendamentosController = $AgendamentosController->get();

		$DadosAgendamentos = [];
		foreach ($AgendamentosController as $Agendamentoss) {
			if ($Agendamentoss->status == "0") {
				$Agendamentoss->status = "Ativo";
			}
			if ($Agendamentoss->status == "1") {
				$Agendamentoss->status = "Inativo";
			}
			$DadosAgendamentos[] = [
				//MODELO DE CA,MPO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM, EXCLUIR O ID, DELETED E UPDATED_AT
				'nome' => $Agendamentoss->nome,				
				'data_agendamento' => $Agendamentoss->data_agendamento,				
				'hora_agendamento' => $Agendamentoss->hora_agendamento,				
			
			];
		}
		return $DadosAgendamentos;
	}

	public function exportarRelatorioExcel()
	{

		$permUser = Auth::user()->hasPermissionTo("create.AgendamentosController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		$filePath = "Relatorio_AgendamentosController.xlsx";

		if (Storage::disk("public")->exists($filePath)) {
			Storage::disk("public")->delete($filePath);
			// Arquivo foi deletado com sucesso
		}

		$cabecalhoAba1 = array('nome', 'data_agendamento', 'hora_agendamento', 'status', 'Data de Cadastro');

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$Agendamentos = $this->DadosRelatorio();

		// Define o título da primeira aba
		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->setTitle("AgendamentosController");

		// Adiciona os cabeçalhos da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($cabecalhoAba1, null, "A1");

		// Adiciona os dados da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($Agendamentos, null, "A2");

		// Definindo a largura automática das colunas na primeira aba
		foreach ($spreadsheet->getActiveSheet()->getColumnDimensions() as $col) {
			$col->setAutoSize(true);
		}

		// Habilita a funcionalidade de filtro para as células da primeira aba
		$spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());


		// Define o nome do arquivo	
		$nomeArquivo = "Relatorio_AgendamentosController.xlsx";
		// Cria o arquivo
		$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
		$writer->save($nomeArquivo);
		$barra = "'/'";
		$barra = str_replace("'", "", $barra);
		$writer->save(storage_path("app" . $barra . "relatorio" . $barra . $nomeArquivo));

		return redirect()->route("download2.files", ["path" => $nomeArquivo]);
	}
}
