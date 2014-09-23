<?php
/**
 * Plugin Name: Caldera Forms - Form Processor Boilerplate
 * Plugin URI:  
 * Description: Boilerplate for creating your own form processors for Caldera Forms
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @author     Your Name <email@example.com>
 */
class Plugin_Name {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_slug    The string used to uniquely identify this plugin.
	 */
	protected $plugin_slug;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Set the hooks for processing
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_slug = 'plugin-name';
		$this->version = '1.0.0';

		$this->set_locale();

		// Add filter to regester the form processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_form_processor') );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		load_plugin_textdomain(
			$this->plugin_slug,
			false,
			plugin_dir_path( __FILE__ ) . '/languages/'
		);

	}

	/**
	 * Register form processor by adding to the processors list
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_form_processor( $processors ) {

		// Add our processor to the $processors array using our processor_slug as the key.
		// It is possible to replace an existing processor by redefining it and hooking in with a lower priority i.e 100

		$processors['processor_slug'] 	= array(
			"name"              =>  __("Processor Name", $this->plugin_slug),					// Required	 	: Processor name
			"description"       =>  __("Processor Description", $this->plugin_slug),			// Required 	: Processor description
			"icon"				=>	plugin_dir_url(__FILE__) . "assets/icon.png",				// Optional 	: Icon / Logo displayed in processors picker modal
			"author"            =>  'Processor Author',											// Optional 	: Author name 
			"author_url"        =>  'http://example.com/my_processor',							// Optional 	: Author URL
			"pre_processor"     =>  array( $this, 'form_pre_processor' ),						// Optional 	: Pre-processor function used to verify and check data, can stop processing and return to user
			"processor"     	=>  array( $this, 'form_processor' ),							// Optional 	: Processor function used to handle data, cannot stop processing. Returned data saved as entry meta
			"post_processor"  	=>  array( $this, 'form_post_processor' ),						// Optional 	: Post-processor function used to cleanup or capture data from processing
			"template"          =>  plugin_dir_path(__FILE__) . "includes/config.php",			// Optional 	: Config template for setting up the processor in form builder
			"meta_template"		=>  plugin_dir_path(__FILE__) . "includes/meta.php",			// Optional 	: template for displaying meta data returned from processor function 
			"conditionals"		=>	true,														// Optional 	: default true  : setting false will disable conditionals for the processor (use always)
			"single"			=>	false,														// Optional 	: default false : setting as true will only allow once per form
			"magic_tags"    	=>  array(														// Optional 	: Array of values processor returns to be used in magic tag autocomplete list
				"returned_tag",			// Adds {processor_slug:returned_tag} to magic tags
				"another_returned"		// Adds {processor_slug:another_returned} to magic tags etc..
			),
			"scripts"			=>	array(														// Optional 	: Array of WordPress script handle / urls to javascript files used in form builder
				'jquery',		// jquery is already included, this is just an example of a handle
			),
			"styles"			=>	array(														// Optional 	: Array of WordPress style handle / urls to stylesheet files used in form builder
				plugin_dir_url(__FILE__) . "assets/css/style.css",	// doesnt exist, but just an example of a style url
			)
		);

		return $processors;

	}

	/**
	 * Define the pre_process function
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array				$config				The config array of the settings for this processor instance
	 * @var      array				$form				Complete form config array
	 * @return   array				optional			Return an abort array to stop processing and return to user in case of invalid data
	 */
	public function form_pre_processor( $config, $form ) {
		
		global $transdata; // globalised transient object - can be used for passing data between processor stages ( pre -> post etc.. )
		
		/* Example $config content
		$config = array(
    		"processor_id" 		=>	'fp_87742436', 		// Auto asigned ID for the processor
    		"first_option" 		=>	'Hello %name%',		// magic tag contained string
    		"second_option" 	=>	'fld_7930752',		// direct bound field
    		"_required_bounds"	=>	array(				// array of direct bound fields - this sets front form to be "Required" automatically
            	"second_option" 						// slug of the required bound field
        	)
        );
		*/

		// Accessing submitted data
		$first_option_value 	=	Caldera_Forms::do_magic_tags( $config['first_option'] ); 			// get value of the magic tag enabled field
		$second_option_value	=	Caldera_Forms::get_field_data( $config['second_option'], $form ); 	// get value of a direct bound field

		// Stoping a pre_process
		if( 'die' === $second_option_value){
			$abort = array(
				'type'		=>	'error',												// Required		: 	Abort types: error, info, warning (additionally a 'success' type can be used, this ends the process and pretends to be completed)
				'note'		=>	'Nope. Sorry.',											// Required		:	The note that is displayed to the user
				'fields'	=> array(													// Optional		:	Array of fields that caused the fail: key: field_id, value: reason text
					$config['second_option']	=>	'Dont like ' . $second_option_value	// Key: Field ID , Value : Reason
				)
			);
			
			return $abort;
		}
		// returning nothing indicates a successfull pre-process

	}


	/**
	 * Define the processor function
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array				$config				The config array of the settings for this processor instance
	 * @var      array				$form				Complete form config array
	 * @return   array				optional			Array data returned is magic_tag translateble and appended to entry as meta data
	 */
	public function form_processor( $config, $form ) {

		global $transdata; // globalised transient object - can be used for passing data between processor stages ( pre -> post etc.. )
		
		/* Example $config content
		$config = array(
    		"processor_id" 		=>	'fp_87742436', 		// Auto asigned ID for the processor
    		"first_option" 		=>	'Hello %name%',		// magic tag contained string
    		"second_option" 	=>	'fld_7930752',		// direct bound field
    		"_required_bounds"	=>	array(				// array of direct bound fields - this sets front form to be "Required" automatically
            	"second_option" 						// slug of the required bound field
        	)
        );
		*/
		
		/* Code to do something with the submission */
		
		// This example will return the users input and the date in the defined tags

		$return_meta = array(
			'returned_tag'		=>	Caldera_Forms::do_magic_tags( $config['first_option'] ),
			'another_returned'	=>	date('Y-m-d H:i:s')
		);

		return $return_meta;
	
	}

	/**
	 * Define the post_processor function
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array				$config				The config array of the settings for this processor instance
	 * @var      array				$form				Complete form config array
	 */
	public function form_post_processor( $config, $form ) {

		global $transdata; // globalised transient object - can be used for passing data between processor stages ( pre -> post etc.. )
		
		/* Example $config content
		$config = array(
    		"processor_id" 		=>	'fp_87742436', 		// Auto asigned ID for the processor
    		"first_option" 		=>	'Hello %name%',		// magic tag contained string
    		"second_option" 	=>	'fld_7930752',		// direct bound field
    		"_required_bounds"	=>	array(				// array of direct bound fields - this sets front form to be "Required" automatically
            	"second_option" 						// slug of the required bound field
        	)
        );
		*/
	
		// any low prioroty code will run here. at this point all processors have been run

	}

}

// Create the instance. (can be done however you like)
new Plugin_Name();

















