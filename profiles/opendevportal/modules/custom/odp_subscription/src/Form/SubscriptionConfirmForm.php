<?php

namespace Drupal\odp_subscription\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\odp_subscription\SubscriptionContent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * SubscriptionConfirmForm class constructor.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity object.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $request_stack) {
    $this->account = \Drupal::currentUser();
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
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
    $parameter_bag = $this->requestStack->getCurrentRequest()->query->all();
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
    return Url::fromRoute('entity.node.canonical', ['node' => $this->id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $parameter_bag = $this->requestStack->getCurrentRequest()->query->all();
    $node = $this->entityTypeManager->getStorage('node')->load($this->id);
    if (!isset($parameter_bag['op']) || $parameter_bag['op'] != "remove") {
      return $this->t('Do you want to subscribe to %name?',
      ['%name' => $node->getTitle()]);
    }
    else {
      return $this->t('Do you want to unsubscribe to %name?',
      ['%name' => $node->getTitle()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    $parameter_bag = $this->requestStack->getCurrentRequest()->query->all();
    if (!isset($parameter_bag['op']) || $parameter_bag['op'] != "remove") {
      return $this->t('Subscribe');
    }
    else {
      return $this->t('Unsubscribe');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $node = $this->entityTypeManager->getStorage('node')->load($this->id);
    $parameter_bag = $this->requestStack->getCurrentRequest()->query->all();
    if (!isset($parameter_bag['op']) || $parameter_bag['op'] != "remove") {
      return $this->t('Subscribe to product @node_title today to receive more updates!', [
        '@node_title' => $node->getTitle(),
      ]);
    }
    else {
      return $this->t('Sorry! You will not receive any more emails for product @node_title.', [
        '@node_title' => $node->getTitle(),
      ]);
    }
  }

}
