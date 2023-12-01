<?php

namespace Drupal\custom_address_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Ajax\RedirectCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;



class ConfirmDeletionAjax extends FormBase {
  
  protected $formBuilder;
  protected $urlGenerator;

  protected $database;
  public function __construct(FormBuilderInterface $formBuilder, UrlGeneratorInterface $urlGenerator, Connection $database){
    $this->formBuilder = $formBuilder;
    $this->urlGenerator = $urlGenerator;
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('url_generator'),
      $container->get('database')    
  );
  }
 
  public function getFormId() {
    return 'confirm_deletion_ajax';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $aid = NULL) {

    $form['#theme'] = 'deletion_form';

    $form['aid'] = [
      '#type' => 'value',
      '#value' => $aid,
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Are you sure you want to delete this entry?'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm Deletion'),
    ];

      
    return $form;

  }

 

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $aid = $form_state->getValue('aid');

    // If the user confirms deletion, delete and redirect to managepage.

    $query = $this->database->delete('address_entry');
    $query->condition('aid', $aid)
    ->execute();

     // Set a destination for the redirect.
    $form_state->setRedirect('custom_address_book.managepage');

    // Return a response for Ajax.
    $response = new AjaxResponse();

    // Close the modal dialog.
    $response->addCommand(new CloseModalDialogCommand());

     return $response;

  }

}


  

  
