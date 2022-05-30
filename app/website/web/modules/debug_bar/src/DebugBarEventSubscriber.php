<?php

namespace Drupal\debug_bar;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\CronInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Debug bar event subscriber.
 */
final class DebugBarEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The cron service.
   *
   * @var \Drupal\Core\CronInterface
   */
  private $cron;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  private $csrfTokenGenerator;


  /**
   * The Debug Bar builder.
   *
   * @var \Drupal\debug_bar\DebugBarBuilder
   */
  private $builder;

  /**
   * Constructs the event subscriber.
   */
  public function __construct(
    AccountInterface $current_user,
    CronInterface $cron,
    CsrfTokenGenerator $csrf_token_generator,
    DebugBarBuilder $builder
  ) {
    $this->currentUser = $current_user;
    $this->cron = $cron;
    $this->csrfTokenGenerator = $csrf_token_generator;
    $this->builder = $builder;
  }

  /**
   * Kernel request event handler.
   */
  public function onKernelRequest(RequestEvent $event): void {

    if (!$this->currentUser->hasPermission('administer site configuration')) {
      return;
    }

    if (!$this->currentUser->hasPermission('view debug bar')) {
      return;
    }

    $request = $event->getRequest();

    $token = $request->query->get('token');
    if (!\is_string($token)) {
      return;
    }

    if ($request->get(DebugBarBuilder::CRON_KEY) && $this->csrfTokenGenerator->validate($token, DebugBarBuilder::CRON_KEY)) {
      $this->cron->run();
      $this->messenger()->addStatus($this->t('Cron ran successfully.'));
      $event->setResponse(new RedirectResponse(Url::fromRoute('<current>')->toString()));
    }

    if ($request->get(DebugBarBuilder::CACHE_KEY) && $this->csrfTokenGenerator->validate($token, DebugBarBuilder::CACHE_KEY)) {
      drupal_flush_all_caches();
      $this->messenger()->addStatus($this->t('Caches cleared.'));
      $event->setResponse(new RedirectResponse(Url::fromRoute('<current>')->toString()));
    }
  }

  /**
   * Kernel response event handler.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $response = $event->getResponse();
    $is_allowed = !$response->isRedirection() &&
                  $response instanceof AttachmentsInterface &&
                  $event->isMasterRequest() &&
                  !$event->getRequest()->isXmlHttpRequest() &&
                  $this->currentUser->hasPermission('view debug bar');
    if ($is_allowed) {
      $response->addAttachments(['debug_bar' => $this->builder->build()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // Run after AuthenticationSubscriber.
      KernelEvents::REQUEST => ['onKernelRequest', 250],
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
