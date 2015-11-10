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

use Aws\CloudWatch\CloudWatchClient;

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

    public function __construct(CloudWatchClient $client, $namespace, $env)
    {
        $this->client = $client;
        $this->namespace = 'BBCApp/' . $namespace;
        /* From what i could see the PHP SDK doesn't provide you with the instance-id so you need to get it yourself */
        /* Don't do this on localhost or sandboxes as it will fail, just set the instanceID to 'None' */
        $this->instanceID = ($env === 'live') ? file_get_contents("http://instance-data/latest/meta-data/instance-id") : "None";
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
     *
    */
    public function putMetricData($metricName, $value, $dimensions)
    {
        /* append data to $dimensions so it doesn't need to be done every time */
        $dimensions[] = array('Name' => 'BBCEnvironment', 'Value' => $this->env);
        $dimensions[] = array('Name' => 'instanceId', 'Value' => $this->instanceID);

        /* Build metric */
        $this->client->putMetricData(array(
            'Namespace' => $this->namespace,
            'MetricData' => array(
                array(
                    'MetricName' => $metricName,
                    'Dimensions' => $dimensions,
                    'Value' => $value
                    )
                )
            )
        );
    }

    /**
     * Shortcut method for calling monitoring on API's calls such as Blur or nitro,
     *
     * @return  void
     * @param string $backend backend
     * @param string $type type of request made such as: totalRequests, 404, 500 slow etc
     *
    */
    public function addApiCall($backend, $type)
    {
        $this->putMetricData("apicalls", 1, array(
            array(
                'Name' => 'backend',
                'Value' => $backend
                ),
            array(
                'Name' => 'type',
                'Value' => $type
                )
            )
        );
    }

    /* ---- Application Errors ----  */

    /**
    * This is a dimension for application errors, this is a 500 error within the application will live in here
    */
    public function application500Error() {
        $this->putMetricData('Http500Response', 1, array());
    }


    /**
    * This is an dimensions for 404 on application errors, all errors within the application will live in here
    */
    public function application404Error() {
        $this->putMetricData('Http404Response', 1, array());
    }

    /**
    * This is a dimension for application errors, this is a generic catch-all within the application will live in here
    */
    public function applicationError() {
        $this->putMetricData('applicationError', 1, array());
    }

    /**
    * This is an custom dimensions on the application errors meric, an example of this usage is if the application has a specifc statusCode
    * @param string error value
    */
    public function customApplicationError($dimensionName) {
        $this->putMetricData('applicationError', 1, array(array('Name' => 'error', 'Value' => $dimensionName)));
    }
}
