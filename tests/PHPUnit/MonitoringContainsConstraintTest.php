<?php

namespace RMP\CloudwatchMonitoring\Tests\PHPUnit;

use RMP\CloudwatchMonitoring\CloudWatchClientMock;
use RMP\CloudwatchMonitoring\MonitoringHandler;
use RMP\CloudwatchMonitoring\PHPUnit\MonitoringContainsConstraint;

class MonitoringContainsConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatches()
    {
        $monitor = new MonitoringHandler(new CloudWatchClientMock(), 'unittests', 'tests');
        $monitor->putMetricData('applicationError', 1, []);

        $con = new MonitoringContainsConstraint();
        $con->setMonitor($monitor);
        $this->assertEquals($monitor, $con->getMonitor());

        $this->assertTrue($con->matches('applicationError'));

        $this->assertFalse($con->matches('notPresent'));

        $this->assertFalse($con->matches([]));
    }

    public function testToString()
    {
        $con = new MonitoringContainsConstraint();
        $this->assertEquals('is present in the monitoring', $con->toString());
    }
}
