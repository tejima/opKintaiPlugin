<?php 
class KintaiPluginConfigForm extends sfForm
{
  protected $configs = array(
    'kintai_member_id' => 'kintai_member_id',
  );
  public function configure()
  {
    $this->setWidgets(array(
      'kintai_member_id' => new sfWidgetFormInput(),
    ));
    $this->setValidators(array(
      'kintai_member_id' => new sfValidatorString(array(),array()),
    ));

    $this->widgetSchema->setHelp('kintai_member_id', 'メンバーID');
    foreach($this->configs as $k => $v)
    {
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($v);
      if($config)
      {
        $this->getWidgetSchema()->setDefault($k,$config->getValue());
      }
    }
    $this->getWidgetSchema()->setNameFormat('kintai[%s]');
  }
  public function save()
  {
    foreach($this->getValues() as $k => $v)
    {
      if(!isset($this->configs[$k]))
      {
        continue;
      }
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($this->configs[$k]);
      if(!$config)
      {
        $config = new SnsConfig();
        $config->setName($this->configs[$k]);
      }
      $config->setValue($v);
      $config->save();
    }
  }
  public function validate($validator,$value,$arguments = array())
  {
    return $value;
  }
}

