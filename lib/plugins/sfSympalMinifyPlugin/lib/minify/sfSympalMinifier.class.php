<?php

/**
 * Class responsible for minifying the stylesheets and javascripts for the given
 * sfWebResponse and sfRequest instances
 *
 * Pieces of this class are taken from Nicolas Perriault's npAssetsOptimizerPlugin
 * @link http://svn.symfony-project.com/plugins/npAssetsOptimizerPlugin/trunk/lib/optimizer/base/npOptimizerBase.class.php
 * 
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalMinifier
{
  private
    $_response,
    $_request;
  
  protected
    $_stylesheetDriver,
    $_javascriptDriver;
  
  protected
    $_baseAssetsDir;

  public function __construct(sfWebResponse $response, sfWebRequest $request, sfSympalMinifyCssDriver $stylesheetDriver, sfSympalMinifyJsDriver $javascriptDriver)
  {
    $this->_response = $response;
    $this->_request = $request;
    
    $this->_stylesheetDriver = $stylesheetDriver;
    $this->_javascriptDriver = $javascriptDriver;
  }

  /**
   * Start the minification process
   * 
   * This performs the whole process
   *  - Files to be minified are combined into a string and removed from the response
   *  - The combined string is minified, saved to a file, and added to the response
   *
   * @return void
   */
  public function minify()
  {
    $this->_minifyFiles($this->_response->getStylesheets(), 'css');
    $this->_minifyFiles($this->_response->getJavascripts(), 'js');
  }

  /**
   * Check if a file is minifiable
   *
   * @param string $file
   * @return boolean
   */
  private function _isMinifiable($file)
  {
    $exclude = sfSympalConfig::get('minifier', 'exclude', array());

    return !in_array($file, $exclude);
  }

  /**
   * Minify an array of js/css files
   *
   * @param array $files The array of files to minify
   * @param string $type The type of files. Either js or css
   * @return void
   */
  private function _minifyFiles(array $files, $type)
  {
    if ($files)
    {
      $typeName = $type == 'js' ? 'Javascript' : 'Stylesheet';
      $filename = md5(serialize($files)).'.'.$type;
      $webPath = '/cache/'.$type.'/'.$filename;
      $cachedPath = sfConfig::get('sf_web_dir').$webPath;
      if (!file_exists($cachedPath))
      {
        $minified = '';
        $minifyMethod = '_minify'.$typeName;
        foreach ($files as $file => $options)
        {
          if (!$this->_isMinifiable($file))
          {
            continue;
          }
          $path = $this->computeAssetFilepath($file, $type);
          if (file_exists($path))
          {
            $minified .= "\n\n".$this->$minifyMethod(file_get_contents($path), $this->_request->getUriPrefix().$this->_request->getRelativeUrlRoot().$file);
          }
        }

        if (!is_dir($dir = dirname($cachedPath)))
        {
          mkdir($dir, 0777, true);
        }
        if (!is_dir($dir = dirname($cachedPath)))
        {
          mkdir($dir, 0777, true);
        }
        file_put_contents($cachedPath, $minified);
        chmod($cachedPath, 0777);
      }
    
      foreach ($this->_response->{'get'.$typeName.'s'}() as $file => $options)
      {
        if (!$this->_isMinifiable($file))
        {
          continue;
        }
        $this->_response->{'remove'.$typeName}($file);
      }
      $this->_response->{'add'.$typeName}($webPath);
    }
  }

  /**
   * Minify some javascript code
   *
   * @param string $javascript 
   * @param string $path 
   * @return string $javascript
   */
  private function _minifyJavascript($javascript, $path)
  {
    return $this->_javascriptDriver->process($javascript);
  }

  /**
   * Minify some css
   *
   * @param string $stylesheet 
   * @param string $path 
   * @return string $stylesheet
   */
  private function _minifyStylesheet($stylesheet, $path)
  {
    $stylesheet = $this->_fixCssPaths($stylesheet, $path);

    return $this->_stylesheetDriver->process($stylesheet);
  }

  /**
   * Fix the paths to urls in the css since the css file will be in a different location
   * we need the urls to be absolute and not relative. This function will adjust the 
   * given css and fix the urls.
   *
   * @param string $content
   * @param string $path 
   * @return string $content
   */
  private function _fixCssPaths($content, $path)
  {
    if (preg_match_all("/url\(\s?[\'|\"]?(.+)[\'|\"]?\s?\)/ix", $content, $urlMatches))
    {
      $urlMatches = array_unique( $urlMatches[1] );
      $cssPathArray = explode('/', $path);
      
      // pop the css file name
      array_pop( $cssPathArray );
      $cssPathCount   = count( $cssPathArray );

      foreach( $urlMatches as $match )
      {
        $match = str_replace( array('"', "'"), '', $match );
        // replace path if it is relative
        if ( $match[0] !== '/' && strpos( $match, 'http:' ) === false )
        {
          $relativeCount = substr_count( $match, '../' );
          $cssPathSlice = $relativeCount === 0 ? $cssPathArray : array_slice($cssPathArray  , 0, $cssPathCount - $relativeCount);
          $newMatchPath = implode('/', $cssPathSlice) . '/' . str_replace('../', '', $match);
          $content = str_replace($match, $newMatchPath, $content);
        }
      }
    }

    return $content;
  }

  /**
   * Retrieves the absolute path to an asset from its symfony name, its associated extension
   * and an optional web path. The asset file must reside on the local filesytem, foreign
   * ones will be ignored (this might change with a dedicated option in the future).
   *
   * @param  string  $asset      The symfony asset file name (eg. "main", "main.js", "/css/main.css")
   * @param  string  $extension  The file extension (eg. "css", "js")
   * @param  string  $webPath    An optional web path for reconstructing the real 
   *                             path (always starting with the "/" character)
   *
   * @author Nicolas Perriault
   * @return string|null
   */
  public function computeAssetFilepath($asset, $extension, $webPath = '')
  {
    if (preg_match('/^http[s]?:/i', $asset))
    {
      return null;
    }
    
    $webPath = !preg_match('#^/#', $asset) ? sprintf('%s/', $webPath) : '';
    
    $fileName = preg_match(sprintf('/\.%s$/i', $extension), $asset) ? $asset : sprintf('%s.%s', $asset, $extension);
    
    return sprintf('%s%s%s', sfConfig::get('sf_web_dir'), $webPath, $fileName);
  }
}