<?php
namespace Packaged\Ui;

/**
 * Class SDTElement Simple Dynamic Template Element
 */
class SDTElement extends Element
{
  public function __construct($templateFile)
  {
    $this->_templateFilePath = $templateFile;
  }
}
