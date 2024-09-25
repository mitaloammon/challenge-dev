<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\DisabledColumns;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Requests\StoreClientesPost;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use stdClass;

class ClientesController extends Controller
{
	public function index(Request $request)
	{
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("list.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$data = Session::all();

			if (!isset($data["ClientesController"]) || empty($data["ClientesController"])) {
				session(["ClientesController" => array("status" => "0", "orderBy" => array("column" => "created_at", "sorting" => "1"), "limit" => "10")]);
				$data = Session::all();
			}

			$Filtros = new Security;
			if ($request->input()) {
				$Limpar = false;
				if ($request->input("limparFiltros") == true) {
					$Limpar = true;
				}

				$arrayFilter = $Filtros->TratamentoDeFiltros($request->input(), $Limpar, ["ClientesController"]);
				if ($arrayFilter) {
					session(["ClientesController" => $arrayFilter]);
					$data = Session::all();
				}
			}


			$columnsTable = DisabledColumns::whereRouteOfList("list.ClientesController")
				->first()
				?->columns;

			$ClientesController = DB::table("Clientes")

				->select(DB::raw("Clientes.*, DATE_FORMAT(Clientes.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			
			"));

			if (isset($data["ClientesController"]["orderBy"])) {
				$Coluna = $data["ClientesController"]["orderBy"]["column"];
				$ClientesController =  $ClientesController->orderBy("Clientes.$Coluna", $data["ClientesController"]["orderBy"]["sorting"] ? "asc" : "desc");
			} else {
				$ClientesController =  $ClientesController->orderBy("Clientes.created_at", "desc");
			}

			//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

			if (isset($data["ClientesController"]["nome"])) {
				$AplicaFiltro = $data["ClientesController"]["nome"];
				$ClientesController = $ClientesController->Where("Clientes.nome",  "like", "%" . $AplicaFiltro . "%");
			}
			if (isset($data["ClientesController"]["telefone"])) {
				$AplicaFiltro = $data["ClientesController"]["telefone"];
				$ClientesController = $ClientesController->Where("Clientes.telefone",  "like", "%" . $AplicaFiltro . "%");
			}
			if (isset($data["ClientesController"]["email"])) {
				$AplicaFiltro = $data["ClientesController"]["email"];
				$ClientesController = $ClientesController->Where("Clientes.email",  "like", "%" . $AplicaFiltro . "%");
			}
			if (isset($data["ClientesController"]["endereco"])) {
				$AplicaFiltro = $data["ClientesController"]["endereco"];
				$ClientesController = $ClientesController->Where("Clientes.endereco",  "like", "%" . $AplicaFiltro . "%");
			}


			$ClientesController = $ClientesController->where("Clientes.deleted", "0");

			$ClientesController = $ClientesController->paginate(($data["ClientesController"]["limit"] ?: 10))
				->appends(["page", "orderBy", "searchBy", "limit"]);

			$Acao = "Acessou a listagem do Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);
			$Registros = $this->Registros();

			return Inertia::render("ClientesController/List", [
				"columnsTable" => $columnsTable,
				"ClientesController" => $ClientesController,

				"Filtros" => $data["ClientesController"],
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
		$Total = DB::table("Clientes")
			->where("Clientes.deleted", "0")
			->count();

		$Ativos = DB::table("Clientes")
			->where("Clientes.deleted", "0")
			->where("Clientes.status", "0")
			->count();

		$Inativos = DB::table("Clientes")
			->where("Clientes.deleted", "0")
			->where("Clientes.status", "1")
			->count();

		$EsseMes = DB::table("Clientes")
			->where("Clientes.deleted", "0")
			->whereMonth("Clientes.created_at", $mes)
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
		$Modulo = "ClientesController";
		$permUser = Auth::user()->hasPermissionTo("create.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}
		try {



			$Acao = "Abriu a Tela de Cadastro do Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao);

			return Inertia::render("ClientesController/Create", []);
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
		$ClientesController = DB::table("Clientes");
		$ClientesController = $ClientesController->where("deleted", "0");
		$ClientesController = $ClientesController->where("token", $id)->first();

		return $ClientesController->id;
	}

	public function store(Request $request)
	{
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("create.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {


			$data = Session::all();




			$save = new stdClass;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
			$save->nome = $request->nome;
			$save->telefone = $request->telefone;
			$save->email = $request->email;
			$save->endereco = $request->endereco;
			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;
			$save->token = md5(date("Y-m-d H:i:s") . rand(0, 999999999));

			$save = collect($save)->toArray();
			DB::table("Clientes")
				->insert($save);
			$lastId = DB::getPdo()->lastInsertId();

			$Acao = "Inseriu um Novo Registro no Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(2, $Modulo, $Acao, $lastId);

			return redirect()->route("list.ClientesController");
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

		return redirect()->route("list.ClientesController");
	}




	public function edit($IDClientesController)
	{
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("edit.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {



			$AcaoID = $this->return_id($IDClientesController);



			$ClientesController = DB::table("Clientes")
				->where("token", $IDClientesController)
				->first();

			$Acao = "Abriu a Tela de Edição do Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(1, $Modulo, $Acao, $AcaoID);

			return Inertia::render("ClientesController/Edit", [
				"ClientesController" => $ClientesController,

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

		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("edit.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		try {


			$AcaoID = $this->return_id($id);



			$save = new stdClass;

			$save->nome = $request->nome;
			$save->telefone = $request->telefone;
			$save->email = $request->email;
			$save->endereco = $request->endereco;
			//MODELO DE INSERT PARA VOCE FAZER COM TODAS AS COLUNAS DO BANCO DE DADOS, MENOS ID, DELETED E UPDATED_AT
			

			//ESSAS AQUI SEMPRE TERÃO POR PADRÃO
			$save->status = $request->status;

			$save = collect($save)->toArray();
			DB::table("Clientes")
				->where("token", $id)
				->update($save);



			$Acao = "Editou um registro no Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(3, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.ClientesController");
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





	public function delete($IDClientesController)
	{
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("delete.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$AcaoID = $this->return_id($IDClientesController);

			DB::table("Clientes")
				->where("token", $IDClientesController)
				->update([
					"deleted" => "1",
				]);



			$Acao = "Excluiu um registro no Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);

			return redirect()->route("list.ClientesController");
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



	public function deleteSelected($IDClientesController = null)
	{
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("delete.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			$IDsRecebidos = explode(",", $IDClientesController);
			$total = count(array_filter($IDsRecebidos));
			if ($total > 0) {
				foreach ($IDsRecebidos as $id) {
					$AcaoID = $this->return_id($id);
					DB::table("Clientes")
						->where("token", $id)
						->update([
							"deleted" => "1",
						]);
					$Acao = "Excluiu um registro no Módulo de ClientesController";
					$Logs = new logs;
					$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, $AcaoID);
				}
			}

			return redirect()->route("list.ClientesController");
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
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("delete.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("Clientes")
				->update([
					"deleted" => "1",
				]);
			$Acao = "Excluiu TODOS os registros no Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.ClientesController");
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
		$Modulo = "ClientesController";

		$permUser = Auth::user()->hasPermissionTo("delete.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}

		try {

			DB::table("Clientes")
				->update([
					"deleted" => "0",
				]);
			$Acao = "Restaurou TODOS os registros no Módulo de ClientesController";
			$Logs = new logs;
			$Registra = $Logs->RegistraLog(4, $Modulo, $Acao, 0);



			return redirect()->route("list.ClientesController");
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

		$ClientesController = DB::table("Clientes")

			->select(DB::raw("Clientes.*, DATE_FORMAT(Clientes.created_at, '%d/%m/%Y - %H:%i:%s') as data_final
			 
			"))
			->where("Clientes.deleted", "0");

		//MODELO DE FILTRO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM IF PARA APLICAR O FILTRO, EXCLUIR O FILTRO DE ID, DELETED E UPDATED_AT

		
		if (isset($data["ClientesController"]["nome"])) {
			$AplicaFiltro = $data["ClientesController"]["nome"];
			$ClientesController = $ClientesController->Where("Clientes.nome",  "like", "%" . $AplicaFiltro . "%");
		}
		if (isset($data["ClientesController"]["telefone"])) {
			$AplicaFiltro = $data["ClientesController"]["telefone"];
			$ClientesController = $ClientesController->Where("Clientes.telefone",  "like", "%" . $AplicaFiltro . "%");
		}
		if (isset($data["ClientesController"]["email"])) {
			$AplicaFiltro = $data["ClientesController"]["email"];
			$ClientesController = $ClientesController->Where("Clientes.email",  "like", "%" . $AplicaFiltro . "%");
		}
		if (isset($data["ClientesController"]["endereco"])) {
			$AplicaFiltro = $data["ClientesController"]["endereco"];
			$ClientesController = $ClientesController->Where("Clientes.endereco",  "like", "%" . $AplicaFiltro . "%");
		}
	

		$ClientesController = $ClientesController->get();

		$DadosClientes = [];
		foreach ($ClientesController as $Clientess) {
			if ($Clientess->status == "0") {
				$Clientess->status = "Ativo";
			}
			if ($Clientess->status == "1") {
				$Clientess->status = "Inativo";
			}
			$DadosClientes[] = [
				//MODELO DE CA,MPO PARA VOCE COLOCAR AQUI, PARA CADA COLUNA DO BANCO DE DADOS DEVERÁ TER UM, EXCLUIR O ID, DELETED E UPDATED_AT
				'nome' => $Clientess->nome,				
				'telefone' => $Clientess->telefone,				
				'email' => $Clientess->email,				
				'endereco' => $Clientess->endereco,				
			
			];
		}
		return $DadosClientes;
	}

	public function exportarRelatorioExcel()
	{

		$permUser = Auth::user()->hasPermissionTo("create.ClientesController");

		if (!$permUser) {
			return redirect()->route("list.Dashboard", ["id" => "1"]);
		}


		$filePath = "Relatorio_ClientesController.xlsx";

		if (Storage::disk("public")->exists($filePath)) {
			Storage::disk("public")->delete($filePath);
			// Arquivo foi deletado com sucesso
		}

		$cabecalhoAba1 = array('nome', 'telefone', 'email', 'endereco', 'status', 'Data de Cadastro');

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$Clientes = $this->DadosRelatorio();

		// Define o título da primeira aba
		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->setTitle("ClientesController");

		// Adiciona os cabeçalhos da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($cabecalhoAba1, null, "A1");

		// Adiciona os dados da tabela na primeira aba
		$spreadsheet->getActiveSheet()->fromArray($Clientes, null, "A2");

		// Definindo a largura automática das colunas na primeira aba
		foreach ($spreadsheet->getActiveSheet()->getColumnDimensions() as $col) {
			$col->setAutoSize(true);
		}

		// Habilita a funcionalidade de filtro para as células da primeira aba
		$spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());


		// Define o nome do arquivo	
		$nomeArquivo = "Relatorio_ClientesController.xlsx";
		// Cria o arquivo
		$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
		$writer->save($nomeArquivo);
		$barra = "'/'";
		$barra = str_replace("'", "", $barra);
		$writer->save(storage_path("app" . $barra . "relatorio" . $barra . $nomeArquivo));

		return redirect()->route("download2.files", ["path" => $nomeArquivo]);
	}
}
