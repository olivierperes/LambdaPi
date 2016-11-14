<?php

use OlivierPeres\LambdaPi\Vector;

class FilterTest extends PHPUnit_Framework_TestCase {

  public function testEmpty()
  {
    $even = function($x)
    {
      return $x % 2 == 0;
    };
    $values = [];
    $lambda_pi_result = (new Vector($values))->filter($even)->toArray();
    $standard_result = array_values(array_filter($values, $even));
    $this->assertEquals($lambda_pi_result, $standard_result);
  }

  public function testFilter()
  {
    $even = function($x)
    {
      return $x % 2 == 0;
    };
    $values = [4, 8, 15, 16, 23, 42];
    $lambda_pi_result = (new Vector($values))->filter($even)->toArray();
    $standard_result = array_values(array_filter($values, $even));
    $this->assertEquals($lambda_pi_result, $standard_result);
  }

}
