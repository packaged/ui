<?php
namespace Packaged\Ui;

use Composer\Autoload\ClassLoader;

trait TemplateLoaderTrait
{
  protected $_templateFilePath;
  protected $_classLoader;
  /**
   * @var ClassLoader
   */
  private static $globalClassLoader;

  protected function _setClassLoader(ClassLoader $loader, bool $global = true)
  {
    $this->_classLoader = $loader;
    if($global)
    {
      self::$globalClassLoader = $loader;
    }
    return $this;
  }

  protected function _disableClassLoader()
  {
    $this->_classLoader = false;
    return $this;
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

  private function _reflectedFilePath()
  {
    return (new \ReflectionClass(static::class))->getFileName();
  }

  protected function _classPathToTemplatePath($classPath)
  {
    return realpath(substr($classPath, 0, -3) . 'phtml');
  }

  protected function _getTemplateFilePath()
  {
    if($this->_templateFilePath === null)
    {
      $loader = $this->_getClassLoader();
      if($loader instanceof ClassLoader)
      {
        //Use the classLoader to find the path for our file
        $filePath = $loader->findFile(static::class);
        $this->_templateFilePath = $this->_classPathToTemplatePath($filePath ?: $this->_reflectedFilePath());
      }
      else
      {
        //If we dont hace a classLoader, use reflection to get the class path
        $this->_templateFilePath = $this->_classPathToTemplatePath($this->_reflectedFilePath());
      }
    }

    return $this->_templateFilePath;
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
}
