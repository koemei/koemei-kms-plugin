<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 *  serve assets for modules. assets can be images, css, js, swf and flv - set under
 *  the module assets directory. 
 *
 *  requests should be of the form "/[build number]/[module name]/asset/[file name]"  
 *
 *  as an additional precussion, *.php, *.ini, .htaccess are disallowed.
 */

// full module path
$path = getModuleDirectory($module);

if (!empty($path)) 
{
	// constract full path for the file
    $file = $path . DIRECTORY_SEPARATOR .'assets' . DIRECTORY_SEPARATOR . $fileName; 
            
    if (!file_exists($file) || !isFilePermitted($fileName) || !locationPermitted($file, $path)) 
	{
    	// file does not exist or is not permitted		
    	header("HTTP/1.0 404 Not Found");
	}
	else if (returnFileNotChanged($file))
	{
		// file has not changed since last request
    	header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
	}
	else
	{
		// set the response headers
	    header('Content-type: ' . getFileType($fileExtention));
	    header('Cache-Control: private');
	    header('Last-Modified: ' . gmdate ("D, d M Y H:i:s", filemtime($file)) . ' GMT');

	    // remove response headers set by php that prevent caching
	    header_remove('Expires');
	    header_remove('Pragma'); 

	    // write the file content
	    readfile($file);
	}
}
else
{
	// wrong module directory
	header("HTTP/1.0 404 Not Found");
}

/**
 *  get the module directory - regular or custom
 *
 *  @param string $module - the module name
 *  @return string the actual module directory
 */
 function getModuleDirectory($module = '')
{
    $moduleDirectory = '';

    if(file_exists(MODULES_PATH . DIRECTORY_SEPARATOR . $module)){
    	$moduleDirectory = MODULES_PATH . DIRECTORY_SEPARATOR . $module;
    }
    else if (file_exists(CUSTOM_MODULES_PATH . DIRECTORY_SEPARATOR . $module)) {
        $moduleDirectory = CUSTOM_MODULES_PATH . DIRECTORY_SEPARATOR . $module;
    }

    return $moduleDirectory;
}

/**
 *  test that the file is permitted - no directory directives, no php files, no config files.
 *
 *  @param string $fileName - the file name
 *  @return boolean is the file permitted
 */
 function isFilePermitted($fileName = '')
{
    $fileValid = true;

    // test no dots
    if (strpos($fileName, '..') !== FALSE) {
        $fileValid = false;
    }

    // test no .php, .ini. .htaccess, extensionless files
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    if (in_array($extension, array('php','ini','htaccess',''))) {
        $fileValid = false;
    }

    return $fileValid;
}

/**
 *  test that the file is located under its desired path - the theme/module folder.
 *  added as a security measure to foil upward directory traversing.
 *  @param $file - the file to test
 *  @param $path - the base path
 *  @return boolean - true is the file is located under the dir, false otherwise
 */
function locationPermitted($file, $basePath)
{
    if (strpos(realpath($file), $basePath) !== 0) {
        return false;
    }
    return true;
}

/**
 *  get the file content type 
 *
 *  @param string $fileName - the file name
 *  @return string the content type .
 */
 function getFileType($fileExtention = '')
{
    $type = '';
    switch (strtolower($fileExtention)) {
        case 'js':
            $type .= 'text/javascript';
            break;
        case 'css':
            $type .= 'text/css';
            break;
        case 'jpg':
        case 'jpeg':  
            $type .= 'image/jpeg';
            break;              
        case 'png':      
            $type .= 'image/png';      
            break;
        case 'gif':                                  
            $type .= 'image/gif';
            break;   
        case 'flv':                                  
            $type .= 'video/x-flv';
            break;  
        case 'swf':                                  
            $type .= 'application/x-shockwave-flash';
            break; 
        default:
            $type .= 'text/html';
            break;
        }
    return $type;
}

/**
 *	check if this is a conditional get request, and if so
 *	return if the file was changed since last request.
 *
 * 	@param $file - full path of file to check
 *	@return boolean - has the file changed since the date indicated in 
 *	the server HTTP_IF_MODIFIED_SINCE header.
 */
function returnFileNotChanged($file)
{
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	    if (filemtime($file) <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	    	return true;
	    }
	}
	return false;
}

?>