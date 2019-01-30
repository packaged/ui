<?php
namespace Packaged\Ui;

use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Throwable;

class Element implements Renderable, ISafeHtmlProducer
{
  protected $_templateFilePath;

  protected function getTemplateFilePath()
  {
    if($this->_templateFilePath === null)
    {
      $this->_templateFilePath = realpath(
        substr((new \ReflectionClass(static::class))->getFileName(), 0, -3) . 'phtml'
      );
    }
    return $this->_templateFilePath;
  }

  /**
   * Build the view response with the relevant template file
   *
   * @return string
   * @throws \Throwable
   */
  public function render(): string
  {
    $tpl = $this->getTemplateFilePath();
    if(!$tpl)
    {
      throw new \Exception("The template file '$tpl' does not exist", 404);
    }

    ob_start();
    try
    {
      include($tpl);
    }
    catch(\Throwable $e)
    {
      ob_end_clean();
      throw $e;
    }
    return ob_get_clean();
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
