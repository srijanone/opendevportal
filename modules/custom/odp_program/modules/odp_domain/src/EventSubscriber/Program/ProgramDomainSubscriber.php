<?php

namespace Drupal\odp_domain\EventSubscriber\Program;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\odp_domain\Program\ProgramDomainInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\odp_domain\Utility\Program\ProgramUtility;

/**
 * Access Denied node page.
 */
class ProgramDomainSubscriber implements EventSubscriberInterface {

  /**
   * Programs.
   *
   * @var mixed
   */
  protected $tempstore;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Object ProgramDomain.
   *
   * @var Drupal\odp_domain\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * Program utility instance.
   *
   * @var \Drupal\odp_domain\Utility\Program\ProgramUtility
   */
  protected $programUtility;

  /**
   * Current path instance.
   *
   * @var Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store,
    DomainNegotiatorInterface $negotiator,
    EntityTypeManagerInterface $entity_type_manager,
    ProgramDomainInterface $program_domain,
    CurrentPathStack $current_path,
    AccountInterface $current_user,
    ProgramUtility $program_utility
    ) {
    $this->tempstore = $temp_store;
    $this->domainNegotiator = $negotiator;
    $this->entityTypeManager = $entity_type_manager;
    $this->programDomain = $program_domain;
    $this->currentPath = $current_path;
    $this->currentUser = $current_user;
    $this->programUtility = $program_utility;
  }

  /**
   * Entity add page check.
   *
   * @param Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Event instance.
   */
  public function setProgramDomain(GetResponseEvent $event) {
    $this->programDomain->deleteProgramDomainId();
    $active = $this->domainNegotiator->getActiveDomain();
    if ($active) {
      $domainid = explode('_', $this->entityTypeManager->getStorage('domain')->load($active->id())->id());
      if ($domainid[0] == 'group') {
        $this->programDomain->setProgramDomainId($domainid[1]);

        if (in_array($this->programUtility->getProgramType($domainid[1]),
         ['private', 'protected']) && $this->currentUser->isAnonymous() &&
         !in_array($this->currentPath->getPath(),
         ['/user/login', '/user/password', '/user/register'])) {
          $response = new RedirectResponse(Url::fromRoute('user.login')->toString(), 302);
          $response->send();
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['setProgramDomain', 100];
    return $events;
  }

  /**
   * Get program domain.
   */
  public function getProgramDomain() {
    $active = $this->domainNegotiator->getActiveDomain();
    if ($active) {
      $domainid = explode('_', $this->entityTypeManager->getStorage('domain')->load($active->id())->id());
      if ($domainid[0] == 'group') {
        return $domainid[1];
      }
    }
  }

}
