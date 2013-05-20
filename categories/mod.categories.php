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
        $this->EE->load->library('file_field');

        $category_group_id = intval($this->_get_param('category_group_id'));

		$children = ($this->_get_param('children', 'yes') == 'yes');
        $fetch_entry_counts = ($this->_get_param('fetch_entry_counts') == 'yes');
        $only_count_status = ($this->_get_param('only_count_status',FALSE));
		$style = $this->_get_param('style', 'nested');
        $show_empty = ($this->_get_param('show_empty') != 'no');
        if(!$show_empty)
        {
            $fetch_entry_counts = TRUE; // need to fetch them if show_empty = no
        }

		$padding = $this->_get_param('padding', '<ul><li>');
        $padding_after = $this->_get_param('padding_after', '</li></ul>');

        $limit = $this->_get_param('limit');
        $channel = $this->_get_param('channel');
		$url_title = $this->_get_param('url_title');
        $category_id = $this->_get_param('category_id');
        $orderby = $this->_get_param('orderby','cat_order');
        $order = $this->_get_param('order', 'ASC');
        $order = (strtoupper(trim($order)) == 'DESC') ? 'DESC' : 'ASC';

        $where_params = array();
        if($category_group_id != 0)
        {
		    $where_params['group_id'] = $category_group_id;
        }
        else if($channel) {

            $where_params['channel_name'] = $channel;
            $this->EE->db->join('channels', 'channels.cat_group = categories.group_id');
        }

        if($category_id) {
            $where_params['cat_id'] = $category_id;
        }

		if($url_title != "")
		{
			$where_params['cat_url_title'] = $url_title;
		}
		if(!$children)
		{
			$where_params['parent_id'] = 0;
		}
        $select = 'categories.*';
		$this->EE->db->order_by($orderby.' '.$order);
        $this->EE->db->where($where_params);
		$this->EE->db->from('categories');

    if($limit != "")
    {
      $this->EE->db->limit($limit);
    }

        if($fetch_entry_counts)
        {
            $select = '*, categories.cat_id AS ucid';
            $statussql = '';
            if($only_count_status)
            {
                $statuses = explode('|', $only_count_status);
                $statussql = ' AND (e.status='.$this->EE->db->escape($statuses[0]);
                for($i=1; $i < count($statuses); $i++)
                {
                    $statussql .= ' OR e.status='.$this->EE->db->escape($statuses[$i]);
                }
                $statussql .= ')';
            }

            $where_sql = 'WHERE';
            if($channel)    // if channel is specified we restrict entry count to it
            {
                $where_sql = ', '.$this->EE->db->dbprefix('channels').' ec WHERE ec.channel_name='.$this->EE->db->escape($channel).' AND ec.channel_id=e.channel_id AND';
            }
            $this->EE->db->join('(SELECT cat_id AS post_cat_id, count(*) as  entry_count FROM '.$this->EE->db->dbprefix('category_posts').' p, '.$this->EE->db->dbprefix('channel_titles').' e '.$where_sql.' p.entry_id = e.entry_id'.$statussql.' GROUP BY post_cat_id) AS entrycounttbl', 'categories.cat_id = entrycounttbl.post_cat_id','left');

            $this->EE->db->group_by('ucid');
            if($show_empty == FALSE)
            {
                $this->EE->db->where('entry_count >', '0');
            }
        }

        $this->EE->db->select($select);
        $query = $this->EE->db->get();

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

		}
		elseif($style == 'full_nested')
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
					$children_categories[$row->parent_id][] = $this->_get_category_arr($row);
				}
			}

			foreach($root_categories as $cat)
			{
				$temp = $this->_full_nested_categories($cat, $children_categories, $padding, $padding_after);
				$vars = array_merge($vars, $temp);
			}

		} else {
			foreach($query->result() as $row)
			{
				$vars[] = $this->_get_category_arr($row);
			}
		}

        if(count($vars) > 0)
        {
            $this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
        }
        else
        {
            $this->return_data = '';
        }


		return $this->return_data;
	}


	/**
	 * Store categories in full nested hierarchical order
	 *
	 * @param array $cat
	 * @param array $children_categories
	 * @param string $padding
	 * @param int $level
	 */
	function _full_nested_categories($cat, $children_categories, $padding='', $padding_after, $level=0)
	{
		$cats = array();

        $cat['pre_padding'] = '';
        $cat['post_padding'] = '';
        if($level > 1)
        {
            $cat['pre_padding'] = str_repeat($padding, $level-1);
            $cat['post_padding'] = str_repeat($padding_after, $level-1);
        }

		$cats[] = $cat;

		if(isset($children_categories[$cat['category_id']]))	// if has children
		{
			foreach($children_categories[$cat['category_id']] as $child_cat)
			{
				$this_level = $level + 1;
				$temp = $this->_full_nested_categories($child_cat, $children_categories, $padding, $padding_after, $this_level);
				$cats = array_merge($cats, $temp);
			}
		}

		return $cats;
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
				$prefix.'category_image' => $this->EE->file_field->parse_string($row->cat_image),
				$prefix.'category_description' => $row->cat_description,
                $prefix.'category_entry_count' => (isset($row->entry_count) ? intval($row->entry_count) : 0 ),
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