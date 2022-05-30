<?php

namespace Drupal\Tests\debug_bar\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Base class for Debug Bar tests.
 *
 * @group debug_bar
 */
abstract class BaseDebugBarTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['debug_bar'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $user = $this->drupalCreateUser(['view debug bar']);
    $this->drupalLogin($user);
  }

  /**
   * Sets debug bar position.
   */
  final protected static function setPosition(string $position): void {
    \Drupal::configFactory()->getEditable('debug_bar.settings')
      ->set('position', $position)
      ->save();
  }

  /**
   * Returns the size of the bar and its position relative to the viewport.
   */
  final protected function getBoundingRect(): \stdClass {
    $script = "document.getElementById('debug-bar').getBoundingClientRect()";
    return (object) $this->getSession()
      ->getDriver()
      ->evaluateScript($script);
  }

  /**
   * Returns the size of the bar and its position relative to the viewport.
   */
  final protected function getWindowSize(): \stdClass {
    $script = "{width: window.innerWidth, height: window.innerHeight}";
    return (object) $this->getSession()
      ->getDriver()
      ->evaluateScript($script);
  }

  /**
   * Returns window inner width.
   */
  final protected function getWindowWidth(): int {
    return $this->getSession()
      ->getDriver()
      ->evaluateScript('window.innerWidth');
  }

  /**
   * Returns widows inner height.
   */
  final protected function getWindowHeight(): int {
    return $this->getSession()
      ->getDriver()
      ->evaluateScript('window.innerHeight');
  }

  /**
   * Returns widows inner height.
   */
  final protected function getDebugBarWidth(): int {
    return $this->getBoundingRect()->width;
  }

  /**
   * Asserts open state of debug bar.
   */
  final protected function assertOpenState(): void {
    $toggler = $this->cssSelect('#debug-bar-toggler')[0];
    self::assertEquals($toggler->getAttribute('aria-expanded'), 'true');
    self::assertEquals($toggler->getAttribute('title'), 'Hide debug bar');
    self::assertEquals($toggler->find('css', 'span')->getHtml(), 'Hide debug bar');
    // The actual width varies with content.
    self::assertGreaterThan(500, $this->getDebugBarWidth());
  }

  /**
   * Asserts closed state of debug bar.
   */
  final protected function assertClosedState(): void {
    $toggler = $this->cssSelect('#debug-bar-toggler')[0];
    self::assertEquals($toggler->getAttribute('aria-expanded'), 'false');
    self::assertEquals($toggler->getAttribute('title'), 'Show debug bar');
    self::assertEquals($toggler->find('css', 'span')->getHtml(), 'Show debug bar');
    self::assertEquals(30, $this->getDebugBarWidth());
  }

}
