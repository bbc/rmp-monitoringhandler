<?php

namespace RMP\CloudwatchMonitoring;

use RMP\CloudwatchMonitoring\PHPUnit\MonitoringContainsConstraint;
use RMP\CloudwatchMonitoring\PHPUnit\MonitoringHasDimensionConstraint;
use RMP\CloudwatchMonitoring\PHPUnit\MonitoringHasValueConstraint;

/**
 * Class MonitoringAssertions
 *
 * A simple trait that allows you to add monitoring assertions into your
 * PHPUnit test suite.
 *
 * @package     RMP\CloudwatchMonitoring
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 * @codeCoverageIgnore
 */
trait MonitoringAssertions
{
    public function assertMonitoringContains($monitor, $metricName, $message = '')
    {
        $condition = new MonitoringContainsConstraint();
        $condition->setMonitor($monitor);
        \PHPUnit_Framework_Assert::assertThat($metricName, $condition, $message);
    }

    public function assertMonitoringHasDimension($monitor, $metricName, $dimension, $message = '')
    {
        $condition = new MonitoringHasDimensionConstraint();
        $condition->setMonitor($monitor);
        $condition->setMetricName($metricName);
        \PHPUnit_Framework_Assert::assertThat($dimension, $condition, $message);
    }

    public function assertMonitoringHasValue($monitor, $metricName, $value, $message = '')
    {
        $condition = new MonitoringHasValueConstraint();
        $condition->setMonitor($monitor);
        $condition->setMetricName($metricName);
        \PHPUnit_Framework_Assert::assertThat($value, $condition, $message);
    }
}
