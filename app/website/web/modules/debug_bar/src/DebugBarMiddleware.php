<?php

namespace Drupal\debug_bar;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Debug Bar Middleware.
 *
 * The middleware may run on a non-fully functional Drupal instance. That may
 * happen when a response comes from page cache module. In this case some Drupal
 * sub-systems are not initialized yet. For that reason the debug bar is built
 * in event subscriber. However some performance metrics are added here through
 * placeholder interpolation. This makes them more accurate and helps to avoid
 * caching issues.
 *
 * @see \Drupal\debug_bar\DebugBarEventSubscriber
 */
final class DebugBarMiddleware implements HttpKernelInterface {

  use StringTranslationTrait;

  /**
   * The kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  private $httpKernel;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Constructs a DebugBarMiddleware object.
   */
  public function __construct(HttpKernelInterface $http_kernel, Connection $connection) {
    $this->httpKernel = $http_kernel;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE): Response {
    Database::startLog('debug_bar');

    $response = $this->httpKernel->handle($request, $type, $catch);

    if ($response instanceof AttachmentsInterface) {
      $this->injectDebugBar($request, $response);
    }

    return $response;
  }

  /**
   * Injects debug bar into response body.
   */
  private function injectDebugBar(Request $request, Response $response): void {

    /** @var \Drupal\Core\Render\AttachmentsInterface $response */
    $debug_bar = $response->getAttachments()['debug_bar'] ?? NULL;
    if (!$debug_bar) {
      return;
    }

    $execution_time = 1000 * (\microtime(TRUE) - $request->server->get('REQUEST_TIME_FLOAT'));
    $db_queries = $this->connection->getLogger()->get('debug_bar');
    $memory_usage = \memory_get_peak_usage(TRUE) / 1024 / 1024;
    $anonymous_cache = $response->headers->get('X-Drupal-Cache') ?: 'NONE';
    $dynamic_cache = $response->headers->get('X-Drupal-Dynamic-Cache') ?: 'NONE';

    $tokens = [
      '[execution_time]' => \number_format($execution_time, 1, '.', ''),
      '[db_queries]' => \count($db_queries),
      '[memory_usage]' => \round($memory_usage, 2),
      '[anonymous_cache]' => $anonymous_cache,
      '[dynamic_cache]' => $dynamic_cache,
    ];
    $debug_bar = \strtr($debug_bar, $tokens);

    $content = \str_replace(
      '</body>',
      $debug_bar . '</body>',
      $response->getContent(),
    );
    $response->setContent($content);

  }

}
