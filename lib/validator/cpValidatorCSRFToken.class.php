<?php
class cpValidatorCSRFToken extends sfValidatorBase {
  
  protected function configure($options = array(), $messages = array()) {
    $this->addRequiredOption('token');

    $this->setOption('required', true);

    $this->addMessage('csrf_attack', sfConfig::get('app_cp_formulate_csrf_attack_message', 'Your session has expired.'));
  }

  protected function doClean($value) {
    if ($value != $this->getOption('token')) {
      $exception = new sfValidatorError($this, 'csrf_attack');
      throw new sfValidatorErrorSchema($this, array($exception));
    }

    return $value;
  }
}