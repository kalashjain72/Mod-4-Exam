<?php

namespace Drupal\author_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Author Registration form.
 */
class AuthorRegistrationForm extends FormBase {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs an AuthorRegistrationForm object.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   */
  public function __construct(MailManagerInterface $mail_manager) {
    $this->mailManager = $mail_manager;
  }

  /**
   * Creates an instance of the form class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return static
   *   The instance of this form class.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'author_registration_form';
  }

  /**
   * Builds the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
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
   * Handles form submission.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $values = $form_state->getValues();

    // Check if a user with the same username already exists.
    if ($this->isUsernameTaken($values['full_name'])) {
      \Drupal::messenger()->addError($this->t('The username is already taken. Please choose a different username.'));
      return;
    }

    // Create new user.
    $user = User::create();
    $user->setPassword($values['password']);
    $user->enforceIsNew();
    $user->setEmail($values['email']);
    $user->setUsername($values['full_name']);
    $user->addRole($values['role']);
    $user->block();
    $user->save();

    \Drupal::messenger()->addMessage($this->t('User created successfully.'));

    // Send admin notification.
    $this->sendAdminNotification($user, $values['full_name'], $values['email'], $values['role']);

    // Send welcome email to the user.
    $this->sendWelcomeEmail($user);

    // You can send admin notifications or thank you emails here if needed.
  }

  /**
   * Checks if a username is already taken.
   *
   * @param string $username
   *   The username to check.
   *
   * @return bool
   *   TRUE if the username is taken, FALSE otherwise.
   */
  private function isUsernameTaken($username) {
    $user = user_load_by_name($username);
    return !empty($user);
  }

  /**
   * Sends an admin notification email.
   */
  private function sendAdminNotification($user, $full_name, $email, $role) {
    $module = 'author_registration';
    $key = 'admin_notification';
    $to = 'kalash.jain@innoraft.com';
    $params = [
      'user' => $user,
      'full_name' => $full_name,
      'email' => $email,
      'role' => $role,
    ];
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;

    $result = \Drupal::service('plugin.manager.mail')->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      \Drupal::logger('author_registration')->error('Problem sending admin notification email.');
    }
  }

  /**
   * Sends a welcome email to the user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The newly created user.
   */
  private function sendWelcomeEmail(User $user) {
    // Prepare email parameters.
    $params = [
      'user' => $user,
      'url' => Url::fromRoute('user.login', [], ['absolute' => TRUE])->toString(),
    ];

    // Send email using the SMTP module.
    $this->mailManager->mail(
      'author_registration', // Module name.
      'welcome_email', // Email key
      $user->getEmail(), // Recipient email
      $user->getPreferredLangcode(), // Language code
      $params // Email parameters
    );
  }

}
