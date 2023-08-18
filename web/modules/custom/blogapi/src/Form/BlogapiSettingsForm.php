<?php

namespace Drupal\blogapi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for the Blog API module.
 */
class BlogapiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'blogapi_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['blogapi.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('blogapi.settings');

    $form['date_range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date Range'),
      '#description' => $this->t('Enter the date range for filtering blogs.'),
      '#default_value' => $config->get('date_range'),
    ];

    $form['authors'] = [
      // Add form element for authors.
    ];

    $form['tags'] = [
      // Add form element for tags.
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('blogapi.settings')
      ->set('date_range', $form_state->getValue('date_range'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
