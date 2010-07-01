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
			return $this->EE->output->show_user_error('general', "{exp:categories} required parameter missing: category_group_id (can only be a specific id)");	
		}
		$children = ($this->_get_param('children', 'y') == 'y');
				
		$url_title = $this->_get_param('url_title');
				
		$where_params = array('group_id' => $category_group_id);
		if($url_title != "")
		{
			$where_params['cat_url_title'] = $url_title;
		}
		if(!$children)
		{
			$where_params['parent_id'] = 0;
		}
		
		$query = $this->EE->db->get_where('categories', $where_params);
		
		$vars = array(); 
		foreach($query->result() as $row)
		{
			$vars[] = array(
				'category_name' => $row->cat_name,
				'category_url_title' => $row->cat_url_title,
				'category_image' => $row->cat_image,
				'category_description' => $row->cat_description,			
			);						
		}
		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
		return $this->return_data;		
	}
		
	function _get_category_group_id()
	{
		
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