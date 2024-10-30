<?php
/**
 * Class CDS_settings_class
 *
 * @category  WordPress_Plugin
 * @package   CP Demo Switcher
 * @author    CodePassenger
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CDS_settings_class' ) ):

class CDS_settings_class {
	
	private $settings;
	private $settings_prefix;
	private $settings_option_page;
	
	public function __construct( $settings_fields, $settings_option_page, $settings_prefix ) {
		if( !is_file($settings_fields) ) return;
		require_once( $settings_fields );
		$this->settings_option_page = $settings_option_page;

		$this->settings_prefix = $settings_prefix;
		
		$this->settings = array();
		$this->settings = apply_filters( $this->settings_option_page, $this->settings );
		if( !is_array($this->settings) ){
			return new WP_Error( 'broke', esc_html__( 'settings must be an array' ) );
		}
		
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array($this, 'admin_notices') );
	}
	
	/**
	 * Register settings
	 * @return void
	 */
	public function admin_init() {
		$this->register_settings();
	}

	/**
	 * Display WordPress settings API errors
	 */
	public function admin_notices()
	{
		settings_errors();
	}
	
	/**
	 * Register settings
	 * @return void
	 */
	public function register_settings() {
		if( !empty( $this->settings ) ):
			foreach( $this->settings as $section_id => $section_data ):

				// Add section to settings page
				add_settings_section( $section_id, '', array( $this, 'display_section' ), $this->settings_option_page.'_'.$section_id );

				foreach( $section_data['fields'] as $field ) {
					$sanitize_callback = isset( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : '';
					$args = array(
						'id' => $field['id'],
						'title' => $field['title'],
						'description' => isset( $field['description'] ) ? $field['description'] : '',
						'type' => isset( $field['type'] ) ? $field['type'] : 'text',
						'std' => isset( $field['std'] ) ? $field['std'] : '',
						'options' => isset( $field['options'] ) ? $field['options'] : '',
						'section' => $section_id,
						'class' => isset( $field['class'] ) ? $field['class'] : null,
						'sanitize_callback' => isset( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : '',
						'placeholder' => isset( $field['placeholder'] ) ? $field['placeholder'] : ''
					);

					// Register field
					$field_name = $this->settings_prefix . $field['id'];
					register_setting( $this->settings_option_page.'_'.$section_id, $field_name, $sanitize_callback );

					// Add field to page
					add_settings_field( $field['id'], $field['title'], array( $this, 'display_field' ), $this->settings_option_page.'_'.$section_id, $section_id, $args );
				}
			endforeach;
		endif;
	}
	/**
	 * [display_section]
	 * @param  [text] $section 
	 * @return [text]          
	 */
	public function display_section( $section ) {
		if(isset($this->settings[ $section['id'] ]['description'])){
			$section_description = $this->settings[ $section['id'] ]['description'];
			if ( isset($section_description) && !empty($section_description) ):
				$html = '<div class="inside">'.$section_description.'</div>';
				echo $html;
			else:
				return '__return_false';
			endif;
		}
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field( $args ) {

		$html = '';

		$field_name = $this->settings_prefix . $args['id'];
		$option = get_option( $field_name );
		$args['id'] = $field_name;
		if(isset($option)):
			$value = $option;
			if($value == ''):
				$value = $args['std'];
			endif;
		elseif(isset($args['std'])):
			$value = $args['std'];
		else:
			$value = '';
		endif;
		$class = isset( $args['class'] ) && !is_null( $args['class'] ) ? $args['class'] : 'regular-text';


		switch( $args['type'] ) {

			case 'text':
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" class="'.  esc_attr($class) . '" type="' . $args['type'] . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr($value) . '" />' . "\n";
				$html .= '<span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
				break;
			case 'password':
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" class="'.  esc_attr($class) . '" type="' . $args['type'] . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr($value) . '" />' . "\n";
				$html .= '<span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
				break;
			case 'number':
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" class="number '.  esc_attr($class) . '" type="text" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr($value) . '" />' . "\n";
				$html .= '<span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";			
				break;

			case 'hidden':
				$html .= '<input id="' . esc_attr( $args['id'] ) . '" type="hidden" name="' . esc_attr( $field_name ) . '" value="' . $value . '" />' . "\n";
				break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $args['id'] ) . '" class="'.  esc_attr($class) . '" rows="5" cols="50" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '">' . esc_textarea($value) . '</textarea><br/>'. "\n";
				$html .= '<br/><span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
			break;

			case 'checkbox':
				$checked = '';
				if( $value && 'on' == $value ){
					$checked = 'checked="checked"';
				}
				$html = '<input type="hidden" name="' . esc_attr( $field_name ) . '" value="off" />';
				$html .= '<label class="switch">';
					$html .= '<input id="' . esc_attr( $args['id'] ) . '" type="' . $args['type'] . '" name="' . esc_attr( $field_name ) . '" ' . $checked . '>';
					$html .= '<div class="slider round"></div>';
				$html .= '</label>';
				$html .= '<span for="description" class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
			break;

			case 'multiple_checkboxes':
				foreach( $args['options'] as $k => $v ) {
					$checked = false;
					if(!empty($value)):
						if( in_array( $k, $value ) ):
							$checked = true;
						endif;
					endif;
					$html .= '<label for="' . esc_attr( $args['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $field_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
					$html .= '<br/><span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
				}
			break;

			case 'radio':
				foreach( $args['options'] as $k => $v ) {
					$checked = false;
					if( $k == $value ) {
						$checked = true;
					}
					$html .= '<input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $field_name ) . '" class="radio '.esc_attr($class).'" value="' . esc_attr( $k ) . '" id="' . esc_attr( $args['id'] . '_' . $k ) . '" /> <label for="' . esc_attr( $args['id'] . '_' . $k ) . '">' . $v . '</label> ';
					$html .= '<br/><span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
				}
			break;

			case 'select':
				$html .= '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $args['id'] ) . '" class="cds-select-multiple '.  esc_attr($class) . '" >';
				foreach( $args['options'] as $k => $v ) {
					$selected = false;
					if( $k == $value ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				$html .= '<br/><span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
			break;

			case 'multiple_select':
				$html .= '<select class="cds-select-multiple" name="' . esc_attr( $field_name ) . '[]" id="' . esc_attr( $args['id'] ) . '" multiple="multiple">';
				foreach( $args['options'] as $k => $v ) {
					$selected = false;
					if(!empty($value)):
						if(in_array( $k, $value )):
							$selected = true;
						endif;
					endif;
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '" />' . $v . '</label> ';
				}
				$html .= '</select> ';
				$html .= '<br/><span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
			break;

			case 'image':
				$image_thumb = '';
				if( $value ) {
					$image_thumb = wp_get_attachment_thumb_url( $value );
				}
				$html .= '<img id="' . $field_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $field_name . '_button" type="button" data-uploader_title="' .  esc_html__( 'Upload an image' , 'warq' ) . '" data-uploader_button_text="' .  esc_html__( 'Use image' , 'warq' ) . '" class="image_upload_button button" value="'.  esc_html__( 'Upload new image' , 'warq' ) . '" />' . "\n";
				$html .= '<input id="' . $field_name . '_delete" type="button" class="image_delete_button button" value="'.  esc_html__( 'Remove image' , 'warq' ) . '" />' . "\n";
				$html .= '<input id="' . $field_name . '" class="image_data_field" type="hidden" name="' . $field_name . '" value="' . $value . '"/><br/>' . "\n";
			break;

			case 'color':
				$html .='<input type="text"  class="'.  esc_attr($class) . ' wp-color-picker-field" id="' . esc_attr( $args['id'] ) . '" name="' . $field_name . '"  value="' . $value . '" data-default-color="" />';
				$html .= '<br/><span class="description">'. esc_attr( $args['description'] ) .'</span>'. "\n";
			break;

		}

		echo $html;
	}
	/**
	 * [settings_navigation Setting Page Menu]
	 * @return [text]
	 */
	public function settings_navigation() {
		$html = '<h2 class="nav-tab-wrapper">';
		foreach($this->settings as $section_id => $section_data):
			 $html .= '<a href="#'. esc_attr( $section_id ) .'" class="nav-tab" id="'. esc_attr( $section_id ) .'-tab">'. esc_attr( $section_data['title'] ) .'</a>';
		endforeach;
		$html .= '</h2>';
		
		echo $html;
	}
	/**
	 * [settings_form Setting Form]
	 * @return [text]
	 */
	public function settings_form() {
		echo '<div class="metabox-holder">
					<div class="postbox">';
		foreach($this->settings as $section_id => $section_data):
			echo '  	<div id="'. esc_attr( $section_id ) .'" class="group">
							<form method="post" action="options.php">';
							do_action( 'wsa_form_top_' . $section_id, $section_data );
							settings_fields( $this->settings_option_page.'_'.$section_id );
							do_settings_sections( $this->settings_option_page.'_'.$section_id );
							do_action( 'wsa_form_bottom_' . $section_id, $section_data );
			echo '          	<div style="padding-left: 10px">
                                ' .submit_button(). '
								</div>
							</form>
						</div>';
		endforeach;
			echo '	</div>
			</div>';
	}
	/**
	 * [display_settings]
	 * @return [text] 
	 */
	public function display_settings() {
		echo '<div class="wrap cds-setting">';
			$this->settings_navigation();
			$this->settings_form();
		echo '</div>';
	}
}
endif;