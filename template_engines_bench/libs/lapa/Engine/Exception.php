<?php
/**
 * Exception
 * 
 * Lapa Template ToolKit
 * 
 * PHP versions 5
 *
 * Copyright (c) 2000-2006 Stepanov Sergey
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 * 
 * @package    Lapa
 * @subpackage LapaEngine
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    v 0.5.7.3 2007/10/27
 */
 
class LapaEngineException extends Exception 
{

    public $template_name;
    public $template_line= 0;
    
    public function __construct($message = null, $code = 0)
    {
        $params = func_get_args();
        $this->message = call_user_func_array('sprintf', $params);
    }
    
    public function setTemplate($template_name, $template_line)
    {
        $this->template_name= $template_name;
        $this->template_line= $template_line;
    }
    
    
    public function __toString()
    {
        return 'Lapa Engine: ' . $this->message . sprintf(' Template %s ', $this->template_name);
        //parent::__toString();
    } 

}