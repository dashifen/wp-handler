# WP-Handler

This object is a way to hide the gritty details of attaching handlers to WordPress action and filter hooks to protected functions within an object.  Without it, only public methods of an object are accessible to the WordPress core ecosystem, but with it we can use protected methods as well to help avoid anyone calling a method of our objects in an inappropriate way.  

## Usage

Simply extend the `AbstractHandler` object and implement the `initialize()` method.  In that method, you should use the the `addAction()` and `addFilter()` methods of the object to attach other methods to WordPress actions and filters.  For example:

```php
class ExampleHandler extends AbstractHandler {
    public function initialize(): void {
        $this->addAction("admin_enqueue_scripts", "addAdminScripts");
    }
    
    protected function addAdminScripts(): void {
        /* do important things here */
    }
}
```

Both the `addAction()` and `addFilter()` methods have similar arguments to WordPress's core `add_action()` and `add_filter()` functions:  the hook's name, the name of the callback method, the priority at which it's called, and the number of arguments to send your callback.  Like the WP Core functions, these last two parameters have default values of 10 and 1 respectively.

## Why?

Simply put, having an object with only public methods helps avoid naming collisions, but does not avoid putting all of that code in the global scope where it can be accessed from anywhere.  The AbstractHandler object makes sure that your protected methods can only be called on the action/filter hook for which they're intended at the priority level that you provide.  Any other attempt to use them will either cause a PHP error (because the methods are protected) or throw a `HandlerException`.

## Additional Objects

There are some additional helper objects in the `Pages` folder and a few traits in the one named `Traits`.  These are here because they're frequently utilized by handlers to display template files.  Conceivably, they could be in their own repo, but since these objects are so often used together, we might decided to let them live in the same one for now.


## Provenance
I wrote this object to use at work with [Engage](https://enga.ge) in Alexandria, VA.  They've given me permission to make a copy of it and alter it for my own purposes, which is this repo.  Their copy, which is the initial commit into this repo, is their own and I think neither of us guarantee that, after some time passes, that they'll be interchangeable.
