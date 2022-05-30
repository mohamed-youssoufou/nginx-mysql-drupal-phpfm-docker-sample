<?php

namespace Drupal\mymodule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class ExampleForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'drupalform_example_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface
    $form_state)
    {
        // Return array of Form API elements.
        $form['company_name'] = [
            "#type" => "textfield",
            "#title"=> $this->t('Company name')
        ];

        $form['submit'] = [
            '#type' => "submit",
            "#value" => $this->t('Soumettre')
        ];

        return $form;
    }
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form,  FormStateInterface
    $form_state)
    {
        if(strlen($form_state->getValue('company_name'))<5){
            $form_state->setErrorByName('company_name', t('Company name is less than 5 characters'));
        }
        // Validation covered in later recipe, required to satisfy interface.
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form,  FormStateInterface
    $form_state)
    {
        // Validation covered in later recipe, required to satisfy interface.
    }
}
