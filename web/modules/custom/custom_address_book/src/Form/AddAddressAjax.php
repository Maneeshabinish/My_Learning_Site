<?php

namespace Drupal\custom_address_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;



class AddAddressAjax extends FormBase {
  
  protected $formBuilder;
  public function __construct(FormBuilderInterface $formBuilder){
    $this->formBuilder = $formBuilder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
       $container->get('form_builder'),    
    );
  }
 
  public function getFormId() {
    return 'add_address_ajax';
  }

    public function buildForm(array $form, FormStateInterface $form_state) {

      $form['#theme'] = 'AddAddress_form';

      $form['name'] = [
          '#type' => 'textfield',
          '#title' => t('Name'),
          '#required' => true,
          '#attributes' => ['id' => 'edit-name'], 
          ];
      
      $form['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => true,
      '#attributes' => ['id' => 'edit-email'], 
      ];
  
      $form['phone'] = [
      '#type' => 'tel',
      '#title' => t('Phone'),
      '#attributes' => ['id' => 'edit-phone'], 
      ];
  
      $form['dob'] = [
      '#type' => 'date',
      '#title' => t('Date of Birth'),
      '#attributes' => ['id' => 'edit-dob'], 
      ];
  
      $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add Address'),
      '#ajax' => [
        'callback' => '::submitFormAjaxCallback',
        'event' => 'click',
        'wrapper' => 'add-address-wrapper', // The HTML ID of the element to be replaced.
             
        ],

    ];
       
    return $form;

  }

 

   public function submitForm(array &$form, FormStateInterface $form_state) {

      // Extract the submitted values from the form state.
  $values = $form_state->getValues();

  // Create an array of data to be inserted into the custom table.
  $data = [
    'name' => $values['name'],
    'email' => $values['email'],
    'phone' => $values['phone'],
    'dob' => $values['dob'],
  ];

  // Insert the data into the custom table.
  \Drupal::database()
    ->insert('address_entry') 
    ->fields($data)
    ->execute();


  }

  public function submitFormAjaxCallback(array &$form, FormStateInterface $form_state) {
   
    $response = new AjaxResponse();

    // Add a command to place a message after the form.
    $response->addCommand(new AfterCommand('#add-address-wrapper', $this->t('Address Entry added successfully.')));

    return $response;

  }

  
}