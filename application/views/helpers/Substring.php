<?php

class Application_View_Helper_Substring extends Zend_View_Helper_Abstract
{
    protected $_view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
    }
    public function substring($string, $maxLength = 35)
    {
        if ($maxLength < strlen($string)) {
            $halfLength = ($maxLength / 2) - 4;
            $begin = substr($string, 0, $halfLength);
            $end = substr($string, -$halfLength);
            $string = $begin . '...' . $end;
        }
        return $string;
    }
}