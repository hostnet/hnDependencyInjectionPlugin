hnDependencyInjectionPlugin
===========================
- Introduces the Symfony 2 DI container in a Symfony 1 application.
- Transforms a Symfony 2 doctrine configuration into a format readable for Symfony 1.x.
  Use Doctrine entities and propel classes with the same database configuration!

### Installation
1. [Download Composer][1].
2. Add to your composer.json
  ```
  "require": {
      "hostnet/hn-dependency-injection-plugin": "1.0.*"
  }

  ```
3. Run ```php composer.phar install```.
4. Make ```apps/<app>/config/<app>Configuration``` extend ```Hostnet\HnDependencyInjectionPlugin\ApplicationConfiguration```.
5. [Optional] override the ```getKernel``` method to return your own kernel, registering the bundles you want.
   ```
   protected function createKernel()
   {
       return new MyKernel($this);
   }
   ```
   ```
   class MyKernel extends Hostnet\HnDependencyInjectionPlugin\Symfony1Kernel
   {
       public function registerBundles()
       {
           $bundles = array(
               new Symfony\Bundle\FrameworkBundle\FrameworkBundle()
           );
           return array_merge($bundles, parent::registerBundles());
       }
   }
   ```
6. Create ```apps/<app>/config/config.yml``` to
   configure your [Doctrine dbal](http://symfony.com/doc/current/reference/configuration/doctrine.html#doctrine-dbal-configuration),
   [Doctrine orm](http://symfony.com/doc/current/reference/configuration/doctrine.html#configuration-overview),
   and possibly the [FrameworkBundle](http://symfony.com/doc/current/reference/configuration/framework.html).
   See also the [example configuration](https://github.com/symfony/symfony-standard/blob/master/app/config/config.yml).
7. If you don't want to generate the propel backwards compatability layer, add this
   ```
   parameters:
       hn_entities_enable_backwards_compatible_connections: false
   ```
   to ```config.yml```, or ```parameters.yml``` if you prefer to have them separate.
8. To ensure proper autoloading when using Doctrine entities, remove if you have
   ```
   require_once __DIR__ . '/../vendor/autoload.php';
   ```
   and add the following to your ```config/ProjectConfiguration.php```.
   ```
   use Doctrine\Common\Annotations\AnnotationRegistry;
   
   $loader = require __DIR__.'/../vendor/autoload.php';
   AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
   ```
   That way Doctrine knows where to find your entities.
9. Be sure to set up your permissions properly, see "[Setting up your permissions](http://symfony.com/doc/current/book/installation.html#configuration-and-setup)".
10. After this is done we can do a little cleanup. To prevent confusion you should remove ```config/databases.yml```, since only the Symfony 2 configuration is read at this point.

### Changelog

2.0.0
- Removed the Symfony1Panel class, it's added automatically now.

1.1.0
- Added a web debug panel with a link to the Symfony 2 profiler.

For this you need to activate the WebProfilerBundle, which you should only activate in dev
```
if ($this->getEnvironment() == 'dev') {
    $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
}
```

Then you need to configure the WebProfilerBundle and the profiler; add this to your config_dev.yml:
```
web_profiler:
    toolbar: true
framework:
    router:   { resource: "%kernel.root_dir%/../config/sf2_routing_dev.yml" }
    profiler:
        only_exceptions: false
        only_master_requests: true
```

Add this to ```sf2_routing_dev.yml``` to make the WebProfilerBundle accessible:
```
_wdt:
    resource: "@WebProfilerBundle/Resources/config/routing/wdt.xml"
    prefix:   /_wdt

_profiler:
    resource: "@WebProfilerBundle/Resources/config/routing/profiler.xml"
    prefix:   /_profiler

_main:
    resource: sf2_routing.yml
```

And in web/*.php, replace ```$configuration->handle($request)->send();``` with:
```
$response = $configuration->handle($request);
$response->send();
$configuration->terminate($request, $response);
```

You should now have a new panel in the Symfony 1 web debug toolbar with a link to the Symfony 2 profiler!

1.0.0
- First official release

0.15
- Added ```hn_entities_enable_backwards_compatible_connections``` parameter

### Running the unit-tests

1. Clone the repository yourself
2. Go to the directory of the clone
3. Run ```composer.phar install```
4. Run ```phpunit```

[1]: http://getcomposer.org/doc/00-intro.md
