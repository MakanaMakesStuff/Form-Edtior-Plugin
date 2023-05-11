### Form Editor Plugin

Within the FormEditor/index.php file, we will load classes based on the paths that we define here.
e.g:

```php
function loadClasses()
{
	$base = 'FormEditor';
	$files = glob(__DIR__ . '/**/*.php', GLOB_BRACE);
	$loaded_classes  = [];

	foreach ($files as $file) {
		if(file_exists($file)) {
			if(strpos($file, 'Classes') != false) {
				require_once($file);
			} else {
				require_once($file);
				$file = str_replace(__DIR__, '', $file);
				$file = preg_replace('/\//', '\\', $file);
				$file = str_replace('.php', '', $file);
				$loaded_classes[] = $base . $file;
			}
		}
	}

	foreach($loaded_classes as $class) {
		
		if(class_exists($class)) {
			$init = new $class();

			if(method_exists($init, 'init')) {
				$init->init();
			}
		} 
	}
}
```

The order in which we load classes is important. If we create abstract classes then they must be loaded first. In the above example you can see that I designated a `Classes` folder as the source of our project's abstract classes and it is the very first to be loaded.

We define an `init` function in our `Base.php` abstract class and in our example we call the init function on all the loaded classes. So all the classes that are being loaded extend the Base class, which is also the reason we need to load it before anything else.
