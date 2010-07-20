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

    $this->form = new CommentForm();
    $this->getResponse()->addJavascript('/opKintaiPlugin/js/jquery-1.4.2.min.js', 'first');
    $this->getResponse()->addJavascript('/opKintaiPlugin/js/jquery.csv2table.js', 'first');
    if('error' == $request->getParameter('result')){
      return sfView::ERROR;
    }else{
      return sfView::SUCCESS;
    }
  }
  public function executeClear(sfWebRequest $request){
    $this->makeCSV();
    $this->redirect('/kintai');
  }
  public function executeComment(sfWebRequest $request){
    $this->form = new CommentForm();
    if ($request->isMethod(sfWebRequest::POST))
    {
      $this->form->bind($request->getParameter('comment'));
      if ($this->form->isValid())
      {
        $this->doSLEEP(null,$this->form->getValue('kintai_time'));
        $this->doCOMMENT(null,mb_ereg_replace("\n", "", $this->form->getValue('kintai_comment')));
      }
    }
    $this->redirect('/kintai');
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
  private function makeCSV($unixtime = null){
    $unixtime = $unixtime ? $unixtime : time();
    $target_year = date("Y",$unixtime);
    $target_month = date("m",$unixtime);

    $time = strtotime ("next month -1 day", strtotime($target_year."/".$target_month."/1"));
    $last_day = date("d",$time);
    //print_r($last_day);
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
  private function doUpdate($index,$value,$unixtime,$block_override=true){
    $unixtime = $unixtime ? $unixtime : time();
    $h = date("H",(int)$unixtime);
    if(0 <= $h && $h <= 5){
      $h += 24;
      $target_year = date("Y",$unixtime - 86400);
      $target_month = date("m",$unixtime - 86400);
      $target_date = date("d",$unixtime - 86400);
      $target_time = $h . ":" . date("i",$unixtime - 86400);
    }else{
      $target_year = date("Y",(int)$unixtime);
      $target_month = date("m",(int)$unixtime);
      $target_date = date("d",(int)$unixtime);
      $target_time = date("H:i",(int)$unixtime);
    }

    $member = $this->getUser()->getMember();
    $csv = $member->getConfig("KINTAI".$target_year . $target_month);
    if(!$csv){
      $csv = $this->makeCSV();
    }
    $re = '/^('.$target_year.'\/'.$target_month.'\/'.$target_date.')'. ',(.*?),(.*?),(.*?),(.*?)$/m';  
    //echo $re;
    switch($index){
      case 1: //in
      $replace = "$1,".$target_time.",$3,$4,$5";
      break; 
      case 2: //out
      $replace = "$1,$2,".$target_time.",$4,$5";
      break;  
      case 3: //sleep
      $replace = "$1,$2,$3,".$value.",$5";
      break;
      case 4: //comment
      $replace = "$1,$2,$3,$4,".$value;
      break;
    }
    $str = preg_replace($re,$replace,$csv,-1,$count);
    
    preg_match($re,$csv,$matches);
    //print_r($matches);
    //print_r($matches[$index+1]);
    //print_r($block_override);
    //print_r($str);
    //exit;
    if($block_override && $matches && $matches[$index+1]){
      return false;
    }else{
      $member->setConfig("KINTAI".$target_year . $target_month,$str);
      return true;
    }
  }
  private function doIN($unixtime=null){
    return $this->doUpdate(1,null,$unixtime,false);
  }
  private function doOUT($unixtime=null){
    return $this->doUpdate(2,null,$unixtime,false);
  }
  private function doSLEEP($unixtime=null,$time="1:00"){
    return $this->doUpdate(3,$time,$unixtime,false);
  } 
  private function doCOMMENT($unixtime=null,$comment=""){
    return $this->doUpdate(4,$comment,$unixtime,false);
  } 
}
