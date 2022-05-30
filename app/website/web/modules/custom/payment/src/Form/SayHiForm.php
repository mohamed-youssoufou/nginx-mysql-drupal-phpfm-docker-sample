<?php

namespace Drupal\payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SayHiForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['hello_world.custom_salutation'];
    }
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'salutation_configuration_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('hello_world.custom_salutation');

        $userData = \Drupal::service('user.data');
        dump(\Drupal::currentUser());

        //$userData->set("payment", 1, 'oki', ['test']);
        dd($userData->get("payment", 1, 'oki'));
        // dd($config);

        $form['salutation'] = array(
            array(
                '#type' => 'textfield',
                '#title' => $this->t('Salutation'),
                '#description' => $this->t('Please provide the salutationyou want to use.'),
                '#default_value' => $config->get('salutation'),
            ),
            array(
                "#type" => "tel",
                "#title" => "Phone Number",
                "#default_value" => $config->get("Salutation")
            )
        );
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('hello_world.custom_salutation')
            ->set('salutation', $form_state->getValue('salutation'))
            ->save();
        parent::submitForm($form, $form_state);
    }
}
