<?php

namespace Drupal\custom_address_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;

class EditAddressAjax extends FormBase {

  public function getFormId() {
    return 'edit_address_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $aid = NULL) {
    // Load the address entry from the database based on the $aid parameter.
    
    $address_entry = $this->loadAddressEntry($aid);

   
    // Add form elements and prepopulate with data from $address_entry.
    $form['#theme'] = ['edit_form'];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $address_entry->name,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $address_entry->email,
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone'),
      '#default_value' => $address_entry->phone,
    ];
  

    $form['dob'] = [
    '#type' => 'date',
    '#title' => $this->t('Date of Birth'),
    '#default_value' => date('Y-m-d', strtotime($address_entry->dob)),
    ];

 
    // Store the address entry ID in a hidden field.
    $form['aid'] = [
      '#type' => 'hidden',
      '#value' => $address_entry->aid,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    
    return $form;
    
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $id = $form_state->getValue('aid');
    // Update the address entry in the database based on the submitted values.
    $this->updateAddressEntry($id, $form_state->getValues());

    // Set a destination for the redirect.
    $form_state->setRedirect('custom_address_book.managepage');

    // Return a response for Ajax.
    $response = new AjaxResponse();

     // Close the modal dialog.
     $response->addCommand(new CloseModalDialogCommand());


     return $response;
  }

  protected function loadAddressEntry($aid) {
    // Load the address entry from the database and return it.
    $query = \Drupal::database()->select('address_entry', 'a')
      ->fields('a')
      ->condition('a.aid', $aid)
      ->execute();
      $address_entry = $query->fetchObject(); 
  
    return $address_entry;
  }

  protected function updateAddressEntry($aid, array $values) {
    // Update the address entry in the database based on the submitted values.
    $fields = [
      'name' => $values['name'],
      'email' => $values['email'],
      'phone' => $values['phone'],
      'dob' => $values['dob'],
    ];

 
    \Drupal::database()->update('address_entry')
      ->fields($fields)
      ->condition('aid', $aid)
      ->execute();
  }
}
