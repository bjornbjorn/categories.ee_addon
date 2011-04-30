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

		$children = ($this->_get_param('children', 'yes') == 'yes');
        $fetch_entry_counts = ($this->_get_param('fetch_entry_counts') == 'yes');
        $only_count_status = ($this->_get_param('only_count_status',FALSE));
        $only_count_channelid = ($this->_get_param('only_count_channelid',FALSE));
		$style = $this->_get_param('style', 'nested');
        $show_empty = ($this->_get_param('show_empty') != 'no');

        if(!$show_empty)
        {
            $fetch_entry_counts = TRUE; // need to fetch them if show_empty = no
        }

        $channel = $this->_get_param('channel');
		$url_title = $this->_get_param('url_title');
        $category_id = $this->_get_param('category_id');

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
		$this->EE->db->order_by('cat_order');
        $this->EE->db->where($where_params);
		$this->EE->db->from('categories');

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

            $channelidsql = '';
        	if($only_count_channelid)
            {
                $channelids = explode('|', $only_count_channelid);
                $channelidsql = ' AND (e.channel_id='.$this->EE->db->escape($channelids[0]);
                for($i=1; $i < count($channelids); $i++)
                {
                    $channelidsql .= ' OR e.status='.$this->EE->db->escape($channelids[$i]);
                }
               $channelidsql .= ') ';
            }

            $this->EE->db->join('(SELECT cat_id AS post_cat_id, count(*) as  entry_count FROM '.$this->EE->db->dbprefix('category_posts').' p, '.$this->EE->db->dbprefix('channel_titles').' e WHERE p.entry_id = e.entry_id'.$statussql.$channelidsql.' AND (e.expiration_date = 0 OR e.expiration_date > UNIX_TIMESTAMP())  GROUP BY post_cat_id) AS entrycounttbl', 'categories.cat_id = entrycounttbl.post_cat_id','left');
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