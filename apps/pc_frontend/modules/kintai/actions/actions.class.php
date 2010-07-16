<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * kintai actions.
 *
 * @package    OpenPNE
 * @subpackage kintai
 * @author     Mamoru Tejima
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class kintaiActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
//    $this->makeCSV();
//    $this->doOUT();
//    $this->doSLEEP();
//    $this->doCOMMENT("COMMENT");
    if('error' == $request->getParameter('result')){
      return sfView::ERROR;
    }else{
      return sfView::SUCCESS;
    }
  }
  public function executeGetcsv(sfWebRequest $request){
      $target_year = date("Y");
      $target_month = date("m");
      $csv = $this->getUser()->getMember()->getConfig("KINTAI".$target_year.$target_month);
      $this->csv = $csv; 
  }
  public function executeIn(sfWebRequest $request){
    $result = $this->doIN();
    if($result){
      $this->redirect('/kintai');
    }else{
      $this->redirect('/kintai?result=error');
    }
 }

  public function executeOut(sfWebRequest $request){
    $result = $this->doOUT();
    if($result){
      $this->redirect('/kintai');
    }else{
      $this->redirect('/kintai?result=error');
    }
  }
  private function makeCSV($target_year=null, $target_month=null){
    if(!$target_year){
      $target_year = date("Y");
    }
    if(!$target_month){
      $target_month = date("m");
    }
   $time = strtotime ("next month -1 day", strtotime($target_year."/".$target_month."/1"));
    $last_day = date("d",$time);
    print_r($last_day);
    //$fp = fopen('/tmp/file.csv', 'w');
    $fp = fopen("php://temp", 'r+');

    fputcsv($fp,array('日付','出勤','退勤','休憩','メモ'));
    for ($i=1;$i<=$last_day;$i++) {
        fputcsv($fp,array($target_year."/".$target_month."/".sprintf("%02d",$i),'','','',''));
    }

    // 先ほど書き込んだデータを読み込みます。
    rewind($fp);
    $csv =  stream_get_contents($fp);
    fclose($fp);

    //$csv_table = "7/10";
    $member = $this->getUser()->getMember();
    $member->setConfig("KINTAI".$target_year . $target_month,$csv);
    return $csv;
  }
  private function doUpdate($value,$index,$target_year=null,$target_month=null,$target_date=null,$block_override=true){
    $target_year = $target_year ?: date("Y");
    $target_month = $target_month ?: date("m");
    $target_date = $target_date ?: date("d");

    $member = $this->getUser()->getMember();
    $csv = $member->getConfig("KINTAI".$target_year . $target_month);
    if(!$csv){
      $csv = $this->makeCSV();
    }
    $re = '/^('.$target_year.'\/'.$target_month.'\/'.$target_date.')'. ',(.*?),(.*?),(.*?),(.*?)$/m';  
    //echo $re;
    switch($index){
      case 1:
      $replace = "$1,".$value.",$3,$4,$5";
      break;
      case 2:
      $replace = "$1,$2,".$value.",$4,$5";
      break;
      case 3:
      $replace = "$1,$2,$3,".$value.",$5";
      break;
      case 4:
      $replace = "$1,$2,$3,$4,".$value;
      break;
    }
    $str = preg_replace($re,$replace,$csv,-1,$count);
    
    preg_match($re,$csv,$matches);
    //print_r($matches);
    //print_r($matches[$index+1]);
    //print_r($block_override);
    //exit;
    if($block_override && $matches && $matches[$index+1]){
      return false;
    }else{
      $member->setConfig("KINTAI".$target_year . $target_month,$str);
      return true;
    }
  }
  private function doIN($time=null,$target_year=null,$target_month=null,$target_date=null){
    $time = $time ?: date("H:i");
    return $this->doUpdate($time,1,$target_year,$target_month,$target_date);
  }
  private function doOUT($time=null,$target_year=null,$target_month=null,$target_date=null){
    $time = $time ?: date("H:i");
    return $this->doUpdate($time,2,$target_year,$target_month,$target_date);
  }
  private function doSLEEP($time=null,$target_year=null,$target_month=null,$target_date=null){
    $time = $time ?: "1:00";
    return $this->doUpdate($time,3,$target_year,$target_month,$target_date);
  } 
  private function doCOMMENT($comment="",$target_year=null,$target_month=null,$target_date=null){
    return $this->doUpdate($comment,4,$target_year,$target_month,$target_date);
  } 
}
