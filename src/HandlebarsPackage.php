<?php //-->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\Handlebars;

use Cradle\Handlebars\HandlebarsHandler;

/**
 * Handlebars Package
 *
 * @vendor   Cradle
 * @package  Package
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class HandlebarsPackage extends HandlebarsHandler
{
  /**
   * @var string path
   */
  protected $path = null;

  /**
   * Returns folder path
   *
   * @return ?string
   */
  public function getTemplateFolder(): ?string
  {
    return $this->path;
  }

  /**
   * Registers a helper from a file
   *
   * @param *string $name
   * @param *string $helper
   *
   * @return HandlebarsPackage
   */
  public function registerHelperFromFile(
    string $name,
    string $helper
  ): HandlebarsPackage
  {
    if (!file_exists($helper)) {
      throw HandlebarsException::forFileNotFound($helper);
    }

    return $this->registerHelper($name, include $helper);
  }

  /**
   * Registers a partial from a file
   *
   * @param *string $name
   * @param *string $partial
   *
   * @return HandlebarsPackage
   */
  public function registerPartialFromFile(
    string $name,
    string $partial
  ): HandlebarsPackage
  {
    if (!file_exists($partial)) {
      throw HandlebarsException::forFileNotFound($partial);
    }

    return $this->registerPartial($name, file_get_contents($partial));
  }

  /**
   * Registers a partial from a folder
   *
   * @param *string $name
   * @param string  $extension
   *
   * @return HandlebarsPackage
   */
  public function registerPartialFromFolder(
    string $name,
    string $extension = 'html'
  ): HandlebarsPackage
  {
    if (!$this->path || !is_dir($this->path)) {
      throw HandlebarsException::forFolderNotSet($partial);
    }

    // THE GOAL:
    //eg. head -> _head
    //eg. post_row -> post/_row
    //eg. search_post_row -> search/post/_row

    // STEP 1:
    //eg. head -> head
    //eg. post_row -> post/row
    //eg. search_post_row -> search/post/row
    $path = str_replace('_', '/');
    $last = strrpos($path, '/');

    // STEP 2:
    if ($last !== false) {
      //eg. head -> head
      //eg. post_row -> post/_row
      //eg. search_post_row -> search/post/_row
      $path = substr_replace($path, '/_', $last, 1);
    }

    // STEP 3:
    //eg. head -> _head
    if (strpos($path, '_') === false) {
      $path = '_' . $path;
    }

    $partial = sprintf('%s/%s.%s', $this->path, $path, $extension);

    return $this->registerPartialFile($name, $partial);
  }

  /**
   * Quickly renders a template
   *
   * @param *string $template
   * @param ?array  $data
   *
   * @return string
   */
  public function render(string $template, array $data = []): string
  {
    return $this->compile($template)($data);
  }

  /**
   * Quickly renders a template from a file
   *
   * @param *string $file
   * @param ?array  $data
   *
   * @return string
   */
  public function renderFromFile(string $file, array $data = []): string
  {
    if (!file_exists($file)) {
      throw HandlebarsException::forFileNotFound($file);
    }

    return $this->render(file_get_contents($file), $data);
  }

  /**
   * Quickly renders a template from a folder
   *
   * @param *string $file
   * @param ?array  $data
   * @param string  $extension
   *
   * @return string
   */
  public function renderFromFolder(
    string $path,
    array $data = [],
    string $extension = 'html'
  ): string
  {
    if (!$this->path || !is_dir($this->path)) {
      throw HandlebarsException::forFolderNotSet($partial);
    }

    //eg. product/search/detail
    $file = sprintf('%s/%s.%s', $this->path, $path, $extension);
    return $this->renderFile($file);
  }

  /**
   * Sets template folder path
   *
   * @param *string $path
   *
   * @return HandlebarsPackage
   */
  public function setTemplateFolder(string $path): HandlebarsPackage
  {
    if (!is_dir($path)) {
      throw HandlebarsException::forFolderNotFound($path);
    }

    $this->path = $path;
    return $this;
  }
}
