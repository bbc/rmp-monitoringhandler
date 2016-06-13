<?php

namespace RMP\CloudwatchMonitoring\PHPUnit;

class MonitoringHasDimensionConstraint extends MonitoringConstraint
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
            if ($metric['MetricName'] === $this->metricName) {
                foreach ($metric['Dimensions'] as $dim) {
                    if ($dim === $other) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function toString()
    {
        return 'is a dimension within the '.$this->metricName.' Metric';
    }
}
