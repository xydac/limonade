<?php
                                                                              
# ============================================================================ #
#                                                                              # 
#    L I M O N A D E                                                           # 
#                                                                              # 
#    a PHP micro framework                                                     #
#                                                                              # 
#   -----------------------------------------------------------------------    # 
#    For more informations: <http://github/sofadesign/limonade>                #
#                                                                              #
#                                                                              #
#   -----------------------------------------------------------------------    #                                                                              #
#    Copyright (c) 2009 Fabrice Luraine                                        #
#                                                                              #
#    Permission is hereby granted, free of charge, to any person               #
#    obtaining a copy of this software and associated documentation            #
#    files (the "Software"), to deal in the Software without                   #
#    restriction, including without limitation the rights to use,              #
#    copy, modify, merge, publish, distribute, sublicense, and/or sell         #
#    copies of the Software, and to permit persons to whom the                 #
#    Software is furnished to do so, subject to the following                  #
#    conditions:                                                               #
#                                                                              #
#    The above copyright notice and this permission notice shall be            #
#    included in all copies or substantial portions of the Software.           #
#                                                                              #
#    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,           #
#    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES           #
#    OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND                  #
#    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT               #
#    HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,              #
#    WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING              #
#    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR             #
#    OTHER DEALINGS IN THE SOFTWARE.                                           #
# ============================================================================ # 








# ============================================================================ #
#    0. PREPARE                                                                #
# ============================================================================ #


## CONSTANTS __________________________________________________________________
define('LIMONADE', '0.3');
define('PHP_ERRORS', 'PHP crispy errors');
define('HTTP_STATUS_CODES', 'fancy HTTP reponse status');
define('NOT_FOUND', 404);
define('SERVER_ERROR', 500);
define('ENV_PRODUCTION', 10);
define('ENV_DEVELOPMENT', 100);
define('X-SENDFILE', 10);
define('X-LIGHTTPD-SEND-FILE', 20);


## SETTING BASIC SECURITY _____________________________________________________

# A. Unsets all global variables set from a superglobal array
function unregister_globals()
{
  $args = func_get_args();
  foreach($args as $k => $v)
    if(array_key_exists($k, $GLOBALS)) unset($GLOBALS[$key]);
}

if(ini_get('register_globals'))
{
  unregister_globals( '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', 
                      '_ENV', '_FILES');
  ini_set('register_globals', 0);
}

# B. removing magic quotes
function remove_magic_quotes($array)
{
  foreach ($array as $k => $v)
    $array[$k] = is_array($v) ? remove_magic_quotes($v) : stripslashes($v);
  return $array;
}

if (get_magic_quotes_gpc())
{
  $_GET    = remove_magic_quotes($_GET);
  $_POST   = remove_magic_quotes($_POST);
  $_COOKIE = remove_magic_quotes($_COOKIE);
  ini_set('magic_quotes_gpc', 0);
}

if(get_magic_quotes_runtime()) set_magic_quotes_runtime(false);

# C. Disable error display
#    by default, no error reporting; it will be switched on later in run().
#    ini_set('display_errors', 1); must be called explicitly in app file
#    if you want to show errors before running app
ini_set('display_errors', 0);





                                     # # #




# ============================================================================ #
#    1. BASE                                                                   #
# ============================================================================ #

## ABSTRACTS ___________________________________________________________________

# function configure(){}
# function before(){}
# function after(){}
# function not_found(){}
# function route_missing(){}


## MAIN PUBLIC FUNCTIONS _______________________________________________________

/**
 * Set and returns options values
 * 
 * If multiple values are provided, set $name option with an array of those values.
 * If only ther is only one value, set $name option with the provided $values
 *
 * @param string $name 
 * @param mixed  $values,... 
 * @return mixed option value for $name if $name argument is provided, else return all options
 */
function option($name = null, $values = null)
{
   static $options = array();
   $args = func_get_args();
   $name = array_shift($args);
   if(is_null($name)) return $options;
   if(!empty($args))
   {
     $options[$name] = count($args) > 1 ? $args : $args[0];
   }
   if(array_key_exists($name, $options)) return $options[$name];
   return;
}

/**
 * Set and returns params
 * 
 * Depending on provided arguments:
 * 
 *  * Reset params if first argument is null
 * 
 *  * If first argument is an array, merge it with current params
 * 
 *  * If there is a second argument $value, set param $name (first argument) with $value
 * <code>
 *  params('name', 'Doe') // set 'name' => 'Doe'
 * </code>
 *  * If there is more than 2 arguments, set param $name (first argument) value with
 *    an array of next arguments
 * <code>
 *  params('months', 'jan', 'feb', 'mar') // set 'month' => array('months', 'jan', 'feb', 'mar')
 * </code>
 * 
 * @param mixed $name_or_array_or_null could be null || array of params || name of a param (optional)
 * @param mixed $value,... for the $name param (optional)
 * @return mixed all params, or one if a first argument $name is provided
 */
function params($name_or_array_or_null = null, $value = null)
{
  static $params = array();
  $args = func_get_args();

  if(func_num_args() > 0)
  {
    $name = array_shift($args);
    if(is_null($name))
    {
      # Reset params
      $params = array();
      return $params;
    }
    if(is_array($name))
    {
      $params = array_merge($params, $name);
      return $params;
    }
    $nargs = count($args);
    if($nargs > 0)
    {
      $value = $nargs > 1 ? $args : $args[0];
      $params[$name] = $value;
    }
    return $params[$name];
  }

  return $params;
}

/**
 * Set and returns template variables
 * 
 * If multiple values are provided, set $name variable with an array of those values.
 * If only ther is only one value, set $name variable with the provided $values
 *
 * @param string $name 
 * @param mixed  $values,... 
 * @return mixed variable value for $name if $name argument is provided, else return all variables
 */
function set($name = null, $values = null)
{
  static $vars = array();
  $args = func_get_args();
  $name = array_shift($args);
  if(is_null($name)) return $vars;
  if(!empty($args))
  {
    $vars[$name] = count($args) > 1 ? $args : $args[0];
  }
  if(array_key_exists($name, $vars)) return $vars[$name];
  return $vars;
}

/**
 * Sets a template variable with a value or a default value if value is empty
 *
 * @param string $name 
 * @param string $value 
 * @param string $default 
 * @return void
 */
function set_or_default($name, $value, $default)
{
  return set($name, value_or_default($value, $default));
}

function run($env = null)
{
  if(is_null($env)) $env = env();
   
  # 0. Set default configuration
  $root_dir = dirname(app_file());
  option('root_dir',        $root_dir);
  option('limonade_dir',    dirname(dirname(__FILE__)).'/');
  option('public_dir',      $root_dir.'/public/');
  option('views_dir',       $root_dir.'/views/');
  option('controllers_dir', $root_dir.'/controllers/');
  option('lib_dir',         $root_dir.'/lib/');
  option('env',             ENV_PRODUCTION);
  option('debug',           true);
  option('encoding',        'utf-8');
  option('x-sendfile',      0); // 0: disabled, 
                                // X-SENDFILE: for Apache and Lighttpd v. >= 1.5,
                                // X-LIGHTTPD-SEND-FILE: for Apache and Lighttpd v. < 1.5
  
  # 1. Set error handling
  ini_set('display_errors', 1);
  set_error_handler('error_handler', E_ALL ^ E_NOTICE);
  
  # 2. Loading libs
  require_once_dir(option('lib_dir'));
  
  # 3. Set user configuration
  call_if_exists('configure');
  
  # 4. Set some default methods  
  if(!function_exists('not_found'))
  {
    function not_found($msg="")
    {
      option('views_dir', option('limonade_dir').'limonade/views/');
      $msg = h($msg);
      return html("<h1>Page not found:</h1><p>{$msg}</p>", "default_layout.php");
    }
  }
  if(!function_exists('after'))
  {
    function after($output)
    {
      return $output;
    }
  }
  if(!function_exists('route_missing'))
  {
    function route_missing($request_method, $request_uri)
    {
      halt(NOT_FOUND, "($request_method) $request_uri");
    }
  }
  
  # 5. Check request
  if($rm = request_method())
  {
    # 5.1 Check matching route
    if($route = route_find($rm, request_uri()))
    {
      params($route['params']);
      
      # 5.2 Load controllers dir
      require_once_dir(option('controllers_dir'));
      
      if(function_exists($route['function']))
      {
        # 5.3 Call before function
        call_if_exists('before');
        
        # 5.4 Call matching controller function and output result
        if($output = call_user_func($route['function']))
        {
          if(option('debug') && option('env') > ENV_PRODUCTION)
          {
            $notices = error_notice();
            if(!empty($notices))
            {
              foreach($notices as $notice) echo $notice;
              echo '<hr>';
            }
          }
          echo after($output);
        }
        exit;
      }
      else halt(SERVER_ERROR, "Routing error: undefined function '{$route['function']}'", $route);      
    }
    else route_missing($rm, request_uri());
    
  }
  else halt(SERVER_ERROR, "Unknown request method <code>$rm</code>");
  
}

function session()
{
  # TODO a session helper for setting and returning session data
}


/**
 * Returns limonade environment variables:
 *
 * 'SERVER', 'FILES', 'REQUEST', 'SESSION', 'ENV', 'COOKIE', 
 * 'GET', 'POST', 'PUT', 'DELETE'
 * 
 * If a null argument is passed, reset and rebuild environment
 *
 * @param null @reset reset and rebuild environment
 * @return array
 */
function env($reset = null)
{
  static $env = array();
  if(func_num_args() > 0)
  {
    $args = func_get_args();
    if(is_null($args[0])) $env = array();
  }
  
  if(empty($env))
  {
    $glo_names = array('SERVER', 'FILES', 'REQUEST', 'SESSION', 'ENV', 'COOKIE');
      
    $vars = array_merge($glo_names, request_methods());
    foreach($vars as $var)
    {
      $varname = "_$var";
      if(!array_key_exists("$varname", $GLOBALS)) $GLOBALS[$varname] = array();
      $env[$var] =& $GLOBALS[$varname];
    }
    
    $method = request_method($env);
    if($method == 'PUT' || $method == 'DELETE')
    {
      $varname = "_$method";
      if(array_key_exists('_method', $_POST) && $_POST['_method'] == $method)
      {
        foreach($_POST as $k => $v)
        {
          if($k == "_method") continue;
          $GLOBALS[$varname][$k] = $v;
        }
      }
      else
      {
        parse_str(file_get_contents('php://input'), $GLOBALS[$varname]);
      }
    }
  }
  return $env;
}

/**
 * Returns application root file path
 *
 * @return string
 */
function app_file()
{
  static $file;
  if(empty($file))
  {
    $stacktrace = array_pop(debug_backtrace());
    $file = $stacktrace['file'];
  }
  return $file;
}




                                     # # #




# ============================================================================ #
#    2. ERROR                                                                  #
# ============================================================================ #

/**
 * Associate a function with error code(s) and return all associations
 *
 * @param string $errno 
 * @param string $function 
 * @return void
 */
function error($errno = null, $function = null)
{
  static $errors = array();
  if(func_num_args() > 0)
  {
    $errors[] = array('errno'=>$errno, 'function'=> $function);
  }
  return $errors;
}

/**
 * Raise an error, passing a given error number and an optional message,
 * then exit.
 * Error number should be a HTTP status code or a php user error (E_USER...)
 * $errno and $msg arguments can be passsed in any order
 * If no arguments are passed, default $errno is SERVER_ERROR (500)
 *
 * @param integer,string $errno Error number or message string
 * @param string,string $msg Message string or error number
 * @param mixed $debug_args extra data provided for debugging
 * @return void
 */
function halt($errno = SERVER_ERROR, $msg = '', $debug_args = null)
{
  $args = func_get_args();
  $error = array_shift($args);

  # switch $errno and $msg args
  if(is_string($errno))
  {
   $msg = $errno;
   $oldmsg = array_shift($args);
   $errno = empty($oldmsg) ? SERVER_ERROR : $oldmsg;
  }
  else if(!empty($args)) $msg = array_shift($args);

  if(empty($msg) && $errno == NOT_FOUND) $msg = request_uri();
  if(empty($msg)) $msg = "";
  if(!empty($args)) $debug_args = $args;
  set('_lim_err_debug_args', $debug_args);
  if(http_response_status_is_valid($errno))
  {
     $back_trace = debug_backtrace();
     while($trace = array_shift($back_trace))
     {
       if($trace['function'] == 'halt')
       {
         $errfile = $trace['file'];
         $errline = $trace['line'];
         break;
       }
     }
     if(!error_call_matching_handler($errno, $msg, $errfile, $errline))
     {
       status($errno);
       switch($errno)
       {
         case NOT_FOUND:
         $o = not_found($msg);
         break;
       
         default:
         $o = server_error($errno, $msg);
         break;
       }
       echo $o;
       exit;  
     }
     
  }
  else trigger_error($msg, $errno);
}

/**
 * Internal error handler dispatcher
 *
 * @param integer $errno 
 * @param string $errstr 
 * @param string $errfile 
 * @param string $errline 
 * @return void
 */
function error_handler($errno, $errstr, $errfile, $errline)
{
  $back_trace = debug_backtrace();
  while($trace = array_shift($back_trace))
  {
    if($trace['function'] == 'halt')
    {
      $errfile = $trace['file'];
      $errline = $trace['line'];
      break;
    }
  }
  
  if(!error_call_matching_handler($errno, $errstr, $errfile, $errline))
  {
    error_default_handler($errno, $errstr, $errfile, $errline);  
  }
}

/**
 * Find and call matching error handler and exit
 * Return false if no match found
 *
 * @param integer $errno 
 * @param string $errstr 
 * @param string $errfile 
 * @param string $errline 
 * @return mixed 
 */
function error_call_matching_handler($errno, $errstr, $errfile, $errline)
{
  $handlers = error();
  $is_http_err = http_response_status_is_valid($errno);
  foreach($handlers as $handler)
  {
    $e = is_array($handler['errno']) ? $handler['errno'] : array($handler['errno']);
    while($ee = array_shift($e))
    {
      if($ee == PHP_ERRORS || $errno == $ee || ($ee == HTTP_STATUS_CODES && $is_http_err))
      {
        echo call_if_exists($handler['function'], $errno, $errstr, $errfile, $errline);
        exit;
      }
    }
  }
  return false;
}

/**
 * Défault error handler
 *
 * @param string $errno 
 * @param string $errstr 
 * @param string $errfile 
 * @param string $errline 
 * @return void
 */
function error_default_handler($errno, $errstr, $errfile, $errline)
{
  status(500);
  if(($errno == E_USER_NOTICE || $errno == E_NOTICE) && option('debug'))
  {
    $o  = "<p>[".error_type($errno)."] ";
	  $o .= "$errstr in <strong>$errfile</strong> line <strong>$errline</strong>: ";
	  $o .= "</p>";
	  error_notice($o);
  }
  else
  {
    echo server_error($errno, $errstr, $errfile, $errline);
    exit;
  }
}

/**
 * Set a notice if provided and return all stored notices
 *
 * @param string $str 
 * @return array
 */
function error_notice($str = null)
{
  static $notices = array();
  if(!is_null($str))
  {
    $notices[] = $str;
  }
  return $notices;
}

/**
 * Default server error output
 *
 * @param integer $errno 
 * @param string $errstr 
 * @param string $errfile 
 * @param string $errline 
 * @return string
 */
function server_error($errno, $errstr, $errfile=null, $errline=null)
{
  $is_http_error = http_response_status_is_valid($errno);
  $o  = "<h1>";
  $o .= $is_http_error ? http_response_status($errno) : "Internal Server Error";
  $o .= "</h1>";
  
  if($is_http_error)
  {
    $o .= "<p>".h($errstr)."</p>";
  }

	if(option('env') > ENV_PRODUCTION && option('debug'))
	{
    if(!$is_http_error)
    {
      $o .= "<p>[".error_type($errno)."] ";
  	  $o .= "$errstr in <strong>$errfile</strong> line <strong>$errline</strong>";
  	  $o .= "</p>";
    }
    
	  if($debug_args = set('_lim_err_debug_args'))
	  {
	    $o .= "<p><strong>Debug arguments</strong></p>";
		  $o .= "<pre><code>".h(print_r($debug_args, true))."</code></pre>";
	  }
	  $o .= "<p><strong>Debug Trace</strong></p>";
	  $o .= "<pre><code>".h(print_r(debug_backtrace(), true))."</code></pre>";
	  $o .= "<p><strong>Limonade options</strong></p>";
	  $o .= "<pre><code>".h(print_r(option(), true))."</code></pre>";
	}
	option('views_dir', option('limonade_dir').'limonade/views/');
	return html($o, 'default_layout.php');
}

/**
 * return error code name for a given code num, or return all errors names
 *
 * @param string $num 
 * @return mixed
 */
function error_type($num = null)
{
  $types = array (
              E_ERROR              => 'ERROR',
              E_WARNING            => 'WARNING',
              E_PARSE              => 'PARSING ERROR',
              E_NOTICE             => 'NOTICE',
              E_CORE_ERROR         => 'CORE ERROR',
              E_CORE_WARNING       => 'CORE WARNING',
              E_COMPILE_ERROR      => 'COMPILE ERROR',
              E_COMPILE_WARNING    => 'COMPILE WARNING',
              E_USER_ERROR         => 'USER ERROR',
              E_USER_WARNING       => 'USER WARNING',
              E_USER_NOTICE        => 'USER NOTICE',
              E_STRICT             => 'STRICT NOTICE',
              E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR'
              );
  return is_null($num) ? $types : $types[$num];
}




                                     # # #




# ============================================================================ #
#    3. REQUEST                                                                #
# ============================================================================ #

/**
 * Returns current request method for a given environment or current one
 *
 * @param string $env 
 * @return void
 */
function request_method($env = null)
{
  if(is_null($env)) $env = env();
  $m = array_key_exists('REQUEST_METHOD', $env['SERVER']) ? $env['SERVER']['REQUEST_METHOD'] : null;
  if($m == "POST" && array_key_exists('_method', $env['POST'])) 
    $m = strtoupper($env['POST']['_method']);
  if(!in_array(strtoupper($m), request_methods()))
  {
    trigger_error("'$m' request method is unkown or unavailable.", E_USER_WARNING);
    $m = false;
  }
  return $m;
}

/**
 * Checks if a request method or current one is allowed
 *
 * @param string $m 
 * @return boolean
 */
function request_method_is_allowed($m = null)
{
  if(is_null($m)) $m = request_method();
  return in_array(strtoupper($m), request_methods());
}

/**
 * Checks if request method is GET
 *
 * @param string $env 
 * @return bolean
 */
function request_is_get($env = null)
{
  return request_method($env) == "GET";
}

/**
 * Checks if request method is POST
 *
 * @param string $env 
 * @return bolean
 */
function request_is_post($env = null)
{
  return request_method($env) == "POST";
}

/**
 * Checks if request method is PUT
 *
 * @param string $env 
 * @return bolean
 */
function request_is_put($env = null)
{
  return request_method($env) == "PUT";
}

/**
 * Checks if request method is DELETE
 *
 * @param string $env 
 * @return bolean
 */
function request_is_delete($env = null)
{
  return request_method($env) == "DELETE";
}

/**
 * Returns allowed request methods
 *
 * @return array
 */
function request_methods()
{
   return array("GET","POST","PUT","DELETE");
}

/**
 * Returns current request uri (the path that will be compared with routes)
 * 
 * (Inspired from codeigniter URI::_fetch_uri_string method)
 *
 * @return string
 */
function request_uri($env = null)
{
  static $uri = null;
  if(is_null($env))
  {
    if(!is_null($uri)) return $uri;
    $env = env();
  }

  if(array_key_exists('uri', $env['GET']))
  {
    $uri = $env['GET']['uri'];
  }
  else if(array_key_exists('u', $env['GET']))
  {
    $uri = $env['GET']['u'];
  }
  else if (count($env['GET']) == 1 && trim(key($env['GET']), '/') != '')
	{
		$uri = key($env['GET']);
	}
	else
	{
    $app_file = app_file();
    $path_info = isset($env['SERVER']['PATH_INFO']) ? $env['SERVER']['PATH_INFO'] : @getenv('PATH_INFO');
    $query_string =  isset($env['SERVER']['QUERY_STRING']) ? $env['SERVER']['QUERY_STRING'] : @getenv('QUERY_STRING');
    
	  // Is there a PATH_INFO variable?
  	// Note: some servers seem to have trouble with getenv() so we'll test it two ways
  	if (trim($path_info, '/') != '' && $path_info != "/".$app_file)
  	{
  		$uri = $path_info;
  	}
  	// No PATH_INFO?... What about QUERY_STRING?
  	elseif (trim($query_string, '/') != '')
  	{
  		$uri = $query_string;
  	}
  	elseif(array_key_exists('REQUEST_URI', $env['SERVER']) && !empty($env['SERVER']['REQUEST_URI']))
  	{
  	  $request_uri = rtrim($env['SERVER']['REQUEST_URI'], '?');
  	  $base_path = $env['SERVER']['SCRIPT_NAME'];

      if($request_uri."index.php" == $base_path) $request_uri .= "index.php";
  	  $uri = str_replace($base_path, '', $request_uri);
  	}
  	elseif($env['SERVER']['argc'] > 1 && trim($env['SERVER']['argv'][1], '/') != '')
    {
      $uri = $env['SERVER']['argv'][1];
    }
	}
  
  $uri = rtrim($uri, "/"); # removes ending /
  if($uri[0] != '/') $uri = '/' . $uri; # add a leading slash
  return $uri;
}




                                     # # #




# ============================================================================ #
#    4. ROUTER                                                                 #
# ============================================================================ #

/**
 * an alias of dispatch_get
 *
 * @return void
 */
function dispatch($path_or_array, $function, $agent_regexp = null)
{
  dispatch_get($path_or_array, $function, $agent_regexp);
}

/**
 * Add a GET route
 *
 * @param string $path_or_array 
 * @param string $function 
 * @param string $agent_regexp 
 * @return void
 */
function dispatch_get($path_or_array, $function, $agent_regexp = null)
{
  route("GET", $path_or_array, $function, $agent_regexp);
}

/**
 * Add a POST route
 *
 * @param string $path_or_array 
 * @param string $function 
 * @param string $agent_regexp 
 * @return void
 */
function dispatch_post($path_or_array, $function, $agent_regexp = null)
{
   route("GET", $path_or_array, $function, $agent_regexp);
}

/**
 * Add a PUT route
 *
 * @param string $path_or_array 
 * @param string $function 
 * @param string $agent_regexp 
 * @return void
 */
function dispatch_put($path_or_array, $function, $agent_regexp = null)
{
   route("GET", $path_or_array, $function, $agent_regexp);
}

/**
 * Add a DELETE route
 *
 * @param string $path_or_array 
 * @param string $function 
 * @param string $agent_regexp 
 * @return void
 */
function dispatch_delete($path_or_array, $function, $agent_regexp = null)
{
   route("GET", $path_or_array, $function, $agent_regexp);
}


/**
 * Add route if required params are provided.
 * Delete all routes if null is passed as a unique argument
 * Return all routes
 * 
 *
 * @param string $method 
 * @param string $path_or_array 
 * @param string $func 
 * @param string $agent_regexp 
 * @return array
 */
function route()
{
	static $routes = array();
	$nargs = func_num_args();
	if( $nargs > 0)
	{
	  $args = func_get_args();
	  if($nargs === 1 && is_null($args[0])) $routes = array();
	  else if($nargs < 3) trigger_error("Missing arguments for route()", E_USER_ERROR);
	  else
	  {
	    $method        = $args[0];
  	  $path_or_array = $args[1];
  	  $func          = $args[2];
  	  $agent_regexp  = array_key_exists(3, $args) ? $args[3] : null;

  	  $routes[] = route_build($method, $path_or_array, $func, $agent_regexp);
	  }
	  
	}
	return $routes;
}

/**
 * An alias of route(null): reset all routes
 *
 * @return void
 */
function route_reset()
{
  route(null);
}

/**
 * Build a route and return it
 *
 * @param string $method 
 * @param string $path_or_array 
 * @param string $func 
 * @param string $agent_regexp 
 * @return array
 */
function route_build($method, $path_or_array, $func, $agent_regexp = null)
{
   $method = strtoupper($method);
   if(!in_array($method, request_methods())) 
      trigger_error("'$method' request method is unkown or unavailable.", E_USER_ERROR);
   
   if(is_array($path_or_array))
   {
      $path  = array_shift($path_or_array);
      $names = $path_or_array[0];
   }
   else
   {
      $path  = $path_or_array;
      $names = array();
   }
   
   $single_asterisk_subpattern = "(?:/([^\/]*))?";
   $double_asterisk_subpattern = "(?:/(.*))?";
   $optionnal_slash_subpattern = "(?:/*?)";
   
   if($path[0] == "^")
   {
     if($path{strlen($path) - 1} != "$") $path .= "$";
     $pattern = "#".$path."#i";
   }
   else if(empty($path) || $path == "/")
   {
     $pattern = "#^".$optionnal_slash_subpattern."$#";
   }
   else
   {
     $parsed = array();
     $elts = explode('/', $path);
     $parameters_count = 0;
     
     foreach($elts as $elt)
     {
       if(empty($elt)) continue;
       
       $name = null; 
       
       # extracting double asterisk **
       if($elt == "**"):
         $parsed[] = $double_asterisk_subpattern;
         $name = $parameters_count;
       
       # extracting single asterisk *
       elseif($elt == "*"):
         $parsed[] = $single_asterisk_subpattern;
         $name = $parameters_count;
               
       # extracting named parameters :my_param 
       elseif($elt[0] == ":"):
         if(preg_match('/^:([^\:]+)$/', $elt, $matches))
         {
           $parsed[] = $single_asterisk_subpattern;
           $name = $matches[1];
         };
       
       else:
         $parsed[] = "/".preg_quote($elt, "#");
       
       endif;
       
       /* set parameters names */ 
       if(is_null($name)) continue;
       if(!array_key_exists($parameters_count, $names) || is_null($names[$parameters_count]))
         $names[$parameters_count] = $name;
       $parameters_count++;
     }
     
     $pattern = "#^".implode('', $parsed).$optionnal_slash_subpattern."?$#i";
   }
   
   return array( "method"       => $method,
                 "pattern"      => $pattern,
                 "names"        => $names,
                 "function"     => $func,
                 "agent_regexp" => $agent_regexp );
}

/**
 * Find a route and returns it
 * If not found, returns false
 * Routes are checked from first added to last added.
 *
 * @param string $method 
 * @param string $path 
 * @return void
 */
function route_find($method, $path)
{
   $routes = route();
   $method = strtoupper($method);
   foreach($routes as $route)
   {
     if($method == $route["method"] && preg_match($route["pattern"], $path, $matches))
     {
       $params = array();
       if(count($matches) > 1)
       {
         array_shift($matches);
         $params = array_combine(array_values($route["names"]), $matches);
       }
       $route["params"] = $params;
       return $route;
     }
   }
   return false;
}





# ============================================================================ #
#    OUTPUT AND RENDERING                                                      #
# ============================================================================ #

function html($content_or_func, $layout = '', $locals = array())
{
   # TODO complete headers in output methods if needed  http://en.wikipedia.org/wiki/List_of_HTTP_headers
   header('Content-Type: text/html; charset='.strtolower(option('encoding')));
   $args = func_get_args();
   return call_user_func_array('render', $args);
}

/**
 * Set and return current layout
 *
 * @param string $function_or_file 
 * @return void
 */
function layout($function_or_file = null)
{
	static $layout = null;
	if(func_num_args() > 0) $layout = $function_or_file;
	return $layout;
}

function xml($data)
{
   # TODO testing xml output function
   header('Content-Type: text/xml; charset='.strtolower(option('encoding')));
   return array_to_xml($data);
}

function json($data, $json_option = 0)
{
   # TODO testing json output function
   header('Content-Type: application/x-javascript; charset='.strtolower(option('encoding')));
   return json_encode($data, $json_option);
}

function txt($content_or_func, $layout = '', $locals = array())
{
   # TODO testing txt output function
   header('Content-Type: text/plain; charset='.strtolower(option('encoding')));
   $args = func_get_args();
   return call_user_func_array('render', $args);
}

function render_file($filename)
{
  # TODO implements render_file
  // if($x-sendfile = option('x-sendfile'))
  // {
  //    // add a X-Sendfile header for apache and Lighttpd >= 1.5
  //    if($x-sendfile > X-SENDFILE) // add a X-LIGHTTPD-send-file header 
  //   
  // }
  // else
  // {
  //   
  // }
}

function render($content_or_func, $layout = '', $locals = array())
{
	$args = func_get_args();
	$content_or_func = array_shift($args);
	$layout = count($args) > 0 ? array_shift($args) : layout();
	$view_path = option('views_dir').$content_or_func;
	$vars = array_merge(set(), $locals);

  if(function_exists($content_or_func))
	{
		ob_start();
		call_user_func($content_or_func, $vars);
		$content = ob_get_clean();
	}
	elseif(file_exists($view_path))
	{
		ob_start();
		extract($vars);
		include $view_path;
		$content = ob_get_clean();
	}
	else
	{
	  $content = vsprintf($content_or_func, $vars);
	}

	if(empty($layout)) return $content;

	return render($layout, null, array('content' => $content));
}




                                     # # #




# ============================================================================ #
#    5. HELPERS                                                                #
# ============================================================================ #

function url_for($params = null)
{
  $env = env();
  $request_uri = rtrim($env['SERVER']['REQUEST_URI'], '?');
  $base_path   = $env['SERVER']['SCRIPT_NAME'];

  $base_path = ereg_replace('index\.php$', '?', $base_path);

  $paths = array();
  $params = func_get_args();
  foreach($params as $param)
  {
    $p = explode('/',$param);
    foreach($p as $v)
    {
      if(!empty($v)) $paths[] = urlencode($v);
    }
  }
  
  return rtrim($base_path."/".implode('/', $paths), '/');
}

function h($str, $quote_style = ENT_NOQUOTES, $charset = null)
{
	if(is_null($charset)) $charset = strtoupper(option('encoding'));
	return htmlspecialchars($str, $quote_style, $charset); 
}




                                     # # #




# ============================================================================ #
#    6. UTILS                                                                  #
# ============================================================================ #

/**
 * Calls a function if exists
 *
 * @param string $func the function name
 * @param mixed $arg,.. (optional)
 * @return mixed
 */
function call_if_exists($func)
{
  $args = func_get_args();
  $func = array_shift($args);
  if(function_exists($func)) return call_user_func_array($func, $args);
  return;
}

function define_unless_exists($name, $value)
{
  if(!defined($anme)) define($name, $value);
}

/**
 * Return a default value if provided value is empty
 *
 * @param string $value 
 * @param string $default default value returned if $value is empty
 * @return void
 */
function value_or_default($value, $default)
{
  return empty($value) ? $default : $value;
}

/**
 * Load php files with require_once in a given dir
 *
 * @param string $path Path in which are the file to load
 * @param string $pattern a regexp pattern that filter files to load
 * @return array paths of loaded files
 */
function require_once_dir($path, $pattern = "*.php")
{
  if($path[strlen($path) - 1] != "/") $path .= "/";
  $filenames = glob($path.$pattern);
  foreach($filenames as $filename) require_once $filename;
  return $filenames;
}

/**
 * Converting an array to an XML document
 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
 *
 * (inspired from http://snipplr.com/view/3491/convert-php-array-to-xml-or-simple-xml-object-if-you-wish/)
 * 
 * @param array $data
 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
 * @param SimpleXMLElement $xml - should only be used recursively
 * @return string XML
 */
function array_to_xml($data, $rootNodeName = 'data', &$xml=null)
{
	// turn off compatibility mode as simple xml throws a wobbly if you don't.
	if (ini_get('zend.ze1_compatibility_mode') == 1) ini_set ('zend.ze1_compatibility_mode', 0);

	if (is_null($xml))
	{
		$xml_str = "<?xml version='1.0' encoding='".
		            option(encoding)."'?><$rootNodeName />";
		$xml = simplexml_load_string($xml_str);
	}

	// loop through the data passed in.
	foreach($data as $key => $value)
	{
		// no numeric keys in our xml please!
		if (is_numeric($key)) $key = "node_". (string) $key;

		// replace anything not alpha numeric
		$key = preg_replace('/[^\w\d-_]/i', '_', $key);

		// if there is another array found recrusively call this function
		if (is_array($value))
		{
			$node = $xml->addChild($key);
			array_to_xml($value, $rootNodeName, $node);
		}
		else 
		{
			// add single node.
      $value = h($value);
			$xml->addChild($key, $value);
		}

	}
	return $xml->asXML();
}

## HTTP utils  _________________________________________________________________

function status($code = 500)
{
	$str = http_response_status_code($code);
	header($str);
}

function http_response_status($num = null)
{
  $status =  array(
      100 => 'Continue',
      101 => 'Switching Protocols',
      102 => 'Processing',

      200 => 'OK',
      201 => 'Created',
      202 => 'Accepted',
      203 => 'Non-Authoritative Information',
      204 => 'No Content',
      205 => 'Reset Content',
      206 => 'Partial Content',
      207 => 'Multi-Status',
      226 => 'IM Used',

      300 => 'Multiple Choices',
      301 => 'Moved Permanently',
      302 => 'Found',
      303 => 'See Other',
      304 => 'Not Modified',
      305 => 'Use Proxy',
      306 => 'Reserved',
      307 => 'Temporary Redirect',

      400 => 'Bad Request',
      401 => 'Unauthorized',
      402 => 'Payment Required',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      406 => 'Not Acceptable',
      407 => 'Proxy Authentication Required',
      408 => 'Request Timeout',
      409 => 'Conflict',
      410 => 'Gone',
      411 => 'Length Required',
      412 => 'Precondition Failed',
      413 => 'Request Entity Too Large',
      414 => 'Request-URI Too Long',
      415 => 'Unsupported Media Type',
      416 => 'Requested Range Not Satisfiable',
      417 => 'Expectation Failed',
      422 => 'Unprocessable Entity',
      423 => 'Locked',
      424 => 'Failed Dependency',
      426 => 'Upgrade Required',

      500 => 'Internal Server Error',
      501 => 'Not Implemented',
      502 => 'Bad Gateway',
      503 => 'Service Unavailable',
      504 => 'Gateway Timeout',
      505 => 'HTTP Version Not Supported',
      506 => 'Variant Also Negotiates',
      507 => 'Insufficient Storage',
      510 => 'Not Extended'
  );
  return is_null($num) ? $status : $status[$num];
}

function http_response_status_is_valid($num)
{
  $r = http_response_status($num);
  return !empty($r);
}

function http_response_status_code($num)
{
  if($str = http_response_status($num)) return "HTTP/1.1 $num $str";
}

## FILE utils  _________________________________________________________________

/**
 * Returns all mime types in an associative array, with extensions as keys
 * (extracted from Orbit source http://orbit.luaforge.net/)
 *
 * @return array
 */
function mime_types()
{
  return array(
    'ai'      => 'application/postscript',
    'aif'     => 'audio/x-aiff',
    'aifc'    => 'audio/x-aiff',
    'aiff'    => 'audio/x-aiff',
    'asc'     => 'text/plain',
    'atom'    => 'application/atom+xml',
    'atom'    => 'application/atom+xml',
    'au'      => 'audio/basic',
    'avi'     => 'video/x-msvideo',
    'bcpio'   => 'application/x-bcpio',
    'bin'     => 'application/octet-stream',
    'bmp'     => 'image/bmp',
    'cdf'     => 'application/x-netcdf',
    'cgm'     => 'image/cgm',
    'class'   => 'application/octet-stream',
    'cpio'    => 'application/x-cpio',
    'cpt'     => 'application/mac-compactpro',
    'csh'     => 'application/x-csh',
    'css'     => 'text/css',
    'dcr'     => 'application/x-director',
    'dir'     => 'application/x-director',
    'djv'     => 'image/vnd.djvu',
    'djvu'    => 'image/vnd.djvu',
    'dll'     => 'application/octet-stream',
    'dmg'     => 'application/octet-stream',
    'dms'     => 'application/octet-stream',
    'doc'     => 'application/msword',
    'dtd'     => 'application/xml-dtd',
    'dvi'     => 'application/x-dvi',
    'dxr'     => 'application/x-director',
    'eps'     => 'application/postscript',
    'etx'     => 'text/x-setext',
    'exe'     => 'application/octet-stream',
    'ez'      => 'application/andrew-inset',
    'gif'     => 'image/gif',
    'gram'    => 'application/srgs',
    'grxml'   => 'application/srgs+xml',
    'gtar'    => 'application/x-gtar',
    'hdf'     => 'application/x-hdf',
    'hqx'     => 'application/mac-binhex40',
    'htm'     => 'text/html',
    'html'    => 'text/html',
    'ice'     => 'x-conference/x-cooltalk',
    'ico'     => 'image/x-icon',
    'ics'     => 'text/calendar',
    'ief'     => 'image/ief',
    'ifb'     => 'text/calendar',
    'iges'    => 'model/iges',
    'igs'     => 'model/iges',
    'jpe'     => 'image/jpeg',
    'jpeg'    => 'image/jpeg',
    'jpg'     => 'image/jpeg',
    'js'      => 'application/x-javascript',
    'kar'     => 'audio/midi',
    'latex'   => 'application/x-latex',
    'lha'     => 'application/octet-stream',
    'lzh'     => 'application/octet-stream',
    'm3u'     => 'audio/x-mpegurl',
    'man'     => 'application/x-troff-man',
    'mathml'  => 'application/mathml+xml',
    'me'      => 'application/x-troff-me',
    'mesh'    => 'model/mesh',
    'mid'     => 'audio/midi',
    'midi'    => 'audio/midi',
    'mif'     => 'application/vnd.mif',
    'mov'     => 'video/quicktime',
    'movie'   => 'video/x-sgi-movie',
    'mp2'     => 'audio/mpeg',
    'mp3'     => 'audio/mpeg',
    'mpe'     => 'video/mpeg',
    'mpeg'    => 'video/mpeg',
    'mpg'     => 'video/mpeg',
    'mpga'    => 'audio/mpeg',
    'ms'      => 'application/x-troff-ms',
    'msh'     => 'model/mesh',
    'mxu'     => 'video/vnd.mpegurl',
    'nc'      => 'application/x-netcdf',
    'oda'     => 'application/oda',
    'ogg'     => 'application/ogg',
    'pbm'     => 'image/x-portable-bitmap',
    'pdb'     => 'chemical/x-pdb',
    'pdf'     => 'application/pdf',
    'pgm'     => 'image/x-portable-graymap',
    'pgn'     => 'application/x-chess-pgn',
    'png'     => 'image/png',
    'pnm'     => 'image/x-portable-anymap',
    'ppm'     => 'image/x-portable-pixmap',
    'ppt'     => 'application/vnd.ms-powerpoint',
    'ps'      => 'application/postscript',
    'qt'      => 'video/quicktime',
    'ra'      => 'audio/x-pn-realaudio',
    'ram'     => 'audio/x-pn-realaudio',
    'ras'     => 'image/x-cmu-raster',
    'rdf'     => 'application/rdf+xml',
    'rgb'     => 'image/x-rgb',
    'rm'      => 'application/vnd.rn-realmedia',
    'roff'    => 'application/x-troff',
    'rss'     => 'application/rss+xml',
    'rtf'     => 'text/rtf',
    'rtx'     => 'text/richtext',
    'sgm'     => 'text/sgml',
    'sgml'    => 'text/sgml',
    'sh'      => 'application/x-sh',
    'shar'    => 'application/x-shar',
    'silo'    => 'model/mesh',
    'sit'     => 'application/x-stuffit',
    'skd'     => 'application/x-koan',
    'skm'     => 'application/x-koan',
    'skp'     => 'application/x-koan',
    'skt'     => 'application/x-koan',
    'smi'     => 'application/smil',
    'smil'    => 'application/smil',
    'snd'     => 'audio/basic',
    'so'      => 'application/octet-stream',
    'spl'     => 'application/x-futuresplash',
    'src'     => 'application/x-wais-source',
    'sv4cpio' => 'application/x-sv4cpio',
    'sv4crc'  => 'application/x-sv4crc',
    'svg'     => 'image/svg+xml',
    'svgz'    => 'image/svg+xml',
    'swf'     => 'application/x-shockwave-flash',
    't'       => 'application/x-troff',
    'tar'     => 'application/x-tar',
    'tcl'     => 'application/x-tcl',
    'tex'     => 'application/x-tex',
    'texi'    => 'application/x-texinfo',
    'texinfo' => 'application/x-texinfo',
    'tif'     => 'image/tiff',
    'tiff'    => 'image/tiff',
    'tr'      => 'application/x-troff',
    'tsv'     => 'text/tab-separated-values',
    'txt'     => 'text/plain',
    'ustar'   => 'application/x-ustar',
    'vcd'     => 'application/x-cdlink',
    'vrml'    => 'model/vrml',
    'vxml'    => 'application/voicexml+xml',
    'wav'     => 'audio/x-wav',
    'wbmp'    => 'image/vnd.wap.wbmp',
    'wbxml'   => 'application/vnd.wap.wbxml',
    'wml'     => 'text/vnd.wap.wml',
    'wmlc'    => 'application/vnd.wap.wmlc',
    'wmls'    => 'text/vnd.wap.wmlscript',
    'wmlsc'   => 'application/vnd.wap.wmlscriptc',
    'wrl'     => 'model/vrml',
    'xbm'     => 'image/x-xbitmap',
    'xht'     => 'application/xhtml+xml',
    'xhtml'   => 'application/xhtml+xml',
    'xls'     => 'application/vnd.ms-excel',
    'xml'     => 'application/xml',
    'xpm'     => 'image/x-xpixmap',
    'xsl'     => 'application/xml',
    'xslt'    => 'application/xslt+xml',
    'xul'     => 'application/vnd.mozilla.xul+xml',
    'xwd'     => 'image/x-xwindowdump',
    'xyz'     => 'chemical/x-xyz',
    'zip'     => 'application/zip'
  );
}

/**
 * Read and output file content and return filesize in bytes or status after 
 * closing file.
 * This function is very efficient for outputing large files without timeout
 * nor too expensive memory use
 *
 * @param string $filename 
 * @param string $retbytes 
 * @return void
 */
function file_read_chunked($filename = null, $retbytes = true)
{
	if(is_null($filename)) $filename = $this->filename;
  $chunksize = 1*(1024*1024); // how many bytes per chunk
  $buffer    = '';
  $cnt       = 0;
  $handle    = fopen($filename, 'rb');
  if ($handle === false) return false;
  
	ob_start();
    while (!feof($handle)) {
  	  $buffer = fread($handle, $chunksize);
      echo $buffer;
      ob_flush();
  	  flush();
      if ($retbytes) $cnt += strlen($buffer);
  	  set_time_limit(0);
    }
	ob_end_flush();
	
  $status = fclose($handle);
  if ($retbytes && $status) return $cnt; // return num. bytes delivered like readfile() does.
  return $status;
}


function file_extension($filename)
{
	$pos = strrpos($filename, '.');
	if($pos !== false) return substr($filename, $pos + 1);
	return false;
}

function file_is_text($filename)
{
	if($mime = mime_content_type($filename)) return substr($mime,0,5) == "text/";
	return null;
}

function file_is_binary($filename)
{
	$is_text = file_is_text($filename);
	return is_null($is_text) ? null : !$is_text;
}

/**
 * file_read: return or output file content
 *
 * @return 	mixed null if no filename provided or filesize
 *				
 **/

function file_read($filename = null, $output = false)
{
	if(is_null($filename)) return null;
	if($output) return file_read_chunked($filename);
	return file_get_contents($filename);
}

function file_list_dir($dir)
{
	$files = array(); 
	if ($handle = opendir($dir))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file[0] != "." && $file != "..") $files[] = $file;
		}
		closedir($handle);
	}
	return $files;
}








#   ================================= END ==================================   #

?>