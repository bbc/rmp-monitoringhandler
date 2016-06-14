<?php

namespace RMP\CloudwatchMonitoringHandler\Tests;

use PHPUnit_Framework_TestCase;
use RMP\CloudwatchMonitoring\CloudWatchClientMock;
use RMP\CloudwatchMonitoring\MonitoringHandler;

class CloudWatchMonitoringTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var     \RMP\CloudwatchMonitoring\CloudWatchClientMock
     */
    protected $cloudwatchClient;

    /**
     * @var     \RMP\CloudwatchMonitoring\MonitoringHandler
     */
    protected $monitoring;

    public function setUp()
    {
        $this->cloudwatchClient = new CloudWatchClientMock();
        $this->monitoring = new MonitoringHandler($this->cloudwatchClient, 'radio-nav-service', 'unittests');
    }

    public function test500Error()
    {
        $this->monitoring->application500Error();
        $this->monitoring->sendMetrics();

        $actualMetric = $this->cloudwatchClient->getLatestMetric()->wait();

        $this->assertEquals('BBCApp/radio-nav-service', $actualMetric['Namespace']);
        $this->assertEquals('Http500Response', $actualMetric['MetricData'][0]['MetricName']);
        $this->assertEquals(
            [[ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ]],
            $actualMetric['MetricData'][0]['Dimensions']
        );
        $this->assertEquals(1, $actualMetric['MetricData'][0]['Value']);
        $this->assertEquals('Count', $actualMetric['MetricData'][0]['Unit']);
        $this->assertTrue(isset($actualMetric['MetricData'][0]['Timestamp']));
    }

    public function test404Error()
    {
        $this->monitoring->application404Error();
        $this->monitoring->sendMetrics();

        $actualMetric = $this->cloudwatchClient->getLatestMetric()->wait();

        $this->assertEquals('BBCApp/radio-nav-service', $actualMetric['Namespace']);
        $this->assertEquals('Http404Response', $actualMetric['MetricData'][0]['MetricName']);
        $this->assertEquals(
            [[ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ]],
            $actualMetric['MetricData'][0]['Dimensions']
        );
        $this->assertEquals(1, $actualMetric['MetricData'][0]['Value']);
        $this->assertEquals('Count', $actualMetric['MetricData'][0]['Unit']);
        $this->assertTrue(isset($actualMetric['MetricData'][0]['Timestamp']));
    }

    public function testApiCall()
    {
        $this->monitoring->addApiCall('blur', 'error_404');
        $this->monitoring->sendMetrics();

        $actualMetric = $this->cloudwatchClient->getLatestMetric()->wait();

        $this->assertEquals('BBCApp/radio-nav-service', $actualMetric['Namespace']);
        $this->assertEquals('apicalls', $actualMetric['MetricData'][0]['MetricName']);
        $this->assertEquals(
            [
                [ 'Name' => 'backend', 'Value' => 'blur' ],
                [ 'Name' => 'type', 'Value' => 'error_404' ],
                [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
            ],
            $actualMetric['MetricData'][0]['Dimensions']
        );
        $this->assertEquals(1, $actualMetric['MetricData'][0]['Value']);
        $this->assertEquals('Count', $actualMetric['MetricData'][0]['Unit']);
        $this->assertTrue(isset($actualMetric['MetricData'][0]['Timestamp']));
    }

    public function testCatchAllError()
    {
        $this->monitoring->applicationError();
        $this->monitoring->sendMetrics();

        $actualMetric = $this->cloudwatchClient->getLatestMetric()->wait();

        $this->assertEquals('BBCApp/radio-nav-service', $actualMetric['Namespace']);
        $this->assertEquals('applicationError', $actualMetric['MetricData'][0]['MetricName']);
        $this->assertEquals(
            [
                [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
            ],
            $actualMetric['MetricData'][0]['Dimensions']
        );
        $this->assertEquals(1, $actualMetric['MetricData'][0]['Value']);
        $this->assertEquals('Count', $actualMetric['MetricData'][0]['Unit']);
        $this->assertTrue(isset($actualMetric['MetricData'][0]['Timestamp']));
    }


    public function testCustomError()
    {
        $this->monitoring->customApplicationError("something_has_broken");
        $this->monitoring->sendMetrics();

        $actualMetric = $this->cloudwatchClient->getLatestMetric()->wait();

        $this->assertEquals('BBCApp/radio-nav-service', $actualMetric['Namespace']);
        $this->assertEquals('applicationError', $actualMetric['MetricData'][0]['MetricName']);
        $this->assertEquals(
            [
                [ 'Name' => 'error', 'Value' => 'something_has_broken' ],
                [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
            ],
            $actualMetric['MetricData'][0]['Dimensions']
        );
        $this->assertEquals(1, $actualMetric['MetricData'][0]['Value']);
        $this->assertEquals('Count', $actualMetric['MetricData'][0]['Unit']);
        $this->assertTrue(isset($actualMetric['MetricData'][0]['Timestamp']));
    }

    public function testBatchErrors()
    {
        for($i = 0; $i < 173; $i++) {
            $this->monitoring->customApplicationError("metric $i");
        }
        $this->monitoring->sendMetrics();
        $this->assertEquals(173, $this->cloudwatchClient->getSentMetricCount());
        // 9 total requests (batches of 20)
        $this->assertEquals(9, $this->cloudwatchClient->getRequestCount());
    }

    public function testBatchEventsSentOnce()
    {
        for($i = 0; $i < 33; $i++) {
            $this->monitoring->customApplicationError("metric $i");
        }
        $this->monitoring->sendMetrics();
        $this->assertEquals(33, $this->cloudwatchClient->getSentMetricCount());
        // 2 total requests (batches of 20)
        $this->assertEquals(2, $this->cloudwatchClient->getRequestCount());
        $this->cloudwatchClient->resetMetrics();
        $this->monitoring->customApplicationError("wibble");
        $this->monitoring->sendMetrics();
        $this->assertEquals(1, $this->cloudwatchClient->getSentMetricCount());
        $this->assertEquals(1, $this->cloudwatchClient->getRequestCount());

    }

    public function testNoEvents()
    {
        $this->monitoring->sendMetrics();
        $this->assertEquals(0, $this->cloudwatchClient->getSentMetricCount());
        $this->assertEquals(0, $this->cloudwatchClient->getRequestCount());
    }

    /**
     * Now lets try and break it
     *
     * @expectedException Exception
     * @expectedExceptionMessage dimension argument must be a string
     */
    public function testExceptionWhenUsingBadArgument()
    {
        $this->monitoring->customApplicationError(NULL);
        $this->monitoring->customApplicationError(43897539328937);
        $this->monitoring->customApplicationError(FALSE);
        $this->monitoring->customApplicationError(true);
        $this->monitoring->customApplicationError(array("dimensionName"));
    }
}
