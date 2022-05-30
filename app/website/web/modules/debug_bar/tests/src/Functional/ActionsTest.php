<?php

namespace Drupal\Tests\debug_bar\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * A test for debug bar actions.
 *
 * @group debug_bar
 */
final class ActionsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['debug_bar', 'page_cache', 'dynamic_page_cache'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $user = $this->drupalCreateUser(['view debug bar', 'administer site configuration']);
    $this->drupalLogin($user);
  }

  /**
   * Test callback.
   */
  public function testHomePageLink(): void {
    $this->drupalGet('/some-page');
    $this->click('.debug-bar-item-home a');
    // By default home page is set to user profile for authenticated users.
    $this->assertSession()->addressEquals('/user/' . $this->loggedInUser->id());
  }

  /**
   * Test callback.
   */
  public function testPhpLink(): void {
    $this->click('.debug-bar-item-php a');
    $this->assertSession()->addressEquals('/admin/reports/status/php');
  }

  /**
   * Test callback.
   */
  public function testRunCron(): void {
    $state = $this->container->get('state');
    $cron_last_before = $state->get('system.cron_last');

    // Wait a bit to change request time.
    sleep(1);
    $this->click('.debug-bar-item-cron a');
    $this->assertSession()->pageTextContains('Cron ran successfully.');

    $state->resetCache();
    $cron_last_after = $state->get('system.cron_last');
    self::assertGreaterThan($cron_last_before, $cron_last_after);
  }

  /**
   * Test callback.
   */
  public function testClearCaches(): void {
    $this->click('.debug-bar-item-cache a');
    $this->assertSession()->pageTextContains('Caches cleared.');
  }

  /**
   * Test callback.
   */
  public function testUserProfileLink(): void {
    $this->drupalGet('/some-page');
    $this->click('.debug-bar-item-user a');
    $this->assertSession()->addressEquals('/user/' . $this->loggedInUser->id());
  }

  /**
   * Test callback.
   */
  public function testLogOutLink(): void {
    $this->drupalGet('/some-page');
    $this->click('.debug-bar-item-logout a');
    $this->assertSession()->addressEquals('/');
  }

}
