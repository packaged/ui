<?php

namespace Packaged\Ui\Tests\Html;

use Packaged\Ui\Tests\Supporting\Html\TestTemplatedHtmlElement;
use PHPUnit\Framework\TestCase;

class TemplatedHtmlElementTest extends TestCase
{
  public function testTemplated()
  {
    $this->assertEquals(
      '<div><strong>This is strong</strong>
</div>',
      new TestTemplatedHtmlElement()
    );
  }
}
