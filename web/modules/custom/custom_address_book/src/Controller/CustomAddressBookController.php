<?php

namespace Drupal\custom_address_book\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Pager\PagerManagerInterface; 
use Drupal\Core\Link;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;
class CustomAddressBookController extends ControllerBase {

  protected $urlGenerator;
  protected $formBuilder;
  protected $renderer;
  protected $pagerManager;
  protected $database;

  public function __construct(UrlGeneratorInterface $urlGenerator, PagerManagerInterface $pagerManager , FormBuilderInterface $formBuilder, RendererInterface $renderer, Connection $database) {
    $this->urlGenerator = $urlGenerator;
    $this->pagerManager = $pagerManager;
    $this->formBuilder = $formBuilder;
    $this->renderer = $renderer;
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('url_generator'),
      $container->get('pager.manager'),
      $container->get('form_builder'),
      $container->get('renderer'),
      $container->get('database')
    );
  }

  public function managepage() {
    
    $build = [];
  
    // Add form.
    $addForm = $this->formBuilder->getForm('\Drupal\custom_address_book\Form\AddAddressAjax');
    $build['add_form'] = $addForm;
  
   // Get the current page from the query parameters.
  $current_page = \Drupal::request()->query->get('page', 0);

  // Set the number of items per page.
  $items_per_page = 10;

  // Get total number of entries.
  $total_entries = $this->getTotalEntries();

  // Calculate the offset based on the current page and items per page.
  $offset = ($current_page) * $items_per_page;

  // Create a pager element.
  $pager = $this->pagerManager->createPager($total_entries, $items_per_page);

  // Get entries for the current page.
  $entries = $this->getEntries($items_per_page, $offset);

    $build['entries'] = [
      '#theme' => 'address_book_entries',
      '#header' => $this->getEntriesHeader(),
      '#rows' => $entries,
      '#prefix' => '<div id="entries-list-wrapper">',
      '#suffix' => '</div>',
           
        ];


    // Add the pager to the build array.
    $build['pager'] = [
      '#type' => 'pager',
    ];
 
  
    return $build;

  }
  public function getEntriesHeader(){


    $header = [
      'ID',
      'Name',
      'Email',
      'Phone',
      'Date of Birth',
    ];

    return $header;

  }
  // Add a method to get the total number of entries.
public function getTotalEntries() {
  $query = $this->database->select('address_entry', 'a');
  $query->addExpression('COUNT(*)');
  return $query->execute()->fetchField();
}

  public function getEntries($limit, $offset) {

    $offset = max(0, $offset);
    $query = $this->database->select('address_entry', 'a');
    $query->fields('a')
    ->range($offset, $limit)
    ->orderBy('aid', 'ASC');
    $result = $query->execute()->fetchAll();

    $rows = [];
    foreach ($result as $row) {
      $rows[] = [
        $row->aid,
        $row->name,
        $row->email,
        $row->phone,
        $row->dob,
      ];
    }
    error_log('Rows:'.print_r($rows, true));
    return $rows;
  }
 
  public function searchpage() {

    // Add form.
    $searchForm = $this->formBuilder->getForm('\Drupal\custom_address_book\Form\SearchAddressAjax');
    $build['search_form'] = $searchForm;

    return $build;

  }

}


 

