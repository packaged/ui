<?php
namespace Packaged\Ui\Html;

use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Renderable;
use function error_log;
use function preg_match;
use function preg_replace;

abstract class HtmlElement implements Renderable, ISafeHtmlProducer
{
  use HtmlAttributesTrait;

  protected $_tag;

  // For tags which can't self-close, treat null as the empty string -- for
  // example, always render `<div></div>`, never `<div />`.
  protected static $_selfClosing = [
    'area'    => true,
    'base'    => true,
    'br'      => true,
    'col'     => true,
    'command' => true,
    'embed'   => true,
    'frame'   => true,
    'hr'      => true,
    'img'     => true,
    'input'   => true,
    'keygen'  => true,
    'link'    => true,
    'meta'    => true,
    'param'   => true,
    'source'  => true,
    'track'   => true,
    'wbr'     => true,
  ];

  public function __toString()
  {
    try
    {
      return $this->render();
    }
    catch(\Exception $e)
    {
      error_log(
        ($e->getCode() > 0 ? '[' . $e->getCode() . '] ' : '')
        . $e->getMessage()
        . ' (' . $e->getFile() . ':' . $e->getLine() . ')'
      );
      return $e->getMessage();
    }
  }

  public function render(): string
  {
    return (string)$this->produceSafeHTML();
  }

  /**
   * @return SafeHtml
   * @throws \Exception
   */
  public function produceSafeHTML(): SafeHtml
  {
    $ele = $this->_prepareForProduce();
    $tag = $ele->getTag();

    $attrString = $this->_generateAttributesString($ele);
    $content = $ele->_getContentForRender();
    if(empty($content))
    {
      if(isset(self::$_selfClosing[$tag]))
      {
        return new SafeHtml('<' . $tag . $attrString . ' />');
      }
      $content = '';
    }
    else
    {
      $content = SafeHtml::escape($content, '');
    }

    return new SafeHtml($tag ? ('<' . $tag . $attrString . '>' . $content . '</' . $tag . '>') : $content);
  }

  protected function _generateAttributesString(HtmlElement $ele)
  {
    // If the `href` attribute is present:
    //   - make sure it is not a "javascript:" URI. We never permit these.
    //   - if the tag is an `<a>` and the link is to some foreign resource,
    //     add `rel="nofollow"` by default.
    if(!empty($ele->_attributes['href']))
    {

      // This might be a URI object, so cast it to a string.
      $href = (string)$ele->_attributes['href'];

      if(isset($href[0]))
      {
        $isAnchorHref = ($href[0] == '#');

        // Is this a link to a resource on the same domain? The second part of
        // this excludes "///evil.com/" protocol-relative hrefs.
        $isDomainHref = ($href[0] == '/') && (!isset($href[1]) || $href[1] != '/');

        // Block 'javascript:' hrefs at the tag level: no well-designed
        // application should ever use them, and they are a potent attack vector.

        // This function is deep in the core and performance sensitive, so we're
        // doing a cheap version of this test first to avoid calling preg_match()
        // on URIs which begin with '/' or `#`.
        if(!$isAnchorHref && !$isDomainHref)
        {
          // Chrome 33 and IE 11 both interpret "javascript\n:" as a Javascript
          // URI, and all browsers interpret "  javascript:" as a Javascript URI,
          // so be aggressive about looking for "javascript:" in the initial
          // section of the string.

          $normalizedHref = preg_replace('([^a-z0-9/:]+)i', '', $href);
          if(preg_match('/^javascript:/i', $normalizedHref))
          {
            throw new \Exception(
              "Attempting to render a tag with an 'href' attribute that begins with 'javascript:'. " .
              "This is either a serious security concern or a serious architecture concern. Seek urgent remedy."
            );
          }
        }
      }
    }

    $attrString = '';
    foreach($ele->_attributes as $k => $v)
    {
      if($v === null || $v === true)
      {
        $attrString .= ' ' . $k;
      }
      else if(is_string($v))
      {
        $attrString .= ' ' . $k . '="' . \htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"';
      }
      else if(is_numeric($v))
      {
        $attrString .= ' ' . $k . '="' . $v . '"';
      }
      else
      {
        $attrString .= ' ' . $k . '="' . SafeHtml::escape($v) . '"';
      }
    }
    return $attrString;
  }

  protected function _prepareForProduce(): HtmlElement
  {
    //Make any changes to the tag just before generating html output
    return $this;
  }

  /**
   * @return string
   */
  public function getTag()
  {
    return $this->_tag;
  }

  protected function _getContentForRender()
  {
    return null;
  }

}
