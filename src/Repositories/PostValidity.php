<?php

namespace Dashifen\WPHandler\Repositories;

use Dashifen\Repository\Repository;

/**
 * Class PostValidity
 *
 * @property-read bool  $valid
 * @property-read array $problems
 * @property-read array $data
 *
 * @package Dashifen\WPHandler\Repositories
 */
class PostValidity extends Repository
{
  protected bool $valid = false;
  protected array $problems = [];
  protected array $data = [];
  
  public function __construct(array $data = [])
  {
    if (!isset($data['valid'])) {
      
      // if we don't explicitly have a valid index within the data parameter,
      // then we'll assume the array we received is a set of problems within
      // the posted data.  then, validity is determined by the lack of those
      // problems as follows:
      
      $data = [
        'valid'    => sizeof($data) === 0,
        'problems' => $data,
      ];
    }
    
    // otherwise, if we did have a valid index, we assume that $data is prepped
    // and ready by the constructing scope to construct this repository.  if it
    // isn't we'll likely end up with some sort of error anyway.
    
    parent::__construct($data);
  }
  
  /**
   * setValid
   *
   * Sets the success property.
   *
   * @param bool $valid
   *
   * @return void
   */
  protected function setValid(bool $valid): void
  {
    $this->valid = $valid;
  }
  
  /**
   * setProblems
   *
   * Sets the problems property.
   *
   * @param array $problems
   *
   * @return void
   */
  protected function setProblems(array $problems): void
  {
    $this->problems = $problems;
  }
  
  /**
   * setData
   *
   * Sets the data property.
   *
   * @param array $data
   *
   * @return void
   */
  protected function setData(array $data): void
  {
    $this->data = $data;
  }
}
