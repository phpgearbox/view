The Blade View Gear
================================================================================
[![Build Status](https://travis-ci.org/phpgearbox/view.svg)](https://travis-ci.org/phpgearbox/view)
[![Latest Stable Version](https://poser.pugx.org/gears/view/v/stable.svg)](https://packagist.org/packages/gears/view)
[![Total Downloads](https://poser.pugx.org/gears/view/downloads.svg)](https://packagist.org/packages/gears/view)
[![License](https://poser.pugx.org/gears/view/license.svg)](https://packagist.org/packages/gears/view)

**Laravel Blade Views Standalone**

Okay so by now hopefully you have heard of [Laravel](http://laravel.com/),
the PHP framework that just makes things easy. So first things first full credit
goes to [Taylor Otwell](https://github.com/taylorotwell) for the Blade API.

How to Install
--------------------------------------------------------------------------------
Installation via composer is easy:

	composer require gears/view:*

How to Use
--------------------------------------------------------------------------------
In your *legacy* - non Laravel application.
You can use the Laravel Blade API like so:

```php
// Make sure you have composer included
require('vendor/autoload.php');

// Create a new View Instance
$views = new Gears\View('/path/to/my/views');

// Next you will probably want to make the view object global.
$views->globalise();
```

And thats it, now you can use code like the following:

```php
echo View::make('greeting', array('name' => 'Brad'));
```

Where the view might look like:

```php
<!-- View stored in /path/to/my/views/greeting.php -->

<html>
    <body>
        <h1>Hello, <?php echo $name; ?></h1>
    </body>
</html>
```

For more info on the View API it's self see:
http://laravel.com/docs/responses#views
http://laravel.com/docs/templates#blade-templating

View Scope
--------------------------------------------------------------------------------
When you run ```$views->globalise();``` it checks to see if the class ```View```
exists globally. If not it use the function ```class_alias``` to alias it's self
in much the same a Laravel Application does.

This enables us to use the ```View``` API we are familar with.

View Include Path:
--------------------------------------------------------------------------------
You can provide an array of paths, instead of just one path. So in effect you
can have a View Include Path. Very handy for setting up a HMVC type system.
Here is an example:

```php
$views = new Gears\View(['/views/specific', '/views/generic']);
```

So now for the why?
--------------------------------------------------------------------------------
While laravel is so awesomely cool and great. If you want to pull a feature out
and use it in another project it can become difficult. Firstly you have to have
an innate understanding of the [IoC Container](http://laravel.com/docs/ioc).

You then find that this class needs that class which then requires some other
config variable that is normally present in the IoC when run inside a normal
Laravel App but in your case you haven't defined it and don't really want
to define that value because it makes no sense in your lets say *legacy*
application.

Perfect example is when I tried to pull the session API out to use in wordpress.
It wanted to know about a ```booted``` method, which I think comes from
```Illuminate\Foundation\Application```. At this point in time I already had to
add various other things into the IoC to make it happy and it was the last straw
that broke the camels back, I chucked a coders tantrum, walked to the fridge,
grabbed another Redbull and sat back down with a new approach.

The result is this project.

--------------------------------------------------------------------------------
Developed by Brad Jones - brad@bjc.id.au