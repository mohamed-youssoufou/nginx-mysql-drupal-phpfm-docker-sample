<?php

namespace Drupal\Tests\debug_bar\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Regex;
use Trafaret\PhpUnit\TrafaretTrait;
use Trafaret\Trafaret;

/**
 * Debug bar test.
 *
 * @group debug_bar
 */
final class DebugBarTest extends BrowserTestBase {

  use TrafaretTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['debug_bar'];

  /**
   * Test callback.
   *
   * @noinspection HtmlUnknownTarget
   * @noinspection CssUnknownTarget
   */
  public function testDebugBar(): void {

    // -- Unprivileged user.
    $user = $this->drupalCreateUser(['view debug bar']);
    $this->drupalLogin($user);

    $this->drupalGet('<front>');
    $debug_bar = $this->xpath('//div[@id = "debug-bar"]')[0];

    $expected_html = <<< 'HTML'
    <div id="debug-bar" aria-labelledby="debug-bar-heading" class="debug-bar debug-bar_bottom-right debug-bar_hidden">
      <button id="debug-bar-toggler" class="debug-bar__toggler" aria-expanded="false" aria-controls="debug-bar-items" title="Show debug bar">
        <span class="visually-hidden">Show debug bar</span>
      </button>
      <h3 class="visually-hidden debug-bar__heading" id="debug-bar-heading">Debug Bar</h3>
      <ul class="debug-bar__list" id="debug-bar-items" hidden>
        <li class="debug-bar-item-home">
          <a href="{{ base_path }}" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}home.png);">Home</a>
        </li>
        <li class="debug-bar-item-execution-time">
          <span title="Execution time" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}time.png);">
          <span class="visually-hidden">Execution time</span>{{ execution_time }} ms</span>
        </li>
        <li class="debug-bar-item-memory-usage">
          <span title="Peak memory usage" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}memory.png);">
            <span class="visually-hidden">Peak memory usage</span> {{ memory_usage }} MB
          </span>
        </li>
        <li class="debug-bar-item-db-queries">
          <span title="DB queries" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}db-queries.png);">
             <span class="visually-hidden">DB queries</span> {{ db_queries }}
          </span>
        </li>
        <li class="debug-bar-item-git">
          <span title="Current Git branch" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}git.png);">
          <span class="visually-hidden">Current Git branch:</span> {{ git_branch }}</span>
        </li>
        <li class="debug-bar-item-cache">
          <span class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}cache.png);">
          <span class="visually-hidden">Page cache:</span> NONE /
          <span class="visually-hidden">Dynamic page cache:</span> HIT</span>
        </li>
        <li class="debug-bar-item-user">
          <a href="{{ base_path }}user/{{ user_id }}" title="Open profile" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}user.png);">{{ user_name}}</a>
        </li>
        <li class="debug-bar-item-logout">
          <a href="{{ base_path }}user/logout" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}logout.png);">Log out</a>
        </li>
      </ul>
    </div>
    HTML;

    $this->assertStringByTrafaret(
      new Trafaret($expected_html, $this->getConstraints()),
      $debug_bar->getOuterHtml(),
    );

    // -- Privileged user.
    $user = $this->drupalCreateUser(
      ['view debug bar', 'administer site configuration', 'access site reports'],
    );
    $this->drupalLogin($user);

    $this->drupalGet('<front>');
    $debug_bar = $this->xpath('//div[@id = "debug-bar"]')[0];
    $expected_html = <<< 'HTML'
    <div id="debug-bar" aria-labelledby="debug-bar-heading" class="debug-bar debug-bar_bottom-right debug-bar_hidden">
      <button id="debug-bar-toggler" class="debug-bar__toggler" aria-expanded="false" aria-controls="debug-bar-items" title="Show debug bar">
        <span class="visually-hidden">Show debug bar</span>
      </button>
      <h3 class="visually-hidden debug-bar__heading" id="debug-bar-heading">Debug Bar</h3>
      <ul class="debug-bar__list" id="debug-bar-items" hidden>
        <li class="debug-bar-item-home">
          <a href="{{ base_path }}" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}home.png);">Home</a>
        </li>
        <li class="debug-bar-item-status-report">
            <a href="{{ base_path }}admin/reports/status" title="View status report" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}druplicon.png);">
            <span class="visually-hidden">Drupal version:</span> {{ core_version }}</a>
        </li>
        <li class="debug-bar-item-execution-time">
          <span title="Execution time" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}time.png);">
          <span class="visually-hidden">Execution time</span>{{ execution_time }} ms</span>
        </li>
        <li class="debug-bar-item-memory-usage">
          <span title="Peak memory usage" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}memory.png);">
            <span class="visually-hidden">Peak memory usage</span> {{ memory_usage }} MB
          </span>
        </li>
        <li class="debug-bar-item-db-queries">
          <span title="DB queries" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}db-queries.png);">
             <span class="visually-hidden">DB queries</span> {{ db_queries }}
          </span>
        </li>
        <li class="debug-bar-item-php">
         <a href="{{ base_path }}admin/reports/status/php" title="Information about PHP's configuration" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}php.png);">
         <span class="visually-hidden">PHP version:</span> {{ php_version }}</a>
        </li>
        <li class="debug-bar-item-cron">
          <a href="{{ current_path }}?debug-bar-cron=1&amp;token={{ token }}" title="Last run {{ last_run }} sec ago" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}cron.png);">Run cron</a>
        </li>
        <li class="debug-bar-item-git">
          <span title="Current Git branch" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}git.png);">
          <span class="visually-hidden">Current Git branch:</span> {{ git_branch }}</span>
        </li>
        <li class="debug-bar-item-cache">
          <a href="{{ current_path }}?debug-bar-cache=1&amp;token={{ token }}" style="background-image: url({{ image_path }}cache.png);">
            <span class="visually-hidden">Page cache:</span> NONE /
            <span class="visually-hidden">Dynamic page cache:</span> HIT</a>
        </li>
        <li class="debug-bar-item-user">
          <a href="{{ base_path }}user/{{ user_id }}" title="Open profile" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}user.png);">{{ user_name}}</a>
        </li>
        <li class="debug-bar-item-logout">
          <a href="{{ base_path }}user/logout" class="debug-bar__item debug-bar__icon-item" style="background-image: url({{ image_path }}logout.png);">Log out</a>
        </li>
      </ul>
    </div>
    HTML;

    $this->assertStringByTrafaret(
      new Trafaret($expected_html, $this->getConstraints()),
      $debug_bar->getOuterHtml(),
    );
  }

  /**
   * Returns constraint list for a trafaret.
   */
  private function getConstraints(): array {
    return [
      'base_path' => new EqualTo(\base_path()),
      'image_path' => new EqualTo(\base_path() . \drupal_get_path('module', 'debug_bar') . '/images/'),
      'user_name' => new EqualTo($this->loggedInUser->getDisplayName()),
      'user_id' => new EqualTo($this->loggedInUser->id()),
      'php_version' => new EqualTo(\PHP_VERSION),
      'current_path' => new EqualTo(\base_path() . 'user/' . $this->loggedInUser->id()),
      'last_run' => new PositiveOrZero(),
      'git_branch' => new Regex('/^\d+\.\d+\./'),
      'core_version' => new Regex('/^\d+\.\d+\.\d+/'),
      'execution_time' => new Positive(),
      'memory_usage' => new Positive(),
      'db_queries' => new Positive(),
    ];
  }

}
