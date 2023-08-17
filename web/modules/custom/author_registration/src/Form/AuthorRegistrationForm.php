<?php

namespace Drupal\author_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Provides the Author Registration form.
 */
class AuthorRegistrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'author_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Build form elements.
    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];
    $form['blogger_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Blogger Type'),
      '#options' => [
        'blogger' => $this->t('Blogger'),
        'guest_blogger' => $this->t('Guest Blogger'),
      ],
      '#default_value' => 'blogger',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Implement form field validation logic if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Create a new user account in blocked state.
    $user = User::create([
      'name' => $form_state->getValue('email'),
      'mail' => $form_state->getValue('email'),
      'pass' => $form_state->getValue('password'),
      'status' => 0,
    ]);
    $user->save();

    // Send a notification email to the admin using the 'new_author_registration' mail plugin.
    $admin_email = 'kalashjain72@gmail.com';
    $params = [
      'subject' => $this->t('New Author Registration'),
      'body' => $this->t('A new author registration has been submitted. Full Name: @full_name, Email: @email', [
        '@full_name' => $form_state->getValue('full_name'),
        '@email' => $form_state->getValue('email'),
      ]),
    ];
    \Drupal::service('plugin.manager.mail')
      ->mail('author_registration', 'new_author_registration', $admin_email, NULL, $params);

    // Send a thank you email to the user using the 'thank_you' mail plugin.
    $params = [
      'subject' => $this->t('Thank You for Your Submission'),
      'body' => $this->t('Thank you for your submission. We will get back to you soon.'),
    ];
    \Drupal::service('plugin.manager.mail')
      ->mail('author_registration', 'thank_you', $form_state->getValue('email'), NULL, $params);

    // Redirect to the homepage after successful form submission.
    $form_state->setRedirect('/');
  }

}
