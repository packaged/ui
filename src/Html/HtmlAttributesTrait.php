<?php
namespace Packaged\Ui\Html;

use Packaged\Helpers\Arrays;
use function array_key_exists;

trait HtmlAttributesTrait
{
  protected $_attributes = [];

  /**
   * @param string $id
   *
   * @return $this
   */
  public function setId($id)
  {
    return $this->setOrRemoveAttribute('id', $id);
  }

  /**
   * @param string $key
   * @param string $value
   *
   * @return $this
   */
  public function setOrRemoveAttribute(string $key, $value)
  {
    if($value === null || $value === '')
    {
      $this->removeAttribute($key);
    }
    else
    {
      $this->setAttribute($key, $value);
    }
    return $this;
  }

  /**
   * @param string $key
   *
   * @return $this
   */
  public function removeAttribute(string $key)
  {
    unset($this->_attributes[$key]);
    return $this;
  }

  /**
   * @param string $key
   * @param string $value
   *
   * @param bool   $ignoreEmpty Do not set attributes where the value is empty string or null
   *
   * @return $this
   */
  public function setAttribute(string $key, $value, $ignoreEmpty = false)
  {
    if($ignoreEmpty && ($value === '' || $value === null))
    {
      return $this;
    }

    $this->_attributes[$key] = $value;
    return $this;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->getAttribute('id');
  }

  /**
   * @param string $key
   * @param string $default
   *
   * @return string
   */
  public function getAttribute(string $key, $default = null)
  {
    return Arrays::value($this->_attributes, $key, $default);
  }

  /**
   * Array of attributes for the tag
   *
   * @param array $attributes
   * @param bool  $overwriteIfExists
   *
   * @return $this
   */
  public function addAttributes(array $attributes, bool $overwriteIfExists = false)
  {
    foreach($attributes as $k => $v)
    {
      if($overwriteIfExists || !array_key_exists($k, $this->_attributes))
      {
        $this->setOrRemoveAttribute($k, $v);
      }
    }
    return $this;
  }

  /**
   * @return array
   */
  public function getAttributes()
  {
    return $this->_attributes;
  }

  /**
   * Array of attributes for the tag
   *
   * @param array $attributes
   *
   * @return $this
   */
  public function setAttributes(array $attributes)
  {
    $this->_attributes = $attributes;
    return $this;
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function hasAttribute(string $key)
  {
    return array_key_exists($key, $this->_attributes);
  }

  /**
   * @param string ...$class
   *
   * @return $this
   */
  public function addClass(...$class)
  {
    foreach($class as $c)
    {
      $this->_addClass($c);
    }

    return $this;
  }

  /**
   * @param string $class
   *
   * @return $this
   */
  private function _addClass(string $class)
  {
    if(!isset($this->_attributes['class']))
    {
      $this->_attributes['class'] = [];
    }
    $this->_attributes['class'][$class] = $class;
    return $this;
  }

  /**
   * @param string $class
   *
   * @return bool
   */
  public function hasClass(string $class)
  {
    return isset($this->_attributes['class'][$class]);
  }

  /**
   * @param string ...$class
   *
   * @return $this
   */
  public function removeClass(...$class)
  {
    foreach($class as $c)
    {
      $this->_removeClass($c);
    }
    return $this;
  }

  /**
   * @param string $class
   *
   * @return $this
   */
  private function _removeClass(string $class)
  {
    unset($this->_attributes['class'][$class]);
    return $this;
  }

  /**
   * Retrieve all classes set on the element
   *
   * @return string[]
   */
  public function getClasses()
  {
    return (array)Arrays::value($this->_attributes, 'class', []);
  }
}
