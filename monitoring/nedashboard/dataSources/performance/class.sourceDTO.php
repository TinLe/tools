<?php
class sourceDTO {

    public $label;
    private $min;
    private $max;
    private $unit;
    private $green;
    private $yellow;
    private $red;
    private $step;
    private $current;

    function loadDefaults($base, $id) {
        $filename = $base;
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));

        $xml=new SimpleXMLElement($contents);

        foreach ($xml->DATASOURCE as $source) {
            if ($source->DS==$id) {
                $this->label=$source->NAME;
                $this->unit=$source->UNIT;
                $this->green=$source->WARN;
                $this->yellow=$source->CRIT;
                $this->min=$source->MIN;
                $this->max=$source->MAX;
                $this->current=$source->ACT;
                $this->step = ($this->max-$this->min)/4;
                return $this->current;
            }
        }
        return -1;
    }

    function sourceDTO() {
    }

    function setDetails($min,$max,$label,$unit,$green,$yellow,$red,$step) {
        $this->min=$min;
        $this->max=$max;
        $this->label=$label;
        $this->unit=$unit;
        $this->green=$green;
        $this->yellow=$yellow;
        $this->red=$red;
        $this->step=$step;
    }

    function getMin() {
        return $this->min;
    }

    function getMax() {
        return $this->max;
    }

    function getUnit() {
        return $this->unit;
    }

    function getLabel() {
        return $this->label;
    }
    function getGreen() {
        return $this->green;
    }

    function getYellow() {
        return $this->yellow;
    }

    function getRed() {
        return $this->red;
    }
    function getStep() {
        return $this->step;
    }
    function getCurrent() {
        return $this->current;
    }
}
?>
