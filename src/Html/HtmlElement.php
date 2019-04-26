<?php
namespace Packaged\Ui\Html;

use Packaged\Helpers\Arrays;
use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Renderable;
use function array_key_exists;
use function error_log;
use function is_scalar;
use function preg_match;
use function preg_replace;
use const ENT_QUOTES;

abstract class HtmlElement implements Renderable, ISafeHtmlProducer
{
  protected $_tag;
  protected $_attributes = [];

  /**
   * @param string $id
   *
   * @return $this
   */
  public function setId($id)
  {
    return $this->setOrRemoveAttribute('id', $id);
  }

  /**
   * @param string $key
   * @param string $value
   *
   * @return $this
   */
  public function setOrRemoveAttribute(string $key, $value)
  {
    if($value === null || $value === '')
    {
      $this->removeAttribute($key);
    }
    else
    {
      $this->setAttribute($key, $value);
    }
    return $this;
  }

  /**
   * @param string $key
   *
   * @return $this
   */
  public function removeAttribute(string $key)
  {
    unset($this->_attributes[$key]);
    return $this;
  }

  /**
   * @param string $key
   * @param string $value
   *
   * @param bool   $ignoreEmpty Do not set attributes where the value is empty string or null
   *
   * @return $this
   */
  public function setAttribute(string $key, $value, $ignoreEmpty = false)
  {
    if($ignoreEmpty && ($value === '' || $value === null))
    {
      return $this;
    }

    $this->_attributes[$key] = $value;
    return $this;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->getAttribute('id');
  }

  /**
   * @param string $key
   * @param string $default
   *
   * @return string
   */
  public function getAttribute(string $key, $default = null)
  {
    return Arrays::value($this->_attributes, $key, $default);
  }

  /**
   * Array of attributes for the tag
   *
   * @param array $attributes
   * @param bool  $overwriteIfExists
   *
   * @return $this
   */
  public function addAttributes(array $attributes, bool $overwriteIfExists = false)
  {
    foreach($attributes as $k => $v)
    {
      if($overwriteIfExists || !array_key_exists($k, $this->_attributes))
      {
        $this->setOrRemoveAttribute($k, $v);
      }
    }
    return $this;
  }

  /**
   * @return array
   */
  public function getAttributes()
  {
    return $this->_attributes;
  }

  /**
   * Array of attributes for the tag
   *
   * @param array $attributes
   *
   * @return $this
   */
  public function setAttributes(array $attributes)
  {
    $this->_attributes = $attributes;
    return $this;
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function hasAttribute(string $key)
  {
    return array_key_exists($key, $this->_attributes);
  }

  /**
   * @param string ...$class
   *
   * @return $this
   */
  public function addClass(...$class)
  {
    foreach($class as $c)
    {
      $this->_addClass($c);
    }

    return $this;
  }

  private function _addClass(string $class)
  {
    if(!isset($this->_attributes['class']))
    {
      $this->_attributes['class'] = [];
    }
    $this->_attributes['class'][$class] = $class;
    return $this;
  }

  /**
   * @param string $class
   *
   * @return bool
   */
  public function hasClass(string $class)
  {
    return isset($this->_attributes['class'][$class]);
  }

  /**
   * @param string ...$class
   *
   * @return $this
   */
  public function removeClass(...$class)
  {
    foreach($class as $c)
    {
      $this->_removeClass($c);
    }
    return $this;
  }

  private function _removeClass(string $class)
  {
    unset($this->_attributes['class'][$class]);
    return $this;
  }

  /**
   * Retrieve all classes set on the element
   *
   * @return string[]
   */
  public function getClasses()
  {
    return (array)Arrays::value($this->_attributes, 'class', []);
  }

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
        // on URIs which begin with '/' or `#`. These cover essentially all URIs
        // in Phabricator.
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
              "Attempting to render a tag with an 'href' attribute that " .
              "begins with 'javascript:'. This is either a serious security " .
              "concern or a serious architecture concern. Seek urgent " .
              "remedy."
            );
          }
        }
      }
    }

    // For tags which can't self-close, treat null as the empty string -- for
    // example, always render `<div></div>`, never `<div />`.
    $selfClosingTags = [
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

    $attrString = '';
    foreach($ele->_attributes as $k => $v)
    {
      if($v === null || $v === true)
      {
        $attrString .= ' ' . $k;
      }
      else if(is_scalar($v))
      {
        $attrString .= ' ' . $k . '="' . \htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"';
      }
      else
      {
        $attrString .= ' ' . $k . '="' . SafeHtml::escape($v) . '"';
      }
    }

    $content = $ele->_getContentForRender();
    if(empty($content))
    {
      if(isset($selfClosingTags[$tag]))
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
