<?php

use OlivierPeres\LambdaPi\Vector;

class MapTest extends PHPUnit_Framework_TestCase {

  public function testEmpty()
  {
    $double = function($x)
    {
      return $x*2;
    };
    $values = [];
    $lambda_pi_result = (new Vector($values))->map($double)->toArray();
    $standard_result = array_map($double, $values);
    $this->assertEquals($lambda_pi_result, $standard_result);
  }

  public function testMap()
  {
    $double = function($x)
    {
      return $x*2;
    };
    $values = [4, 8, 15, 16, 23, 42];
    $lambda_pi_result = (new Vector($values))->map($double)->toArray();
    $standard_result = array_map($double, $values);
    $this->assertEquals($lambda_pi_result, $standard_result);
  }

}
