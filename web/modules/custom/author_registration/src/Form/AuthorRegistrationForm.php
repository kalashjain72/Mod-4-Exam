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
    // Define form fields.
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

    $form['role'] = [
      '#type' => 'radios',
      '#title' => $this->t('Role'),
      '#options' => [
        'blogger' => $this->t('Blogger'),
        'guest_blogger' => $this->t('Guest Blogger'),
      ],
      '#required' => TRUE,
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $values = $form_state->getValues();

    // Create new user.
    $user = User::create();
    $user->setPassword($values['password']);
    $user->enforceIsNew();
    $user->setEmail($values['email']);
    $user->setUsername($values['full_name']);
    $user->addRole($values['role']);
    $user->block();
    $user->save();

    // Send notification to admin.
    $this->sendAdminNotification($user, $values['full_name']);

    // Send thank you email to user.
    $this->sendUserThankYouEmail($values['email']);
  }

  /**
   * Sends an admin notification email.
   *
   * @param \Drupal\user\Entity\User $user
   *   The newly created user.
   * @param string $fullName
   *   The full name of the user.
   */
  private function sendAdminNotification(User $user, $fullName) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'author_registration';
    $key = 'admin_notification';
    $to = 'kalashjain72@gmail.com';
    $params['message'] = 'A new user has submitted the form: ' . $fullName;
    $params['user'] = $user;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addMessage($this->t('Problem in sending message.'));
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Admin notification sent.'));
    }
  }

  /**
   * Sends a thank you email to the user.
   *
   * @param string $email
   *   The user's email address.
   */
  private function sendUserThankYouEmail($email) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'author_registration';
    $key = 'user_thank_you';
    $to = $email;
    $params['message'] = 'Thank you for your submission. We will get back to you soon.';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addMessage($this->t('Problem in sending message.'));
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Thank you email sent.'));
    }
  }

}
