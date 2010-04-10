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
class Categories_upd {
		
	var $version        = '1.0'; 
	var $module_name = "Categories";
	
    function Categories_upd( $switch = TRUE ) 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
    } 

    /**
     * Installer for the Categories module
     */
    function install() 
	{				
						
		$data = array(
			'module_name' 	 => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'n'
		);

		$this->EE->db->insert('modules', $data);		
		
		//
		// Add additional stuff needed on module install here
		// 
																									
		return TRUE;
	}

	
	/**
	 * Uninstall the Categories module
	 */
	function uninstall() 
	{ 				
		
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
		
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');
										
		return TRUE;
	}
	
	/**
	 * Update the Categories module
	 * 
	 * @param $current current version number
	 * @return boolean indicating whether or not the module was updated 
	 */
	
	function update($current = '')
	{
		return FALSE;
	}
    
}

/* End of file upd.categories.php */ 
/* Location: ./system/expressionengine/third_party/categories/upd.categories.php */ 