<?php
/**
* Monitoring Handler
*
* PHP version 5.6
*
* @author Jason Williams <jason.williams01@bbc.co.uk>
* @license  http://www.php.net/license/3_01.txt  PHP License 3.01
*/
namespace RMP\CloudwatchMonitoring;

use Aws\CloudWatch\CloudWatchClient;
use GuzzleHttp\Promise\Promise;


/**
* Class MonitoringHandler
*
* Handler for monitoring events to AWS Cloudwatch
*
* @package RMP\Monitoring
*
*/

class CloudWatchClientMock extends CloudWatchClient
{

    /* our mocking client will throw $metrics into here */
    /* this will be useful when testing to see what data was thrown into our CloudWatchClient */
    protected $metricQueue = array();

    protected $sentMetrics = array();

    public function __construct() {

    }
    /**
     * This is the only method called on the cloudwatch client at the moment
     *
     * @return  bool
     * @param string $metricName Metricname
     * @param int $value value of metric
     * @param array $dimensions dimensions
     *
    */
    public function putMetricDataAsync($metric)
    {
        $metricPromise = new Promise(function () use (&$metricPromise, &$metric) {
            $this->sentMetrics = array_merge($this->sentMetrics, $metric['MetricData']);
            $metricPromise->resolve($metric);
        });

        // Add metric to the queue
        array_unshift($this->metricQueue, $metricPromise);
        return $metricPromise;
    }

    public function getLatestMetric()
    {
        return array_shift($this->metricQueue);
    }

    /* useful for outside functions to know how many requests have been saved up */
    public function getRequestCount()
    {
        return count($this->metricQueue);
    }

    /* useful for outside functions to know how many metrics have been saved up */
    public function getSentMetricCount()
    {
        return count($this->sentMetrics);
    }

    /* setup / teardown functions can start this again */
    public function resetMetrics()
    {
        $this->metricQueue = array();
        $this->sentMetrics = array();
    }
}
