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

  public function testTemplatedDe()
  {
    $ele = new TestTemplatedHtmlElement();
    $ele->extensions = ['de.phtml', 'phtml'];
    $this->assertEquals(
      '<div><strong>Das ist stark</strong>
</div>',
      $ele
    );
  }

  public function testTemplatedFr()
  {
    $ele = new TestTemplatedHtmlElement();
    $ele->extensions = ['fr.phtml', 'phtml'];
    $this->assertEquals(
      '<div><strong>This is strong</strong>
</div>',
      $ele
    );
  }
}
