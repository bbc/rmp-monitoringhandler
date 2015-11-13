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
        $this->assertEquals($this->cloudwatchClient->getLatestMetric(), array (
          'Namespace' => 'BBCApp/radio-nav-service',
          'MetricData' =>
          array (
            0 =>
            array (
              'MetricName' => 'Http500Response',
              'Dimensions' =>
              array (
                0 =>
                array (
                  'Name' => 'BBCEnvironment',
                  'Value' => 'unittests',
                ),
              ),
              'Value' => 1,
            ),
          ),
        ));
    }

    public function test404Error()
    {
        $this->monitoring->application404Error();
        $this->assertEquals($this->cloudwatchClient->getLatestMetric(), array (
          'Namespace' => 'BBCApp/radio-nav-service',
          'MetricData' =>
          array (
            0 =>
            array (
              'MetricName' => 'Http404Response',
              'Dimensions' =>
              array (
                0 =>
                array (
                  'Name' => 'BBCEnvironment',
                  'Value' => 'unittests',
                ),
              ),
              'Value' => 1,
            ),
          ),
        ));
    }

    public function testCatchAllError()
    {
        $this->monitoring->applicationError();
        $this->assertEquals($this->cloudwatchClient->getLatestMetric(), array (
          'Namespace' => 'BBCApp/radio-nav-service',
          'MetricData' =>
          array (
            0 =>
            array (
              'MetricName' => 'applicationError',
              'Dimensions' =>
              array (
                0 =>
                array (
                  'Name' => 'BBCEnvironment',
                  'Value' => 'unittests',
                ),
              ),
              'Value' => 1,
            ),
          ),
        ));
    }


    public function testCustomError()
    {
        $this->monitoring->customApplicationError("something_has_broken");
        $this->assertEquals($this->cloudwatchClient->getLatestMetric(), array (
          'Namespace' => 'BBCApp/radio-nav-service',
          'MetricData' =>
          array (
            0 =>
            array (
              'MetricName' => 'applicationError',
              'Dimensions' =>
              array (
                0 =>
                array (
                  'Name' => 'error',
                  'Value' => 'something_has_broken',
                ),
                1 =>
                array (
                  'Name' => 'BBCEnvironment',
                  'Value' => 'unittests',
                ),
              ),
              'Value' => 1,
            ),
          ),
        ));
    }

    /* Now lets try and break it */
    /**
     * @expectedException Exception
     * @expectedExceptionMessage dimension arguement must be a string
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
