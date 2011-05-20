<?php
class cpWidgetFormSchema extends sfWidgetFormSchema {
  protected $form;
  
  public function getForm() { return $this->form; }
  
  public function setForm(sfForm $form) { $this->form = $form; }
  
  /**
   * Renders the widget.
   *
   * @param string $name       The name of the HTML widget
   * @param mixed  $values     The values of the widget
   * @param array  $attributes An array of HTML attributes
   * @param array  $errors     An array of errors
   *
   * @return string An HTML representation of the widget
   *
   * @throws InvalidArgumentException when values type is not array|ArrayAccess
   */
  public function render($name, $values = array(), $attributes = array(), $errors = array()) {
    if (null === $values) {
      $values = array();
    }

    if (!is_array($values) && !$values instanceof ArrayAccess) {
      throw new InvalidArgumentException('You must pass an array of values to render a widget schema');
    }

    $formatter = $this->getFormFormatter();
    
    if ($formatter instanceof cpWidgetFormSchemaFormatter) {
      return $formatter->renderForm($name, $this->getForm(), $values, $attributes, $errors);
    }
    else {
      return parent::render($name, $values, $attributes, $errors);
    }
  }
}