<?php

namespace RMP\CloudwatchMonitoring\PHPUnit;

class MonitoringContainsConstraint extends MonitoringConstraint
{
    public function matches($other)
    {
        $metrics = $this->monitor->getMetrics();
        foreach ($metrics as $metric) {
            if ($metric['MetricName'] === $other) {
                return true;
            }
        }

        return false;
    }

    public function toString()
    {
        return 'is present in the monitoring';
    }
}
