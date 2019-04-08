<?php
namespace Packaged\Ui\Tests\Supporting\Html;

use Packaged\Ui\Html\HtmlElement;

class TestHtmlElement extends HtmlElement
{
  protected $_content;

  public function __construct($tag = 'div')
  {
    $this->_tag = $tag;
  }

  /**
   * @param mixed $content
   *
   * @return TestHtmlElement
   */
  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

  protected function _getContentForRender()
  {
    return $this->_content ?? parent::_getContentForRender();
  }
}
