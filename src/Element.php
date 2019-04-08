<?php
namespace Packaged\Ui;

use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Throwable;

class Element implements Renderable, ISafeHtmlProducer
{
  use TemplateLoaderTrait;

  /**
   * @return string
   * @throws Throwable
   */
  public function render(): string
  {
    return $this->_renderTemplate();
  }

  public function produceSafeHTML(): SafeHtml
  {
    try
    {
      return new SafeHtml($this->render());
    }
    catch(Throwable $e)
    {
      return SafeHtml::escape($e->getMessage());
    }
  }

  public function __toString()
  {
    return (string)$this->produceSafeHTML();
  }
}
