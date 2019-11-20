<?php
namespace Packaged\Ui;

use Composer\Autoload\ClassLoader;
use function file_exists;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function realpath;
use function spl_autoload_functions;
use function substr;

trait TemplateLoaderTrait
{
  /**
   * @var ClassLoader
   */
  private static $globalClassLoader;
  protected $_templateFilePath;
  protected $_classLoader;

  protected function _disableClassLoader()
  {
    $this->_classLoader = false;
    return $this;
  }

  /**
   * Build the view response with the relevant template file
   *
   * @return string
   * @throws \Throwable
   */
  protected function _renderTemplate(): string
  {
    $templatePath = $this->_getTemplateFilePath();
    if(!$templatePath)
    {
      throw new \Exception("The template file '$templatePath' does not exist", 404);
    }

    ob_start();
    try
    {
      include $templatePath;
    }
    catch(\Throwable $e)
    {
      ob_end_clean();
      throw $e;
    }
    return ob_get_clean();
  }

  protected function _getTemplateFilePath()
  {
    if($this->_templateFilePath === null)
    {
      $filePath = null;
      $loader = $this->_getClassLoader();
      $useCl = $loader instanceof ClassLoader;
      foreach($this->_getTemplatedPhtmlClassList() as $class)
      {
        if($useCl)
        {
          $filePath = $loader->findFile($class);
        }
        $classPath = $useCl && $filePath ? $filePath : $this->_reflectedFilePath($class);
        foreach((array)$this->_classPathToTemplatePath($classPath) as $attempt)
        {
          if(file_exists($attempt))
          {
            $this->_templateFilePath = $attempt;
            return $this->_templateFilePath;
          }
        }
      }
    }

    return $this->_templateFilePath;
  }

  protected function _getClassLoader()
  {
    if($this->_classLoader === null)
    {
      if(self::$globalClassLoader !== null)
      {
        $this->_classLoader = self::$globalClassLoader;
      }
      else
      {
        //Initialise the classloader to false, to stop multiple calculations
        $this->_classLoader = false;

        //Look over autoloaders, to see if we have a class loader
        foreach(spl_autoload_functions() as list($loader))
        {
          if($loader instanceof ClassLoader)
          {
            $this->_setClassLoader($loader, true);
            break;
          }
        }
      }
    }
    return $this->_classLoader;
  }

  protected function _setClassLoader(ClassLoader $loader, bool $global = true)
  {
    $this->_classLoader = $loader;
    if($global)
    {
      self::$globalClassLoader = $loader;
    }
    return $this;
  }

  protected function _getTemplatedPhtmlClass()
  {
    return static::class;
  }

  protected function _getTemplatedPhtmlClassList()
  {
    return [$this->_getTemplatedPhtmlClass()];
  }

  protected function _attemptTemplateExtensions()
  {
    return ['phtml'];
  }

  protected function _classPathToTemplatePath($classPath)
  {
    $return = [];
    foreach($this->_attemptTemplateExtensions() as $ext)
    {
      $return[] = realpath(substr($classPath, 0, -3) . $ext);
    }
    return $return;
  }

  private function _reflectedFilePath($class)
  {
    return (new \ReflectionClass($class))->getFileName();
  }
}
