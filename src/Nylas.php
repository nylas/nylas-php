<?php

namespace Nylas;

require('../vendor/autoload.php'); // this shouldnt be required
require('./config.php');


use Nylas\Models;
use GuzzleHttp\Client as GuzzleClient;


error_reporting(E_ALL);


class Nylas {

    protected $apiServer = 'https://api.nylas.com';
    protected $authServer = 'https://www.nylas.com';
    protected $apiClient;
    protected $apiToken;
    protected $apiRoot = 'n';

    public function __construct($appID=NULL, $appSecret=NULL, $token=NULL) {
        $this->appID     = $appID;
        $this->appSecret = $appSecret;
        $this->apiToken  = $token;
        $this->apiClient = $this->createApiClient();
    }

    protected function createHeaders() {
        $token = 'Basic '.base64_encode($this->apiToken.':');
        return ['headers' => ['Authorization' => $token,
                              'X-Nylas-API-Wrapper' => 'php']];
    }

    private function createApiClient() {
        return new GuzzleClient(['base_url' => $this->apiServer]);
    }

    public function namespaces() {
        // needs to be a collection
    }

    public function messages($account) {
        $endpoint = '/n/'.$account.'/messages?limit=1';
        $data = $this->createApiClient()->get($endpoint, $this->createHeaders());
        $msgObj = new Models\Message();
        return new NylasModelCollection($msgObj, $this, NS, array());
    }


    public function getData($namespace, $klass, $filter) {
        $suffix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$suffix.'/'.$klass->collectionName;
        $url = $url.'?'.http_build_query($filter);
        $data = $this->apiClient->get($url, $this->createHeaders())->json();
        return $data;
    }
}


class NylasModelCollection {

    private $chunkSize = 50;

    public function __construct($klass, $api, $namespace=NULL, $filter=NULL) {
        $this->klass = $klass;
        $this->api = $api;
        $this->namespace = $namespace;
        $this->filter = $filter;

        if(!array_key_exists('offset', $filter)) {
            $this->filter['offset'] = 0;
        }
    }

    public function items() {
        $offset = 0;

        while (True) {
            $items = $this->_getModelCollection($offset, $this->chunkSize);
            if(!$items) {
                break;
            }

            foreach ($items as $item) {
                yield $item;
            }

            if (count($items) < $this->chunkSize) {
                break;
            }

            $offset += count($items);
        }
    }

    public function first() {
        $results = $this->_getModelCollection(0, 1);
        if ($results) {
            return $results[0];
        }
        return NULL;
    }

    public function all($limit=INF) {
        return $this->_range($this->filter['offset'], $limit);
    }

    private function _range($offset, $limit) {
        $result = array();
        while (count($result) < $limit) {
            $to_fetch = min($limit - count($result), $this->chunkSize);
            $data = $this->_getModelCollection($offset+count($result), $to_fetch);
            $result = array_merge($result, $data);

            if(!$data || count($data) < $to_fetch) {
                break;
            }
        }
        return $result;
    }

    private function _getModelCollection($offset, $limit) {
        $this->filter['offset'] = $offset;
        $this->filter['limit'] = $limit;
        return $this->api->getData($this->namespace, $this->klass, $this->filter);
    }

}


$client = new Nylas(CLIENT, SECRET, TOKEN);
print_r($client->messages(NS)->all(2));
