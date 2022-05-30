<?php

namespace Drupal\Tests\debug_bar\FunctionalJavascript;

/**
 * Tests the JavaScript functionality of the Debug Bar module.
 *
 * @group debug_bar
 */
final class DebugBarTest extends BaseDebugBarTest {

  /**
   * Test callback.
   */
  public function testDebugBar(): void {
    $this->doTestPosition();
    $this->doTestToggler();
  }

  /**
   * Test callback.
   */
  private function doTestPosition(): void {

    // -- Position: bottom right (default).
    $this->drupalGet('<front>');
    $rect = $this->getBoundingRect();

    self::assertEquals($this->getWindowWidth(), $rect->right);
    self::assertEquals($this->getWindowHeight(), $rect->bottom);

    // -- Position: bottom left.
    self::setPosition('bottom_left');
    $this->drupalGet('<front>');

    $rect = $this->getBoundingRect();

    self::assertEquals(0, $rect->left);
    self::assertEquals($this->getWindowHeight(), $rect->bottom);

    // -- Position: top left.
    self::setPosition('top_left');
    $this->drupalGet('<front>');

    $rect = $this->getBoundingRect();

    self::assertEquals(0, $rect->left);
    self::assertEquals(0, $rect->top);

    // -- Position: top right.
    self::setPosition('top_right');
    $this->drupalGet('<front>');

    $rect = $this->getBoundingRect();

    self::assertEquals($this->getWindowWidth(), $rect->right);
    self::assertEquals(0, $rect->top);
  }

  /**
   * Test callback.
   */
  private function doTestToggler(): void {
    $this->drupalGet('<front>');

    $this->assertClosedState();

    $this->click('#debug-bar-toggler');
    $this->assertOpenState();

    // Reload page and make sure the debug bar is still open.
    $this->drupalGet('<front>');
    $this->assertOpenState();

    $this->click('#debug-bar-toggler');
    $this->assertClosedState();

    // Reload page and make sure the debug bar is still closed.
    $this->drupalGet('<front>');
    $this->assertClosedState();
  }

}
