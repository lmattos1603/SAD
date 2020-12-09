<?php
namespace dimensoes;

mysqli_report(MYSQLI_REPORT_STRICT);

require_once('Data.php');
require_once('Sumario.php');

use dimensoes\Sumario;
use dimensoes\Data;

class DimData{

   public function carregarDimData(){
      
      $sumario = new Sumario();
      try{
         $connDimensao = $this->conectarBanco('dm_comercial');
         $connComercial = $this->conectarBanco('bd_comercial');
      }catch(\Exception $e){
         die($e->getMessage());
      }
      $sqlDim = $connDimensao->prepare('select SK_data, data, dia, mes, ano, semana_mes, bimestre, trimestre, semestre from dim_data');
      $sqlDim->execute();
      $result = $sqlDim->get_result();
      if($result->num_rows === 0){//Dimensão está
         $sqlComercial = $connComercial->prepare("select data_lancamento from contas_receber"); //Cria variável com comando SQL
         $sqlComercial->execute(); //Executa o comando SQL
         $resultComercial = $sqlComercial->get_result(); //Atribui à variával o resultado da consulta
         if($resultComercial->num_rows !== 0){ //Testa se a consulta retornou dados
            while($linhadata = $resultComercial->fetch_assoc()){ //Atibui à variável cada linha até o último
               $data = new data();
               $data->setdata($linhadata['data_lancamento']);
               $slqInsertDim = $connDimensao->prepare("insert into dim_data
                                                      (data, dia, mes, ano, semana_mes, bimestre, trimestre, semestre)
                                                      values
                                                      (?,?,?,?,?,?,?,?)");
               $slqInsertDim->bind_param("sssssiii", $data->data, $data->dia, $data->mes, $data->ano, $data->semanaAno, $data->bimestre, $data->trimestre, $data->semestre);
               $slqInsertDim->execute();
               $sumario->setQuantidadeInclusoes();
            }
            $sqlComercial->close();
            $sqlDim->close();
            $slqInsertDim->close();
            $connComercial->close();
            $connDimensao->close();
         }
      }
      return $sumario;
   }

   private function conectarBanco($banco){
      if(!defined('DS')){
         define('DS', DIRECTORY_SEPARATOR);
      }
      if(!defined('BASE_DIR')){
         define('BASE_DIR', dirname(__FILE__).DS);
      }
      require(BASE_DIR.'config_db.php');
      try{
         $conn = new \MySQLi($dbhost, $user, $password, $banco);
         return $conn;
      }catch(mysqli_sql_exception $e){
         throw new \Exception($e);
         die;
      }
   }
}
?>