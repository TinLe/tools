<?php
include_once 'class.configFormIE.php';
include_once 'class.configFormMozilla.php';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class configFormFactory {
       static function getBrowser($userAgent) {
        // Create list of browsers with browser name as array key and user agent as value.
        $browsers = array(
        'Opera' => 'Opera',
        'Mozilla Firefox'=> '(Firebird)|(Firefox)', // Use regular expressions as value to identify browser
        'Galeon' => 'Galeon',
        'Mozilla'=>'Gecko',
        'MyIE'=>'MyIE',
        'Lynx' => 'Lynx',
        'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
        'Konqueror'=>'Konqueror',
        'SearchBot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)',
        'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
        'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
        'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
        'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
        );

        foreach($browsers as $browser=>$pattern) { // Loop through $browsers array
            // Use regular expressions to check browser type
            if(eregi($pattern, $userAgent)) { // Check if a value in $browsers array matches current user agent.
                return $browser; // Browser was matched so return $browsers key
            }
        }
        return 'Unknown'; // Cannot find browser so return Unknown
    }

    static function getForm($form, $left, $right) {
        if (self::getBrowser($_SERVER['HTTP_USER_AGENT'])=="Mozilla Firefox") {
            return new configFormMozilla($form, $left, $right);
        } else {
            return new configFormIE($form, $left, $right);
        }
    }
}

?>
