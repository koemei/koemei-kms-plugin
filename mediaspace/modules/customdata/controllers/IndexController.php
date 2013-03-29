<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Customdata_IndexController extends Kms_Module_Controller_Abstract
{
    
    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('form-field', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }

    public function indexAction()
    {
        // get the module name
        $moduleName = Customdata_Model_Customdata::MODULE_NAME;
        
        // try to get the Customdata from the entry model
        if(isset(Kms_Resource_Models::getEntry()->modules['Customdata']))
        {
            $moduleData = Kms_Resource_Models::getEntry()->modules['Customdata'];
        }
        else
        {
            // if entry model has no customdata values (for example in case of multiple entries fetch, we do not get the customdata for each entry)
            // in this case get customdata for current entry id (from entry model)
            $model = new Customdata_Model_Customdata();
            $moduleData = $model->get(Kms_Resource_Models::getEntry());
        }
        $fields = array();
        if($moduleData && $moduleData->xml)
        {
            // get the fields (from customdata profile)
            $fields = Customdata_Model_Customdata::getCustomdataFields();
            // get the values from customdata
            $values = Customdata_Model_Customdata::getCustomdataValues();
            
            // get private fields from modules.ini
            $privateFields = NULL;
            $privateFieldsConf = Kms_Resource_Config::getModuleConfig($moduleName, 'privateFields');
            if(count($privateFieldsConf))
            {
                $privateFields = $privateFieldsConf->toArray();
            }

            // pass through all the fields to assign the values
            if(count($fields))
            {
                foreach($fields as $id => $field)
                {
                    // skip private fields
                    if(is_array($privateFields) && in_array($id, $privateFields))
                    {
                        unset($fields[$id]);
                        continue;
                    }


                    if(isset($values[$id]))
                    {
                        // convert values to array for simplicity
                        // in the case of multiple fields the value would be an array

                        if(!is_array($values[$id]))
                        {
                            $values[$id] = array($values[$id]);
                        }

                        // iterate over the values
                        foreach($values[$id] as $key => $val)
                        {
                            // if the field is a date field
                            if($field['type'] == 'dateType')
                            {
                                $val = date(Kms_Resource_Config::getModuleConfig($moduleName, 'dateFormat'), $val);
                            }
                            $values[$id][$key] = $val;
                        }

                        // assign values to fields array
                        $fields[$id]['value'] = $values[$id];
                    }
                    else
                    {
                        // assign empty array if there is no value, to avoid warning in the view
                        $fields[$id]['value'] = array();
                    }


                }
            }            
        }
        
        // assign view params
        $this->view->fields = $fields;
    }

    public function editAction()
    {
        //$this->
    }

    public function saveAction()
    {
        
        // action body
    }
    
    public function formFieldAction()
    {
        // action for adding a form field to multiple customdata fields
        $request = $this->getRequest();
        
        // set the specifications for the form fields, received from the url 
        $spec['type'] = $request->getParam('type');
        $spec['name'] = $request->getParam('name');
        $spec['label'] = $request->getParam('label');
        $spec['belongsTo'] = $request->getParam('belongsTo');
        $spec['isMulti'] = true;
        
        $buttonId = $request->getParam('buttonId');

        // generate a form element via the Customdata model
        $model = new Customdata_Model_Customdata();
        $elem = $model->generateFormElement($spec['name'], $spec);
        if($elem && $spec['type'] != 'listType')
        {
            // remove the label (if not a list type)
            $elem->removeDecorator('Label');
            
            // wrap element inside a div
            $elem->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
            $elem->removeDecorator('HtmlTag');
        }
        // render the element
        $elem->render();
        
        // create a target for the javascript callback
        //$this->view->target = 'input[name^="'.$spec['belongsTo'].'['.$request->getParam('target').']["]:last';
        $this->view->target = 'button[data-buttonid='.$buttonId.']';
        
        // get the dateformat for a javascript datepicker 
        $jsDateFormat =  Kms_Resource_Config::getModuleConfig(Customdata_Model_Customdata::MODULE_NAME, 'jsDateFormat');
        
        $locale = $this->_translate->getAdapter()->getLocale();
        // if the field type is datepicker - then set the javascript to initiliaze the field
        $this->view->script = '';
        if($spec['type'] == 'dateType')
        {
            $this->view->script = '$("input.kmsDate").datepicker('.Zend_Json::encode($model->getDatePickerOptions()).');';
        }

        // assign the element to the view (in html form)
        $this->view->element = $elem->__toString();
    }


}





