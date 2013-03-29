<?php
// ===================================================================================================
//                           _  __     _ _
//                          | |/ /__ _| | |_ _  _ _ _ __ _
//                          | ' </ _` | |  _| || | '_/ _` |
//                          |_|\_\__,_|_|\__|\_,_|_| \__,_|
//
// This file is part of the Kaltura Collaborative Media Suite which allows users
// to do with audio, video, and animation what Wiki platfroms allow them to do with
// text.
//
// Copyright (C) 2006-2011  Kaltura Inc.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// @ignore
// ===================================================================================================

/**
 * @package Client
 * @subpackage Client
 */
class Kaltura_Client_AttUverseDistribution_Type_AttUverseDistributionFile extends Kaltura_Client_ObjectBase
{
	public function getKalturaObjectType()
	{
		return 'KalturaAttUverseDistributionFile';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		$this->remoteFilename = (string)$xml->remoteFilename;
		$this->localFilePath = (string)$xml->localFilePath;
		$this->assetType = (string)$xml->assetType;
		$this->assetId = (string)$xml->assetId;
	}
	/**
	 * 
	 *
	 * @var string
	 */
	public $remoteFilename = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $localFilePath = null;

	/**
	 * 
	 *
	 * @var Kaltura_Client_Enum_AssetType
	 */
	public $assetType = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetId = null;


}

