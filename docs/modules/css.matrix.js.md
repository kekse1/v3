
TODO

# css.matrix.js

The `css.matrix.js` (to be finished, too) is there to help with the `transform()`-CSS-styles
and their encoding of the CSS functional styles like `scale{,X,Y}()` or `rotate{X,Y,Z}()`, etc..
atm the most important part is to recognize whether `rotate*` or `scale*` is enabled in the `matrix`
and `matrix3d` encoding value string of the `tranform` style..

I just did it by trying out and comparing the resulting matrices after changing these styles, .. in
the future there's going to be a real class for this, to handle any `transform` style with an
instance, so you can switch on/off them, etc.. ^_^

