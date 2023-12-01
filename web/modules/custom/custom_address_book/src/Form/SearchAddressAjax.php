<?php

namespace Drupal\custom_address_book\Form;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SearchAddressAjax extends FormBase {

    use StringTranslationTrait;

  public function getFormId() {
    return 'search_address_ajax';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#theme'] = 'search_form';
   
    $form['search_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search by name:'),
      '#required' => true,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Search'),
      '#ajax' => [
        'callback' => '::submitFormAjaxCallback',
        'event' => 'click',
        'wrapper' => 'search-result-wrapper', // The HTML ID of the element to be replaced.
      ]    
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle form submission if needed.
  }

  public function submitFormAjaxCallback(array &$form, FormStateInterface $form_state) {

      $response = new AjaxResponse();

      $search_name = $form_state->getValue('search_name');
  
      $query = \Drupal::database()->select('address_entry', 'a');
      $query->fields('a', ['aid', 'name', 'email', 'phone', 'dob']);
      $query->condition('a.name', '%' . $search_name . '%', 'LIKE');
      $results = $query->execute();
  
      // Fetch the results.
      $searchresult = $results->fetchAll();
  
      error_log('Search result:'.print_r($searchresult, true));
        // Process and display the search results as needed.
      if (!empty($searchresult)) {
  
        foreach ($searchresult as $result) {
  
          $rows[] = [
            $result->aid,
            $result->name,
            $result->email,
            $result->phone,
            $result->dob,
          ];
  
        }
  
        // Create a table to display the search results.
        $header = ['ID', 'Name', 'Email', 'Phone', 'Date of Birth'];
  
        $table = [
          '#theme' => 'search_result_table',
          '#header' => $header,
          '#rows' => $rows,
        ];
  
        
          // Render the table.
          $output = \Drupal::service('renderer')->render($table);
  
          // Log the output.
          error_log('Search result output: ' . $output);
  
          // Add a command to replace the content of the wrapper with the rendered table.
           $response->addCommand(new AfterCommand('#search-result-wrapper', $output));
  
  
      } else {
          // No matching results found.
          $response->addCommand(new AfterCommand('#search-result-wrapper', $this->t('No matching result found.')));
      }
  
      return $response;
    }
  }

