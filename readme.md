# WP Handler

This object is a way to hide the gritty details of attaching handlers to WordPress action and filter hooks to `protected` methods within an object.  Without it, only `public` ones of an object are accessible to the WordPress core ecosystem, but with it we can use `protected` methods as well.  

Wait!? `Protected` methods available to WordPress?  That's right!  

With reflection and the __call() method, the classes in this library allow, but do not require, you to use `protected` methods as action/filter callbacks.  While this is clearly against the "letter of the law" with respect to object method visibility, it's a nice way to add an additional layer of security to our objects.  If `public` methods are an unlocked door into your object's scope, these methods are locked ones.  With the right key, WordPress can unlock them and enter, but other plugins, themes, or even WP Core without the right key won't be able to. 

## Installation

This package is composer ready, simply ...

```shell script
composer install dashifen/wp-handler
```

... and then get to work!


## Usage

This library contains a deeply nested series of objects that work together to provide increasing levels of WordPress specificity within your code.  Interfaces have been provided, but so have `abstract` objects which we expect will be more useful.

### AbstractHandler

At the top of this hierarchy is simply the `AbstractHandler` object.  This one defines a core set of functionality that each of its extensions uses in some way.  See its interface for more information.  Additionally, there are a number of protected methods within this object that you should use to add and remove WordPress action and filter callbacks from your project's ecosystem.  See below for examples.

### AbstractThemeHandler

This is intended as the object that theme objects should extend.  It adds three methods—the first two of which are `public` and the last of which is `protected`:  `getStylesheetDir`, `getStylesheetUrl`, and `enqueue`.  The first two are somewhat self-explanatory; the third enqueues JS and CSS found within the theme's directory without having to go through the rigmarole that we usually execute to do so.

### AbstractPluginHandler

Like the theme handler, it's from here that plugins can be extended.  It's more robust that the theme handler offering directory and URL identification assistance and extending the `enqueue` method to add JS and CSS from the plugin's scope.  But, it also has methods to activate, deactivate, and uninstall your plugin as well as a number of Dashboard menu manipulation functions for your convenience.

### Agents

Handlers have their agents to perform specific tasks for them that they would otherwise have to do themselves.  Thus, Agents are small, focused classes that handle a specific and single responsibility for the Handler thant employs them.  For example, post type registration, especially the arrays of labels for the WordPress `register_post_type` function are rather verbose.  Moving them to a service object keeps things tidier in your plugin or theme object which makes your life better and may help with maintenance.  Got a problem with a type's registration?  You're gonna know right where to look!

There are three abstract Agent objects already defined:  a general `AbstractAgent` and then an `AbstractPluginAgent` and an `AbstractThemeAgent` for your convenience.

## Hooks

Callbacks that utilize protected methods must be registered using the `AbstractHandler`'s `addAction` and `addFilter` methods.  These, in turn, construct `HookInterface` objects that are used to "remember" the key that WordPress needs to unlock the door these methods represent.  That key is, currently, the priority level at which WordPress calls your method and the action or filter hook it's executing.  Adding in the argument count is the next reasonable step in ensuring that WP is calling your callback at the right time, place, and with the expected quantity of information.

A `HookFactoryInterface` is provided in case you need to change the type of Hook that your plugin or theme is using.  Simply implement it to create your factory which produces your type of Hook and when constructing your handlers, simply provide them your factory as the argument to their constructors. 

## MenuItemInterface

For the plugin handler's menu manipulation methods, a series of objects representing menu items have been included herein.  They utilize my [repository](https://github.com/dashifen/repository) objects which allow read-only access to protected properties similar to how these objects offer limited access to protected methods.  This is a newer feature of these objects and I'm not sure I'm fully happy with how they turned out.  Suggestions welcome!

# Examples

```php
class AwesomePlugin extends AbstractPluginHandler {
    public function initialize (): void {
      if (!$this->isInitialized()) {
        $this->addAction("init", "startSession");
      }
    }

    protected function startSession (): void {
      if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
      } 
    }
```

In the above example, we create a very tiny plugin object.  Because the `initialize` method is abstract in our parent, we must implement it here.  Notice that we call the `isInitialized` method within it; if this method returns `false` then it will never do so again.  This is intended to avoid the possibility that any handler re-initializes its callbacks by accident. It's recommended that you follow this pattern when using these objects.

Then, we add a single action callback:  when WordPress initializes, we want to start a PHP session.  Why?  Who knows!  It's only an example 😅.  

By using the handler's `addAction` method, we construct a `Hook` object that "remembers" the callback for us and keeps track of the key that WordPress will need to  unlock our door.  So, when WordPress comes knocking during the `init` action at priority 10 (the default), handlers `__call` magic method inspects its "key," determines that it fits our lock, and then lets it in to execute the `startSession` method.  

```php
class AwesomeTheme extends AbstractThemeHandler {
    public function initialize (): void {
      if (!$this->isInitialized()) {
        $this->addAction("wp_enqueue_scripts", "enqueueAssets");
      }
    }

    protected function addAssets (): void {
       $this->enqueue("//fonts.googleapis.com/css?family=Iceland:400,700|Droid+Sans:400,700|Droid+Serif:400italic,700italic");
       $this->enqueue("assets/dashifen.css");
       $this->enqueue("assets/dashifen.js");
    }
```

Like a breath of fresh air, isn't it?  The `enqueue` method is smart enough to know that the Google fonts are located elsewhere online due to the `//` which precedes their address.  The other two, though, will be included from within the assets folder of this theme's directory for us without us having to do the work to identify that theme's directory, etc. because that work will have already been done within the `AbstractThemeHandler` object.

Note: assets enqueued by our handler functions pass the last modified date of the asset file as the fourth parameter to the WordPress enqueue functions.  We hope that this is useful for cache busting purposes as new versions of CSS or JS files will necessarily have new modification dates.  

# Version 11

Version 11 will support PHP 8.  Other expected changes are as follows:

1. removal of the CaseChangingTrait from within this package's namespace

# Provenance

I wrote this object to use at work with [Engage](https://enga.ge) in Alexandria, VA.  They've given me permission to make a copy of it and alter it for my own purposes, which is this repo.  Their copy, which is the initial commit into this repo, is their own and I think neither of us guarantee that, after some time passes, that they'll be interchangeable.

