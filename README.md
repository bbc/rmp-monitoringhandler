# Cloudwatch Monitoring Handler

This is a small PHP component which will add metrics to your cloudwatch account.


## What powers it

- PHP 5.6
- Composer
- aws sdk

## How to integrate
Add the project in composer.json:

```
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:bbc/rmp-monitoringhandler.git"
        }
    ],
    "require": {
        "bbc-rmp/cloudwatch-monitoringhandler": "dev-master",
    }
```

Run `composer install`

## Usage
```php
use RMP\CloudwatchMonitoringHandler\MonitoringHandler;
use Aws\CloudWatch\CloudWatchClient;
use RMP\CloudwatchMonitoring\CloudWatchClientMock; // cloudwatchMonitoring comes with a cloudWatchClient Mock

$app['env'] = "int";
// This example is using Silex application DI container
$app['monitoring'] = $app->share(function ($c) use ($app) {
    // You will need to grab yourself a cloudwatchClient from aws
    $cloudwatchClient = new CloudWatchClient([
        "region" => "eu-west-1",
        "version" => "2010-08-01"
    ]);
    /*
        If we're running this from our sandbox, just mock the monitoring, as it cannot communicate
        to AWS from our sandbox or localhost
    */
    if ($app['env'] === "local" || $app['env'] === "unittests") {
        $cloudwatchClient = new CloudWatchClientMock();
        return new MonitoringHandler($cloudwatchClient, "your-project-name", $app['env']);
    }
    $monitor = new MonitoringHandler($cloudwatchClient, "your-project-name", $app['env']);
    return $monitor;

});

// Usage

$app['monitoring']->application500Error() // This will send a value of 1 to Http500Error metric, with the instance-id and the BBCEnvironment as values too

$app['monitoring']->application404Error() // This will send a value of 1 to Http404Error metric, with the instance-id and the BBCEnvironment as values too

$app['monitoring']->applicationError() // This will send a value of 1 to applicationError metric, with the instance-id and the BBCEnvironment as values too. This is used as a catchAll error for anything not a 404 or a 500

$app['monitoring']->customApplicationError('your error message') // This will send a value of 1 to applicationError metric, with the instance-id and the BBCEnvironment as values too, it will also send error: your error message as another dimension
  
```

## Unit Test Helpers

Unit testing for monitoring is a pain in the backside as the data structure passed to CloudWatch is fairly complex.
To help, this library provides a trait you can put in your TestCases to ease this process:

```php
<?php

use RMP\CloudwatchMonitoring\MonitoringAssertions;

class MyTest extends \PHPUnit_Framework_TestCase
{
    use MonitoringAssertions;
    
    public function testSomething()
    {
        $monitor = new MonitoringHandler();
        
        // Asserts that the monitoring has seen a metric with the MetricName of "applicationError":
        $this->assertMonitoringContains($monitor, 'applicationError');
        
        // Asserts that the monitoring has seen a metric with the MetricName of 'applicationError' AND
        // that that metric has a given dimension:
        $this->assertMonitoringHasDimension($monitor, 'applicationError', ['Name' => 'backend', 'Value' => 'blur']);
        
        // Asserts that the monitoring has seen a metric with the MetricName of 'applicationError' AND
        // that that metric has a given value (22):
        $this->assertMonitoringHasValue($monitor, 'applicationError', 22);
    }
}

```


## License

This repository is available under the terms of the Apache 2.0 license.
View the LICENSE file for more information.

Copyright (c) 2017 BBC
