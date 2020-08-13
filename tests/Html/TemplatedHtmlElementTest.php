<?php

namespace Packaged\Ui\Tests\Html;

use Exception;
use Packaged\Ui\Tests\Supporting\Html\TestTemplatedHtmlElement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

  public function testMissingTemplatedException()
  {
    $ele = new TestTemplatedHtmlElement();
    $ele->extensions = ['missing'];
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('No template file found for `TestTemplatedHtmlElement`');
    $ele->render();
  }

  public function testTemplatedException()
  {
    $ele = new TestTemplatedHtmlElement();
    $ele->extensions = ['ex.phtml'];
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('this failed (TestTemplatedHtmlElement.ex.phtml:3)');
    $ele->render();
  }
}
