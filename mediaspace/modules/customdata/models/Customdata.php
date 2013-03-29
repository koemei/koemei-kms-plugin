<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Customdata_Model_Customdata extends Kms_Module_BaseModel implements Kms_Interface_Model_Entry_Get, Kms_Interface_Model_Entry_ReadyToPublish, Kms_Interface_Model_Entry_List, Kms_Interface_Form_Entry_Edit, Kms_Interface_Model_Entry_Save, Kms_Interface_Model_ViewHook
{
    const MODULE_NAME = 'customdata';

    private $Customdata;
    private static $customDataFields;
    private static $hasDatePicker = false;
    private $_translate;

    /* view hooks */
    public $viewHooks = array
        (
        'postEntryDetails' => array
            (
            'action' => 'index',
            'controller' => 'index',
        )
    );
    /* end view hooks */

    public function __construct()
    {
        $this->Customdata = new Kaltura_Client_Metadata_Type_Metadata();
        $this->_translate = Zend_Registry::get('Zend_Translate');
    }

    private function listCustomData($entryIds)
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if($customdataProfileId)
        {

            $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
            $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;
            $filter->metadataProfileIdEqual = $customdataProfileId;
            $filter->objectIdIn = join(',', $entryIds);
            $cacheTags = array();
            foreach($entryIds as $entryId)
            {
                $cacheTags[] = 'customdata_entry_'.$entryId;
            }

            $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);

            $result = Kms_Resource_Cache::apiGet('customdata', $cacheParams);

            if (!$result)
            {
                $client = Kms_Resource_Client::getAdminClient();
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
                try{
                    $result = $metadataPlugin->metadata->listAction($filter);
                    
                    Kms_Resource_Cache::apiSet('customdata', $cacheParams, $result, $cacheTags);
                }
                catch(Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('customdata: Could not list entry customdata for entries. '.$e->getCode().': '.$e->getMessage());
                    return null;
                }
            }

            return $result;
        }
        else
        {
            return null;
        }
    }

    public function entryReadyToPublish(Application_Model_Entry $model)
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if($customdataProfileId)
        {
            $requiredFields = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'requiredFields');

            // check if all required fields are filled out
            $customdataFields = $this->getCustomdataFields();
            $allowPublish = $model->readyToPublish;
            if($customdataFields && count($requiredFields))
            {
                $customdataValues = $this->getCustomdataValues($model);
                foreach ($requiredFields as $requiredField)
                {
                    // check each field if it exists and the value is set
                    if (!isset($customdataValues[$requiredField]) || !$customdataValues[$requiredField])
                    {
                        $allowPublish = false;
                    }
                }
            }
            return $allowPublish;
        }        
        else
        {
            return $model->readyToPublish;
        }
    }

    private function getCustomData($entryId)
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if($customdataProfileId)
        {
            $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
            $filter->objectIdEqual = $entryId;

            $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;
            $filter->metadataProfileIdEqual = $customdataProfileId;
            $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);

            $result = Kms_Resource_Cache::apiGet('customdata', $cacheParams);
            if (!$result)
            {
                $client = Kms_Resource_Client::getAdminClient();
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
                $result = $metadataPlugin->metadata->listAction($filter);

                if($result && isset($result->objects))// && count($result->objects))
                {
                    Kms_Resource_Cache::apiSet('customdata', $cacheParams, $result);
                }
            }
            return $result;
        }
        else
        {
            return null;
        }

    }

    public function listAction(Application_Model_Entry $model)
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if($customdataProfileId)
        {
            $entryIds = array();
            if(is_array($model->Entries) && count($model->Entries))
            {
                foreach ($model->Entries as $entry)
                {
                    $entryIds[] = $entry->id;
                }

                $results = $this->listCustomData($entryIds);
                if ($results->objects)
                {
                    $customdata = array();
                    foreach ($results->objects as $res)
                    {
                        if(isset($model->Entries[$res->objectId]))
                        {
                            $model->Entries[$res->objectId]->setModuleData($res, 'Customdata');
                        }
                    }
                }
            }
        }
    }

    public function get(Application_Model_Entry $model)
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if($customdataProfileId)
        {
            $entryId = $model->id;
            $results = $this->getCustomData($entryId);

            if ($results->objects)
            {
                $model->setModuleData($results->objects[0], 'Customdata');
                $this->Customdata = $results->objects[0];
            }
            return $this->Customdata;
        }
        else
        {
            return null;
        }
    }

    public function save(Application_Model_Entry $model)
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if($customdataProfileId)
        {
            $front = Zend_Controller_Front::getInstance();

            $entryId = $model->entry->id;
            if ($front->getRequest()->isPost())
            {
                $request = $front->getRequest();

                // get the fields
                $fields = $this->getCustomdataFields();
                // create simpleXML element
                $customDataXML = new SimpleXMLElement('<metadata/>');
                if (is_array($request->getParam('Customdata')))
                {
                    foreach ($request->getParam('Customdata') as $key => $value)
                    {
                        if (!is_array($value))
                        {
                            $value = array($value);
                        }
                        foreach ($value as $val)
                        {
                            if ($val)
                            {
                                // check for valid date if the type is a date
                                if (isset($fields[$key]) && $fields[$key]['type'] == 'dateType')
                                {
                                    // convert the date according to the dateformat
                                    Zend_Date::setOptions(array('format_type' => 'php'));
                                    $zdate = new Zend_Date($val, Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'dateFormat'));
                                    $val = $zdate->getTimestamp();
                                    if (!$val)
                                    {
                                        // must set date to 0 if not we get error from API
                                        $val = 0;
                                    }
                                }

                                $customDataXML->addChild($key, $val);
                            }
                        }
                    }
                    $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
                    if (is_numeric($customdataProfileId))
                    {
                        $client = Kms_Resource_Client::getAdminClient();
                        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

                        // first try to get customdata to see if it exists
                        $customdata = $this->getCustomData($entryId);

                        $newCustomdata = new Kaltura_Client_Metadata_Type_MetadataListResponse();
                        $newCustomdata->objects = array();
                        if (isset($customdata->objects) && isset($customdata->objects[0]))
                        {
                            // customdata exists, we update it
                            $customdataId = $customdata->objects[0]->id;
                            try
                            {
                                $newCustomdata->objects[] = $metadataPlugin->metadata->update($customdataId, $customDataXML->asXML());
                            }
                            catch (Kaltura_Client_Exception $e)
                            {
                                Kms_Log::log('customdata: Failed updating customdata for entry Id ' . $entryId . ', metadataId ' . $customdataId . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::WARN);
                                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                            }
                        }
                        else
                        {
                            // customdata does not exist for this entryId
                            // we must add it
                            try
                            {
                                $newCustomdata->objects[] = $metadataPlugin->metadata->add(Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId'), Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY, $entryId, $customDataXML->asXML());
                            }
                            catch (Kaltura_Client_Exception $e)
                            {
                                Kms_Log::log('customdata: Failed adding customdata for entry Id ' . $entryId . ', profileId ' . Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId') . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::WARN);
                                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                            }
                        }

                        // clear the cache
                        $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
                        $filter->objectIdEqual = $entryId;
                        $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;
                        $filter->metadataProfileIdEqual = $customdataProfileId;

                        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);
                        $cacheTags = array('customdata_entry_'.$entryId);
                        Kms_Resource_Cache::apiClean('customdata', $cacheParams);
                        // save cache again
                        Kms_Resource_Cache::apiSet('customdata', $cacheParams, $newCustomdata);
                    }
                }
            }
        }
    }

    public static function getCustomdataFields()
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        $customDataFields = Kms_Helper_Metadata::getCustomdataFields($customdataProfileId);

        if ($customDataFields)
        {
            self::$customDataFields = $customDataFields;
        }

        return $customDataFields;
    }

    public static function getCustomdataValues($model = null)
    {
        if($model)
        {
            $entry = $model;
        }
        else
        {
            $entry = Kms_Resource_Models::getEntry();
        }
        $customdataValues = null;
        if ($entry)
        {
            $customData = $entry->getModuleData('Customdata');
        }
        
        $customdataValues = Kms_Helper_Metadata::getCustomdataValues($customData,self::$customDataFields);
        
        return $customdataValues;
    }

    public function getCustomdataPostValues($data)
    {
        return $data;
    }

    public function editForm(Application_Form_EditEntry $form)
    {
        // get the front controller instance to retrieve the view
        $front = Zend_Controller_Front::getInstance();
        $view = $front->getParam('bootstrap')->getResource('view');
        $request = $front->getRequest();
        // get the array of fields from the customdata profile
        $fields = self::getCustomdataFields();
        if (!is_null($fields))
        {

            // if form was submitted, get the field values from the post...
            if ($request->isPost())
            {
                $fieldValues = self::getCustomdataPostValues($request->getParam('Customdata'));
            }
            else
            {
                // get customdata values from Kaltura API (parse the xml to an array)
                $fieldValues = self::getCustomdataValues();
            }
            // init elements array
            $elements = array();

            // iterate over the fields
            foreach ($fields as $fieldName => $fieldInfo)
            {
                $j = 0;
                $multi = false;
                if (isset($fieldValues[$fieldName]))
                {
                    if (is_array($fieldValues[$fieldName])) // field has multiple values
                    {
                        // if select list type, then set the list values to the values
                        if ($fieldInfo['type'] == 'listType')
                        {
                            // assign values from existing
                            $fieldInfo['value'] = $fieldValues[$fieldName];
                            // generate the element
                            $elem = self::generateFormElement($fieldName, $fieldInfo);
                            if ($elem)
                            {
                                // add to the elements array
                                $elements[] = $elem;
                            }
                        }
                        else
                        {
                            // other types of fields (multiple values)
                            $x = 0;
                            foreach ($fieldValues[$fieldName] as $i => $multipleValue)
                            {
                                // iterate over the multiple fields
                                // if value is empty, do not display the field, unless it's the first time
                                if ($multipleValue != '' || $x == 0)
                                {
                                    $x++;
                                    $fieldInfo['value'] = $multipleValue;
                                    // when the field has data we set multi to false, to avoid conflicts
                                    // set the belongsto as the fieldsname, and set the fieldname for the form element as $i (a random number)
                                    $fieldInfo['isMulti'] = false;
                                    $multi = true;
                                    $elem = self::generateFormElement((string) $i, $fieldInfo);
                                    if ($elem)
                                    {
                                        if ($j > 0)
                                        {
                                            $elem->removeDecorator('Label');
                                        }
                                        $belongs = $elem->getBelongsTo();
                                        $elem->setBelongsTo($belongs . '[' . $fieldName . ']');
                                        $elem->addDecorator('FormElements', array('separator', '<bla>'));
                                        $elements[] = $elem;
                                        $j++;
                                    }
                                }
                            }
                        }
                    }
                    else
                    {

                        $fieldInfo['value'] = $fieldValues[$fieldName];
                        $elem = self::generateFormElement($fieldName, $fieldInfo);
                        if ($elem)
                        {
                            $elements[] = $elem;
                        }
                    }
                }
                else
                { // form element that should be created but has no values (neither in xml nor submitted)
                    $elem = self::generateFormElement($fieldName, $fieldInfo);
                    if ($elem)
                    {
                        $elements[] = $elem;
                    }
                }

                // add an "ADD" button for adding another element (only for text elements)
                if (($multi || $fieldInfo['isMulti']) && $fieldInfo['type'] != 'listType')
                {
                    $buttonRandomId = (string) round(rand(1, 1000) * microtime(true));

                    $addButton = new Zend_Form_Element_Button('add-' . $fieldName,
                                    array(
                                        'label' => $this->_translate->translate('Add'),
                                        'value' => 'add',
                                        'belongsTo' => 'Customdata[' . $fieldName . ']',
                                        'href' => $view->baseUrl('/customdata/index/form-field/type/' . $fieldInfo['type'] . '/name/' . $fieldName . '/label/' . $fieldInfo['label'] . '/belongsTo/Customdata/target/' . $fieldName . '/buttonId/' . $buttonRandomId),
                                        'rel' => 'async',
                                        'data-buttonid' => $buttonRandomId,
                                    )
                    );
                    //                $addButton->removeDecorator('DtDdWrapper');
                    $addButton->setDecorators(array(
                        'ViewHelper',
                            //                    array('HtmlTag', array('tag' => 'dd', 'class' => 'nolabel'))
                    ));

                    $elements[] = $addButton;
                }
            }

            // add script for datepickers if exist
            // translate the text in the datepicker
            $datePickerOptions = $this->getDatePickerOptions();
            $dateScript = new Kms_Form_Element_Note('customdataDateScript');
            $dateScript->setValue('<script>$("input.kmsDate").datepicker(' . Zend_Json::encode($datePickerOptions) . ');</script>');
            $elements[] = $dateScript;
            $form->addElements($elements);
        }
        else
        {
            return array();
        }
    }

    /**
     *
     * @param string $fieldName
     * @param array $fieldInfo
     * @return Zend_Form_Element
     */
    public function generateFormElement($fieldName, $fieldInfo)
    {
        // prepare the element specifications
        $spec = array(
            'label' => $fieldInfo['label'],
            'belongsTo' => 'Customdata',
        );

        // if the value is set, then add it to the spec
        if (isset($fieldInfo['value']))
        {
            $spec['value'] = $fieldInfo['value'];
        }

        // check if the field is required (from configuration)
        $required = false;
        $requiredFields = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'requiredFields');
        if (count($requiredFields))
        {
            $requiredFields = $requiredFields->toArray();
        }

        if ($requiredFields && is_array($requiredFields) && in_array($fieldName, $requiredFields))
        {
            $required = true;
        }

        // if field is a multiple field type
        if ($fieldInfo['isMulti'] && $fieldInfo['type'] != 'listType')
        {
            // create an array with the field name as the key
            $spec['belongsTo'] = 'Customdata[' . $fieldName . ']';
            // add a random number to the field name
            $fieldName = (string) (rand(1, 1000) * microtime(true));
            $spec['name'] = $fieldName;
        }

        // get the field type
        switch ($fieldInfo['type'])
        {
            case 'textType':
            case 'objectType':
                $elem = new Zend_Form_Element_Text($fieldName, $spec);
                break;
            case 'dateType':
                // set the hasdatepicker flag
                self::$hasDatePicker = true;
                // if value is a timestamp, convert it to the format from the module configuration dateFormat
                $dateFormat = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'dateFormat');
                $jsDateFormat = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'jsDateFormat');

                // set the datepicker's class to "date"
                $spec['class'] = 'kmsDate';
                if (isset($spec['value']) && is_numeric($spec['value']))
                {
                    $spec['value'] = date($dateFormat, $spec['value']);
                }
                $elem = new ZendX_JQuery_Form_Element_DatePicker($fieldName, $spec);
                $elem->setJQueryParams($this->getDatePickerOptions());
                //  $elem->setJQueryParam('dateFormat', $jsDateFormat);
//                $elem->setJQueryParam('container', '1');
                $elem->addValidator(new Zend_Validate_Date(array('format' => $dateFormat)));
                $datepickerid = (string) round(rand(1, 1000) * microtime(true));
                $elem->setAttrib('id', $elem->getId() . '-' . $datepickerid);
                $elem->setAttrib('readOnly', true);
                //$view->inlineScript()->appendScript('$("#edit_entry .date").datepicker() ')
                break;
            case 'listType':
                if (isset($fieldInfo['isMulti']) && $fieldInfo['isMulti'] == true)
                {
                    $elem = new Zend_Form_Element_Multiselect($fieldName, $spec);
                }
                else
                {
                    $elem = new Zend_Form_Element_Select($fieldName, $spec);
                }
                $elem->setMultiOptions($fieldInfo['listValues']);
                break;
            default:
                $elem = '';
                // go to next field if the type doesn't match explicitly
                break;
        }
        if ($elem && $required)
        {
            $elem->setRequired(true);
            $elem->setAllowEmpty(false);
        }


        if ($elem && $fieldInfo['isMulti'] && $fieldInfo['type'] != 'listType')
        {
            $elem->getDecorator('HtmlTag')->setOption('class', 'multi');
            //$elem->removeDecorator('Label');
        }

        return $elem;
    }

    public function getAccessRules()
    {
        $accessrules = array(
            array(
                'controller' => 'customdata:index',
                'actions' => 'index',
                'role' => Kms_Plugin_Access::ANON_ROLE,
            ),
            array(
                'controller' => 'customdata:index',
                'actions' => array('edit', 'save', 'form-field'),
                'role' => Kms_Plugin_Access::PRIVATE_ROLE,
            ),
        );

        return $accessrules;
    }

    public function getDatePickerOptions()
    {
        return array(
            'dateFormat' => Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'jsDateFormat'),
            'closeText' => $this->_translate->translate('Done'),
            'prevText' => $this->_translate->translate('Prev'),
            'nextText' => $this->_translate->translate('Next'),
            'currentText' => $this->_translate->translate('Today'),
            'monthNames' => array(
                $this->_translate->translate('January'),
                $this->_translate->translate('February'),
                $this->_translate->translate('March'),
                $this->_translate->translate('April'),
                $this->_translate->translate('May'),
                $this->_translate->translate('June'),
                $this->_translate->translate('July'),
                $this->_translate->translate('August'),
                $this->_translate->translate('September'),
                $this->_translate->translate('October'),
                $this->_translate->translate('November'),
                $this->_translate->translate('December')
            ),
            'monthNamesShort' => array(
                $this->_translate->translate('Jan'),
                $this->_translate->translate('Feb'),
                $this->_translate->translate('Mar'),
                $this->_translate->translate('Apr'),
                $this->_translate->translate('May'),
                $this->_translate->translate('Jun'),
                $this->_translate->translate('Jul'),
                $this->_translate->translate('Aug'),
                $this->_translate->translate('Sep'),
                $this->_translate->translate('Oct'),
                $this->_translate->translate('Nov'),
                $this->_translate->translate('Dec'),
            ),
            'dayNames' => array(
                $this->_translate->translate('Sunday'),
                $this->_translate->translate('Monday'),
                $this->_translate->translate('Tuesday'),
                $this->_translate->translate('Wednesday'),
                $this->_translate->translate('Thursday'),
                $this->_translate->translate('Friday'),
                $this->_translate->translate('Saturday')
            ),
            'dayNamesShort' => array(
                $this->_translate->translate('Sun'),
                $this->_translate->translate('Mon'),
                $this->_translate->translate('Tue'),
                $this->_translate->translate('Wed'),
                $this->_translate->translate('Thu'),
                $this->_translate->translate('Fri'),
                $this->_translate->translate('Sat')
            ),
            'dayNamesMin' => array(
                $this->_translate->translate('Su'),
                $this->_translate->translate('Mo'),
                $this->_translate->translate('Tu'),
                $this->_translate->translate('We'),
                $this->_translate->translate('Th'),
                $this->_translate->translate('Fr'),
                $this->_translate->translate('Sa')
            ),
            'weekHeader' => $this->_translate->translate('Wk'),
        );
    }

    /**
     * get the list of customdata profiles for the partner for entries objects
     */
    public static function configGetCustomdataProfiles()
    {
    	$response = Kms_Helper_Metadata::getCustomdataProfilesObjects(Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY);
    	$profiles = $response->objects;
    	
    	// remove "app" from list
    	foreach ($profiles as $key=>$profile) {
    		if ($profile->createMode == Kaltura_Client_Metadata_Enum_MetadataProfileCreateMode::APP) {
    			unset($profiles[$key]);
    		}
    	}

    	$ret = array();
    	if(!empty($profiles) && count($profiles))
    	{
    		foreach ($profiles as $profile)
    		{
    			$ret[$profile->id] = $profile->id.': '.$profile->name;
    		}
    	}
    	return $ret;
    }
    
    /**
     * get the list of customdata fields
     */
    public static function configGetCustomdataFields()
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        $fields = self::getCustomdataFields();
        $ret = array();
        if (count($fields))
        {
            foreach ($fields as $name => $field)
            {
                $ret[$name] = $name . ' (' . $field['type'] . ($field['isMulti'] ? ' - multi' : '') . ')';
            }
        }
        return $ret;
    }

}

