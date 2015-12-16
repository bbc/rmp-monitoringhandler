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
 */
class MonitoringHandler
{
    /**
     * @var     CloudWatchClient
     */
    protected $client;

    /**
     * @var     string
     */
    protected $namespace;

    /**
     * @var     string
     */
    protected $env;

    /**
     * @var     array
     */
    protected $promises = [];

    /**
     * MonitoringHandler constructor.
     *
     * @param CloudWatchClient  $client
     * @param string            $namespace
     * @param string            $env
     */
    public function __construct(CloudWatchClient $client, $namespace, $env)
    {
        $this->client = $client;
        $this->namespace = 'BBCApp/' . $namespace;
        $this->env = $env;
    }

    /**
     * Generic function which will put metric data, and act as the lower level function for most calls in this class
     * CloudWatchClient->putMetricData already uses promises under the hood, so no need to worry about these requests being async - http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/promises.html
     * This function will add on the environment and the instance-d to the dimensions passed in
     *
     * @return  void
     * @param string $metricName Metricname
     * @param int $value value of metric
     * @param array $dimensions dimensions
     * @param string $unit the unit of the metric. From the list found at http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-monitoring-2010-08-01.html#putmetricdata
     *
    */
    public function putMetricData($metricName, $value, $dimensions, $unit='None')
    {
        /* append data to $dimensions so it doesn't need to be done every time */
        $dimensions[] = array('Name' => 'BBCEnvironment', 'Value' => $this->env);

        /* Build metric */
        $this->promises[] = $this->client->putMetricDataAsync([
            'Namespace' => $this->namespace,
            'MetricData' => [[
                'MetricName' => $metricName,
                'Dimensions' => $dimensions,
                'Value' => $value,
                'Unit' => $unit,
            ]]
        ]);
    }

    /**
     * Shortcut method for calling monitoring on API's calls such as Blur or nitro,
     *
     * @return  void
     * @param string $backend backend
     * @param string $type type of request made such as: total_requests, 404, 500 slow etc
     *
    */
    public function addApiCall($backend, $type)
    {
        $dimensions = [
            [ 'Name' => 'backend', 'Value' => $backend ],
            [ 'Name' => 'type', 'Value' => $type ],
        ];

        $this->putMetricData('apicalls', 1, $dimensions, 'Count');
    }

    /**
     * Sends all of the metrics concurrently.
     *
     * @return  void
     */
    public function sendMetrics()
    {
        \GuzzleHttp\Promise\unwrap($this->promises);
    }

    /* ---- Application Errors ----  */

    /**
     * This is a dimension for application errors, this is a 500 error within the application will live in here
     */
    public function application500Error()
    {
        $this->putMetricData('Http500Response', 1, [], 'Count');
    }


    /**
     * This is an dimensions for 404 on application errors, all errors within the application will live in here
     */
    public function application404Error()
    {
        $this->putMetricData('Http404Response', 1, [], 'Count');
    }

    /**
     * This is a dimension for application errors, this is a generic catch-all within the application will live in here
     */
    public function applicationError()
    {
        $this->putMetricData('applicationError', 1, [], 'Count');
    }

    /**
     * This is an custom dimensions on the application errors meric, an example of this usage is if the application has
     * a specifc statusCode
     *
     * @param   string  $dimensionName  error value
     * @throws  \InvalidArgumentException
     */
    public function customApplicationError($dimensionName)
    {
        if (gettype($dimensionName) !== "string") {
            throw new \InvalidArgumentException('dimension argument must be a string');
        }

        $dimensions = [
            ['Name' => 'error', 'Value' => $dimensionName],
        ];

        $this->putMetricData('applicationError', 1, $dimensions, 'Count');
    }
}
