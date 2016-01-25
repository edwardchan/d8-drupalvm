<?php

/**
 * @file
 * Contains \Drupal\system\Form\AddUserToChannelForm.
 */

namespace Drupal\nodejs\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * {@inheritdoc}
 */
class AddUserToChannelForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'nodejs_add_user_to_channel_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();
    $form['nodejs_uid'] = array(
      '#type' => 'textfield',
      '#description' => t('The user uid to add to a channel.'),
      '#title' => t('User uid to add'),
    );
    $form['nodejs_channel'] = array(
      '#type' => 'textfield',
      '#description' => t('The name of the channel to give a user access to.'),
      '#title' => t('Channel to add'),
    );
    $form['nodejs_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Add user'),
    );
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match('/^\d+$/', $form_state->getValue('nodejs_uid'))) {
      $form_state->setErrorByName('nodejs_uid', t('Invalid uid - please enter a numeric uid.'));
    }
    if (!preg_match('/^([a-z0-9_]+)$/i', $form_state->getValue('nodejs_channel'))) {
      $form_state->setErrorByName('nodejs_channel', t('Invalid channel name - only numbers, letters and underscores are allowed.'));
    }
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (nodejs_add_user_to_channel($form_state->getValue('nodejs_uid'), $form_state->getValue('nodejs_channel'))) {
      drupal_set_message(t("Added uid %uid to %channel.", array('%uid' => $form_state->getValue('nodejs_uid'), '%channel' => $form_state->getValue('nodejs_channel'))));
    }
    else {
      drupal_set_message(t("Failed to add uid %uid to %channel.", array('%uid' => $form_state->getValue('nodejs_uid'), '%channel' => $form_state->getValue('nodejs_channel'))), 'error');
    }
  }

}
