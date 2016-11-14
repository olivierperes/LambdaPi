<?php

use OlivierPeres\LambdaPi\Vector;

class ReduceTest extends PHPUnit_Framework_TestCase {

  public function testEmpty()
  {
    $add = function($x, $y)
    {
      return $x+$y;
    };
    $values = [];
    $this->assertEquals((new Vector($values))->reduce($add, 0), 0);
  }

  public function testReduce()
  {
    $add = function($x, $y)
    {
      return $x+$y;
    };
    $values = [4, 8, 15, 16, 23, 42];
    $this->assertEquals((new Vector($values))->reduce($add, 0), 108);
  }

}
