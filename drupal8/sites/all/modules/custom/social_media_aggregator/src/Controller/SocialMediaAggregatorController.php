<?php
/**
 ** @file
 ** Contains \Drupal\hello_world\Controller\SocialMediaAggregatorController.
 **/

namespace Drupal\social_media_aggregator\Controller;

use Drupal\Core\Controller\ControllerBase;

class SocialMediaAggregatorController extends ControllerBase {
	public function content() {
		return array(
			'#type'   => 'markup',
			'#markup' => $this->t('Social Media Aggregator Content'),
		);
	}
}

?>
