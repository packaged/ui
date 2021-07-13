<?php

namespace Packaged\Ui\Tests\Supporting\Html;

use Packaged\Ui\Html\Uri;

class TestExtendingUri extends Uri
{
  public function __construct($uri) { parent::__construct($uri); }

  public function __toString()
  {
    return parent::__toString();
  }

  public function getFragment()
  {
    return parent::getFragment();
  }

  public function setFragment($fragment)
  {
    return parent::setFragment($fragment);
  }

  public function getPath()
  {
    return parent::getPath();
  }

  public function setPath($path)
  {
    return parent::setPath($path);
  }

  public function setQueryParam($key, $value)
  {
    return parent::setQueryParam($key, $value);
  }

  public function setQueryParams(array $params)
  {
    return parent::setQueryParams($params);
  }

  public function getQueryParams()
  {
    return parent::getQueryParams();
  }

  public function getProtocol()
  {
    return parent::getProtocol();
  }

  public function setProtocol($protocol)
  {
    return parent::setProtocol($protocol);
  }

  public function getDomain()
  {
    return parent::getDomain();
  }

  public function setDomain($domain)
  {
    return parent::setDomain($domain);
  }

  public function getPort()
  {
    return parent::getPort();
  }

  public function setPort($port)
  {
    return parent::setPort($port);
  }

  public function appendPath($path)
  {
    return parent::appendPath($path);
  }

  public function getUser()
  {
    return parent::getUser();
  }

  public function setUser($user)
  {
    return parent::setUser($user);
  }

  public function getPass()
  {
    return parent::getPass();
  }

  public function setPass($pass)
  {
    return parent::setPass($pass);
  }
}
