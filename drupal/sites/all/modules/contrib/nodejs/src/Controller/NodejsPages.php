<?php

/**
 * @file
 * Contains \Drupal\nodejs\Controller\SystemController.
 */

namespace Drupal\nodejs\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

/**
 * Returns responses for Node.js routes.
 */
class NodejsPages {


  /**
   * @todo .
   */
  public function messageHandler() {
    if (!isset($_POST['serviceKey']) || !nodejs_is_valid_service_key($_POST['serviceKey'])) {
      return new JsonResponse(array('error' => 'Invalid service key.'));
    }

    if (!isset($_POST['messageJson'])) {
      return new JsonResponse(array('error' => 'No message.'));
    }

    $message = Json::decode($_POST['messageJson']);
    $response = array();
    switch ($message['messageType']) {
      case 'authenticate':
        $response = nodejs_auth_check($message);
        break;

      case 'userOffline':
        nodejs_user_set_offline($message['uid']);
        break;

      default:
        $handlers = array();
        foreach (module_implements('nodejs_message_callback') as $module) {
          $function = $module . '_nodejs_message_callback';
          $handlers += $function($message['messageType']);
        }
        foreach ($handlers as $callback) {
          $callback($message, $response);
        }
    }
    \Drupal::moduleHandler()->alter('nodejs_message_response', $response, $message);

    return new JsonResponse($response ? $response : array('error' => 'Not implemented'));
  }

}
