<?php
class basura {
  public function setAllStockInGeneral(){
    $query = "SELECT ARTSTO, ALMSTO, ACTSTO, DISSTO FROM F_STO WHERE ACTSTO>0";
    $goals = 0; $fails=0;
    try {
      $exec = $this->con->prepare($query);
      $exec->execute(null);
      $products = $exec->fetchall(PDO::FETCH_ASSOC);
      $exh = (array)array_filter($products, function($product){
        return $product['ALMSTO'] == 'EXH';
      });

      $gen = (array)array_filter($products, function($product){
        return $product['ALMSTO'] == 'GEN';
      });
      echo count($exh).'-'.count($gen).'\n';

      foreach($exh as $key => $product){
        $index = array_search($product['ARTSTO'], array_column($gen, 'ARTSTO'));
        if($index!==false){
          $gen[$index]['ACTSTO'] = /* intval($gen[$index]['ACTSTO']) + */ intval($exh[$key]['ACTSTO']);
          $exh[$key]['ACTSTO'] = 0;
          $gen[$index]['DISSTO'] = intval($gen[$index]['ACTSTO'])/* intval($gen[$index]['DISSTO']) + intval($exh[$key]['DISSTO']) */;
          $exh[$key]['DISSTO'] = 0;
        }
        
      }
      foreach($products as $key => $product){
        $indexGen = array_search($product['ARTSTO'], array_column($gen, 'ARTSTO'));
        $indexExh = array_search($product['ARTSTO'], array_column($exh, 'ARTSTO'));
        echo $product['ARTSTO'].' '.intval($product['ACTSTO'])." ".$product['ALMSTO']." ".$indexGen." ".$indexExh;
        /* if($indexGen!==false){
          $res = intval($gen[$indexGen]['ACTSTO']) + intval($gen[$indexGen]['DISSTO']);
          echo $gen[$indexGen]['ACTSTO'].' '.$gen[$indexGen]['DISSTO'].' '.$res.' ';
          echo $gen[$indexGen]['ACTSTO'].' ';
        }else{
          echo '\t';
        }
        if($indexExh!==false){
          echo $exh[$indexExh]['ACTSTO'];
        } */
        echo "\n";
      }
    }catch(\PDOException $e){
      die($e->getMessage());
    }
  }
}