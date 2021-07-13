<?php
namespace Packaged\Ui\Html;

use Packaged\Helpers\Arrays;
use function array_key_exists;

trait HtmlAttributesTrait
{
  protected $_attributes = [];

  /**
   * @return $this
   */
  public function setId(string $id)
  {
    return $this->setOrRemoveAttribute('id', $id);
  }

  /**
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
   * @return $this
   */
  public function removeAttribute(string $key)
  {
    unset($this->_attributes[$key]);
    return $this;
  }

  /**
   * @param bool $ignoreEmpty Do not set attributes where the value is empty string or null
   *
   * @return $this
   */
  public function setAttribute(string $key, $value, bool $ignoreEmpty = false)
  {
    if($ignoreEmpty && ($value === '' || $value === null))
    {
      return $this;
    }

    $this->_attributes[$key] = $value;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getId()
  {
    return $this->getAttribute('id');
  }

  /**
   * @return string|null
   */
  public function getAttribute(string $key, $default = null)
  {
    $attr = Arrays::value($this->_attributes, $key, $default);

    return is_array($attr)
      ? implode(' ', $attr)
      : $attr;
  }

  /**
   * Array of attributes for the tag
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
   * @return array<string, string>
   */
  public function getAttributes()
  {
    return $this->_attributes;
  }

  /**
   * Array of attributes for the tag
   *
   * @return $this
   */
  public function setAttributes(array $attributes)
  {
    $this->_attributes = $attributes;
    return $this;
  }

  /**
   * @return bool
   */
  public function hasAttribute(string $key)
  {
    return array_key_exists($key, $this->_attributes);
  }

  /**
   * @param string|string[] ...$class
   *
   * @return $this
   */
  public function addClass(...$class)
  {
    foreach($class as $c)
    {
      if(is_string($c))
      {
        $this->_addClass($c);
      }
      else if(is_iterable($c))
      {
        foreach($c as $cc)
        {
          $this->_addClass($cc);
        }
      }
    }

    return $this;
  }

  /**
   * @return $this
   */
  private function _addClass(string $class)
  {
    if(!isset($this->_attributes['class']))
    {
      $this->_attributes['class'] = [];
    }
    else if(is_string($this->_attributes['class']))
    {
      $this->_attributes['class'] = explode(' ', $this->_attributes['class']);
    }

    $this->_attributes['class'][$class] = $class;
    return $this;
  }

  /**
   * @return bool
   */
  public function hasClass(string $class)
  {
    return isset($this->_attributes['class'][$class]);
  }

  /**
   * @param string|string[] ...$class
   *
   * @return $this
   */
  public function removeClass(...$class)
  {
    foreach($class as $c)
    {
      if(is_string($c))
      {
        $this->_removeClass($c);
      }
      else if(is_iterable($c))
      {
        foreach($c as $cc)
        {
          $this->_removeClass($cc);
        }
      }
    }
    return $this;
  }

  /**
   * Toggle a class to the desired state, or the opposite of the current state
   *
   * @return $this
   */
  public function toggleClass(string $class, bool $toggle = null)
  {
    if($toggle === true || ($toggle === null && !$this->hasClass($class)))
    {
      return $this->addClass($class);
    }
    return $this->removeClass($class);
  }

  /**
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
   * @return array<string>
   */
  public function getClasses()
  {
    $attrs = Arrays::value($this->_attributes, 'class', []);

    return (is_string($attrs))
      ? explode(' ', $attrs)
      : array_values($attrs);
  }
}
