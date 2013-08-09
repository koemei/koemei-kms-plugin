<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc. To
 * learn more:
 * http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Helper class for preparing sorters for filterBar
 *
 * @author yulia.t
 *        
 */
class Kms_View_Helper_FilterBarSortersPreparator {
	public static function FilterBarSortersPreparator($currentType) {
		$translator = Zend_Registry::get ( 'Zend_Translate' );
		
		// create sorters array per type
		switch ($currentType) {
			case "video" :
			case "audio" :
			case "image" :
				$sorters = array (
						'recent' => $translator->translate ( 'Recent' ),
						'views' => $translator->translate ( 'Views' ),
						'name' => $translator->translate ( 'Alphabetical' ),
						'like' => $translator->translate ( 'Likes' ) 
				);
				break;
			case "all" :
			case "presentation" :
				$sorters = array (
						'recent' => $translator->translate ( 'Recent' ),
						'name' => $translator->translate ( 'Alphabetical' ),
						'like' => $translator->translate ( 'Likes' ) 
				);
				break;
			default :
				// sorters array will be created by module implementing the
				// specific filter type
				$specific_type = true;
				$models = Kms_Resource_Config::getModulesForInterface ( 'Kms_Interface_Functional_Entry_Type' );
				foreach ( $models as $model ) {
					// returns an array($type => $sorters)
					$sortersByType = $model->createSorters ();
					$type = key ( $sortersByType );
					// set sorters if the type matches and halt
					if ($type === $currentType) {
						$sorters = reset ( $sortersByType );
						break;
					}
				}
				// if not found model for specific type, initiate sorters array
				if (! isset ( $sorters )) {
					$sorters = array ();
				}
				break;
		}
				
		// edit sorters in modules, if the module implemets
		// Kms_Interface_Model_Entry_FilterSortByType do it by type
		$models = Kms_Resource_Config::getModulesForInterface ( 'Kms_Interface_Model_Entry_FilterSort' );
		
		foreach ( $models as $model ) {
			if (isset ( $specific_type ) && $specific_type) {
				if (in_array ( 'Kms_Interface_Model_Entry_FilterSortByType', class_implements ( $model ) )) {
					$sorters = $model->editSortersByType ( $sorters, $currentType );
				}
			} else {
				$sorters = $model->editSorters ( $sorters );
			}
		}
		//remove likes solrter if lke is not enabled
		if (!Kms_Resource_Config::getConfiguration ( 'application', 'enableLike' ))
		{
			unset($sorters['like']);
		}
		return $sorters;
	}
}
?>