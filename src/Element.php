<?php
namespace Packaged\Ui;

use Exception;
use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;

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

  public function produceSafeHTML()
  {
    try
    {
      $rendered = $this->render();
      return SafeHtml::escape($rendered);
    }
    catch(Exception $e)
    {
    }
    return new SafeHtml($e->getMessage());
  }

}
