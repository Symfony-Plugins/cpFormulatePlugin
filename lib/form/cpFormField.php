<?php
class cpFormField extends sfFormField {

  /**
   * Returns a formatted row.
   *
   * The formatted row will use the parent widget schema formatter.
   * The formatted row contains the label, the field, the error and
   * the help message.
   *
   * @param array  $attributes An array of HTML attributes to merge with the current attributes
   * @param string $label      The label name (not null to override the current value)
   * @param string $help       The help text (not null to override the current value)
   *
   * @return string The formatted row
   */
  public function renderRow($attributes = array(), $label = null, $help = null) {
    if (null === $this->parent) {
      throw new LogicException(sprintf('Unable to render the row for "%s".', $this->name));
    }

    $field = $this->parent->getWidget()->renderField($this->name, $this->value, !is_array($attributes) ? array() : $attributes, $this->error);

    $error = $this->error instanceof sfValidatorErrorSchema ? $this->error->getGlobalErrors() : $this->error;

    $help = null === $help ? $this->parent->getWidget()->getHelp($this->name) : $help;

    $formatter = $this->parent->getWidget()->getFormFormatter();
    
    if ($formatter instanceof cpWidgetFormSchemaFormatter) {
      return $formatter->renderRow($this->renderLabel($label), 
                                   $field, 
                                   $error, 
                                   $help, 
                                   '',
                                   $formatter->generateAttributes($this->getWidget(), $this->name));
    }
    else {
    	return strtr($ormFormatter->formatRow($this->renderLabel($label), $field, $error, $help), array('%hidden_fields%' => ''));
    }
  }
}