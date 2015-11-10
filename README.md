# Cloudwatch Monitoring Handler

This is a small PHP component which will add metrics to your cloudwatch account.


## What powers it

- PHP 5.4
- Composer
- aws sdk

## How to integrate
```
Add the project in composer.json

    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:bbc/rmp-monitoringhandler.git"
        }
    ],
    "require": {
        "bbc-rmp/CloudwatchMonitoringHandler": "dev-master",
    }
    
Run "composer install"
```

## Usage
```
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

$app['monitoring']->$this->monitoring->application500Error() // This will send a value of 1 to Http500Error metric, with the instance-id and the BBCEnvironment as values too

$app['monitoring']->$this->monitoring->application404Error() // This will send a value of 1 to Http404Error metric, with the instance-id and the BBCEnvironment as values too

$app['monitoring']->$this->monitoring->applicationError()() // This will send a value of 1 to applicationError metric, with the instance-id and the BBCEnvironment as values too. This is used as a catchAll error for anything not a 404 or a 500

$app['monitoring']->$this->monitoring->customApplicationError('your error message') // This will send a value of 1 to applicationError metric, with the instance-id and the BBCEnvironment as values too, it will also send error: your error message as another dimension
  
```
