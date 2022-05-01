<?php

namespace Storyblok;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Storyblok GraphQLClient.
 */
class GraphQLClient extends BaseClient
{
    private $query;
    private $folder;
    /**
     * @var null|int
     */
    private $perPage;
    private $page;
    private $id;
    /**
     * @var null|mixed|string
     */
    private $item;

    /**
     * @param string $apiKey
     * @param string $apiEndpoint
     * @param string $apiVersion
     * @param mixed  $ssl
     */
    function __construct($apiKey = null, $apiEndpoint = 'gapi.storyblok.com', $apiVersion = 'v1', $ssl = true)
    {
        parent::__construct($apiKey, $apiEndpoint, $apiVersion, $ssl);
        $this->initialize();
    }

    /**
     * @param ResponseInterface $responseObj
     * @param null|mixed        $queryString
     *
     * @return self
     */
    public function responseHandler($responseObj, $queryString = null)
    {
        $httpResponseCode = $responseObj->getStatusCode();
        $data = (string) $responseObj->getBody();
        $jsonResponseData = (array) json_decode($data, true);

        // return response data as json if possible, raw if not
        $this->responseBody = $data && empty($jsonResponseData) ? $data : $jsonResponseData;
        $this->responseCode = $httpResponseCode;
        $this->responseHeaders = $responseObj->getHeaders();

        return $this;
    }

    public function getPayloadQuery()
    {
        return '{ ' . $this->query . ' }';
    }

    /**
     * @param array $payload
     * @param mixed $query
     *
     *@throws ApiException|GuzzleException
     *
     * @return self
     */
    public function query()
    {
        $this->queryItem();
        $this->queryItems();
        $payload = [
            'query' => $this->getPayloadQuery(),
        ];
        //$headers = ['Authorization' => $this->getApiKey()];
        $headers = ['Token' => $this->getApiKey()];
        $headers['Version'] = 'draft';

        try {
            $requestOptions = [
                RequestOptions::JSON => $payload,
                RequestOptions::HEADERS => $headers,
            ];

            if ($this->getProxy()) {
                $requestOptions[RequestOptions::PROXY] = $this->getProxy();
            }

            $responseObj = $this->client->request('POST', '', $requestOptions);

            return $this->responseHandler($responseObj);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new ApiException(self::EXCEPTION_GENERIC_HTTP_ERROR . ' - ' . $e->getMessage(), $e->getCode());
        }
    }

    public function contentById($id, $contentType = 'page')
    {
        $this->id = $id;
        $this->item = $contentType;

        return $this;
    }

    public function contents($contentType = 'page')
    {
        $this->items = $contentType;

        return $this;
    }

    public function languageCodes()
    {
        $this->query .= <<<'GQL'
          Space {
          languageCodes
          }
        GQL;

        return $this;
    }

    public function space()
    {
        if ('' !== $this->query) {
            $this->query .= ', ';
        }

        $this->query .= <<<'GQL'

  Space {
    id
    domain
    languageCodes
    name
    version
  }

GQL;

        return $this;
    }

    public function costs()
    {
        $this->query .= <<<'GQL'
          RateLimit {
          maxCost
          }
        GQL;

        return $this;
    }

    public function folder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    public function perPage($perPage = 25)
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function page($page = 1)
    {
        $this->page = $page;

        return $this;
    }

    private function initialize()
    {
        $this->query = '';
        $this->perPage = null;
        $this->page = null;
        $this->items = null;
        $this->item = null;
        $this->id = null;
    }

    private function queryItem()
    {
        if (null !== $this->item) {
            $contentType = ucfirst($this->item);
            if ('' !== $this->query) {
                $this->query .= ', ';
            }
            $this->query .= <<<GQL
                {$contentType}Item(id: "{$this->id}") {
                  id
                  slug
                  content {
                    _uid
                    component
                    body
                  }
                }
            GQL;
        }
    }

    private function queryItems()
    {
        if (null !== $this->items) {
            $argStrings = [];
            if ('' !== $this->query) {
                $this->query .= ', ';
            }
            if (null !== $this->perPage) {
                $argStrings[] = 'per_page: ' . $this->perPage;
            }
            if (null !== $this->page) {
                $argStrings[] = 'page: ' . $this->page;
            }
            if (null !== $this->folder) {
                $argStrings[] = 'starts_with: "' . $this->folder . '"';
            }
            $argString = '';
            if (\count($argStrings) > 0) {
                $argString = '(' . implode(',', $argStrings) . ')';
            }
            $contentType = ucfirst($this->items);
            $this->query .= <<<GQL
                {$contentType}Items{$argString} {
                    items {
                      id
                      name
                    }
                    total
                }
                GQL;
        }
    }
}
