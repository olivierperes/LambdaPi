<?php

namespace OlivierPeres\LambdaPi;

/**
 * LambdaPi is a parallel (pi) implementation of a functional (lambda) interface on vectors.
 */
class Vector
{

  const NB_CHILDREN = 4; // number of processes to spawn

  /** @var array */
  private $data; // contents of the vector

  /**
   * @param $data array values that the vector will contain
   */
  public function __construct(array $data)
  {
    $this->data = array_values($data);
  }

  /**
   * Returns a new Vector containing only the first Vector's values for which $callback is true.
   * The callback must be a pure function.
   *
   * @param callable $callback
   * @return static
   */
  public function filter(callable $callback)
  {
    $parent_callback = function($current_result, $new_result)
    {
      return $current_result + $new_result;
    };
    $child_callback = function($first, $last) use($callback)
    {
      $result = [];
      for ($i = $first; $i < $last; $i++) {
        if ($callback($this->data[$i])) {
          $result[$i] = $this->data[$i];
        }
      }
      return $result;
    };
    $result = $this->genericParallel([], $parent_callback, $child_callback);
    ksort($result);
    return new static($result);
  }

  /**
   * Base of parallel methods. Spawns processes to calculate results using $child_callback and
   * gathers the result using $parent_initial_value and $parent_callback.
   *
   * @param mixed $parent_initial_value
   * @param callable $parent_callback
   * @param callable $child_callback
   * @return mixed
   */
  private function genericParallel(
    $parent_initial_value,
    callable $parent_callback,
    callable $child_callback
  ) {
    $read_sockets = [];
    for ($i = 0; $i < static::NB_CHILDREN; $i++) {
      $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
      $pid     = pcntl_fork();

      if ($pid == -1) {
        die('Fork failed');
      } elseif ($pid) {
        /* parent */
        fclose($sockets[0]);
        $read_sockets[] = $sockets[1];
      } else {
        /* child */
        fclose($sockets[1]);

        $slice = round(count($this->data) / static::NB_CHILDREN);
        $first = $i * $slice;
        $last = min(count($this->data), (($i+1) * $slice));

        $result = $child_callback($first, $last);
        fwrite($sockets[0], serialize($result));

        fclose($sockets[0]);
        exit;
      }
    }

    $result = $parent_initial_value;
    while ($read_sockets) {
      $available_sockets = $read_sockets;
      $write_sockets = [];
      $except_sockets = [];
      stream_select($available_sockets, $write_sockets, $except_sockets, null);
      foreach ($available_sockets as $socket) {
        $message = fgets($socket);
        $result = $parent_callback($result, unserialize($message));
        fclose($socket);
      }
      $read_sockets = array_diff($read_sockets, $available_sockets);
    }
    return $result;
  }

  /**
   * Returns a new Vector containing the result of applying $callback to all the values in
   * this Vector. The callback must be a pure function.
   *
   * @param callable $callback
   * @return static
   */
  public function map(callable $callback)
  {
    $parent_callback = function($current_result, $new_result)
    {
      return $current_result + $new_result;
    };
    $child_callback = function($first, $last) use($callback)
    {
      $result = [];
      for ($i = $first; $i < $last; $i++) {
        $result[$i] = $callback($this->data[$i]);
      }
      return $result;
    };
    $result = $this->genericParallel([], $parent_callback, $child_callback);
    ksort($result);
    return new static($result);
  }

  /**
   * Returns the result of $callback($identity, ($callback(Vector[0], ... Vector[n]))).
   * The callback must be a pure function, commutative and associative.
   * The identity value must be such that $callback($identity, $x) == $x.
   * Calling reduce on an empty Vector returns $identity.
   *
   * @param callable $callback
   * @param mixed $identity
   * @return mixed
   */
  public function reduce(callable $callback, $identity)
  {
    $parent_callback = function($current_result, $new_result) use($callback)
    {
      return $callback($current_result, $new_result);
    };
    $child_callback = function($first, $last) use($callback, $identity)
    {
      $result = $identity;
      for ($i = $first; $i < $last; $i++) {
        $result = $callback($result, $this->data[$i]);
      }
      return $result;
    };
    return $this->genericParallel($identity, $parent_callback, $child_callback);
  }

  /**
   * Returns the values contained in this Vector.
   * @return array
   */
  public function toArray()
  {
    return $this->data;
  }

}
