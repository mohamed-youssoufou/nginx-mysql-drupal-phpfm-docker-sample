<?php

namespace Drupal\optgroup_taxonomy_select\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'optgroup_term_select' widget.
 *
 * @FieldWidget(
 *   id = "optgroup_term_select",
 *   label = @Translation("Optgroup Term Select"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class OptgroupTermSelectWidget extends OptionsWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $field_definition = $items->getFieldDefinition();
    $target_bundle = $field_definition->getSettings();
    $voc_list = $target_bundle['handler_settings']['target_bundles'];
    // Initailizing the variable.
    $parent = NULL;
    foreach ($voc_list as $key => $value) {
      $data = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($key);
      foreach ($data as $item) {
        if ($item->depth == 0) {
          $parent = $item->name;
        }
        else {
          $result[$parent][$item->tid] = $item->name;
        }
      }
    }
    $element += [
      '#type' => 'select',
      '#options' => $result,
      '#default_value' => $this->getSelectedOptions($items),
      // Do not display a 'multiple' select box if there is only one option.
      '#multiple' => $this->multiple && count($this->options) > 1,
    ];
    return $element;
  }

}
