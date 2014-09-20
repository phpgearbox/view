The Blade View Gear
================================================================================
**Laravel Blade Views Standalone**

Okay so by now hopefully you have heard of [Laravel](http://laravel.com/),
the PHP framework that just makes things easy. So first things first full credit
goes to [Taylor Otwell](https://github.com/taylorotwell) for the Blade API.

How to Install
--------------------------------------------------------------------------------
Installation via composer is easy:

	composer require gears/blade:*

How to Use
--------------------------------------------------------------------------------
In your *legacy* - non Laravel application.
You can use the Laravel Blade API like so:

```php
// Make sure you have composer included
require('vendor/autoload.php');

// Install the gears view component
Gears\View::install('/path/to/my/views', '/path/to/cache');
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
When you run ```Gears\View::install``` it checks to see if the class ```View```
exists globally. If not it use the function ```class_alias``` to alias it's self
in much the same a Laravel Application does.

So if the class ```View``` does already exist globally.
You will need to call ```Gears\View::make('index'); instead, for example.

View Include Path:
--------------------------------------------------------------------------------
Something that I just discovered, you can provide an array of paths like a
so you can now have a View Include Path. Very handy for overriding views, etc.
Here is an example:

```php
Gears\View::install(['/views/specific', '/views/generic'], '/path/to/cache');
```

Sorry about the package name confusion:
--------------------------------------------------------------------------------
The cluey coder might have noticed the composer package is called *gears/blade*
while the php namespace is *Gears\View*. This probably isn't the best practice.
It's just a remnant from when I first setup the project.

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