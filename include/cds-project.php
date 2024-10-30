<?php 
class Cds_project {
	protected $templates;
	public function __construct(){
		
		add_action( 'init', array( $this, 'cds_create_post' ) );
		add_action( 'add_meta_boxes',array($this,'cds_add_metabox'));
		add_action( 'save_post',array($this,'cds_save_meta_date'));
		$this->cds_add_custom_page();
		add_filter( 'template_include',array($this,'cds_post_type_template'));
		add_filter( 'theme_page_templates', array($this,'cds_add_custom_page_tamplate'), 20);
		add_filter( 'wp_insert_post_data', array( $this, 'cds_register_project_templates' ) );
		
	}
	
	public function cds_post_type_template($single_template){
		global $post;
		$template_dir = plugin_dir_path( __FILE__ ).'../';
		if ( ! isset( $this->templates[get_post_meta($post->ID, '_wp_page_template', true )] ) ) {
			return $single_template;
		}
		$single_template = $template_dir. get_post_meta( $post->ID, '_wp_page_template', true);
		
		return $single_template;
	}
	
	public function cds_add_custom_page(){
		$this->templates['/template/page-demo-switcher.php'] = esc_html__('Demo Switcher','cp-demo-switcher');
	}
	
	public function cds_register_project_templates($atts){
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		wp_cache_delete( $cache_key , 'themes');

		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	}
	/**
	 * [add_custom_page_tamplate description]
	 * @param [array] $pages [Add New Page]
	 */
	public function cds_add_custom_page_tamplate($pages){
		$pages = array_merge( $pages, $this->templates );
		return $pages;
	}
	
	public function cds_create_post(){
		$labels = array(
			'name'               => esc_html__( 'Demo Switcher', 'Post Type General Name', 'cp-demo-switcher'),
			'singular_name'      => esc_html__( 'Demo Switcher', 'Post Type Singular Name', 'cp-demo-switcher'),
			'menu_name'          => esc_html__( 'Demo Switcher', 'cp-demo-switcher'),
			'parent_item_colon'  => esc_html__( 'Parent Project', 'cp-demo-switcher'),
			'all_items'          => esc_html__( 'All Project', 'cp-demo-switcher'),
			'view_item'          => esc_html__( 'View Project', 'cp-demo-switcher'),
			'add_new_item'       => esc_html__( 'Add New Project', 'cp-demo-switcher'),
			'add_new'            => esc_html__( 'Add New Project', 'cp-demo-switcher'),
			'edit_item'          => esc_html__( 'Edit Project', 'cp-demo-switcher'),
			'update_item'        => esc_html__( 'Update Project', 'cp-demo-switcher'),
			'search_items'       => esc_html__( 'Search Project', 'cp-demo-switcher'),
			'not_found'          => esc_html__( 'Not found', 'cp-demo-switcher'),
			'not_found_in_trash' => esc_html__( 'Not found in Trash', 'cp-demo-switcher'),
		);

		$args   = array(
			'label'               => esc_html__( 'CDS Project', 'cp-demo-switcher'),
			'description'         => esc_html__( 'Create and manage all CDS Project', 'cp-demo-switcher'),
			'labels'              => $labels,
			'supports'            => array('title'),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 14,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'menu_icon'           => 'dashicons-format-gallery',
		);
		register_post_type( 'cds-project', $args );
	}	
	
	public function cds_add_metabox(){
		add_meta_box('cds-project-meta',esc_html__('Content Info','cp-demo-switcher'), array($this,'cds_view_callback'),'cds-project','normal', "low");
	}
	
	public function cds_view_callback(){
		$this->cds_render_data(get_the_ID(),$this->field_array());
	}
	public function cds_render_data($id,$fields){
		$fieldD = '';
		$metaID = $id;
		if(!empty($fields) && is_array($fields)){
			foreach($fields as $field){
				$context = "normal";
				$class = isset($field['class'])?$field['class']:"";
				$description = '<p class="description">'.esc_html(isset($field['desc'])?$field['desc']:'').'</p>';
				$value = get_post_meta($metaID,$field['id'],true);
				switch($field['type']){
					case 'textarea':
						$fieldD .='<div class="cds-input-field '.$context.'">';
							$fieldD .='<label for="'.$field['id'].'">'.$field['label'].'</label>';
							$fieldD .='<textarea rows="4" cols="50" id ="'.$field['id'].'" name="'.$field['id'].'" >'.esc_html($value).'</textarea>';
							$fieldD .= esc_html($description);
						$fieldD .='</div>';
						break;
					case 'select':
						$fieldD .='<div class="cds-input-field cds-single-dropdown '.$context.'">';
							$fieldD .='<label for="'.$field['id'].'">'.$field['label'].'</label>';
							$fieldD .= $this->cds_droupdown($field,$value,false,$class);
							$fieldD .= esc_html($description);
						$fieldD .='</div>';
						break;
					case 'multie_text':
						$sp_id = $field['id'];
						$fieldD .='<div class="cds-input-field multi-text '.$context.'">';
							$fieldD .='<label for="'.$field['id'].'">'.$field['label'].'</label>';
							$fieldD .='<ul class="multi-text list_'.$sp_id.'">';
								$meta_data = get_post_meta($metaID,$field['id'],true);
								$link = isset($meta_data['link'])?$meta_data['link']:array();
								$name = isset($meta_data['name'])?$meta_data['name']:array();
								$lname = $field['label'].' 1';
								if(!empty($name) && is_array($name)){
									foreach($name as $key => $item){
										$lname = $field['label'].' '.($key+1);
										$get_link = isset($link[$key])?$link[$key]:'';
										$fieldD .= '<li>'.$lname.' <input placeholder="Page Name" value="'.$item.'" name="'.$field['id'].'[name][]" type="text"><input placeholder="Page Link" value="'.$get_link.'" name="'.$field['id'].'[link][]" type="text"><span title="'.esc_html__('Remove','cp-demo-switcher').'" class="dashicons dashicons-dismiss field-remove"></span></li>';
									}
								}else{
									$fieldD .= '<li>'.$lname.' <input name="'.$field['id'].'[name][]" type="text"><input name="'.$field['id'].'[link][]" type="text"></li>';
								}
								$fieldD .= '<li><input type="button" data-name="'.$field['label'].'" data-id="'.$sp_id.'" value="Add Text Field" class="button add_field"></li>';
							$fieldD .='</ul>';
							$fieldD .= $description;
						$fieldD .='</div>';
						break;
					default:
						$fieldD .='<div class="cds-input-field '.$context.'">';
							$fieldD .='<label for="'.$field['id'].'">'.$field['label'].'</label>';
							$fieldD .='<input class="'.$class.'" type="'.$field['type'].'" id ="'.$field['id'].'" name="'.$field['id'].'" value="'.esc_html($value).'">';
							$fieldD .= esc_html($description);
						$fieldD .='</div>';
				}
			}
		}
		echo $fieldD;
	}
	
	public function cds_droupdown($field,$select,$multiple=false,$class=''){
		$fieldD = '';
		$select = (array)$select;
		$markup ='<select class="cds-select-multiple '.$class.'" name="'.$field['id'].'" id="'.$field['id'].'">';
		if($multiple){
			$markup ='<select class="cds-select-multiple '.$class.'" name="'.$field['id'].'[]" multiple="multiple" id="'.$field['id'].'">';
		}
		$fieldD .= $markup;
			if(!empty($field['value']) && is_array($field['value'])){
				foreach($field['value'] as $key => $value){
					$selected = in_array($key,$select)?'selected':'';
					$fieldD .='<option '.$selected.' value="'.$key.'">'.$value.'</option>';
				}
			}
		$fieldD .= '</select>';
		return $fieldD;
	}
	
	public function cds_save_meta_date($post_id){
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		if (defined('DOING_AJAX') ) {
			return $post_id;
		}
		$post_name = get_post_type( $post_id );
		if($post_name == 'cds-project'){
			$fields = $this->field_array();
			if(!empty($fields)){
				foreach($fields as $field){
					$value = '';
					if(isset($_POST[$field['id']]) && !empty($_POST[$field['id']])){
						if(is_array($_POST[$field['id']])){
							$value = $_POST[$field['id']];
						}else{
							$value = sanitize_text_field($_POST[$field['id']]);
						}
					}
					update_post_meta( $post_id,$field['id'],$value);
				}
			}
		}
	}
	
	public function field_array(){
		$ar = array(
			array(
				'label'=> esc_html__('Tag','cp-demo-switcher'),
				'id'=>'cds_tag',
				'type'=>'select',
				'value'=> array(1=> esc_html__('HTML','cp-demo-switcher'),2=> esc_html__('WordPress','cp-demo-switcher'))
			),
			array(
				'label'=> esc_html__('Theme Preview Image','cp-demo-switcher'),
				'id'=>'theme_p_img',
				'type'=>'text'
			),
			array(
				'label'=> esc_html__('Theme URL','cp-demo-switcher'),
				'id'=>'theme_url',
				'type'=>'text'
			),
			array(
				'label'=> esc_html__('Theme Purchase URL','cp-demo-switcher'),
				'id'=>'theme_p_url',
				'type'=>'text'
			),
			array(
				'label'=> esc_html__('Description','cp-demo-switcher'),
				'id'=>'description',
				'type'=>'textarea'
			),
			array(
				'label'=> esc_html__('Pages','cp-demo-switcher'),
				'id'=>'cds_pages',
				'type'=>'multie_text'
			)
		);
		return $ar;
	}
}

new Cds_project();
