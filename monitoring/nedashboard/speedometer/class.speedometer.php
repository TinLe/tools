<?php
include_once 'class.dataProviderFactory.php';
include_once 'class.urlCleaner.php';

class meter {
    private $id;
    private $min;
    private $max;
    private $label;
    private $unit;
    private $green;
    private $yellow;
    private $red;
    private $step;
    private $source;

    public function meter($id, $min,$max,$label,$unit,$green,$yellow,$red,$step,$source) {
        $this->id=$id;
        if ($min=="")
        $min=0;

        $this->min=$min;

        $this->label=$label;
        $this->unit=$unit;

        if ($green==$yellow) {
            $yellow=$yellow++;
        }
        if ($yellow=="") {
            $yellow=100;
        }
        if ($green=="") {
            $green= $yellow*0.7;
        }
        $this->green=$green;


        $this->yellow=$yellow;

        $this->source=$source;

        if ($max=="") {
            $this->max=$this->yellow;
        } else {
            $this->max=$max;
        }

        $this->red=$red;

        if ($step=="" || $step==0) {
            $this->step=($this->max-$this->min)/5;
        } else {
            $this->step=$step;
        }
    }

    public function getMin(){
        return $this->min;
    }
    public function getMax(){
        return $this->max;
    }
    public function getLabel(){
        return $this->label;
    }
    public function getUnit(){
        return $this->unit;
    }
    public function getGreen(){
        return $this->green;
    }
    public function getYellow(){
        return $this->yellow;
    }
    public function getRed(){
        return $this->red;
    }
    public function getStep(){
        return $this->step;
    }
    public function getMeterId() {
        return $this->id;
    }
    public function getSource() {
        return $this->source;
    }

    function validate() {
        if ($this->ds<=0) {
            return "DatasOurce must be greater than 0";
        }
        if ((float)$this->max<(float)$this->min) {
            return "Max value is higher than min value";
        }
        if ((float)$this->yellow<(float)$this->green) {
            return "Warning value is higher than critical value";
        }
        if ((float)$this->green<(float)$this->min) {
            return "Min value is higher than warning";
        }
        if ((float)$this->max<(float)$this->yellow) {
            return "Warning value is higher than max value";
        }
    }
}

/**
 * Plugin 'NETWAYS Speedometer' for the 'net_speedometer' extension.
 * Proudly sponsored by Neckermann.de GmbH (www.neckermann.info)
 *
 * @author	Marmsoler Diego
 */
class speedometer {
    // storage path to swf file, relative to www root
    private $swfpath;
    private $scale;
    private $scalevalue;
    private $meters;
    private $mode;

    public function speedometer($mode="service",$swfbase="./swf/",$scale='height',$scalevalue=200) {
        $this->mode=$mode;
        $this->swfpath=$swfbase;
        $this->scale=$scale;
        $this->scalevalue=$scalevalue;

        $this->meters=array();
    }

    public function addMeter($meter) {
        $this->meters[] = $meter;
    }

    public function includeScript() {
        return '<script type="text/javascript" src="' . $this->swfpath . 'swfobject.js"></script>';
    }

    /*
     * Create and define content for Tachos to draw
     */
    public function createMeter($meter) {
        // calculate size of flash object
        if($this->scale == 'height') {
            // calculate width from height
            $width = intval($this->scalevalue);
            $height = (int)($width / 1.4);
        } else {

            // calculate height from width (default)
            $height = intval($this->scalevalue);
            $width = (int)($height * 1.4);
        }

        // start generation of content
        $content="";

        // set updater variables flash object

        //ATTENTION:
        //you can only pass two parameters to this update url
        //ths first is custom and
        //the other must be named with u   (see below)
        $updateurl = "./speedometer/update".$this->mode.".php?source=".$meter->getSource();
        
        $dataProvider=dataProviderFactory::createDataProvider($meter->getSource());
        $update=$dataProvider->status();
        // set arguments for flash object

        $flashargs = sprintf(
                                    '?min=%s&max=%s&label=%s&unit=%s&green=%s&yellow=%s&red=%s&step=%s&round=1&update=%s&url=%s&u=%s',
            $meter->getMin(),
            $meter->getMax(),
            $meter->getLabel(),
            $meter->getUnit(),
            $meter->getGreen(),
            $meter->getYellow(),
            $meter->getRed(),
            $meter->getStep(),
            $update,
            $updateurl,
            $meter->getMeterId()
        );

        /*encode strings to be passed without problems in GET
        urlCleaner::encodeUrl($flashargs);
        get html code for flash object */
        $content .= $this->embedSWF(
            $this->swfpath . 'speedometer.swf',
            $width, $height, urlCleaner::encodeUrl($flashargs), urlCleaner::encodeUrl($meter->getMeterId()), $dataProvider->isActive()
        );
//echo "DEBUG: ".$this->swfpath ."speedometer.swf $width $height | ".urlCleaner::encodeUrl($flashargs) ." | ". urlCleaner::encodeUrl($meter->getMeterId()) ." || " . $dataProvider->isActive();        
        return $content;
    }

        /**
         * createTable - builds content string and returns it
         *
         * @param		none
         * @return		string		content string w/ table holding flash objects
         */
    function createTable ($title) {
        // get maximum count of columns per row
        $maxcol = 3;

        $content="";
        if (count($this->meters)>0) {
            $content .= '<table cellspacing=20 cellpadding=0 border=0>';

            $content .= '<tr>';
            $content .= '<th colspan='.$maxcol.'>'.$title.'</th>';
            $content .= '</tr>';

            $content .= '<tr>';
            $content .= '<td colspan='.$maxcol.'><hr><td>';
            $content .= '</tr>';

            $col = 0;
            foreach($this->meters as $meter)
            for($i=0;$i<count($this->meters);$i++) {
                // set new row tag if max count of columns has been reached
                $content .= (!$col) ? '<tr>' : '';

                // embed swf object
                $content .= "<td>".$content .= $this->createMeter($meter)."</td>";

                // increase column counter
                // close row tag and reset column counter if max count of columns has been reached
                if (++$col == $maxcol) {
                    $col = 0;
                    $content .= '</tr>';
                }
            }
        }

        // 'fill up' left data cells (columns)
        if ($col && $col < $maxcol) {
            for ($col; $col < $maxcol; $col++) {
                $content .= '<td>&nbsp;</td>';
            }
            $content .= '</tr>';
        }

        // close table tag
        $content .= '</table>';

        // return table string
        return $content;
    }

        /*
         * embedSWF - creates html output to embed a flash object
         *
         * @param		string		$src		location of swf source file, relative to server root
         * @param		integer		$width		width of swf object
         * @param		integer		$height		height of swf object
         * @param		string		$args		arguments to be passed to flash object
         * @param		string		$meterid	unique identifier of flash object
         * @return		string		$content	string containing html code to embed swf object
         */
    private function embedSWF ($src, $width, $height, $args, $meterid, $active=true) {

        // set background color
        if ($active) {
            $bgcol = '#c3c7d3';
        }else {
            $bgcol = '#cc0000';
        }


        // create content string using passed parameters


            /* embed content via javascript
            $content = '<div id="' . $meterid . '"></div>' . '
                                <script type="text/javascript">
                                        // <![CDATA[
                                        var so' . $meterid . ' = new SWFObject("' . $src . $args . '", "' . $meterid . '", "' . $width . '", "' . $height . '", "5", "' . $bgcol . '");
                                        so' . $meterid . '.write("' . $meterid . '");
                                        // ]]>
                                </script>';

*/
        // embed content via html object
        $content = '
                                <object classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' . $width . '" height="' . $height . '"
                                        codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0">
                                        <param name="allowScriptAccess" value="sameDomain" />
                                        <param name="movie" value="' . $src . $args . '" />
                                        <param name="quality" value="high" />
                                        <param name="scale" value="exactfit" />
                                        <param name="menu" value="true" />
                                        <param name="bgcolor" value="' . $bgcol . '" />
                                        <embed src="' . $src . $args . '" quality="high" scale="exactfit" menu="false"
                                                bgcolor="' . $bgcol . '" width="' . $width . '" height="' . $height . '" swLiveConnect="false"
                                                type="application/x-shockwave-flash"
                                                pluginspage="http://www.macromedia.com/go/getflashplayer">
                                        </embed>
                                </object>';
        // return content string
        return $content;
    }
}
?>
