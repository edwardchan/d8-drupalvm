<?php

/**
 * @file
 * Contains \Drupal\nodejs\Nodejs.
 */

namespace Drupal\nodejs;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Nodejs {

  protected $queuedMessages = [];

  protected $contentTokens = [];

  protected $config = NULL;

  protected $httpClient = NULL;

  protected $moduleHandler= NULL;

  protected $logger = NULL;

  public function __construct(Client $http_client, LoggerChannelInterface $logger, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
    $this->moduleHandler = $module_handler;
    $this->config = $config->get('nodejs.config')->get();
  }

  public function getUrl($path) {
    return $this->config['nodejs']['scheme'] . '://' .
           $this->config['nodejs']['host'] . ':' .
           $this->config['nodejs']['port'] . "/$path";
  }

  public function getMessages() {
    return $this->queuedMessages;
  }

  public function getContentTokens() {
    return $this->contentTokens;
  }

  public function enqueueMessage(\StdClass $message) {
    $this->queuedMessages[] = $message;
  }

  public function enqueueContentToken($channel, $token) {
    $this->contentTokens[$channel] = $token;
  }

  public function healthCheck() {
    return $this->httpRequest('nodejs/health/check');
  }

  public function sendMessages() {
    foreach ($this->queuedMessages as $message) {
      $this->sendMessage($message);
    }
    $this->queuedMessages = [];
  }

  public function sendMessage(\StdClass $message) {
    $this->moduleHandler->alter('nodejs_message', $message);

    $options = [
      'method' => 'POST',
      'body' => Json::encode($message),
    ];
    return $this->httpRequest('nodejs/publish', $options);
  }

  public function setUserPresenceList($uid, array $uids) {
    return $this->httpRequest("nodejs/user/presence-list/$uid/" . implode(',', $uids));
  }

  public function logoutUser($token) {
    $options = [
      'method' => 'POST',
    ];
    return $this->httpRequest("nodejs/user/logout/$token", $options);
  }

  public function sendContentTokenMessage($message) {
    $this->moduleHandler->alter('nodejs_content_channel_message', $message);

    $options = [
      'method' => 'POST',
      'body' => Json::encode($message),
    ];
    return $this->httpRequest('nodejs/content/token/message', $options);
  }

  public function sendContentToken($message) {
    $options = [
      'method' => 'POST',
      'body' => Json::encode($message),
    ];
    return $this->httpRequest('nodejs/content/token', $options);
  }

  public function getContentTokenUsers($message) {
    $options = [
      'method' => 'POST',
      'body' => Json::encode($message),
    ];
    return $this->httpRequest('nodejs/content/token/users', $options);
  }

  public function kickUser($uid) {
    $options = [
      'method' => 'POST',
    ];
    return $this->httpRequest("nodejs/user/kick/$uid", $options);
  }

  public function addUserToChannel($uid, $channel) {
    $options = [
      'method' => 'POST',
    ];
    return $this->httpRequest("nodejs/user/channel/add/$channel/$uid", $options);
  }

  public function removeUserFromChannel($uid, $channel) {
    $options = [
      'method' => 'POST',
    ];
    return $this->httpRequest("nodejs/user/channel/remove/$channel/$uid", $options);
  }

  public function addChannel($channel) {
    $options = [
      'method' => 'POST',
    ];
    return $this->httpRequest("nodejs/channel/add/$channel", $options);
  }

  public function checkChannel($channel) {
    return $this->httpRequest("nodejs/channel/check/$channel");
  }

  public function removeChannel($channel) {
    $options = [
      'method' => 'POST',
    ];
    return $this->httpRequest("nodejs/channel/remove/$channel", $options);
  }

  protected function httpRequest($path, array $options = []) {
    $options += [
      'method' => 'GET',
      'timeout' => !empty($this->config['timeout']) ? $this->config['timeout'] : 5,
      'headers' => [],
    ];
    $options['headers'] += [
      'NodejsServiceKey' => $this->config['service_key'],
      'Content-type' => 'application/json',
    ];

    try {
      $response = $this->httpClient->request($options['method'], $this->getUrl($path), $options);
      if ($response->getStatusCode() != 200) {
        return FALSE;
      }
      return (object)Json::decode($response->getBody(TRUE));
    }
    catch (BadResponseException $e) {
      $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
    }
    catch (RequestException $e) {
      $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
    }
    return FALSE;
  }
}

