<?php

namespace Drupal\opendevx_core\EventSubscriber\Program;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\opendevx_core\Program\ProgramDomainInterface;

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
   * @var Drupal\opendevx_core\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store,
    DomainNegotiatorInterface $negotiator,
    EntityTypeManagerInterface $entity_type_manager,
    ProgramDomainInterface $program_domain
    ) {
    $this->tempstore = $temp_store;
    $this->domainNegotiator = $negotiator;
    $this->entityTypeManager = $entity_type_manager;
    $this->programDomain = $program_domain;
  }

  /**
   * Entity add page check.
   *
   * @param GetResponseEvent $event
   */
  public function setProgramDomain(GetResponseEvent $event) {
    $domain_storage = $this->entityTypeManager->getStorage('domain');
    $this->programDomain->deleteProgramDomainId();
    $active = $this->domainNegotiator->getActiveDomain();
    if ($active) {
      $group_domain = $domain_storage->load($active->id());
      $domainid = explode('_', $group_domain->id());
      if ($domainid[0] == 'group') {
        $this->programDomain->setProgramDomainId($domainid[1]);
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

}
