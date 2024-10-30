<?php 
/*
Plugin Name: CP Demo Switcher
Plugin URI: https://codepassenger.com/wp/demo
Description: Switch between Theme Demo and Pages for Theme Author
Author: CodePassenger
Author URI: https://codepassenger.com/
Text Domain: cp-demo-switcher
Domain Path: /languages/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Version: 1.0
*/
define("CDS_PREFIX", "cds_");
define("CDS_SETTING",'cds_settings');
class Cp_demo_switcher{
	
	protected static $instance = null;
	protected $plugin_path;
	protected $plugin_settings_page;
	protected $plugin_prefix;
    /**
     * [instance description]
     * @return [object] 
     */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this, 'cds_enqueue_script' ));
		add_action( 'admin_enqueue_scripts', array($this, 'cds_loadd_script') );
		$this->file_include();
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_settings_page = CDS_SETTING;
		$this->plugin_prefix = CDS_PREFIX;
		add_action( 'admin_init', array($this, 'cds_admin_init'));
        add_action( 'admin_menu', array($this, 'cds_admin_menu_item') );
	}

	/**
	 * [pluginsLoaded ]
	 * @return [null] [initialize Language File ]
	 */
	public function pluginsLoaded(){
		load_plugin_textdomain( 'cp-demo-switcher', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
	
	/**
	 * [file_include ]
	 * @return [null] [Load File]
	 */
	public function file_include(){
		require_once dirname( __FILE__ ) . '/include/cds-project.php';
		require_once dirname( __FILE__ ) . '/include/cds-settings-class.php';
	}
	
	/**
	 * [mpg_enqueue_script ]
	 * @return [null] [Load Css or Js File]
	 */
	public function cds_enqueue_script(){
		
		wp_enqueue_style('bootstrap-min',plugins_url('/css/bootstrap.min.css',__FILE__));
		wp_enqueue_style('font-awesome-min',plugins_url('/css/font-awesome.min.css',__FILE__));
		wp_enqueue_style('owl-carousel',plugins_url('/css/owl.carousel.css',__FILE__));
		wp_enqueue_style('owl-theme',plugins_url('/css/owl.theme.css',__FILE__));
		wp_enqueue_style('cds-style',plugins_url('/css/cds-style.css',__FILE__));
		
		wp_enqueue_script('bootstrap',plugins_url('/js/bootstrap.min.js',__FILE__),array('jquery'),false,true);
		wp_enqueue_script('owl-carousel',plugins_url('/js/owl.carousel.min.js',__FILE__),array('jquery'),false,true);
		$value = $this->cds_get_data();
		wp_register_script('cds-items',plugins_url('/js/cds-items.js',__FILE__),array('jquery'),false,false);
		$translation_array = array(
			'cds' => $value
		);
		wp_localize_script( 'cds-items', 'cds', $translation_array );
		wp_enqueue_script('cds-items');
		wp_enqueue_script('cds-apps',plugins_url('/js/cds-apps.js',__FILE__),array('jquery'),false,true);
	}
	
	public function cds_loadd_script(){
		wp_enqueue_media();
		wp_enqueue_style('cds-admin.css',plugins_url('/css/cds-admin.css',__FILE__));
		wp_enqueue_script('cds-admin-script',plugins_url('/js/cds-admin-script.js',__FILE__),array('jquery'),false,false);
	
	}
	public function cds_admin_init() {
		$this->settings_class = new CDS_settings_class($this->plugin_path .'include/cds-settings-fields.php', $this->plugin_settings_page, $this->plugin_prefix );
		$this->settings_class->admin_init();
    }
	
	/**
	 * [admin_menu_item]
	 * @return [null] 
	 * Create Admin Menu for Plugin 
	 */
	public function cds_admin_menu_item() {
        add_submenu_page('edit.php?post_type=cds-project',esc_html__( 'Setting', 'cp-demo-switcher' ) ,  esc_html__( 'Setting', 'cp-demo-switcher' ) , 'manage_options' , $this->plugin_settings_page ,  array( $this, 'cds_settings_page' ),'dashicons-nametag', 26);
    }

    
	public function cds_settings_page() {
        $this->settings_class->display_settings();
    }
	
	public function cds_get_data(){
		$cache_key = 'cds_demo_cache';
		$data = get_transient( $cache_key );
		if (!$data){
			$query = new WP_Query(array('post_type' => 'cds-project','posts_per_page'=> -1));
			$data = $link = $page_n = array();
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$id = get_the_ID();
					$name = (get_the_title()!='')?get_the_title():'Demo';
					$tag = get_post_meta($id,'cds_tag',true);
					$theme_p_img = get_post_meta($id,'theme_p_img',true);
					$theme_p_url = get_post_meta($id,'theme_p_url',true);
					$theme_url = get_post_meta($id,'theme_url',true);
					$description = get_post_meta($id,'description',true);
					$cds_pages = get_post_meta($id,'cds_pages',true);
					if(!empty($cds_pages)){
						$p_link = isset($cds_pages['link'])?$cds_pages['link']:array();
						$p_name = isset($cds_pages['name'])?$cds_pages['name']:array();
						foreach($p_name as $key => $item){
							$get_link = isset($p_link[$key])?$p_link[$key]:'';
							$link[] = esc_url($get_link);
							$page_n[] = esc_html($item);
							
						}
					}
					$data[$name.$id] = array(
						'name' => $name,
						'tag' => $tag==1?esc_html__('HTML','cp-demo-switcher'):esc_html__('WordPress','cp-demo-switcher'),
						'img' => esc_url($theme_p_img),
						'url' => esc_url($theme_url),
						'purchase' => esc_url($theme_p_url),
						'tooltip' => esc_html($description),
						'pages' => array('title'=>$page_n,'links'=>$link),
					);
				}
				set_transient( $cache_key, $data, 24 * HOUR_IN_SECONDS );
			}
		}
		return $data;
	}
}
Cp_demo_switcher::instance();

function cds_get_data($field_name){
	$option_name = CDS_PREFIX.$field_name;
	$value = get_option( $option_name );
	if( $value ):
		return $value;
	else:
		return false;
	endif;
}

function cds_logo(){
	$logo = cds_get_data('cds_logo');
	$logo_text = esc_html__('CDS Logo','cp-demo-switcher'); 
	if(!empty($logo)){
		$logo = wp_get_attachment_image_src($logo,array(40,40));
		return '<img src="'.esc_url(current($logo)).'" alt="'.esc_html__('Logo','cp-demo-switcher').'">';
	}else{
		$logo = cds_get_data('logo_text');
		if(!empty($logo)){
			$logo_text = $logo;
		}
		return '<span class="cds-logo-text">'.$logo_text.'</span>';
	}
}

function cds_url(){
	global $wp;  
	$current_url = home_url(add_query_arg(array(),$wp->request));
	return esc_url($current_url);
}