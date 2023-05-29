# multiset.js
`class extends Map`, to define some kinda `Set` w/ numeric/count values instead of only true/false;
so one can also check elements in a Set, but with their amount.

## get/set negative()
If NOT enabled (the default behavior), any `.set()` or `.dec[rease]()` will replace a negative value
by zero.

## get/set floating()
Whether floating point values will be reduced to an integer (see `Math.int()`).

## set(_key, _value)
Will check the values, and will so also allow only numeric values (etc).

## add(_key)
Like `.increase()`, but w/o optional '_by'.

## sub(_key)
Same style as `.add()`.. etc.

## inc\[rease\](_key, _by = 1)
You don't need to initialize via `.set()` or so..

## dec\[rease\](_key, _by = 1)
..

## has(_key)
As regular, but returns the counted amounts instead of a Boolean type.
And if a _key is not present in map, it'll return zero (0).

## get(_key)
Is the same as .has().

