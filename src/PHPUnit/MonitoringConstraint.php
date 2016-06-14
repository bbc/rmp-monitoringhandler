<?php

namespace RMP\CloudwatchMonitoring\PHPUnit;

use RMP\CloudwatchMonitoring\MonitoringHandler;

abstract class MonitoringConstraint extends \PHPUnit_Framework_Constraint
{
    /**
     * @var MonitoringHandler
     */
    protected $monitor;

    public function setMonitor(MonitoringHandler $monitor)
    {
        $this->monitor = $monitor;
    }

    public function getMonitor()
    {
        return $this->monitor;
    }
}
