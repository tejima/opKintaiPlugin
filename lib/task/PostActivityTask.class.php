<?php
class PostActivityTask extends sfBaseTask{
  protected function configure()
  {
    mb_language("Japanese");
    mb_internal_encoding("utf-8");

    $this->namespace        = 'tjm';
    $this->name             = 'kintai';
    $this->aliases          = array('tjm-kintai');
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [feed-reader|INFO] task does things.
Call it with:

  [php symfony socialagent:feed-reader [--env="..."] application|INFO]
EOF;
    //$this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    //$this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addArgument('mode', null , sfCommandOption::PARAMETER_REQUIRED, 'mode');
  }
  protected function execute($arguments = array(),$options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    if($arguments['mode'] == 'in'){
      $this->in();
    } else if($arguments['mode'] == 'out') {
      $this->out();
    }
  }

  private function out(){
    $url = sfConfig::get('op_base_url');
    $id = Doctrine::getTable('SnsConfig')->get('kintai_member_id',1);
    $this->log2activity($id,"お疲れさま。勤怠報告をよろしく。".date("Y/n/j")." ".$url."/kintai/out");
  }
  private function in(){
    $url = sfConfig::get('op_base_url');
    $id = Doctrine::getTable('SnsConfig')->get('kintai_member_id',1);
    $this->log2activity($id,"おはようございます。勤怠報告をよろしく。".date("Y/n/j")." ".$url."/kintai/in");
  }
  private function log2activity($id,$body){
    $act = new ActivityData();
    $act->setMemberId($id);
    $act->setBody($body);
    $act->setIsMobile(0);
    $act->save();
  }
}
?>
