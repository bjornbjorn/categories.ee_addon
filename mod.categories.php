<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * {exp:categories} tag
 *
 * @package		Categories
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		bjorn
 * @link		http://ee.bybjorn.com/categories/
 */
class Categories {

	var $return_data;
	
	function Categories()
	{		
		$this->EE =& get_instance(); // Make a local reference to the ExpressionEngine super object
		
		$category_group_id = intval($this->_get_param('category_group_id'));
		if($category_group_id == 0)
		{
			return $this->EE->output->show_user_error('general', "{exp:categories} required parameter not specified: category_group_id (can only be a specific id)");	
		}		
		
		$query = $this->EE->db->get_where('categories', array('group_id' => $category_group_id));
		
		$vars = array(); 
		foreach($query->result() as $row)
		{
			$vars[] = array(
				'category_name' => $row->cat_name,
				'category_url_title' => $row->cat_url_title,
				'category_image' => $row->cat_image			
			);						
		}
		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
		return $this->return_data;		
	}
	
		
	/**
     * Helper function for getting a parameter
	 */		 
	function _get_param($key, $default_value = '')
	{
		$val = $this->EE->TMPL->fetch_param($key);
		
		if($val == '') {
			return $default_value;
		}
		return $val;
	}

}

/* End of file mod.categories.php */ 
/* Location: ./system/expressionengine/third_party/categories/mod.categories.php */ 