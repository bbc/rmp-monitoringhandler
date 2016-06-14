<?php

namespace RMP\CloudwatchMonitoring\Tests\PHPUnit;

use RMP\CloudwatchMonitoring\CloudWatchClientMock;
use RMP\CloudwatchMonitoring\MonitoringHandler;
use RMP\CloudwatchMonitoring\PHPUnit\MonitoringHasValueConstraint;

class MonitoringHasValueConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatches()
    {
        $monitor = new MonitoringHandler(new CloudWatchClientMock(), 'unittests', 'tests');
        $monitor->putMetricData('applicationError', 1, []);

        $con = new MonitoringHasValueConstraint();
        $con->setMonitor($monitor);
        $con->setMetricName('applicationError');
        $this->assertEquals($monitor, $con->getMonitor());

        $this->assertTrue($con->matches(1));
        $this->assertFalse($con->matches(10));
    }

    public function testToString()
    {
        $con = new MonitoringHasValueConstraint();
        $con->setMetricName('applicationError');
        $this->assertEquals('is the value of the applicationError Metric', $con->toString());
    }
}
