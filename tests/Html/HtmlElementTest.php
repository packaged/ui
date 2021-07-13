<?php
namespace Packaged\Ui\Tests\Html;

use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\Uri;
use Packaged\Ui\Tests\Supporting\Html\TestExtendingElement;
use Packaged\Ui\Tests\Supporting\Html\TestHtmlElement;
use PHPUnit\Framework\TestCase;

class HtmlElementTest extends TestCase
{
  public function testGettersAndSetters()
  {
    $tag = new TestHtmlElement();
    self::assertEquals('div', $tag->getTag());

    $tag->setId('myid');
    self::assertEquals('myid', $tag->getId());

    $tag->setId('');
    self::assertEquals(null, $tag->getId());

    self::assertFalse($tag->hasAttribute('random'));
    self::assertEquals('no', $tag->getAttribute('random', 'no'));
    $tag->setAttribute('random', 'test');
    self::assertTrue($tag->hasAttribute('random'));
    self::assertEquals('test', $tag->getAttribute('random'));

    $attr = ['test' => 'ran', 'class' => 'test', 'id' => 'four'];
    $tag->setAttributes($attr);
    self::assertSame($attr, $tag->getAttributes());
    self::assertEquals('four', $tag->getId());

    $tag->addAttributes(['test' => 'no'], false);
    self::assertEquals('ran', $tag->getAttribute('test'));

    $tag->addAttributes(['test' => 'yes'], true);
    self::assertEquals('yes', $tag->getAttribute('test'));

    $tag->removeAttribute('class');
    self::assertFalse($tag->hasClass('red'));
    $tag->addClass('red');
    self::assertTrue($tag->hasClass('red'));
    self::assertEquals([0 => 'red'], $tag->getClasses());
    $tag->removeClass('red');
    self::assertFalse($tag->hasClass('red'));

    $tag->addClass('red', 'blue', ['green', 'yellow'], 'orange');
    self::assertTrue($tag->hasClass('yellow'));
    self::assertTrue($tag->hasClass('blue'));
    self::assertTrue($tag->hasClass('green'));
    self::assertTrue($tag->hasClass('red'));
    self::assertTrue($tag->hasClass('orange'));

    $tag->removeClass('yellow', ['blue', 'green'], 'red');
    self::assertFalse($tag->hasClass('yellow'));
    self::assertFalse($tag->hasClass('blue'));
    self::assertFalse($tag->hasClass('green'));
    self::assertFalse($tag->hasClass('red'));
    self::assertTrue($tag->hasClass('orange'));

    $tag->removeClass('toggled');
    self::assertFalse($tag->hasClass('toggled'));
    $tag->toggleClass('toggled');
    self::assertTrue($tag->hasClass('toggled'));
    $tag->toggleClass('toggled');
    self::assertFalse($tag->hasClass('toggled'));
    $tag->toggleClass('toggled', false);
    self::assertFalse($tag->hasClass('toggled'));
    $tag->toggleClass('toggled');
    self::assertTrue($tag->hasClass('toggled'));
    $tag->toggleClass('toggled', true);
    self::assertTrue($tag->hasClass('toggled'));
    $tag->toggleClass('toggled', true);
    self::assertTrue($tag->hasClass('toggled'));
    $tag->toggleClass('toggled', false);
    self::assertFalse($tag->hasClass('toggled'));
  }

  /**
   * @noinspection HtmlUnknownTarget
   * @noinspection HtmlRequiredAltAttribute
   */
  public function testSelfClosers()
  {
    self::assertEquals('<br />', (string)new TestHtmlElement('br'));
    self::assertEquals('<br />', (string)(new TestHtmlElement('br'))->setContent([]));
    self::assertEquals(
      '<img src="x.gif" />',
      (string)(new TestHtmlElement('img'))->setAttributes(['src' => 'x.gif'])
    );
  }

  public function testClasses()
  {
    $tag = new TestHtmlElement();

    $tag->addClass('aa');
    self::assertEquals(['aa'], $tag->getClasses());
    self::assertEquals('aa', $tag->getAttribute('class'));

    $tag->setAttribute('class', 'xx yy');
    self::assertEquals(['xx', 'yy'], $tag->getClasses());
    self::assertEquals('xx yy', $tag->getAttribute('class'));

    $tag->addClass('zz');
    self::assertEquals(['xx', 'yy', 'zz'], $tag->getClasses());
    self::assertEquals('xx yy zz', $tag->getAttribute('class'));
  }

  public function testNullContent()
  {
    self::assertEquals('<div></div>', (string)new TestHtmlElement());
  }

  public function testContent()
  {
    self::assertEquals('<p>0</p>', (string)(new TestHtmlElement('p'))->setContent(0));
    self::assertEquals('<p>0</p>', (string)(new TestHtmlElement('p'))->setContent('0'));

    self::assertEquals('<div>Hello</div>', (string)(new TestHtmlElement())->setContent('Hello'));
    self::assertEquals('<div>&amp;</div>', (string)(new TestHtmlElement())->setContent('&'));
    self::assertEquals('<div>&</div>', (string)(new TestHtmlElement())->setContent(new SafeHtml('&')));
  }

  public function testBooleanAttribute()
  {
    self::assertEquals(
      '<option selected></option>',
      (string)(new TestHtmlElement('option'))->setAttributes(['selected' => null])
    );
  }

  public function testTagJavascriptProtocolRejection()
  {
    $hrefs = [
      'javascript:alert(1)'                 => true,
      'JAVASCRIPT:alert(2)'                 => true,
      // NOTE: When interpreted as a URI, this is dropped because of leading
      // whitespace.
      '     javascript:alert(3)'            => [true, false],
      '/'                                   => false,
      '/path/to/stuff/'                     => false,
      ''                                    => false,
      'http://example.com/'                 => false,
      '#'                                   => false,
      'javascript://anything'               => true,
      // Chrome 33 and IE11, at a minimum, treat this as Javascript.
      "javascript\n:alert(4)"               => true,
      // Opera currently accepts a variety of unicode spaces. This test case
      // has a smattering of them.
      "\xE2\x80\x89javascript:"             => true,
      "javascript\xE2\x80\x89:"             => true,
      "\xE2\x80\x84javascript:"             => true,
      "javascript\xE2\x80\x84:"             => true,
      // Because we're aggressive, all of unicode should trigger detection
      // by default.
      "\xE2\x98\x83javascript:"             => true,
      "javascript\xE2\x98\x83:"             => true,
      "\xE2\x98\x83javascript\xE2\x98\x83:" => true,
      // We're aggressive about this, so we'll intentionally raise false
      // positives in these cases.
      'javascript~:alert(5)'                => true,
      '!!!javascript!!!!:alert(6)'          => true,
      // However, we should raise true negatives in these slightly more
      // reasonable cases.
      'javascript/:docs.html'               => false,
      'javascripts:x.png'                   => false,
      'COOLjavascript:page'                 => false,
      '/javascript:alert(1)'                => false,
    ];

    foreach([false, true] as $useUri)
    {
      foreach($hrefs as $href => $expect)
      {
        if(is_array($expect))
        {
          $expect = ($useUri ? $expect[1] : $expect[0]);
        }

        if($useUri)
        {
          $href = new Uri($href);
        }

        $caught = null;
        try
        {
          (new TestHtmlElement('a'))->setAttributes(['href' => $href])->produceSafeHTML();
        }
        catch(\Exception $ex)
        {
          $caught = $ex;
        }
        self::assertEquals(
          $expect,
          $caught instanceof \Exception,
          "Rejected href: {$href}"
        );
      }
    }
  }

  public function testFlagAttribute()
  {
    $ele = new TestHtmlElement('div');
    $ele->setAttribute('flag1', null);
    $ele->setAttribute('flag2', true);
    self::assertEquals('<div flag1 flag2></div>', $ele->produceSafeHTML()->getContent());
  }

  public function testAttributes()
  {
    $ele = new TestHtmlElement('div');
    $ele->setAttribute('flag1', null, true);
    self::assertEquals('<div></div>', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('div');
    $ele->setAttribute('flag2', '', false);
    self::assertEquals('<div flag2=""></div>', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('div');
    $ele->setAttribute('flag3', '', true);
    self::assertEquals('<div></div>', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('div');
    $ele->setAttribute('flag4', true);
    self::assertEquals('<div flag4></div>', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('div');
    $ele->setAttribute('flag5', 1234);
    self::assertEquals('<div flag5="1234"></div>', $ele->produceSafeHTML()->getContent());
  }

  public function testContentOnly()
  {
    $ele = new TestHtmlElement('span');
    $ele->setContent("Hello");
    self::assertEquals('<span>Hello</span>', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('');
    $ele->setContent("Hello");
    self::assertEquals('Hello', $ele->produceSafeHTML()->getContent());
  }

  public function testToStringException()
  {
    $tag = (new TestHtmlElement('a'))->setAttributes(['href' => 'javascript:alert(\'Hi\');']);
    self::assertStringContainsString('Attempting to render a tag with an', (string)$tag);
  }

  public function testEmpty()
  {
    $ele = new TestHtmlElement('');
    $ele->setContent(null);
    self::assertEquals('', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('');
    $ele->setContent('');
    self::assertEquals('', $ele->produceSafeHTML()->getContent());

    $ele = new TestHtmlElement('');
    $ele->setContent(0);
    self::assertEquals('0', $ele->produceSafeHTML()->getContent());
  }

  public function testExtending()
  {
    $ele = new TestExtendingElement();
    self::assertInstanceOf(TestExtendingElement::class, $ele);
  }
}
