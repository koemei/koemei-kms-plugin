<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of EmbedCode
 *
 * @author leon
 */
class Kms_View_Helper_ConfigFields extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function ConfigFields($fields = false, $empty = false)
    {
        // return the helper if fields is false, to enable use of other functions in this class
        if(!$fields)
            return $this;
        $ret = '';
        
        // iterate over the fields 
        foreach($fields as $name => $field)
        {
            // if the value is not set initially, get it from the config.ini
            // unless $empty argument is true (defaults to false)
            if(!isset($field['value']) && !$empty)
            {
               $field['value'] = $this->loadConfig($this->view->currentTab, $name);
            }

            // set the name of the field from the key
            $field['name'] = $name;

            $dependencyInfoArray = $this->getFieldDependencyAttributes($field);
            
            // draw the div wrapping the configuration item
            $ret .= '<div class="tabItem" '.$dependencyInfoArray['fields'].' '.$dependencyInfoArray['values'].' '.$dependencyInfoArray['notValue'].'>';
            
            // get an md5 identifier for the field
            $fieldIdentifier = $this->getFieldIdentifier($field);
            
            // get the elements of the config field
            $elements = $this->ConfigField($field);
            
            // if there are multiple fields for the field
            if(is_array($elements))
            {
                foreach($elements as $element)
                {
                    // wrap the fields with an "element" div
                    $ret .= '<div class="element" data-id="'.$fieldIdentifier.'">'.$element.'</div>';   
                }
            }
            else
            {
                $ret .= $elements;
            }
            
            $ret .= '</div>';
        }
        
        return $ret;
    }
    
    
    /* output a configuration item (or a collection */
    public function ConfigField($spec)
    {
        // check the type
        switch($spec['type'])
        {
            case 'text':
                $elements = $this->ConfigFieldText($spec);
            break;
            case 'textarea':
                $elements = $this->ConfigFieldTextArea($spec);
            break;
            case 'select':
                $elements = $this->ConfigFieldSelect($spec);
                break;
            case 'boolean':
                $elements = $this->ConfigFieldBoolean($spec);
                break;
            case 'int':
                $elements = $this->ConfigFieldInt($spec);
                break;
            case 'array': 
                $elements = $this->ConfigFieldArray($spec);
                break;
            case 'object': 
                $elements = $this->ConfigFieldObject($spec);
                break;
            case 'readonly':
                $elements = $this->ConfigFieldReadOnly($spec);
                break;
            default:
                break;
        }
        
        // in case NOT array
        if($spec['type'] != 'array' && $spec['type'] != 'object')
        {
            foreach($elements as $element)
            {
                // iterate over the elements
                if(isset($spec['belongsTo'])  && $spec['belongsTo'] )
                {
                    // set the belongsTo (curly brackets in the name property of the input element - ARRAY)
                    $element->setBelongsTo($spec['belongsTo']);
                }
                
                // set the data-name attribute (for filtering and dependencies)
                $element->setAttrib('data-id', $element->getId());
                $element->setAttrib('data-name', $element->getName());
                
            }
            
            // get the index of the last element
            $lastElement = count($elements) - 1;
            $noLabel = $elements[$lastElement]->getAttrib('data-nolabel');
            
            if(!isset($spec['nowrapper']) || !$spec['nowrapper'])
            {
                // add the comment
                // insert the comment after the first element
                $elements[0] .= isset($spec['comment']) && $spec['comment'] ? '<div class="comment">'.$spec['comment'].'</div>'  : "";
            }
            
            // in case multiple values are allowed for this field
            if($spec['allowMulti'] && $spec['type'] != 'select')
            {
                // get an md5 string identifier for the given field
                $fieldIdentifier = $this->getFieldIdentifier($spec);
                
                // check to see if we need to create an element with no label
                if($noLabel)
                {
                    $spec['nolabel'] = 1;
                }
                // create the "ADD" link
                $link = $this->buildAddLink($spec);
                
                // if "NOWRAPPER" is false (nowrapper is set to true when we get one field by AJAX)
                if(!isset($spec['nowrapper']) || !$spec['nowrapper'])
                {
                    
                    //  add an "ADD" link to the last element
                    $elements[$lastElement] .= '<div style="clear: both; float: right;" id="'.$fieldIdentifier.'"><a class="add" rel="async" href="'.$link.'" data-field="'.$fieldIdentifier.'">+ Add "'.$spec['name'].'"</a></div>';
                }
            }
            
            // if "NOWRAPPER" is false (nowrapper is set to true when we get one field by AJAX)
        }
        
        $out = '';
        
        // concat the elements into one string and return it
        if(is_array($elements))
        {
            foreach($elements as $element)
            {
                $out .= $element;
            }
            return $out;
        }
        else
        {
            return $elements;
        }
    }

    private function ConfigFieldReadOnly($spec)
    {
        $elements = $this->ConfigFieldText($spec);
        foreach($elements as $key => $element)
        {
            $elements[$key]->setAttrib('readonly', 'readonly');
        }
        return $elements;
    }
    
    private function ConfigFieldText($spec)
    {
        $elements = array();
        // keep a flag that the label was set already
        // in order to output many fields with just one label
        // also, we dont set the label if $spec['nolabel'] equals 1
        $labelIsSet = isset($spec['nolabel']) && $spec['nolabel'] ? true : false;
        $autocompleteValues = false;
        
        // default class name
        $className = 'Application_Model_Config';
        $autoCompleteFunction = isset($spec['autocomplete']) && $spec['autocomplete'] ? $spec['autocomplete'] : false;
        if(preg_match('/::/', $autoCompleteFunction))
        {
            // override class name from modules
            list($className, $autoCompleteFunction) = explode('::', $autoCompleteFunction);
        }

        if($autoCompleteFunction && method_exists($className, $autoCompleteFunction))
        {
            eval('$autocompleteValues = '.$className.'::'.$autoCompleteFunction.'();');
        }
        
        // are multiple values allowed?
        if($spec['allowMulti'])
        {
            // check to see if we have values for the fields
            if(isset($spec['value']) && count($spec['value']))
            {
                
                // iterate over the value array
                foreach($spec['value'] as $val)
                {
                    // create a zend form element
                    if($autocompleteValues)
                    {
                        $element = new ZendX_JQuery_Form_Element_AutoComplete($spec['name'], array (
                            'jQueryParams' => array (
                                'source' => $autocompleteValues,
                        )));
                    }
                    else
                    {
                        $element = new Zend_Form_Element_Text($spec['name']);
                    }
                    
                    
                    $element->setAttrib('class' ,'multi');
                    // set the label if not already set
                    if(!$labelIsSet)
                    {
                        $element->setLabel($spec['name']);
                    }
                    else
                    {
                        // label was set already, so set an empty label
                        $element->setLabel(' ');
//                        $element->removeDecorator('Label');
                        $element->setAttrib('data-nolabel', true);
                        
                    }
                    // toggle label the flag
                    $labelIsSet = true;
                    
                    // set the value of the element
                    $element->setValue($val);
                    
                    // setisarray adds brackets ([]) to the end of the name property
                    $element->setIsArray(1);
                    
                    //add element to elements array
                    $elements[] = $element;
                }
            }
            else
            {
                // values are not set.
                // create in this case, just one empty element
                // create a zend form element
                if($autocompleteValues)
                {
                    $element = new ZendX_JQuery_Form_Element_AutoComplete($spec['name'], array (
                        'jQueryParams' => array (
                            'source' => $autocompleteValues,
                    )));
                }
                else
                {
                    $element = new Zend_Form_Element_Text($spec['name']);
                }
                
                $element->setAttrib('class' ,'multi');
                if(!$labelIsSet)
                {
                    $element->setLabel($spec['name']);
                }
                else
                {
                    // label was set already, so set an empty label
                    $element->setLabel(' ');
//                    $element->removeDecorator('Label');
                    $element->setAttrib('data-nolabel', true);

                }
                
//                $element->setValue($spec['default'] ? $spec['default'] : '');
                $element->setIsArray(1);
                $elements[] = $element;
            }
        }
        else
        {
            // create a zend form element
            if($autocompleteValues)
            {
                $element = new ZendX_JQuery_Form_Element_AutoComplete($spec['name'], array (
                    'jQueryParams' => array (
                        'source' => $autocompleteValues,
                )));
            }
            else
            {
                $element = new Zend_Form_Element_Text($spec['name']);
            }
            
            $element->setLabel($spec['name']);
            if(isset($spec['value']))
            {
                $element->setValue($spec['value']);
            }
            
            $elements[] = $element;
        }
        return $elements;
        
    }
    
    private function ConfigFieldTextArea($spec)
    {
        $elements = array();
        // keep a flag that the label was set already
        // in order to output many fields with just one label
        // also, we dont set the label if $spec['nolabel'] equals 1
        $labelIsSet = isset($spec['nolabel']) && $spec['nolabel'] ? true : false;
        
        
        // are multiple values allowed?
        if($spec['allowMulti'])
        {
            // check to see if we have values for the fields
            if(isset($spec['value']) && count($spec['value']))
            {
                
                // iterate over the value array
                foreach($spec['value'] as $val)
                {
                    // create a zend form element
                    $element = new Zend_Form_Element_Textarea($spec['name']);
                    $element->setAttrib('class' ,'multi');
                    // set the label if not already set
                    if(!$labelIsSet)
                    {
                        $element->setLabel($spec['name']);
                    }
                    else
                    {
                        // label was set already, so set an empty label
                        $element->setLabel(' ');
//                        $element->removeDecorator('Label');
                        $element->setAttrib('data-nolabel', true);
                        
                    }
                    // toggle label the flag
                    $labelIsSet = true;
                    
                    // set the value of the element
                    $element->setValue($val);
                    
                    // setisarray adds brackets ([]) to the end of the name property
                    $element->setIsArray(1);
                    
                    //add element to elements array
                    $elements[] = $element;
                }
            }
            else
            {
                // values are not set.
                // create in this case, just one empty element
                $element = new Zend_Form_Element_Textarea($spec['name']);
                if(!$labelIsSet)
                {
                    $element->setLabel($spec['name']);
                }
                $element->setValue($spec['default'] ? $spec['default'] : '');
                $element->setIsArray(1);
                $elements[] = $element;
            }
        }
        else
        {
            $element = new Zend_Form_Element_Textarea($spec['name']);
            $element->setLabel($spec['name']);
            if(isset($spec['value']))
            {
                $element->setValue($spec['value']);
            }
            $elements[] = $element;
        }
        return $elements;
        
    }
    
    

    private function ConfigFieldInt($spec)
    {
        $elements = $this->ConfigFieldText($spec);
        foreach($elements as $element)
        {
            $element->addValidator(new Zend_Validate_Int());
        }
        
        return $elements;
    }

    
    private function ConfigFieldSelect($spec)
    {
        $elements = array();
        
        if(isset($spec['autoValues']) && $spec['autoValues'])
        {
            // default class name
            $className = 'Application_Model_Config';
            $autoCompleteFunction = isset($spec['autoValues']) && $spec['autoValues'] ? $spec['autoValues'] : false;
            if($autoCompleteFunction)
            {
                if(preg_match('/::/', $autoCompleteFunction))
                {
                    // override class name from modules
                    list($className, $autoCompleteFunction) = explode('::', $autoCompleteFunction);
                }
                
                if(method_exists($className, $autoCompleteFunction))
                {
                    // eval the method
                    eval('$spec["values"] = '.$className.'::'.$autoCompleteFunction.'();');
                    
                }
            }
        }

        if($spec['allowCustom'])
        {
            if(isset($spec['values']) && !in_array($spec['value'], $spec['values']))
            {
                $spec['values']{$spec['value']} = $spec['value'];
            }
        }
        
        if($spec['allowMulti'])
        {
            $element = new Zend_Form_Element_Multiselect($spec['name']);
            if(isset($spec['values']))
            {
                $element->setMultiOptions($spec['values']);
            }
        }
        else
        {
            $element = new Zend_Form_Element_Select($spec['name']);
            if(isset($spec['values']))
            {
                $element->setMultiOptions($spec['values']);
            }
        }

        $element->setLabel($spec['name']);
        
        if(isset($spec['value']))
        {
            if($spec['allowMulti'] && count($spec['value']))
            {
                $multiValues = array();
                foreach($spec['value'] as $val)
                {
                    $multiValues[] = $val;
                }
                $element->setValue($multiValues);
            }
            else
            {
                $element->setValue($spec['value']);
            }
        }
        
        $elements[] = $element;
        if(isset($spec['allowCustom']) && $spec['allowCustom'])
        {
            $customField = new Zend_Form_Element_Text($spec['name'].'_custom');
            $button = new Zend_Form_Element_Button($spec['name'].'_add');
            $button->setLabel("Add custom value");
            $button->setAttrib('onclick', 'var val = $("input#'.$spec['name'].'_custom").val(); if(val){$("select#'.$spec['name'].'").append($("<option></option>").attr("value", val).text(val).attr("selected", "true"));}');
            
            $elements[] = $customField;
            $elements[] = $button;
            
        }
        return $elements;
        
    }
    
    private function ConfigFieldBoolean($spec)
    {
        $elements = array();
        $element = new Zend_Form_Element_Select($spec['name'], array( 'separator'    => '&nbsp;'));
        $element->setLabel($spec['name']);
        $element->setMultiOptions( array(0 => 'No', 1 => 'Yes'));
        $element->setValue($spec['value']);

        $elements[] = $element;
        return $elements;
    }
    
    private function ConfigFieldObject($spec)
    {
        $ret = '';
        $dependencyInfoArray = $this->getFieldDependencyAttributes($spec);
        
        $ret .= '<a name="'.$spec['name'].'"></a><fieldset class="itemCollection" '.$dependencyInfoArray['fields'].' '.$dependencyInfoArray['values'].' '.$dependencyInfoArray['notValue'].'><legend>'.$spec['name'].'</legend>';
        $ret .= isset($spec['comment']) ? '<div class="comment">'.$spec['comment'].'</div>' : '';
        
        $fieldIdentifier = $this->getFieldIdentifier($spec);
        $emptySpec = $spec;
        if(isset($spec['value']) && count($spec['value']))
        {
            foreach($spec['value'] as $key => $value)
            {
                if(isset($spec['fields']) && isset($spec['fields'][$key]) )
                {
                    $spec['fields'][$key]['value'] = $value;
                }
            }
            
        }
        
        foreach($spec['fields'] as $key => $field)
        {
            $dependencyInfoArray = $this->getFieldDependencyAttributes($field);

            $ret .= '<div class="tabItem" '.$dependencyInfoArray['fields'].' '.$dependencyInfoArray['values'].' '.$dependencyInfoArray['notValue'].'>';
            
            $field['name'] = $key;
            if(isset($spec['belongsTo']))
            {
                $field['belongsTo'] = $spec['belongsTo'].'['.$spec['name'].']';
            }
            else
            {
                $field['belongsTo'] = $spec['name'];
            }
            
            $elements = $this->ConfigField($field);
            if(is_array($elements))
            {
                foreach($elements as $element)
                {
                    $ret .= $element;
                }
            }
            else
            {
                $ret .= $elements;
            }            
            $ret .= '</div>';
        }
        
        $ret .= '</fieldset>';
        
        return $ret;
    }

    public function getFieldDependencyAttributes($fieldSpec)
    {
        // parse the dependancies for the field
        if(isset($fieldSpec['depends']) && $fieldSpec['depends'] && isset($fieldSpec['depends']->field))
        {
            $fieldDependName = str_replace('.', '-', $fieldSpec['depends']->field);
            $dependsField = 'data-depends-field="'.$fieldDependName.'"';
            $dependsValue = 'data-depends-value="'.$fieldSpec['depends']->value.'"';
            // allow "opposite dependency" - field is relevant only if value of dependent field is NOT X
            $dependsNotValue = 'data-depends-not-value="'.$fieldSpec['depends']->notValue.'"';
        }
        elseif(isset($fieldSpec['depends']) && $fieldSpec['depends'] && isset($fieldSpec['depends']->fields))
        {
            $fieldDependNames = str_replace('.', '-', $fieldSpec['depends']->fields);
            $dependsField = 'data-depends-fields="'.$fieldDependNames.'"';
            $dependsValue = 'data-depends-multi-value="'.$fieldSpec['depends']->values.'"';
            // allow "opposite dependency" - field is relevant only if value of dependent field is NOT X
            $dependsNotValue = 'data-depends-not-values="'.$fieldSpec['depends']->notValues.'"';
        }
        else
        {
            $dependsField = '';
            $dependsValue = '';
            $dependsNotValue = '';
        }

        return array( 'fields' => $dependsField, 'values' => $dependsValue, 'notValue' => $dependsNotValue);
    }

    private function ConfigFieldArray($spec)
    {
        $ret = '';
        if(!isset($spec['nowrapper']) ||  !$spec['nowrapper'])
        {
            $ret .= '<a name="'.$spec['name'].'"></a><fieldset class="itemCollection"><legend>'.$spec['name'].'</legend>';
            $ret .= isset($spec['comment']) ? $spec['comment'] : '';
        }
        $i = 1;
        $fieldIdentifier = $this->getFieldIdentifier($spec);
        $emptySpec = $spec;
        if(isset($spec['value']) && count($spec['value']))
        {
            foreach(clone $spec['value'] as $key => $values)
            {
                $ret .= '<fieldset class="itemCollection"><legend >'.$i++.'&nbsp;';
                if($spec['allowMulti'])
                {
                    $ret .= '<a class="delete">DELETE</a>';
                }
                $ret .= '</legend><div class="collection">';
                foreach($spec['fields'] as $field => $fieldInfo)
                {
                    $dependencyInfoArray = $this->getFieldDependencyAttributes($fieldInfo);

                    $ret .= '<div class="tabItem" '.$dependencyInfoArray['fields'].' '.$dependencyInfoArray['values'].' '.$dependencyInfoArray['notValue'].'>';
                    
                    if(is_object($values))
                    {
                        $spec['fields'][$field]['value'] = $values->$field;
                        if(isset($spec['belongsTo']))
                        {
                            $spec['fields'][$field]['belongsTo'] = $spec['belongsTo'].'['.$spec['name'].']'.'['.$key.']';
                        }
                        else
                        {
                            $spec['fields'][$field]['belongsTo'] = $spec['name'].'['.$key.']';
                        }
                    }
                    else
                    {
                        $spec['fields'][$field]['value'] = $values;
                        $spec['fields'][$field]['name'] = $key;
                        
                        if(isset($spec['belongsTo'])  && $spec['belongsTo'] )
                        {
                            $spec['fields'][$field]['belongsTo'] = $spec['belongsTo'].'['.$spec['name'].']';
                        }
                        else
                        {
                            $spec['fields'][$field]['belongsTo'] = $spec['name'];
                        }

                    }
                    $elements = $this->ConfigField($spec['fields'][$field]);
                    if(is_array($elements))
                    {
                        foreach($elements as $element)
                        {
                             $ret .= $element;
                        }
                    }
                    else
                    {
                        $ret .= $elements;
                    }
                    
                    if($fieldInfo['type'] != 'array')
                    {
//                        $ret .= '<div class="comment">'.(isset($fieldInfo['comment']) ? $fieldInfo['comment'] : "").'</div>';
                    }
                    $ret .= '</div>';
                }
                $ret .= "</div></fieldset>";
            }
        }
        elseif( (isset($spec['ajaxfield']) && $spec['ajaxfield']) || !$spec['allowMulti'] || $spec['type'] == 'select')
        {
/*        $spec = $emptySpec;
        $randomKey = $this->getRandomKey();
        */
            $randomKey = $this->getRandomKey();
        
            $ret .= '<fieldset class="itemCollection" data-id="'.$fieldIdentifier.'"><legend class="num">*&nbsp;<a class="delete">DELETE</a></legend><div class="collection">';
            foreach($spec['fields'] as $field => $fieldInfo)
            {

                $dependencyInfoArray = $this->getFieldDependencyAttributes($fieldInfo);
                
                $ret .= '<div class="tabItem" '.$dependencyInfoArray['fields'].' '.$dependencyInfoArray['values'].' '.$dependencyInfoArray['notValue'].'>';

                if(isset($spec['belongsTo']) && $spec['belongsTo'])
                {
                    $spec['fields'][$field]['belongsTo'] = $spec['belongsTo'].'['.$spec['name'].']['.$randomKey.']';
                }
                else
                {
                    $spec['fields'][$field]['belongsTo'] = $spec['name'].'['.$randomKey.']';
                }

                $elements = $this->ConfigField($spec['fields'][$field]);
                if(is_array($elements))
                {
                    foreach($elements as $element)
                    {
                        $ret .= $element;
                    }
                }
                else
                {
                    $ret .= $elements;
                }

                if($fieldInfo['type'] != 'array')
                {
//                    $ret .= '<div class="comment">'.(isset($fieldInfo['comment']) ? $fieldInfo['comment'] : "").'</div>';
                }
                //$ret .= $element;
                $ret .= "</div>";

            }
            $ret .= "</div></fieldset>";
        }
        if(!isset($spec['nowrapper']) ||  !$spec['nowrapper'])
        {
            if($spec['allowMulti'] && $spec['type'] != 'select')
            {
                $link = $this->buildAddLink($spec);
                $ret .= '
                <div style="clear: both; float: right;" id="'.$fieldIdentifier.'">
                    <a class="add" href="'.$link.'" rel="async" data-field="'.$fieldIdentifier.'">+ Add "'.$spec['name'].'"</a>
                </div>';
            }

            $ret .= '</fieldset>';        
        }
        
        return $ret;
    }
    
    
    private function getRandomKey()
    {
        return rand(0, microtime(true));
    }
    
    
    private function getFieldIdentifier($field)
    {
        $fieldIdentifier = md5 ( isset($field['belongsTo']) ? $field['belongsTo'] . '-' .$field['name'] : $field['name']);
        return $fieldIdentifier;
    }
    
    
    private function buildAddLink($spec)
    {
        $link = $this->view->url( 
            array(
                'action' => 'config-field', 
                'controller' => 'admin', 
                'tab' => $this->view->currentTab,
                'field' => $spec['name'],
                'parent' => isset($spec['parent']) ? $spec['parent'] : '',
                'target' => $this->getFieldIdentifier($spec),
                'belongsTo' => isset($spec['belongsTo']) && $spec['belongsTo'] ? $spec['belongsTo'] : '',
                'noLabel' => 1, //isset($spec['nolabel']) ? $spec['nolabel'] : '0',
            )
        );
        return $link;
        
    }
    
    
    private function loadConfig($section, $name)
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrapOptions = $front->getParam('bootstrap')->getOptions();
/*        if($moduleName)
        {
            $modulesPath = $bootstrapOptions['resources']['frontController']['moduleDirectory'];
            $filename = realpath($modulesPath . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . self::MODULE_CONFIG_FILE);
        }
        else
        {
            $filename = realpath($bootstrapOptions['resources']['config']['config']);
        }
  */      
        $filename = realpath($bootstrapOptions['resources']['config']['config']);
        $confSection = Kms_Resource_Config::getSection($section);
        
        if($confSection)
        {
            return Kms_Resource_Config::getConfiguration($section,$name);
        }
        else
        {
            // try modules
            $modulesPath = $bootstrapOptions['resources']['frontController']['moduleDirectory'];
            $confSection = Kms_Resource_Config::getModuleSection($section);
            if($confSection)
            {

                return Kms_Resource_Config::getModuleConfig($section,$name);
            }
        }
    }
    
}

?>
