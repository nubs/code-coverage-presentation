# About This Project
This simple project has a request and a response object as well as an adapter interface with one implementation using curl.  The goal of this project is to show how to achieve 100% code coverage when you have conditionals around the result of seemingly untestable PHP functions.


# Inital Release
Currently all of the project's tests pass however code coverage is not 100%.  You can see this by downloading the code and running `build.php`


## Here are a few cases in which testing may be difficult.

How do you _disable_ an extension at runtime?
```php
if (!extension_loaded('curl')) {
    throw new \RuntimeExtension('cURL extension must be enabled');
}
```

You can pass in headers which would cover this line, but how do you test that your headers were sent?
```php
$curlHeaders = array('Expect:');//stops curl automatically putting in expect 100.
foreach ($request->getHeaders() as $key => $value) {
    $curlHeaders[] = "{$key}: {$value}";
}
```

`curl_init`, `curl_setopt_array`, `curl_exec` and `curl_getinfo` are all documented to return `false` on error? How would you ensure your code handles a `false` response properly?
```php
$curl = curl_init();
if ($curl === false) {
    throw new Exception('Unable to initialize connection');
}
```


# References

* [Composer](http://getcomposer.org)
* [PHPUnit](http://phpunit.de)
* [PHP Namespaces](http://www.php.net/manual/en/language.namespaces.php)
* [PHP cURL](http://www.php.net/manual/en/ref.curl.php)


# Disclaimer

The use of PHP's `curl` library is simply to demonstrate the mocking functionality. If you want to do http requests in your production code use [Guzzle](https://github.com/guzzle/guzzle)
