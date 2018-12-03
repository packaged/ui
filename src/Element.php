<?php
namespace Packages\Ui;

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
      $this->_templateFilePath = substr(__FILE__, -3) . 'phtml';
    }
    return $this->_templateFilePath;
  }

  /**
   * Build the view response with the relevant template file
   *
   * @return string
   * @throws \Exception
   */
  public function render(): string
  {
    $tpl = $this->getTemplateFilePath();
    if(!file_exists($tpl))
    {
      throw new \Exception("The template file '$tpl' does not exist", 404);
    }
    ob_start();
    try
    {
      include $tpl;
    }
    catch(\Exception $e)
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
