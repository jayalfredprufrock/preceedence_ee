<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
========================================================
 
--------------------------------------------------------
 Copyright: Andrew Smiley
 http://proteanweb.com
--------------------------------------------------------
 This addon may be used free of charge. Should you
 employ it in a commercial project, I'd appreciate a 
 small donation.
========================================================
 File: pi.preceedence.php
--------------------------------------------------------
 Purpose: Returns first non-empty parameter, or the first 
 parameter that meets a specified condition.
========================================================
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF
 ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT 
 LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO 
 EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
 FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE 
 OR OTHER DEALINGS IN THE SOFTWARE.
=========================================================

*/


$plugin_info = array(
                        'pi_name'			=> 'PrecEEdence',
                        'pi_version'		=> '1.0',
                        'pi_author'			=> 'Andrew Smiley',
                        'pi_author_url'		=> 'http://proteanweb.com',
                        'pi_description'	=> 'Returns first non-empty parameter, or the first parameter that meets a specified condition.',
                        'pi_usage'			=>  PrecEEdence::usage()
                    );


class Preceedence {


	var $return_data="";
	
	var $max_params = 10;
	
    public function __construct(){
  
        $this->EE =& get_instance();
		
		$condition = $this->EE->TMPL->fetch_param('condition','!');
		$default = $this->EE->TMPL->fetch_param('default','');
		
		$before = $this->EE->TMPL->fetch_param('before','');
		$after  = $this->EE->TMPL->fetch_param('after','');
		
		$not = false;
		if (substr($condition,0,1) == '!'){
			$not = true;
			$condition = substr($condition,1);
		}
		
		$regex = (substr($condition,0,1) == '#' && substr($condition,-1) == '#');
		
		$this->return_data = $default;
		
		
		for($i=1; $i <= $this->max_params; $i++){
				
			$val = $this->_param($i);	
			
			if ($regex){
				$match = preg_match($condition, $val, $matches);

				if (($not && !$match) || (!$not && $match)){
					$this->return_data = $before . (!$not && count($matches) > 1 ? $matches[1] : $val) . $after;
					break;
				}
			}
			else if (($not && $val != $condition) || (!$not && $val == $condition)){
				$this->return_data = $before . $val . $after;	
				break;
			}
		}
		
    }

	public function _param($num){
			
			
		//grab variable from tag	
		$var = $this->EE->TMPL->fetch_param('arg'.$num, $this->EE->TMPL->fetch_param('var'.$num, $this->EE->TMPL->fetch_param('param'.$num,'')));	
			
		// The following 3 code blocks were taken from the fantastic
		// Switchee Plugin by Mark Croxton

		// register POST and GET values
		if (strncmp($var, 'get:', 4) == 0)
		{	
			$var = filter_var($this->EE->input->get(substr($var, 4)), FILTER_SANITIZE_STRING);
		}
		
		if (strncmp($var, 'post:', 5) == 0)
		{
			$var = filter_var($this->EE->input->post(substr($var, 5)), FILTER_SANITIZE_STRING);
		}
		
		// register variables created by Stash
		if (strncmp($var, 'stash:', 6) == 0 && class_exists('Stash'))
		{
			$var = substr($var, 6);
			$var = stash::get($var);
		}
		
		// register global vars
		if (strncmp($var, 'global:', 7) == 0)
		{
			$var = substr($var, 7);
			if (array_key_exists($var, $this->EE->config->_global_vars))
			{
				$var = $this->EE->config->_global_vars[$var];
			}
			else
			{
				$var = '';
			}
		}	
			
		return $var;		
	   
	}
    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage() {
ob_start(); 
?>

This plugin returns the first non-empty parameter, or the first parameter that meets a specified condition/regex.

SIMPLE EXAMPLES:
====================

{exp:preceedence var1="" var2="test2" var3="test3"}

Outputs: test2

{exp:preceedence var1="test1" var2="test2" var3="test3" condition="!test1" before="<em>" after="</em>"}

Outputs: <em>test2</em>

{exp:preceedence var1="get:qs" condition="!test1" default="<b>default</b>" before="<em>" after="</em>"}

Assuming there exists a querystring get variable, "qs=test1":
Outputs: <b>default</b>

Otherwise the value of the querystring variable (or an empty string if its undefined) is returned


ADVANCED EXAMPLES:
=====================

{exp:preceedence var1="test1" var2="test2" var3="3" condition="#test(\d)#"}

Outputs: 1

{exp:preceedence var1="test1" var2="test2" var3="3" condition="!#test(\d)#"}

Outputs: 3




PARAMETERS (with defaults):
----------------------------------

varN, argN, paramN (where N = 1-10)
The variables used to test the condition. Get, Post, Global, and Stash variables are supported, 
courtesy of Mike Croxton's Switchee Plugin, and use the same prefixes:
get:,post:,global:,stash:

condition = "!" (tests for non-empty)
The string used for comparison, or a regular expression (enclosed with #)
to match against. If the first character of the condition is "!", the 
condition is checked for inequality or no matches. In the case of a match,
the first grouping is returned.

default = ""
The value to return if no variables meet the condition

before = ""
Markup to prepend to the matched variable value. This
is NOT prepended in the case of a default output.

after = ""
Markup to append to the matched variable value. This
is NOT appended in the case of a default output.



CHANGELOG:
==========

1.0
Inital release.


<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}
/* END */


}
/* END Class */
/* End of file pi.preceedence.php */
/* Location: ./system/expressionengine/third_party/preceedence/pi.preceedence.php */