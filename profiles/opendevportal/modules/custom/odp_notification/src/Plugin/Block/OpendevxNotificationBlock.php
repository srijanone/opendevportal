<?php

namespace Drupal\odp_notification\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\odp_notification\Services\OpendevxNotificationService;

/**
 * Provides a block with list of notification items.
 *
 * @Block(
 *   id = "odp_notification_widget_block",
 *   admin_label = @Translation("Opendevx Notification block"),
 *   category = @Translation("Openddevx Notifications widget")
 * )
 */
class OpendevxNotificationBlock extends BlockBase implements ContainerFactoryPluginInterface, BlockPluginInterface {

  /**
   * Drupal\Core\Session\AccountInterface definition.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;


  /**
   * The NotificationsService.
   *
   * @var \Drupal\odp_notification\Services\OpendevxNotificationService
   */
  protected $notification;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    AccountInterface $current_user,
    Connection $database,
    OpendevxNotificationService $notificationService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->database = $database;
    $this->notification = $notificationService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('database'),
      $container->get('odp_notification.notification'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['block_notification_type'] = [
      '#type'    => 'select',
      '#title'   => $this->t('Notification Content'),
      '#options' => ['As Admin', 'As Logged-In user'],
      '#default_value' => isset($config['block_notification_type']) ? $config['block_notification_type'] : '',
    ];

    $form['block_notification_logs_display'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Skip Display to own activities'),
      '#default_value' => TRUE,
      '#states'        => [
        'visible'      => [
          ':input[name="settings[block_notification_type]"]' => ['value' => '1'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('block_notification_type', $form_state->getValue('block_notification_type'));
    $this->setConfigurationValue('block_notification_logs_display', $form_state->getValue('block_notification_logs_display'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->isAuthenticated());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $notificationMessage = $this->notification->getNotification();
    $notificationType = 1;
    $uid = $this->currentUser->id();
    return [
      '#theme' => 'odp_notification_widget',
      '#uid' => $uid,
      '#notification_type' => $notificationType,
      '#total' => $notificationMessage['totalCount'],
      '#unread' => $notificationMessage['unreadCount'],
      '#notification_list' => $notificationMessage['notificationList'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
