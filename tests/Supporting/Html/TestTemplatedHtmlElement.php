<?php
namespace Packaged\Ui\Tests\Supporting\Html;

use Packaged\Ui\Html\TemplatedHtmlElement;

class TestTemplatedHtmlElement extends TemplatedHtmlElement
{
  protected $_tag = 'div';

  public $extensions = ['phtml'];

  protected function _attemptTemplateExtensions()
  {
    return $this->extensions;
  }
}
