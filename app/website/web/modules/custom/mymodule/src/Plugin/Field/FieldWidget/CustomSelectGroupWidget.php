<?php

namespace Drupal\mymodule\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'customselectgroup_d' widget. *
 * @FieldWidget(
 * id = "customselectgroup_default",
 * label = @Translation("Real name"),
 * field_types = { 
 * "CustomSelectGroup" 
 *}
 *)
 */
class CustomSelectGroupWidget extends WidgetBase
{
    /**
     * {@inheritdoc}
     */
    public function formElement(
        FieldItemListInterface $items,
        $delta,
        array $element,
        array &$form,
        FormStateInterface $form_state
    ) {
        $element['first_name'] = [
            '#type' => 'textfield', '#title' => t('First name'), '#default_value' => '', '#size' => 25,
            '#required' => $element['#required'],
        ];
        $element['last_name'] = [
            '#type' => 'textfield', '#title' => t('Last name'), '#default_value' => '', '#size' => 25,
            '#required' => $element['#required'],
        ];
        return $element;
    }
}
