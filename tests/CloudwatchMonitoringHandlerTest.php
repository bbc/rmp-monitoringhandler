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
        /* Make sure our environment is unittests otherwise monitoringHandler will try and make calls */
        $this->monitoring = new MonitoringHandler($this->cloudwatchClient, 'radio-nav-service', 'unittests');

    }

    public function test500Error()
    {
        $this->monitoring->application500Error();

        $expectedMetric = [
            'Namespace' => 'BBCApp/radio-nav-service',
            'MetricData' => [[
                'MetricName' => 'Http500Response',
                'Dimensions' => [
                    [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
                ],
                'Value' => 1,
                'Unit' => 'Count',
            ]],
        ];

        $this->assertEquals($expectedMetric, $this->cloudwatchClient->getLatestMetric());
    }

    public function test404Error()
    {
        $this->monitoring->application404Error();

        $expectedMetric = [
            'Namespace' => 'BBCApp/radio-nav-service',
            'MetricData' => [[
                'MetricName' => 'Http404Response',
                'Dimensions' => [
                    [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
                ],
                'Value' => 1,
                'Unit' => 'Count',
            ]],
        ];

        $this->assertEquals($expectedMetric, $this->cloudwatchClient->getLatestMetric());
    }

    public function testCatchAllError()
    {
        $this->monitoring->applicationError();

        $expectedMetric = [
            'Namespace' => 'BBCApp/radio-nav-service',
            'MetricData' => [[
                'MetricName' => 'applicationError',
                'Dimensions' => [
                    [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
                ],
                'Value' => 1,
                'Unit' => 'Count',
            ]],
        ];

        $this->assertEquals($expectedMetric, $this->cloudwatchClient->getLatestMetric());
    }


    public function testCustomError()
    {
        $this->monitoring->customApplicationError("something_has_broken");

        $expectedMetric = [
            'Namespace' => 'BBCApp/radio-nav-service',
            'MetricData' => [[
                'MetricName' => 'applicationError',
                'Dimensions' => [
                    [ 'Name' => 'error', 'Value' => 'something_has_broken' ],
                    [ 'Name' => 'BBCEnvironment', 'Value' => 'unittests' ],
                ],
                'Value' => 1,
                'Unit' => 'Count',
            ]],
        ];

        $this->assertEquals($expectedMetric, $this->cloudwatchClient->getLatestMetric());
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
