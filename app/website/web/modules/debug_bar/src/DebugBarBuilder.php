<?php

namespace Drupal\debug_bar;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Debug bar builder.
 */
final class DebugBarBuilder {

  use StringTranslationTrait;
  use MessengerTrait;

  public const CRON_KEY = 'debug-bar-cron';
  public const CACHE_KEY = 'debug-bar-cache';

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * The state key value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  private $csrfTokenGenerator;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * Constructs Debug Bar builder.
   */
  public function __construct(
    AccountInterface $current_user,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config,
    StateInterface $state,
    DateFormatterInterface $date_formatter,
    CsrfTokenGenerator $csrf_token_generator,
    RendererInterface $renderer,
    TimeInterface $time
  ) {
    $this->currentUser = $current_user;
    $this->moduleHandler = $module_handler;
    $this->config = $config;
    $this->state = $state;
    $this->dateFormatter = $date_formatter;
    $this->csrfTokenGenerator = $csrf_token_generator;
    $this->renderer = $renderer;
    $this->time = $time;
  }

  /**
   * Builds debug bar.
   */
  public function build() {

    $links = $this->buildLinks();

    foreach ($links as $id => $link) {
      if ($link['access']) {
        $links[$id]['title'] = new FormattableMarkup($link['title'], []);
        $links[$id]['attributes']['class'][] = 'debug-bar__item';
        if (isset($link['icon_path'])) {
          $links[$id]['attributes']['style'] = 'background-image: url(' . $link['icon_path'] . ');';
          $links[$id]['attributes']['class'][] = 'debug-bar__icon-item';
        }
      }
      else {
        unset($links[$id]);
      }
    }

    $debug_bar = [
      '#theme' => 'debug_bar',
      '#attributes' => [
        'id' => 'debug-bar',
        'aria-labelledby' => 'debug-bar-heading',
        'class' => [
          'debug-bar',
          'debug-bar_' . Html::cleanCssIdentifier($this->config->get('debug_bar.settings')->get('position')),
          'debug-bar_hidden',
        ],
      ],
      '#items' => [
        '#theme' => 'links',
        '#heading' => [
          'text' => $this->t('Debug Bar'),
          'attributes' => [
            'class' => [
              'visually-hidden',
              'debug-bar__heading',
            ],
            'id' => 'debug-bar-heading',
          ],
          'level' => 'h3',
        ],
        '#attributes' => [
          'class' => 'debug-bar__list',
          'id' => 'debug-bar-items',
          'hidden' => '',
        ],
        '#links' => $links,
      ],
    ];

    return $this->renderer->renderPlain($debug_bar);
  }

  /**
   * Returns list of elements for debug bar.
   */
  private function buildLinks(): array {
    // CSRF tokens depend on session data so the user must be authenticated.
    $is_admin = $this->currentUser->isAuthenticated() &&
      $this->currentUser->hasPermission('administer site configuration');

    $images_path = base_path() . \drupal_get_path('module', 'debug_bar') . '/images';

    $items['debug_bar_item_home'] = [
      'title' => $this->t('Home'),
      'url' => Url::fromRoute('<front>'),
      'icon_path' => $images_path . '/home.png',
      'weight' => 10,
      'access' => TRUE,
    ];

    // @phpcs:ignore Drupal.Semantics.FunctionT.ConcatString
    $hidden_title = '<span class="visually-hidden">' . $this->t('Drupal version') . ':</span> ';
    $items['debug_bar_item_status_report'] = [
      'title' => $hidden_title . \Drupal::VERSION,
      'url' => Url::fromRoute('system.status'),
      'icon_path' => $images_path . '/druplicon.png',
      'attributes' => ['title' => $this->t('View status report')],
      'weight' => 20,
      'access' => $this->currentUser->hasPermission('access site reports'),
    ];

    $title = $this->t('Execution time');
    $hidden_title = '<span class="visually-hidden">' . $title . '</span> ';
    $items['debug_bar_item_execution_time'] = [
      'title' => $hidden_title . $this->t('@time ms', ['@time' => '[execution_time]']),
      'icon_path' => $images_path . '/time.png',
      'attributes' => ['title' => $title],
      'weight' => 30,
      'access' => TRUE,
    ];

    $title = $this->t('Peak memory usage');
    $hidden_title = '<span class="visually-hidden">' . $title . '</span> ';
    $items['debug_bar_item_memory_usage'] = [
      'title' => $hidden_title . $this->t('@memory MB', ['@memory' => '[memory_usage]']),
      'icon_path' => $images_path . '/memory.png',
      'attributes' => ['title' => $title],
      'weight' => 40,
      'access' => TRUE,
    ];

    $title = $this->t('DB queries');
    $hidden_title = '<span class="visually-hidden">' . $title . '</span> ';
    $items['debug_bar_item_db_queries'] = [
      'title' => $hidden_title . '[db_queries]',
      'icon_path' => $images_path . '/db-queries.png',
      'attributes' => ['title' => $this->t('DB queries')],
      'weight' => 50,
      'access' => TRUE,
    ];

    // @phpcs:ignore Drupal.Semantics.FunctionT.ConcatString
    $hidden_title = '<span class="visually-hidden">' . $this->t('PHP version') . ':</span> ';
    $items['debug_bar_item_php'] = [
      'title' => $hidden_title . \phpversion(),
      'url' => Url::fromRoute('system.php'),
      'icon_path' => $images_path . '/php.png',
      'attributes' => [
        'title' => $this->t("Information about PHP's configuration"),
      ],
      'weight' => 60,
      'access' => $is_admin,
    ];

    $cron_last = $this->state->get('system.cron_last');
    $items['debug_bar_item_cron'] = [
      'title' => $this->t('Run cron'),
      'url' => Url::fromRoute('<current>'),
      'icon_path' => $images_path . '/cron.png',
      'attributes' => [
        'title' => $this->t(
          'Last run @time ago',
          ['@time' => $this->dateFormatter->formatInterval($this->time->getRequestTime() - $cron_last)]
        ),
      ],
      'query' => [
        self::CRON_KEY => '1',
        'token' => $this->csrfTokenGenerator->get(self::CRON_KEY),
      ],
      'weight' => 70,
      'access' => $is_admin,
    ];

    // Drupal can be installed to a subdirectory of Git root.
    $git_branch = self::getGitBranch(DRUPAL_ROOT) ?: self::getGitBranch(DRUPAL_ROOT . '/..');

    $title = $this->t('Current Git branch');
    $hidden_title = '<span class="visually-hidden">' . $title . ':</span> ';
    $items['debug_bar_item_git'] = [
      'title' => $hidden_title . $git_branch,
      'icon_path' => $images_path . '/git.png',
      'attributes' => ['title' => $title],
      'weight' => 80,
      'access' => $git_branch,
    ];

    if ($this->moduleHandler->moduleExists('dblog')) {
      $items['debug_bar_item_watchdog'] = [
        'title' => $this->t('Log'),
        'url' => Url::fromRoute('dblog.overview'),
        'icon_path' => $images_path . '/log.png',
        'attributes' => ['title' => $this->t('Recent log messages')],
        'weight' => 90,
        'access' => $this->currentUser->hasPermission('access site reports'),
      ];
    }

    $anonymous_cache_title = $this->t('Page cache');
    $dynamic_page_cache = $this->t('Dynamic page cache');
    $title = <<< HTML
        <span class="visually-hidden">$anonymous_cache_title:</span> [anonymous_cache] /
        <span class="visually-hidden">$dynamic_page_cache:</span> [dynamic_cache]
    HTML;

    $items['debug_bar_item_cache'] = [
      'title' => $title,
      'icon_path' => $images_path . '/cache.png',
      'weight' => 100,
      'access' => TRUE,
    ];
    if ($is_admin) {
      $items['debug_bar_item_cache']['url'] = Url::fromRoute('<current>');
      $items['debug_bar_item_cache']['query'] = [
        self::CACHE_KEY => '1',
        'token' => $this->csrfTokenGenerator->get(self::CACHE_KEY),
      ];
      $items['debug_bar_item_cache']['attributes']['title'] = $this->t('Clear caches');
    }

    if ($this->currentUser->isAnonymous()) {
      $items['debug_bar_item_login'] = [
        'title' => $this->t('Log in'),
        'url' => Url::fromRoute('user.login'),
        'icon_path' => $images_path . '/login.png',
        'weight' => 110,
        'access' => TRUE,
      ];
    }
    else {
      $title = $this->t('Open profile');
      $items['debug_bar_item_user'] = [
        'title' => $this->currentUser->getDisplayName(),
        'url' => Url::fromRoute('entity.user.canonical', ['user' => $this->currentUser->id()]),
        'icon_path' => $images_path . '/user.png',
        'attributes' => ['title' => $title],
        'weight' => 120,
        'access' => TRUE,
      ];
      $items['debug_bar_item_logout'] = [
        'title' => $this->t('Log out'),
        'url' => Url::fromRoute('user.logout'),
        'icon_path' => $images_path . '/logout.png',
        'weight' => 130,
        'access' => TRUE,
      ];
    }

    $this->moduleHandler->alter('debug_bar_items', $items);

    \uasort($items, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    return $items;
  }

  /**
   * Extracts the current checked out Git branch.
   *
   * @param string $directory
   *   Git root directory.
   *
   * @return string|null
   *   The branch name or null if no repository was found.
   */
  private static function getGitBranch(string $directory): ?string {
    $file = $directory . '/.git/HEAD';
    if (\is_readable($file) && ($data = \file_get_contents($file)) && ($data = \explode('/', $data))) {
      return \rtrim(end($data));
    }
    return NULL;
  }

}
