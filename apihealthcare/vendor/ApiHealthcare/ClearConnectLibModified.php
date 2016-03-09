<?php

	/*
	 * This file has been modified from API Healthcare's version to allow
	 * curl requests from non-secure (non-SSL) domains to work.
	 */

	class ClearConnectLib
	{
		private $parameters = array();
		//private $appKey = "";
		private $sessionKey = "";
		private $url = "";
		private $resultType = "";
		private $userName = "";
		private $password = "";
		
		private $response = "";
		
		public function __construct($url, $userName, $password, $resultType = "xml")  //$appKey, $userName, $password, $resultType
		{
			$this->setUrl($url);
			//$this->setAppKey($appKey);
			$this->setUserName($userName);
			$this->setPassword($password);
			$this->setResultType($resultType);
		}

		public function sendRequest($action)
		{
			if (empty($this->sessionKey))
			{
				throw new Exception("Invalid session key. You must first acquire a session key by calling 'getSessionKey' before executing requests.");
			}
			
			$this->response = $this->__sendRequest($action);
			return $this->response;
		}
		
		public function getSessionKey()
		{
			$response = $this->__sendRequest("getSessionKey", false, "xml");
			$parsedXml = simplexml_load_string($response);
			
			$this->response = $response;
			
			// An error response occurred.
			if (isset($parsedXml->error))
			{
				throw new Exception(sprintf("Message: %s, Detail: %s", (string) $parsedXml->error->errorInformation->message, (string) $parsedXml->error->errorInformation->detail));
			}
			
			$this->sessionKey = (string) $parsedXml->sessionInformation->sessionKey;
			return $this->sessionKey;
		}
		
		public function add($parameterName, $parameterValue)
		{
			$this->parameters[$parameterName] = $parameterValue;
		}
		
		public function setParameters($parameters)
		{
			$this->parameters = $parameters;
		}
		
		public function clear()
		{
			$this->parameters = array();
		}
		
		public function hasError()
		{
			return strpos($this->__response, "errorInformation");
		}
		
		public function sessionExpired()
		{
			if ($this->hasError())
			{
				if (strpos($this->__response, "DS008") || strpos($this->__response, "DS009"))
				{
					return true;
				}
			}
			
			return false;
		}
		
		//-----------------------------------------------------------------------
		// SECTION: Getters and Setters
		//-----------------------------------------------------------------------
		public function getUrl()
		{
			return $this->url;
		}
		
		public function setUrl($value)
		{
			$this->url = $value;
		}
		
		/*public function getAppKey()
		{
			return $this->appKey;
		}*/
		
		/*public function setAppKey($value)
		{
			$this->appKey = $value;
		}*/
		
		public function getUserName()
		{
			return $this->userName;
		}
		
		public function setUserName($value)
		{
			$this->userName = $value;
		}
		
		public function getPassword()
		{
			return $this->password;
		}
		
		public function setPassword($value)
		{
			$this->password = $value;
		}
		
		public function getResultType()
		{
			return $this->resultType;
		}
		
		public function setResultType($value)
		{
			$this->resultType = $value;
		}

		public function getResponse()
		{
			return $this->response;
		}
		
		//-----------------------------------------------------------------------
		// SECTION: Private Methods
		//-----------------------------------------------------------------------
		private function __sendRequest($action, $useSession = true, $resultType = "")
		{
			$parameters = $this->parameters;
			$result = "";
			
			if (empty($resultType)) $resultType = $this->getResultType();
			
			$ch = curl_init(sprintf("%s/clearConnect/2_0/index.cfm", $this->getUrl()));
			if ($ch)
			{
				//$parameters["appKey"] = $this->getAppKey();
				$parameters["action"] = $action;
				$parameters["resultType"] = $resultType;
				
				if ($useSession)
				{
					$parameters["sessionKey"] = $this->sessionKey;
				}
				else
				{
					$parameters["userName"] = $this->getUserName();
					$parameters["password"] = $this->getPassword();
				}
				
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				// NOTE: The following line was added 2/11/2016 by Hite Billes
				// Sending Curl request from domain without SSL fails
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
				
				$result = curl_exec($ch);
				curl_close($ch);
			}
			
			return $result;
		}
	}

?>