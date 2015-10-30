<?
/**
* Monitoring Handler
*
* PHP version 5.6
*
* @author Jason Williams <jason.williams01@bbc.co.uk>
* @license  http://www.php.net/license/3_01.txt  PHP License 3.01
*/

namespace RMP\Monitoring;

use AWS\Cloudwatch;

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
    protected $namespace

    public function __construct(Cloudwatch $client, string $namespace)
    {
        $this->client = $client;
        $this->namespace = 'BBCApp/' . $namespace;
    }
}
