<?php
/**
* Monitoring Handler
*
* PHP version 5.6
*
* @author Jason Williams <jason.williams01@bbc.co.uk>
* @license  http://www.php.net/license/3_01.txt  PHP License 3.01
*/

namespace RMP\Monitoring;

use AWS\Cloudwatch\CloudWatchClient;

/**
* Class MonitoringHandler
*
* Handler for monitoring events to AWS Cloudwatch
*
* @package RMP\Monitoring
*
*/

class MonitoringHandler
{
    protected $client;
    protected $namespace;

    public function __construct(CloudWatchClient $client, $namespace)
    {
        $this->client = $client;
        $this->namespace = 'BBCApp/' . $namespace;
    }

    /**
     * Generic function which will put metric data, and act as the lower level function for most calls in this class
     *
     * @return  bool
     * @param string $metricName Metricname
     * @param int $value value of metric
     * @param array $dimensions dimensions
     *
    */
    public function putMetricData($metricName, $value, $dimensions)
    {
        $this->client->putMetricData(array(
            'Namespace' => $this->namespace,
            'MetricData' => array(
                'MetricName' => $metricName,
                'Dimensions' => $dimensions,
                'Value' => $value
                )
            )
        );
    }

    /**
     * Shortcut method for calling API's such as Blur or nitro,
     *
     * @return  bool
     * @param string $backend backend
     * @param string $type type of request made such as: totalRequests, 404, 500 slow etc
     *
    */
    public function addApiCall($backend, $type)
    {
        $this->putMetricData("apicalls", 1, array('backend' => $backend, 'type' => $type));
    }

    /* ---- Application Errors ----  */

    /**
    * This is a dimension for application errors, this is a generic catch-all within the application will live in here
    */
    public function applicationError() {
        $this->putMetricData('applicationError', 1, array('error' => "500"));
    }


    /**
    * This is an dimensions for 404 on application errors, all errors within the application will live in here
    */
    public function application404Error() {
        $this->putMetricData('applicationError', 1, array('error' => "404"));
    }

    /**
    * This is an custom dimensions on the application errors meric, an example of this usage is if the application has a specifc statusCode
    */
    public function customApplicationError($dimensionName) {
        $this->putMetricData('applicationError', 1, array('error' => $dimensionName));
    }
}
