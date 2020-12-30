<?php

namespace Drupal\odp_subscription\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\odp_subscription\SubscriptionContent;

/**
 * Defines a confirmation form for Subscription.
 */
class SubscriptionBlockForm extends FormBase {

  /**
   * Account variable.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;


  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * SubscriptionBlockForm class constructor.
   */
  public function __construct() {
    $this->account = $this->currentUser();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "odp_subscription_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = $this->getRouteMatch()->getParameter('node');
    if (!is_object($node)) {
      return FALSE;
    }
    $this->id = $node->id();
    $subscribe_content = new SubscriptionContent();
    $form['actions']['#type'] = 'actions';
    $subscribed_items = $subscribe_content->getSubscribedContent();
    $subscribed_items = $subscribed_items ? array_column($subscribed_items, 'target_id') : [];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'subscribe',
      '#value' => $this->t('Subscribe'),
    ];
    if ($subscribed_items && in_array($this->id, $subscribed_items)) {
      $form['actions']['submit']['#value'] = $this->t('Unsubscribe');
      $form['actions']['submit']['#name'] = 'unsubscribe';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // If user is anonymous redirect to the login form before subscribing.
    if ($this->account->isAnonymous()) {
      return $form_state->setRedirectUrl(Url::fromUserInput('/user/login',
       [
         'query' => [
           'destination' => Url::fromUserInput('/subscribe/' .
          $this->id . '/confirm')->toString(),
         ],
         'absolute' => TRUE,
       ]
      ));
    }
    $url = Url::fromUserInput('/subscribe/' . $this->id . '/confirm');

    if ($form_state->getTriggeringElement()['#name'] == 'unsubscribe') {
      $url = Url::fromUserInput('/subscribe/' .
      $this->id . '/confirm?op=remove');
    }

    return $form_state->setRedirectUrl($url);
  }

}
