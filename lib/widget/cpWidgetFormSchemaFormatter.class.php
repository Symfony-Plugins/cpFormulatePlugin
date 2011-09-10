<?php
class cpWidgetFormSchemaFormatter extends sfWidgetFormSchemaFormatter {
  
  protected $format_form = "%errors%\n%fields%",
            $format_form_subform = "%fields%",
            $format_fields = "<ul>\n%fields%\n  </ul>";

  public function renderForm($name, sfForm $form, $values = array(), $attributes = array(), $errors = array()) {
    $form_name = $name;
    $tokens = array();
    $fieldset = false;
    $is_subform = $form instanceof cpForm && $form->getParentForm();
    
    $schema = $this->getWidgetSchema();
    $hiddenRows = array();
    $validators = $schema->getForm()->getValidatorSchema();
    
    // render each field
    foreach ($schema->getPositions() as $name) {
      $widget = $this->processWidget($schema[$name]);
      $value = isset($values[$name]) ? $values[$name] : null;
      $error = isset($errors[$name]) ? $errors[$name] : array();
      $widgetAttributes = isset($attributes[$name]) ? $attributes[$name] : array();
 
      if ($widget instanceof sfWidgetForm && $widget->isHidden()) {
        $hiddenRows[] = $schema->renderField($name, $value, $widgetAttributes);
      }
      else if ($widget instanceof sfWidgetFormSchema) {
        if ($fieldset) { 
          $token = $this->getFieldsetClosingToken($form, $form_name);
          $fieldset = false;
        }
        else { $token = ''; }
        $tokens[] = $token . $this->renderField($name, $value, $widgetAttributes, $error);
      }
      else {
        if (!$fieldset) {
          $token = $this->getFieldsetToken($form, $form_name); 
          $fieldset = true;
        }
        else { $token = ''; }
        $tokens[] = $token . $this->renderField($name, $value, $widgetAttributes, $error);
      }
    }

    if ($fieldset) { $tokens[] = $this->getFieldsetClosingToken($form, $form_name); }
    
    $tokens[] = implode("\n", $hiddenRows); 
    $form_format = $is_subform ?
                     $this->getFormFormatSubform() :
                     $this->getFormFormat();
    return strtr($form_format, 
                 array('%errors%' => count($errors) ? $this->formatErrorRow($errors) : '',
                       '%fields%' => $this->formatFields($tokens)));
  }

  public function processWidget(sfWidgetForm $widget) {
    return $widget;
  }

  public function renderField($name, $value = null, $attributes, $error) {
    $schema = $this->getWidgetSchema();
    $widget = $schema[$name];
        
    $widgetAttributes = $this->generateAttributes($widget, $name);
    if ($widget->getAttribute('holder_class')) {
      $widget->setAttribute('holder_class', null);
    }
    
    // don't add a label tag if we embed a form schema
    $label = $widget instanceof sfWidgetFormSchema ? 
               $this->generateLabelName($name) : 
               $this->generateLabel($name);
    
    if ($widget instanceof sfWidgetFormSchema) {
      return $widget->render($name, $value, $attributes, $error); 
    }
    else {
      // don't add a label tag and errors if we embed a form schema
      $label = $this->generateLabel($name); 
      $error = $widget instanceof sfWidgetFormSchema ? array() : $error;
      $field = $schema->renderField($name, $value, $attributes, $error);
     
      return $this->renderRow($label, $field, $error, $schema->getHelp($name), null, $widgetAttributes);
    }
  }
  
  public function renderRow($label, $field, $errors = array(), $help = '', $hiddenFields = null, $attributes = array()) {
    return $this->formatRow($label, $field, $errors, $help);
  }

  public function generateAttributes($widget, $name) { return array(); }
  
  public function formatFields($fields) {
    return strtr($this->getFieldsFormat(),
                 array('%fields%' => implode('', $fields)));
  }
  
  public function getFormFormat() { return $this->format_form; }
  
  public function getFormFormatSubForm() { return $this->format_form_subform; }
  
  public function setFormFormat($format) { $this->format_form = $format; }
  
  public function getFieldsFormat() { return $this->format_fields; }
  
  public function setFieldsFormat($format) { $this->format_fields = $format; }
  
  protected function getFieldsetToken(sfForm $form, $name = null) {
    return '<fieldset' . ($name ? ' id="__' . $name . '"' : '') . 
                         ($form->getOption('fieldset_class') ? ' class="' . $form->getOption('fieldset_class') . '">' : '>');  
  }
  
  protected function getFieldsetClosingToken(sfForm $form, $name = null) {
    return '</fieldset>';
  }
}
