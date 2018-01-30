<?php
/*
* This file is part of the surveymonkey-v3-api-php package.
*
* (c) Gassan Idriss <ghassani@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @author Gassan Idriss <ghassani@gmail.com>
*/

namespace Spliced\SurveyMonkey;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Exception\ClientException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Spliced\SurveyMonkey\SurveyMonkeyException;
use Spliced\SurveyMonkey\Response;
use Spliced\SurveyMonkey\Api;

/**
* Client
**/
class Client
{

	/** @const string */
	const BASE_ENDPOINT = 'https://api.surveymonkey.net/v3/';
	
	/** @var GuzzleHttp\Client */
	protected $httpClient;

	/** @var string */
	protected $apiKey;

	/** @var string */
	protected $accessToken;

	/**
	* Include all traits to expose API methods
	*/
	use Api\CommonTrait;
	use Api\UsersTrait;
	use Api\SurveysTrait;
	use Api\CollectorsTrait;
	use Api\ResponsesTrait;
	use Api\ContactTrait;
	use Api\WebooksTrait;
	use Api\BenchmarkTrait;
	use Api\ErrorsTrait;

	/**
	* Constructor
	*
	* @return Client
	*/
	public function __construct($apiKey, $accessToken)
	{
		$this->apiKey = $apiKey;
		$this->accessToken = $accessToken;
		$this->initHttpClient();
	}

	/**
	* setAccessToken
	*
	* @param string $accessToken
	*
	* @return Client
	*/
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
		$this->initHttpClient();
		return $this;
	}

	/**
	* getAccessToken
	*
	* @return string
	*/
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	* setApiKey
	*
	* @param string $apiKey
	*
	* @return Client
	*/
	public function setApiKey($apiKey)
	{
		$this->apiKey = $apiKey;
		return $this;
	}

	/**
	* getApiKey
	*
	* @return string
	*/
	public function getApiKey()
	{
		return $this->apiKey;
	}

	/**
	* getHttpClient
	*
	* @return \GuzzleHttp\Client
	*/
	public function getHttpClient()
	{
		return $this->httpClient;
	}

	/**
	* sendRequest
	* 
	* @param RequestInterface
	*
	* @return Response
	*/
	public function sendRequest(RequestInterface $request)
	{

		try {
			$response = $this->httpClient->send($request);
		} catch (ClientErrorResponseException $e) {
			return new Response($request, $e->getResponse());
		} catch (ClientException $e) {
			return new Response($request, $e->getResponse());
		} catch (\Exeption $e) {
			throw new SurveyMonkeyException($e->getMessage(), $e->getCode(), $e);
		}
		
		return new Response($request, $response);	
	}

	/**
	* createRequest
	* 
	* @param string $method
	* @param string $uri
	* @param array $options - Guzzle compatible request options
	*
	* @return RequestInterface
	*/
	private function createRequest($method, $uri, array $options = [], $body = '{}')
	{
		$ret = new Request($method, $uri, $options, is_array($body) ? json_encode($body, JSON_FORCE_OBJECT) : $body);

		if (isset($options['query'])) {
			$uri = $ret->getUri()->withQuery(is_array($options['query']) ? http_build_query($options['query']) : $options['query']);
			return $ret->withUri($uri, true);
		}

		return $ret;
	}

	/**
	* initHttpClient
	*
	* @return void
	*/
	private function initHttpClient()
	{
		$this->httpClient = new HttpClient([ 
			'base_uri' => static::BASE_ENDPOINT,
			'headers'  => [
				'User-Agent'	=> 'ghassani/surveymonkey-v3-api-php',
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->getAccessToken(),
			]
		]);
	}
}
