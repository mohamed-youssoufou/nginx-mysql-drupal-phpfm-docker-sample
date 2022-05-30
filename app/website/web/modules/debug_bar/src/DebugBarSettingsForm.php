<?php

namespace Drupal\debug_bar;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds and process a form for debug bar configuration.
 */
final class DebugBarSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'debug_bar_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Position'),
      '#options' => [
        'top_left' => $this->t('Top left'),
        'top_right' => $this->t('Top right'),
        'bottom_left' => $this->t('Bottom left'),
        'bottom_right' => $this->t('Bottom right'),
      ],
      '#default_value' => $this->config('debug_bar.settings')->get('position'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('debug_bar.settings')
      ->set('position', $form_state->getValue('position'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['debug_bar.settings'];
  }

}
