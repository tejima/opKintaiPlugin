<?php
class CommentForm extends sfForm
{
  protected $configs = array(
    'kintai_comment' => 'kintai_comment',
    'kintai_time' => 'kintai_time',
  );
  public function configure()
  {
    $this->setWidgets(array(
      'kintai_comment' => new sfWidgetFormTextArea(),
      'kintai_time' => new sfWidgetFormSelect(array('choices' => array('10'=>'10','20'=>'20','30'=>'30','40'=>'40','50'=>'50','60'=>'60','70'=>'70','80'=>'80','90'=>'90','100'=>'100','110'=>'110','120'=>'120'),'default'=>'60')),
    ));
    $this->setDefault('kintai_time','60');
    $this->setValidators(array(
      'kintai_comment' => new sfValidatorString(array(),array()),
      'kintai_time' => new sfValidatorString(array(),array()),
    ));
    $this->widgetSchema->setHelp('kintai_comment', 'comment');
    $this->getWidgetSchema()->setNameFormat('comment[%s]');
  }
  public function validate($validator,$value,$arguments = array())
  {
    return $value;
  }
}
