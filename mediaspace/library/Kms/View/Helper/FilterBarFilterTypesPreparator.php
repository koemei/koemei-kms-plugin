<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc. To
 * learn more:
 * http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Helper class for preparing filter types for filterBar
 *
 * @author yulia.t
 *        
 */
class Kms_View_Helper_FilterBarFilterTypesPreparator {
	public static function FilterBarFilterTypesPreparator() {
		$translator = Zend_Registry::get ( 'Zend_Translate' );
		// create a list of search options
		$filterTypes = array (
				'all' => $translator->translate ( 'All Media' ),
				'video' => $translator->translate ( 'Videos' ),
				'audio' => $translator->translate ( 'Audios' ),
				'image' => $translator->translate ( 'Images' ) 
		);
		// add search video presentations is enabled
		if (Kms_Resource_Config::getConfiguration ( 'application', 'enablePresentations' )) {
			$filterTypes += array (
					'presentation' => $translator->translate ( 'Video Presentations' ) 
			);
		}
		
		// modify filter types inside modules
		$models = Kms_Resource_Config::getModulesForInterface ( 'Kms_Interface_Functional_Entry_Type' );
		foreach ( $models as $model ) {
			$filterTypes = $model->modifyFilterTypes ( $filterTypes );
		}
		
		return $filterTypes;
	}
}
?>