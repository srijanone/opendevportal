<?php

namespace Drupal\opendevx_subscription\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\opendevx_subscription\SubscriptionContent;
use Drupal\node\Entity\Node;

/**
 * Defines a confirmation form for for Subscription.
 */
class SubscriptionConfirmForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * SubscriptionConfirmForm class constructor.
   */
  public function __construct() {
    $this->account = \Drupal::currentUser();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    // If user is anonymous redirect to the login form before subscribing.
    if ($this->account->isAnonymous()) {
      $response = new RedirectResponse('/user/login', 301);
      $response->send();
      exit(0);
    }
    $this->id = $id;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $parameter_bag = \Drupal::request()->query->all();
    $subscribe_content = new SubscriptionContent();
    if (!isset($parameter_bag['op']) || $parameter_bag['op'] != "remove") {
      if ($subscribe_content->setSubscription($this->id) !== TRUE) {
        $message = $this->t('You are already subscribed to this product.');
      }
      else {
        $message = $this->t('You have successfully subscribed.');
      }
    }
    elseif ($parameter_bag['op'] == "remove" &&
      $subscribe_content->deleteSubscription($this->id) === TRUE) {
      $message = $this->t('You have successfully un-subscribed.');
    }
    \Drupal::messenger()->addMessage($message);

    return $form_state->setRedirectUrl(Url::fromRoute('entity.node.canonical', ['node' => $this->id]));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "confirm_subscription_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.node.canonical', ['node' => 5]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $node = Node::load($this->id);
    return $this->t('Do you want to subscribe to %name?',
      ['%name' => $node->getTitle()]);
  }

}
