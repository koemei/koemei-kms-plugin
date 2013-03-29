<?php

class Kaltura_Client_SharepointExtension_SharepointExtensionService extends Kaltura_Client_ServiceBase
{
	function __construct(Kaltura_Client_Client $client = null)
	{
		parent::__construct($client);
	}

	function isVersionSupported($serverMajor, $serverMinor, $serverBuild)
	{
		$kparams = array();
		$this->client->addParam($kparams, "serverMajor", $serverMajor);
		$this->client->addParam($kparams, "serverMinor", $serverMinor);
		$this->client->addParam($kparams, "serverBuild", $serverBuild);
		$this->client->queueServiceActionCall("kalturasharepointextension_sharepointextension", "isVersionSupported", $kparams);
		if ($this->client->isMultiRequest())
			return null;
		$resultObject = $this->client->doQueue();
		$this->client->throwExceptionIfError($resultObject);
		$resultObject = (bool) $resultObject;
		return $resultObject;
	}

	function listUiconfs()
	{
		$kparams = array();
		$this->client->queueServiceActionCall("kalturasharepointextension_sharepointextension", "listUiconfs", $kparams);
		if ($this->client->isMultiRequest())
			return null;
		$resultObject = $this->client->doQueue();
		$this->client->throwExceptionIfError($resultObject);
		$this->client->validateObjectType($resultObject, "Kaltura_Client_Type_UiConfListResponse");
		return $resultObject;
	}
}
