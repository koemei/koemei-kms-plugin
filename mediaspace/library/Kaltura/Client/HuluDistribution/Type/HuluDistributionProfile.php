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
class Kaltura_Client_HuluDistribution_Type_HuluDistributionProfile extends Kaltura_Client_ContentDistribution_Type_ConfigurableDistributionProfile
{
	public function getKalturaObjectType()
	{
		return 'KalturaHuluDistributionProfile';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		$this->sftpHost = (string)$xml->sftpHost;
		$this->sftpLogin = (string)$xml->sftpLogin;
		$this->sftpPass = (string)$xml->sftpPass;
		$this->seriesChannel = (string)$xml->seriesChannel;
		$this->seriesPrimaryCategory = (string)$xml->seriesPrimaryCategory;
		if(empty($xml->seriesAdditionalCategories))
			$this->seriesAdditionalCategories = array();
		else
			$this->seriesAdditionalCategories = Kaltura_Client_Client::unmarshalItem($xml->seriesAdditionalCategories);
		$this->seasonNumber = (string)$xml->seasonNumber;
		$this->seasonSynopsis = (string)$xml->seasonSynopsis;
		$this->seasonTuneInInformation = (string)$xml->seasonTuneInInformation;
		$this->videoMediaType = (string)$xml->videoMediaType;
		if(!empty($xml->disableEpisodeNumberCustomValidation))
			$this->disableEpisodeNumberCustomValidation = true;
		if(count($xml->protocol))
			$this->protocol = (int)$xml->protocol;
		$this->asperaHost = (string)$xml->asperaHost;
		$this->asperaLogin = (string)$xml->asperaLogin;
		$this->asperaPass = (string)$xml->asperaPass;
		if(count($xml->port))
			$this->port = (int)$xml->port;
		$this->passphrase = (string)$xml->passphrase;
		$this->asperaPublicKey = (string)$xml->asperaPublicKey;
		$this->asperaPrivateKey = (string)$xml->asperaPrivateKey;
	}
	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpHost = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpLogin = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpPass = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $seriesChannel = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $seriesPrimaryCategory = null;

	/**
	 * 
	 *
	 * @var array of KalturaString
	 */
	public $seriesAdditionalCategories;

	/**
	 * 
	 *
	 * @var string
	 */
	public $seasonNumber = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $seasonSynopsis = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $seasonTuneInInformation = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $videoMediaType = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $disableEpisodeNumberCustomValidation = null;

	/**
	 * 
	 *
	 * @var Kaltura_Client_ContentDistribution_Enum_DistributionProtocol
	 */
	public $protocol = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $asperaHost = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $asperaLogin = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $asperaPass = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $port = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $passphrase = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $asperaPublicKey = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $asperaPrivateKey = null;


}

