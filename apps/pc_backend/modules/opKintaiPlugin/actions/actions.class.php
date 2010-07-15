<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opKintaiPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opKintaiPlugin
 * @author     Mamoru Tejima
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class opKintaiPluginActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new KintaiPluginConfigForm();
    if ($request->isMethod(sfWebRequest::POST))
    {
      $this->form->bind($request->getParameter('kintai'));
      if ($this->form->isValid())
      {
        $this->form->save();
        $this->redirect('opKintaiPlugin/index');
      }
    }
  }
}
