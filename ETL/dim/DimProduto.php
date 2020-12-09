<?php
namespace dimensoes;
mysqli_report(MYSQLI_REPORT_STRICT);
require_once('Produto.php');
require_once('Sumario.php');
use dimensoes\Sumario;
use dimensoes\Produto;

class DimProduto{
   public function carregarDimProduto(){
      $dataAtual = date('Y-m-d');
      $sumario = new Sumario();
      try{
         $connDimensao = $this->conectarBanco('dm_comercial');
         $connComercial = $this->conectarBanco('bd_comercial');
      }catch(\Exception $e){
         die($e->getMessage());
      }
      $sqlDim = $connDimensao->prepare('select SK_produto, descricao, nome, preco, unidade
                                        from dim_produto');
      $sqlDim->execute();
      $result = $sqlDim->get_result();
      if($result->num_rows === 0){//Dimensão está
         $sqlComercial = $connComercial->prepare("select * from produto"); //Cria variável com comando SQL
         $sqlComercial->execute(); //Executa o comando SQL
         $resultComercial = $sqlComercial->get_result(); //Atribui à variával o resultado da consulta
         if($resultComercial->num_rows !== 0){ //Testa se a consulta retornou dados
            while($linhaProduto = $resultComercial->fetch_assoc()){ //Atibui à variável cada linha até o último
               $produto = new Produto();
               $produto->setProduto($linhaProduto['descricao_produto'], $linhaProduto['nome_produto'], $linhaProduto['preco'],
               $linhaProduto['unid_medida']);
               $slqInsertDim = $connDimensao->prepare("insert into dim_produto
                                                      (descricao, nome, preco, unidade, data_ini)
                                                      values
                                                      (?,?,?,?,?)");
               $slqInsertDim->bind_param("sssss", $produto->descricao, $produto->nome, $produto->preco,
                                          $produto->unidade, $dataAtual);
               $slqInsertDim->execute();
               $sumario->setQuantidadeInclusoes();
            }
            $sqlComercial->close();
            $sqlDim->close();
            $slqInsertDim->close();
            $connComercial->close();
            $connDimensao->close();
         }
      }else{//Dimensão já contém dados
         $sqlComercial = $connComercial->prepare('select * from produto');
         $sqlComercial->execute();
         $resultComercial = $sqlComercial->get_result();
         while($linhaComercial = $resultComercial->fetch_assoc()){
            $sqlDim = $connDimensao->prepare('SELECT SK_produto, nome, descricao, preco, unidade
                                             FROM
                                             dim_produto
                                             where
                                             SK_produto = ?
                                             and
                                             data_fim is null');
            $sqlDim->bind_param('i', $linhaComercial['codigo']);
            $sqlDim->execute();
            $resultDim = $sqlDim->get_result();
            if($resultDim->num_rows === 0){// O produto da Comercial não está na dimensional
               $sqlInsertDim = $connDimensao->prepare('INSERT INTO dim_produto
                                                      (descricao, nome, preco, unidade, data_ini)
                                                      VALUES
                                                      (?,?,?,?,?)');
               $sqlInsertDim->bind_param('sssss', $linhaComercial['descricao_produto'], $linhaComercial['nome_produto'],
                                          $linhaComercial['preco'],$linhaComercial['unid_medida'],$dataAtual);
               $sqlInsertDim->execute();
               if($sqlInsertDim->error){
                  throw new \Exception('Erro: Produto novo não incluso');
               }
               $sumario->setQuantidadeInclusoes();
            }else{ // O produto da comercial já está na dimensional
               $strComercialTeste = $linhaComercial['descricao_produto'].$linhaComercial['nome_produto']
                                    .$linhaComercial['preco'].$linhaComercial['unid_medida'];
               $linhaDim = $resultDim->fetch_assoc();
               $strDimensionalTeste = $linhaDim['descricao'].$linhaDim['nome']
                                    .$linhaDim['preco'].$linhaDim['unidade'];
               if(!$this->strIgual($strComercialTeste, $strDimensionalTeste)){
                  $sqlUpdateDim = $connDimensao->prepare('UPDATE dim_produto SET
                                                         data_fim = ?
                                                         where
                                                         SK_produto = ?');
                  $sqlUpdateDim->bind_param('si', $dataAtual, $linhaDim['SK_produto']);
                  $sqlUpdateDim->execute();
                  if(!$sqlUpdateDim->error){
                     $sqlInsertDim = $connDimensao->prepare('INSERT INTO dim_produto
                                                         (descricao, nome, preco, unidade, data_ini)
                                                         VALUES
                                                         (?, ?, ?, ?, ?)');
                      $sqlInsertDim->bind_param("sssss", $linhaComercial['descricao_produto'], $linhaComercial['nome_produto'],
                                                $linhaComercial['preco'], $linhaComercial['unid_medida'], $dataAtual);
                     $sqlInsertDim->execute();
                     $sumario->setQuantidadeAlteracoes();
                  }else{
                      throw new \Exception('Erro: Erro no processo de alteração!');
                  }
               }
            }
         }
      }
      return $sumario;
   }
   private function strIgual($strAtual, $strNovo){
      $hashAtual = md5($strAtual);
      $hashNovo = md5($strNovo);
      if($hashAtual === $hashNovo){
         return TRUE;
      }else{
         return FALSE;
      }
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