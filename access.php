<?php
require_once('./vendor/autoload.php');
define("ACCDB","C:\\Users\\Yusev\\Desktop\\chido\\ACCESS\\SP22020.mdb");
class Access{
  public $con = null;
  
  function __construct(){
    try{
      $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8; DBQ=".ACCDB."; Uid=; Pwd=;");
      /* echo "fsol OK!"."\n"; */
      $this->con = $db;
    }catch(PDOException $e){
        die("Algo salio mal: ".$e->getMessage());
    }
  }

  public function setAllStockInGeneral(){
    $query = "SELECT ARTSTO, ALMSTO, ACTSTO, DISSTO FROM F_STO WHERE ACTSTO>0 AND ALMSTO = 'EXH'";
    $goals = 0; $fails=0;
    try{
      $exec = $this->con->prepare($query);
      $exec->execute(null);
      $products = $exec->fetchall(PDO::FETCH_ASSOC);
      $goals=0; $errors=[];
      foreach($products as $product){
        $query_alm_gen = "SELECT ARTSTO, ALMSTO, ACTSTO, DISSTO FROM F_STO WHERE ARTSTO=? AND ALMSTO = 'GEN'";
        $q_alm = $this->con->prepare($query_alm_gen);
        $q_alm->execute([$product['ARTSTO']]);
        $general = $q_alm->fetch(PDO::FETCH_ASSOC);
        if ($general){
          $stock = intval($product['ACTSTO']) + intval($general["ACTSTO"]);
          $query_update_stock = 'UPDATE F_STO SET ACTSTO=?, DISSTO = ? WHERE ARTSTO = ? AND ALMSTO = ?';
          $q_stock = $this->con->prepare($query_update_stock);
          $q_stock->execute([$stock, $stock, $product['ARTSTO'], 'GEN']);
          $q_stock->execute([0, 0, $product['ARTSTO'], 'EXH']);
          echo $product['ARTSTO']." ".intval($product['ACTSTO'])." ".intval($general["ACTSTO"])." ".$stock."\n";
        }
      }
    }catch(\PDOException $e){
      die($e->getMessage());
    }
  }

  public function setStockInExhibition($file){
    $query_get = "SELECT ARTSTO, ALMSTO, ACTSTO, DISSTO FROM F_STO WHERE ARTSTO = ? AND ALMSTO = ?";
    $query_post = "UPDATE F_STO SET ACTSTO=? WHERE ARTSTO LIKE ? AND ALMSTO= ?";
    $goals=0; $fails=0;
    try {
      $get = $this->con->prepare($query_get);
      $post = $this->con->prepare($query_post);
      /* $q_alm->execute([$product['ARTSTO']]); */
      $objPHPExcel = PHPExcel_IOFactory::load($file);

      $dataSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
      $sizesheet = sizeof($dataSheet);

      echo "Filas a procesar: ".$sizesheet."\n";

      foreach($dataSheet as $col){
        echo $col['A']." ".$col['B']."\n";
      }
      /* foreach($dataSheet as $col){
        $code = $col['A'];
        $get->execute([$code, 'GEN']);
        $general = $get->fetch(PDO::FETCH_ASSOC);
        $get->execute([$code, 'EXH']);
        $exhibition = $get->fetch(PDO::FETCH_ASSOC);
        $stock_gen = intval($col['B']) ? (intval($general['ACTSTO']) - intval($col['B'])) : intval($general['ACTSTO']);
        $$post->execute([$stock_gen, $code, 'GEN']);
        $rowcount = $post->rowCount();
        if($rowcount >0){
          $stock_exh = intval($col['B']) ? (intval($exhibition['ACTSTO']) + intval($col['B'])) : intval($exhibition['ACTSTO']);
          $post->execute([$stock_exh, $code, 'EXH']);
          if($rowcount >0){
            $goals++;
            echo "CODE: ".$code." - STOCK_GEN: ".$stock_gen." - stock_EXH: ".$stock_exh." :: ".$qudt->rowCount()."\n";
          }else{
            echo "CODE: ".$code." FAIL!!!\n";
            $fails++;
          }
        }else{
          echo "CODE: ".$code." FAIL!!!\n";
          $fails++;
        }
      } */

      echo "Cambios: ".$goals."\n";
      echo "Fails: ".$fails."\n";
    } catch (\PDOException $e) {
      die($e->getMessage());
    }    
  }

  public function depureDatabase($file){
    $query_art = "DELETE FROM F_ART WHERE CODART = ?";
    $query_sto = "DELETE FROM F_STO WHERE ARTSTO = ?";
    $query_lta = "DELETE FROM F_LTA WHERE ARTLTA = ?";
    try{
      $art = $this->con->prepare($query_art);
      $sto = $this->con->prepare($query_sto);
      $lta = $this->con->prepare($query_lta);
      $objPHPExcel = PHPExcel_IOFactory::load($file);

      $dataSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
      $sizesheet = sizeof($dataSheet);

      echo "Filas a procesar: ".$sizesheet."\n";

      foreach($dataSheet as $col){
        $code = $col['A'];
        $art->execute([$code]);
        $sto->execute([$code]);
        $lta->execute([$code]);
        $rowcount_art = $art->rowCount();
        $rowcount_sto = $sto->rowCount();
        $rowcount_lta = $lta->rowCount();
        if($rowcount_art>0 || $rowcount_sto>0 || $rowcount_lta>0){
          $goals++;
          echo "CODE: ".$code." - F_ART: ".$rowcount_art." - F_STO: ".$rowcount_sto." F_LTA: ".$rowcount_lta."\n";
        }else{
          echo "CODE: ".$code." FAIL!!!\n";
          $fails++;
        }
      }

      echo "Cambios: ".$goals."\n";
      echo "Fails: ".$fails."\n";
    }catch(\PDOException $e){
      die($e->getMessage());
    }
  }

  public function free($commit=false){
    if($commit){ $this->con->commit(); echo "cambios aplicados"; }
    $this->con=null;
  }
}
