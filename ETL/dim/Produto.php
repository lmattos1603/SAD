<?php
namespace dimensoes;

/**
 * Model da entidade cliente
 * @author Lucas
 */
class Produto{
   
   public $descricao;
   
   public $nome;
   
   public $preco;
   
   public $unidade;

   /**
    * Carrega os atributos da classe Prospect
    * @param $descricao Descrição do produto
    * @param $nome Nome do produto
    * @param $preco Preço do produto
    * @param $unidade Unidade de medida do produto
    *@return Void
    */

   public function setProduto($descricao, $nome, $preco, $unidade){
      $this->descricao = $descricao;
      $this->nome = $nome;
      $this->preco = $preco;
      $this->unidade = $unidade;
   }
}
?>