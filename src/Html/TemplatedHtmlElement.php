<?php
namespace Packaged\Ui\Html;

use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\TemplateLoaderTrait;
use Throwable;

abstract class TemplatedHtmlElement extends HtmlElement
{
  use TemplateLoaderTrait;

  /**
   * @return SafeHtml
   *
   * @throws Throwable
   */
  protected function _getContentForRender()
  {
    return new SafeHtml($this->_renderTemplate());
  }
}
