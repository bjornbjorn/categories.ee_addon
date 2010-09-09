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

    private $entry_counts = array();
	
	function Categories()
	{		
		$this->EE =& get_instance(); // Make a local reference to the ExpressionEngine super object
		
		$category_group_id = intval($this->_get_param('category_group_id'));

		if($category_group_id == 0)
		{
			return $this->EE->output->show_user_error('general', "{exp:categories} required parameter missing: category_group_id (can only be a specific id)");	
		}
		$children = ($this->_get_param('children', 'yes') == 'yes');
        $fetch_entry_counts = ($this->_get_param('fetch_entry_counts') == 'yes');
		$style = $this->_get_param('style', 'nested');
       
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
		$this->EE->db->order_by('cat_order');
		$query = $this->EE->db->get_where('categories', $where_params);

        if($fetch_entry_counts)
        {
            $this->EE->db->select('cat_id, count(*) as entry_count');
            $this->EE->db->from('category_posts');
            $this->EE->db->group_by('entry_id');
            $q = $this->EE->db->get();
            foreach($q->result() as $row)
            {
                $this->entry_counts[$row->cat_id] = $row->entry_count;
            }
        }

		$vars = array();
		
		if($style == 'nested')
		{
			$root_categories = array();
			$children_categories = array();
			 
			foreach($query->result() as $row)
			{
				if($row->parent_id==0)
				{
					$root_categories[] = $this->_get_category_arr($row);
				}
				else
				{
					if(!isset($children_categories[$row->parent_id]))
					{
						$children_categories[$row->parent_id] = array();
					}
					$children_categories[$row->parent_id][] = $this->_get_category_arr($row, TRUE);
				}
			}
			
			foreach($root_categories as $cat)
			{
				if(isset($children_categories[$cat['category_id']]))	// if has children
				{
					$cat['children'] = $children_categories[$cat['category_id']];
					$cat['has_children'] = TRUE;
				}
				else
				{
					$cat['children'] = array();
					$cat['has_children'] = FALSE;
				}
				
				$vars[] = $cat;
			}
				
		} else {
			foreach($query->result() as $row)
			{
				$vars[] = $this->_get_category_arr($row);
			}
		}
		
		 							
		$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
		return $this->return_data;		
	}
	
	/**
	 * Get a template-ready array for categories
	 * 
	 * @param unknown_type $row
	 */
	function _get_category_arr($row, $is_child=FALSE)
	{
		$prefix = ($is_child?'child_':'');
		return array(
				$prefix.'category_id' => $row->cat_id,
				$prefix.'category_name' => $row->cat_name,
				$prefix.'category_url_title' => $row->cat_url_title,
				$prefix.'category_image' => $row->cat_image,
				$prefix.'category_description' => $row->cat_description,
                $prefix.'category_entry_count' => (isset($this->entry_counts[$row->cat_id]) ? $this->entry_counts[$row->cat_id] : 0),
			);
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