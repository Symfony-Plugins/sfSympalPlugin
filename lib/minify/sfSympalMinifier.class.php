<?php

/**
 * Class responsible for minifying the stylesheets and javascripts for the given
 * sfWebResponse and sfRequest instances
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalMinifier
{
  private
    $_response,
    $_request;

  public function __construct(sfWebResponse $response, sfWebRequest $request)
  {
    $this->_response = $response;
    $this->_request = $request;
  }

  /**
   * Start the minification process
   *
   * @return void
   */
  public function minify()
  {
    $this->_minifyFiles($this->_response->getJavascripts(), 'js');
    $this->_minifyFiles($this->_response->getStylesheets(), 'css');
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
        foreach ($files as $file => $options)
        {
          if (!$this->_isMinifiable($file))
          {
            continue;
          }
          $path = sfConfig::get('sf_web_dir').'/'.$file;
          if (file_exists($path))
          {
            $minified .= "\n\n".$this->{'_minify'.$typeName}(file_get_contents($path), $this->_request->getUriPrefix().$this->_request->getRelativeUrlRoot().$file);
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
   * @todo Actually make this minify the JS :)
   * @param string $javascript 
   * @param string $path 
   * @return string $javascript
   */
  private function _minifyJavascript($javascript, $path)
  {
    return $javascript;
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
    return str_replace("\n", null,
      preg_replace(array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i"), array(';', '{', ':#', ',', ":\'", ":$1"),
        preg_replace(array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/"), array(null, ' ', "}\n"),
          str_replace("\r\n", "\n", trim($stylesheet))
        )
      )
    );
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
}