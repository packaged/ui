<?php

namespace Packaged\Ui\Tests\Supporting\Html;

use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\HtmlElement;

/**
 * This test is to make sure we don't change the signature of a function that is extended from
 */
class TestExtendingElement extends HtmlElement
{

  public function render(): string
  {
    return parent::render();
  }

  public function produceSafeHTML(): SafeHtml
  {
    return parent::produceSafeHTML();
  }

  protected function _generateAttributesString(HtmlElement $ele)
  {
    return parent::_generateAttributesString($ele);
  }

  protected function _prepareForProduce(): HtmlElement
  {
    return parent::_prepareForProduce();
  }

  public function getTag()
  {
    return parent::getTag();
  }

  protected function _getContentForRender()
  {
    return parent::_getContentForRender();
  }

  public function setId($id)
  {
  }

  public function setOrRemoveAttribute(string $key, $value)
  {
  }

  public function removeAttribute(string $key)
  {
  }

  public function setAttribute(string $key, $value, $ignoreEmpty = false)
  {
  }

  public function getId()
  {
  }

  public function getAttribute(string $key, $default = null)
  {
  }

  public function addAttributes(array $attributes, bool $overwriteIfExists = false)
  {
  }

  public function getAttributes()
  {
  }

  public function setAttributes(array $attributes)
  {
  }

  public function hasAttribute(string $key)
  {
  }

  public function addClass(...$class)
  {
  }

  public function hasClass(string $class)
  {
  }

  public function removeClass(...$class)
  {
  }

  public function toggleClass($class, bool $toggle = null)
  {
  }

  public function getClasses()
  {
  }
}
