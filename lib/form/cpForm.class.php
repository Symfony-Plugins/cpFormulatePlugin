<?php
class cpForm extends sfFormSymfony {

  protected $parentForm = null;

  /**
   * Public constructor
   *
   * @see sfForm
   */
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
    $this->setDefaults($defaults);
    $this->options = $options;
    $this->localCSRFSecret = $CSRFSecret;

    $this->validatorSchema = new sfValidatorSchema();
    $this->setWidgetSchema(new cpWidgetFormSchema());
    $this->errorSchema     = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setup();
    $this->configure();

    $this->addCSRFProtection($this->localCSRFSecret);
    $this->resetFormFields();
    $this->postSetup();
  }

  protected function postSetup() {}

  /**
   * Sets the widgets associated with this form.
   *
   * @param array $widgets An array of named widgets
   *
   * @return sfForm The current form instance
   */
  public function setWidgets(array $widgets) {
    $this->setWidgetSchema(new cpWidgetFormSchema($widgets));
    return $this;
  }

  public function setWidgetSchema(sfWidgetFormSchema $widgetSchema) {
    $this->widgetSchema = $widgetSchema;
    if ($this->widgetSchema instanceof cpWidgetFormSchema) {
      $this->widgetSchema->setForm($this);
    }

    $this->resetFormFields();

    return $this;
  }

  /**
   * Gets the embedded form by given name.
   *
   * @param string $name  Name of embedded form
   * @return moxed sfForm obejct
   */
  public function embedForm($name, sfForm $form, $decorator = null) {
    $form->setParentForm($this);
    parent::embedForm($name, $form, $decorator);
  }

  private function setParentForm($form) { $this->parentForm = $form; }

  public function getParentForm() { return $this->parentForm; }

  /*
   *  Unsetting all fields from Form
   *  except elements given in array
   */
  public function unsetAllExcept($fields = array()) {
    if (class_exists('BasePeer')) {
      foreach ($this->getObject()->toArray(BasePeer::TYPE_FIELDNAME) as $key => $val) {
        $tmp[] = strtolower($key);
      }
    }
    else {
      $tmp = $this->object->getTable()->getFieldNames();
    }

    $tmp = array_diff($tmp, $fields);
    foreach ($tmp as $value) { unset($this[$value]); }
  }

  /**
   * Sets a value for a form field.
   *
   * @param string $field   The field name
   * @param mixed  $value   The default value
   */
  public function setValue($field, $value) {
    if (!in_array($field, array_keys($this->values))) {
      throw new sfException(sprintf('Unkown field" "%s" in "%s" object.', $field, get_class($this)));
    }

    $this->values[$field] = $value;
  }

  protected function getCulture() {
    return $this->getOption('culture');
  }

  public function getTranslationCatalogue() {
    return $this->getWidgetSchema()->getFormFormatter()->getTranslationCatalogue();
  }

  public function setTranslationCatalogue($catalogue) {
    $this->getWidgetSchema()->getFormFormatter()->setTranslationCatalogue($catalogue);
  }

  protected function translate($subject, $parameters = array()) {
    return $this->widgetSchema->getFormFormatter()->translate($subject, $parameters);
  }

  /**
   * Returns the form field associated with the name (implements the ArrayAccess interface).
   *
   * @param  string $name  The offset of the value to get
   *
   * @return sfFormField   A form field instance
   */
  public function offsetGet($name) {
    if (!isset($this->formFields[$name])) {
      if (!$widget = $this->widgetSchema[$name]) {
        throw new InvalidArgumentException(sprintf('Widget "%s" does not exist.', $name));
      }

      if ($this->isBound) {
        $value = isset($this->taintedValues[$name]) ? $this->taintedValues[$name] : null;
      }
      else if (isset($this->defaults[$name])) {
        $value = $this->defaults[$name];
      }
      else {
        $value = $widget instanceof sfWidgetFormSchema ? $widget->getDefaults() : $widget->getDefault();
      }

      $class = $widget instanceof sfWidgetFormSchema ? 'sfFormFieldSchema' : 'cpFormField';

      $this->formFields[$name] = new $class($widget, $this->getFormFieldSchema(), $name, $value, $this->errorSchema[$name]);
    }

    return $this->formFields[$name];
  }
}