<?php

namespace RMP\CloudwatchMonitoring\Tests\PHPUnit;

use RMP\CloudwatchMonitoring\CloudWatchClientMock;
use RMP\CloudwatchMonitoring\MonitoringHandler;
use RMP\CloudwatchMonitoring\PHPUnit\MonitoringHasDimensionConstraint;

class MonitoringHasDimensionConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatches()
    {
        $monitor = new MonitoringHandler(new CloudWatchClientMock(), 'unittests', 'tests');
        $monitor->putMetricData('applicationError', 1, [
            [
                'Name' => 'backend',
                'Value' => 'blur'
            ]
        ]);

        $con = new MonitoringHasDimensionConstraint();
        $con->setMonitor($monitor);
        $con->setMetricName('applicationError');
        $this->assertEquals($monitor, $con->getMonitor());

        $this->assertTrue($con->matches(['Name' => 'backend', 'Value' => 'blur']));
        $this->assertFalse($con->matches(['Name' => 'backend', 'Value' => 'nitro']));
        $this->assertFalse($con->matches([]));
    }

    public function testToString()
    {
        $con = new MonitoringHasDimensionConstraint();
        $con->setMetricName('applicationError');
        $this->assertEquals('is a dimension within the applicationError Metric', $con->toString());
    }
}
