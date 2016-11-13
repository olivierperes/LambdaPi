# LambdaPi

LambdaPi is a parallel (π) implementation to a functional (λ) interface on vectors in PHP. It
provides counterparts to PHP’s [array_map](http://php.net/manual/fr/function.array-map.php),
[array_filter](http://php.net/manual/fr/function.array-filter.php) and
[array_reduce](http://php.net/manual/fr/function.array-reduce.php) functions that take advantage of
multicore and multiprocessor systems.

This code is initially intended as a demo to show the benefits of a functional approach to
programming on collections. However, it does work and should be usable in a production environment.

## Basic example:
```php
use OlivierPeres\LambdaPi\Vector;

$values = [4, 8, 15, 16, 23, 42];
$vector = new Vector($values);
echo $vector->map(function($x) { return $x*7; })
            ->filter(function($x) { return $x % 2 == 0; })
            ->reduce(function($x, $y) { return $x + $y; }, 0);
```

This creates a Vector containing the values [4, 8, 15, 16, 23, 42]. Then, using the **map** method,
a new Vector is created, containing the original values multiplied by 7. Afterwards, the **filter**
method creates a new Vector containing only the even values. Finally, the **reduce** method is used
to calculate the sum of the elements of that last vector.

Of course, the point is to do this kind of calculations on very large arrays. The execution time is
then almost divided by the number of available processors, as compared to the time taken by the
regular PHP functions. *Almost* because there is a cost involved in creating and managing processes
and interprocess communication.

## Methods

The Vector class provides the following methods.

* `__construct(array $data)` : build a new Vector containing the given data.
* `filter(callable $callback)` : apply $callback on each element of the Vector and return a new
Vector containing only the elements for which $callback returned **true**. The callback must be a
pure function, i.e. it must not have any side effect (like writing to a global variable).
* `map(callable $callback)` : apply $callback on each element of the Vector and return a new Vector
containing the results. The callback must be a pure function.
* `reduce(callable $callback, $identity)` : returns the result of applying
`$callback($identity, ($callback(Vector[0], ... Vector[n])))`. The callback must be a pure function,
and must also be commutative and associative. The identity value must be such that
`$callback($identity, $x) == $x` for any value of `$x`. Calling this method on an empty Vector
returns the identity value.

## A few notes on implementation

The initial plan was to use threads, but this is incredibly impractical to do in PHP. It requires a
specifically compiled version of PHP, which is provided by almost none of the usual sources for
packages, and it also requires enabling the pthreads module.

Because of this, LambdaPi uses [pcntl_fork](http://php.net/manual/fr/function.pcntl-fork.php) to
create full processes that communicate using Unix domain sockets. Thanks to this, there is no
dependency or prerequisite, nor any configuration to do. Unfortunately, it also means that Windows
is not supported.

The current implementation is very simple and could certainly be optimised. For example, one could
use a process pool instead of forking every time. However, this would be difficult to do while
keeping the simplicity of the current interface, because using a process pool might require
restricting callbacks to serialisable values (i.e., not closures). Feel free to send PR’s.

Currently, the number of processes that will be spawned is hard-coded in a constant, NB_CHILDREN.
Increase it to test LambdaPi on a system that has more cores.

## Licence

MIT.
