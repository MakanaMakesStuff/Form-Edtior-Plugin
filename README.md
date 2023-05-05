### PHP Class FormEditor

Within the FormEditor/index.php file, we will load classes based on the paths that we define here.
e.g:

```php
function loadClasses()
{
	$class_files = glob(__DIR__ . '/Classes/*.php');
	$type_files = glob(__DIR__ . '/Types/*.php');
	$loaded_classes = [];

	foreach ($class_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes = array_merge($loaded_classes, get_declared_classes());
		}
	}

	foreach ($type_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes = array_merge($loaded_classes, get_declared_classes());
		}
	}

	foreach ($loaded_classes as $class) {
		if (strpos($class, 'FormEditor\\') === 0 && class_exists($class)) {
			$instance = new $class();
			$methods = get_class_methods($instance);
			if (in_array('init', $methods)) {
				$instance->init();
			}
		}
	}
}
```

The order in which we load classes is important. If we create abstract classes then they must be loaded first. In the above example you can see that I designated a `Classes` folder as the source of our project's abstract classes and it is the very first to be loaded.

We define an `init` function in our `Base.php` abstract class and in our example we call the init function on all the loaded classes. So all the classes that are being loaded extend the Base class, which is also the reason we need to load it before anything else.

If you want to rename folders or add them, then you will also need to update the index.php entry file accordingly.
