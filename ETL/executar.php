<?php
   require_once('dim/DimCliente.php');

   require_once('dim/DimProduto.php');
   
   require_once('dim/Sumario.php');

   require_once('dim/DimData.php');

   use dimensoes\Sumario;
   
   use dimensoes\DimCliente;

   use dimensoes\DimProduto;

   use dimensoes\DimData;

   $dimCliente = new DimCliente();
   $sumCliente = $dimCliente->carregarDimCliente();

   $dimProduto = new DimProduto();
   $sumProduto = $dimProduto->carregarDimProduto();

   $dimData = new DimData();
   $sumData = $dimData->carregarDimData();

   echo "Clientes: <br>";
   echo "Inclusões: ".$sumCliente->quantidadeInclusoes."<br>";
   echo "Alterações: ".$sumCliente->quantidadeAlteracoes."<br>";
   echo "<br>==============================================<br>";

   echo "Produtos: <br>";
   echo "Inclusões: ".$sumProduto->quantidadeInclusoes."<br>";
   echo "Alterações: ".$sumProduto->quantidadeAlteracoes."<br>";
   echo "<br>==============================================<br>";

   echo "Data: <br>";
   echo "Inclusões: ".$sumData->quantidadeInclusoes."<br>";
   echo "<br>==============================================<br>";

?>