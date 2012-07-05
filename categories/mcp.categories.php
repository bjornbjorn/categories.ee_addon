<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * {exp:categories} tag
 *
 * @package		Categories
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		bjorn
 * @link		http://ee.bybjorn.com/
 */
class Categories_mcp 
{
	var $base;			// the base url for this module			
	var $form_base;		// base url for forms
	var $module_name = "categories";	

	function Categories_mcp( $switch = TRUE )
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance(); 
		$this->base	 	 = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		$this->form_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;

/*		$this->EE->cp->set_right_nav(array(
				'openid_report'			=> $this->base.AMP.'method=report',
				'openid_list_members'	=> $this->base.AMP.'method=list_members',
				'openid_unit_tests'		=> $this->base.AMP.'method=unit_tests',
				'openid_settings'		=> BASE.AMP.'C=addons_extensions&M=extension_settings&file=openid',
			));	
*/		
		
		//  Onward!
	}

	function index() 
	{
		$vars = array();
		return $this->content_wrapper('index', 'welcome', $vars);
	}

	
	function content_wrapper($content_view, $lang_key, $vars = array())
	{
		$vars['content_view'] = $content_view;
		$vars['_base'] = $this->base;
		$vars['_form_base'] = $this->form_base;
		$this->EE->cp->set_variable('cp_page_title', lang($lang_key));
		$this->EE->cp->set_breadcrumb($this->base, lang('categories_module_name'));

		return $this->EE->load->view('_wrapper', $vars, TRUE);
	}
	
}

/* End of file mcp.categories.php */ 
/* Location: ./system/expressionengine/third_party/categories/mcp.categories.php */ 