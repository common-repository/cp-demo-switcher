<?php
/**
 * Class CDS_fields_setting
 *
 * @category  WordPress_Plugin
 * @package   CP Demo Switcher
 * @author    CodePassenger
 */
class CDS_fields_setting {
	
	public function __construct(){
		add_filter( 'cds_settings',array($this,'settings_fields'));
	}
	
	protected static $instance = null;
	
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * [settings_fields description]
	 * @param  [array] $settings [Setting Array]
	 * @return [array]           [return Setting array]
	 * Create Filed in Wordpress Admin Panel
	 */
	public function settings_fields( $settings) {
		
		$settings['cds_setting'] = array (
			'title'					=>  esc_html__( 'Demo Switcher Setting', 'cp-demo-switcher' ),
			'fields'				=> array(
				array(
                    'id' 			=> 'cds_logo',
                    'title'			=>  esc_html__( 'Logo', 'cp-demo-switcher' ),
                    'type'			=> 'image',
                ),
				array(
                    'id' 			=> 'logo_text',
                    'title'			=>  esc_html__( 'Logo Text', 'cp-demo-switcher' ),
                    'type'			=> 'text',
                ),
			)
		);
		return $settings;
	}
}

CDS_fields_setting::instance();



