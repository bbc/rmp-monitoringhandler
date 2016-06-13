<?php

namespace RMP\CloudwatchMonitoring\PHPUnit;

class MonitoringHasValueConstraint extends MonitoringConstraint
{
    protected $metricName;

    public function setMetricName($name)
    {
        $this->metricName = $name;
    }

    public function matches($other)
    {
        $metrics = $this->monitor->getMetrics();
        foreach ($metrics as $metric) {
            if ($metric['MetricName'] === $this->metricName && $metric['Value'] === $other) {
                return true;
            }
        }

        return false;
    }

    public function toString()
    {
        return 'is the value of the '.$this->metricName.' Metric';
    }
}
