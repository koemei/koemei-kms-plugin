<?php
/**
 * Description of Common
 *
 * @author gonen
 */
class Kms_Kb_Common {

    const KB_TAB_GENERAL = 'general';
    const KB_TAB_INTERFACES = 'interfaces';
    const KB_TAB_VIEWHOOKS = 'viewhooks';
    const KB_TAB_VIEWFILES = 'viewfiles';
    const KB_TAB_INTERNAL = 'internal';

    private static $viewsToViewHooks = array();
    
    
    public static function getGeneralTab()
    {
        return self::KB_TAB_GENERAL;
    }

    public static function getTabs()
    {
        $reflection = new ReflectionClass('Kms_Kb_Common');
        return $reflection->getConstants();
    }

    private static $_interfaceFiles = array();

    /**
     * find all interfaces declared in library/Kms, parse their docComment and return associative array of interface-description pairs
     *
     * @return array
     */
    public static function listInterfaces($clearArray = true)
    {
        if($clearArray) 
        {
            self::$_interfaceFiles = array();
        }
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library'.DIRECTORY_SEPARATOR.'Kms'.DIRECTORY_SEPARATOR.'Interface'.DIRECTORY_SEPARATOR;

        $files= self::listFilesRecursive($path);
        foreach($files as $fileName)
        {
            $fileName = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $fileName);
            $relPath = str_replace($path, '', $fileName);
            $ext = pathinfo($relPath, PATHINFO_EXTENSION);
            $relPath = str_replace('.'.$ext, '', $relPath);
            $ifaceName = 'Kms_Interface_'.str_replace(DIRECTORY_SEPARATOR, '_', $relPath);
            $a = new ReflectionClass($ifaceName);
            $comment = new Kms_Kb_DocCommentParser($a->getDocComment());
            if($comment->ignore) continue;
            self::$_interfaceFiles[$ifaceName] = $comment->description;
        }
        return self::$_interfaceFiles;
    }

    /**
     * function recursively runs into the given path and returns an associative array the lists all files in the path.
     * 
     * @param string $path
     * @return array
     */
    private static function listFilesRecursive($path)
    {
        $files = array();
        if(is_dir($path) && is_readable($path))
        {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false)
            {
                if(is_dir($file) || is_dir($path.DIRECTORY_SEPARATOR.$file))
                {
                    if($file == '.' || $file == '..' || $file == '.svn')
                    {
                        continue;
                    }
                    else
                    {
                        $newPath = $path . DIRECTORY_SEPARATOR   . $file;
                        $dirFiles = self::listFilesRecursive($newPath);
                        $files = array_merge($files, $dirFiles);
                    }
                }
                else
                {
                    $files[] = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR , DIRECTORY_SEPARATOR, $path).DIRECTORY_SEPARATOR.$file;
                }
            }
        }
        
        return $files;
    }

    /**
     * function reflects given interface and parses its docComment.
     * function returns associative array that describes the interface {name, description, mthods and constants}
     * 
     * @param string $interfaceName
     * @return array
     */
    public static function getInterfaceInfo($interfaceName)
    {
        $info = array();
        $reflection = new ReflectionClass($interfaceName);
        $comment = new Kms_Kb_DocCommentParser($reflection->getDocComment());
        $info['description'] = $comment->description;
        $info['name'] = $interfaceName;
        $methods = $reflection->getMethods();
        foreach($methods as $method)
        {
            $methodInfo = new Kms_Kb_DocCommentParser($method->getDocComment());
            $info['methods'][$method->name] = array();
            $info['methods'][$method->name]['description'] = $methodInfo->description;
            $reflectionParams = $method->getParameters();
            $info['methods'][$method->name]['params'] = array();
            foreach($reflectionParams as $reflectionParam)
            {
                $name = $reflectionParam->getName();

                $parsedDocComment = new Kms_Kb_DocCommentParser( $method->getDocComment(), array(
                    Kms_Kb_DocCommentParser::DOCCOMMENT_REPLACENET_PARAM_NAME => $name , ) );
                $info['methods'][$method->name]['params']["$".$name] = $parsedDocComment->param;
                if(isset($parsedDocComment->paramDescription) && !empty($parsedDocComment->paramDescription))
                {
                    $info['methods'][$method->name]['params']["$".$name] .= '; '. $parsedDocComment->paramDescription;
                }
            }
            $info['methods'][$method->name]['return'] = $methodInfo->returnType;
        }
        $info['constants'] = $reflection->getConstants();
        return $info;
    }

    /**
     * this function runs through an array of filenames and matches the description and/or viewhooks to the file
     * returns an array
     * 
     * @param array $filesArray
     * @param string $basePath
     * @param boolean $getViewHooks 
     * @return array
     */
    public static function getViewFilesInfo($filesArray, $basePath)
    {
        $ret = array();
        foreach($filesArray as $key => $filePath)
        {
            $filePath = str_replace($basePath, '', $filePath);
            $filePath = ltrim($filePath, DIRECTORY_SEPARATOR);
            $parsedComment = self::getViewFileDocComment($basePath, $filePath);
            
            
            
            if(!$parsedComment->ignore)
            {
                $ret[$filePath] = $parsedComment;
            }
            
            if(count($parsedComment->viewhooks))
            {
                foreach($parsedComment->viewhooks as $viewhook)
                {
                    if(defined($viewhook))
                    {
                        $viewhook = constant($viewhook);
                    }
                    if(!isset(self::$viewsToViewHooks[$viewhook]))
                    {
                        self::$viewsToViewHooks[$viewhook] = array();
                    }
                    self::$viewsToViewHooks[$viewhook][] = $filePath;
                }
            }
            
        }
        return $ret;
        
    }
    
    /**
     * function creates an array of all layout files, view files and modules' view files.
     * each element in the array is a key-value pair of filePath-ViewDescription.
     * 
     * 
     * @param boolean $getViewHooks
     * 
     * @return array
     */
    public static function listViewFiles($getViewHooks = false)
    {
        $themeFiles = array();

        $pathLayouts = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'layouts'. DIRECTORY_SEPARATOR .'scripts';
        $layoutFiles = self::listFilesRecursive($pathLayouts);
        $themeFiles['layouts'] = self::getViewFilesInfo($layoutFiles, $pathLayouts, $getViewHooks);
        
        $pathViews = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'views'. DIRECTORY_SEPARATOR .'scripts';
        $viewFiles = self::listFilesRecursive($pathViews);
        $themeFiles['views'] = self::getViewFilesInfo($viewFiles, $pathViews, $getViewHooks);
        

        $themeFiles['modules'] = array();
        $modules = Kms_Resource_Config::getAllModules();
        
        foreach($modules as $moduleName => $moduleConfig)
        {
            if ($moduleName != '.svn')
            {
                $modulePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views'. DIRECTORY_SEPARATOR .'scripts';
                $moduleFiles = self::listFilesRecursive($modulePath);
                $themeFiles['modules'][$moduleName] = self::getViewFilesInfo($moduleFiles, $modulePath, $getViewHooks);
            }
        }
        return $themeFiles;

    }

    /**
     * 
     */
    
    
    /**
     * function reads view file, extracts fisrt comment from code and parses the comment.
     * if comment includes "@ignore" it returns false;
     * if comment is not found or is not before first php-close tag return empty string;
     *
     * @param string $basePath
     * @param string $filePath
     * @return mixed
     */
    private static function getViewFileDocComment($basePath, $filePath)
    {

        $content = file_get_contents($basePath . DIRECTORY_SEPARATOR . $filePath);
        $commentStart = strpos($content, '/**');
        if($commentStart === false) return new Kms_Kb_DocCommentParser('');

        // make sure comment starts at top of file and not take a comment in the view itself
        $firstPhpClosed = strpos($content, '?>');
        if($firstPhpClosed !== false && $firstPhpClosed < $commentStart) return new Kms_Kb_DocCommentParser('');

        $commentEnd = strpos($content, ' */', $commentStart);
        $comment = substr($content, $commentStart, $commentEnd-$commentStart+3);

        

        $parsedComment = new Kms_Kb_DocCommentParser($comment);
        //if($parsedComment->ignore) return false;

        return $parsedComment;
    }

    public static function listViewHooks()
    {
        $viewHooks = Kms_Resource_Viewhook::listViewHooks();
        $viewFiles = self::listViewFiles();
        
        if(isset($viewHooks['core']))
        {
            foreach($viewHooks['core'] as $key => $description)
            {
                $viewHooks['core'][$key] = array(
                    'description' => $description,
                    'viewfiles' => isset(self::$viewsToViewHooks[$key]) ?self::$viewsToViewHooks[$key] : array()
                );
            }
        }
        
        if(isset($viewHooks['modules']))
        {
            foreach($viewHooks['modules'] as $moduleName => $moduleFiles)
            {
                foreach($moduleFiles as $key => $description)
                {
                    $viewHooks['modules'][$moduleName][$key] = array(
                        'description' => $description,
                        'viewfiles' => isset(self::$viewsToViewHooks[$key]) ?self::$viewsToViewHooks[$key] : array()
                    );
                }
            }
        }
        
        return $viewHooks;
    }

}

?>
