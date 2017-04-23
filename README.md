# Neural Network

## Current status
### General
[![Total Downloads](https://img.shields.io/packagist/dt/nokitakaze/neural.svg?style=flat-square)](https://packagist.org/packages/nokitakaze/neural)
[![Downloads per month](https://img.shields.io/packagist/dm/nokitakaze/neural.svg?style=flat-square)](https://packagist.org/packages/nokitakaze/neural)
[![License](http://img.shields.io/packagist/l/nokitakaze/neural.svg?style=flat-square)](https://packagist.org/packages/nokitakaze/neural)

### PHP 7.0
[![Build Status](https://secure.travis-ci.org/nokitakaze/neural.png?branch=master)](http://travis-ci.org/nokitakaze/neural)
![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nokitakaze/neural/badges/quality-score.png?b=master)
![Code Coverage](https://scrutinizer-ci.com/g/nokitakaze/neural/badges/coverage.png?b=master)
<!-- [![Latest stable version](https://img.shields.io/packagist/v/nokitakaze/neural.svg?style=flat-square)](https://packagist.org/packages/nokitakaze/neural) -->

### PHP 5.6
[![Build Status](https://secure.travis-ci.org/nokitakaze/neural.png?branch=php56)](http://travis-ci.org/nokitakaze/neural)
![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nokitakaze/neural/badges/quality-score.png?b=php56)
![Code Coverage](https://scrutinizer-ci.com/g/nokitakaze/neural/badges/coverage.png?b=php56)

## Usage
At first
```bash
composer require nokitakaze/neural
```

And then
```php
require_once 'vendor/autoload.php';
$network = new NokitaKaze\Neural\Network('neural.dat');
$output = $network->calculate($input);
```
