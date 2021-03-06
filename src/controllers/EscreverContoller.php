<?php
namespace controllers;
use configs\DB as DB;

class EscreverContoller
{
	
	protected $app;
	function __construct($app){
		$this->app=$app;
	}
	public function index($request, $response,$args){
		

		$timeLineArray=array();

		$busca=DB::findAll("escrito","id_user=? ORDER BY id DESC",array($_SESSION["dados_login"]["id"]));

		foreach ($busca as $value) {
			array_push($timeLineArray,
				array(
					"id"=>				$value->id,
					"titulo"=> 			$value->titulo,
					"data"=>			$value->data,
					"conteudo"=> 		$value->texto.(($value->id_user==$_SESSION["dados_login"]["id"])?"</br><a href='/escrita/new/".$value->id."'> Editar</a>":""),
					"qtdLikes"=> 		$value->like,
					"qtdComentarios"=>	0,
					"img"=>DB::findOne("usuarios", "id=?",array($value->id_user))->foto,
					"privacidade"=> ($value->privacidade=="eu")?"Somente eu":$value->privacidade,
					"tipo"=> $value->tipo
				)
			);
		}

		return $this->app->view->render($response, 'minhas_escritas.twig',array("timeline"=>$timeLineArray));
	}

	public function new_index($request, $response,$args){
		
		$busca=DB::findOne("escrito","id=? and id_user=?",array($args["id"],$_SESSION["dados_login"]["id"]));

		$dados=array();

		if($busca!=null){
			$dados=array("titulo"=>$busca->titulo,"texto"=>$busca->texto,"id"=>$busca->id,"tipo"=>$busca->tipo,"privacidade"=>$busca->privacidade);
		}

		return $this->app->view->render($response, 'nova_escrita.twig',array("dados"=>$dados));
	}

	public function newEscrito($request, $response,$args){
		$busca=DB::findOne("escrito","id=? and id_user=?",array(isset($_POST["id"])?$_POST["id"]:0,$_SESSION["dados_login"]["id"]));


		$escrito = DB::dispense('escrito');
		if($busca!=null){
			$escrito->id 	  = $_POST["id"];	
		}
    	$escrito->titulo 	  = $_POST["titulo"];
    	$escrito->id_user 	  = $_SESSION["dados_login"]["id"];
    	date_default_timezone_set('America/Sao_Paulo');
		$date = date('Y-m-d H:i:s');
    	$escrito->data        = $date;
    	$escrito->texto       = $_POST["texto"];
    	$escrito->tipo 		  = $_POST["tipo"];
    	$escrito->privacidade = $_POST["privacidade"];
    	$escrito->like 		  = 0;

    	$id = DB::store($escrito);



		return $response->withRedirect($this->app->router->pathFor("index"));
	}

	public function delEscrito($request, $response,$args){
		if($_SESSION["dados_login"]['id']== DB::findOne("escrito","id=?",array($args["id"]))["id_user"]){
			DB::exec("DELETE FROM escrito WHERE id=?",array($args["id"]));
		}
		return $response->withRedirect($this->app->router->pathFor("index"));
	}


}