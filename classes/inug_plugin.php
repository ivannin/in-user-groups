<?php
/**
 * Класс реализует основной функционал плагина
 */
class INUG_Plugin
{	
	/**
	 * Путь к папке плагина
	 * @var string
	 */	 
	public $path;
	
	/**
	 * URL к папке плагина
	 * @var string
	 */	 
	public $url;
	
	/**
	 * Массив объектов таксономий для пользователя
	 * @var mixed
	 */
	public $taxonomies = array();	

	/**
	 * Конструктор
	 * Инициализация плагина
	 * 
	 * @param string	$path	Путь к папке плагина
	 * @param string	$url	URL к папке плагина
	 */
	public function __construct( $path, $url )
	{
		// Инициализируем свойства
		$this->path = $path;
		$this->url = $url;
			
		// Читаем таксономии
		$taxonomies = $this->getTaxonomies();
		
		// Регистрируем таксономии
		foreach( $taxonomies as $name => $params )
		{
			$this->taxonomies[$name] = new INUG_User_Taxonomy( $name, $params, $this );
		}
		
	}
	
	/**
	 * Возвращает массим требуемых таксономий
	 * TODO: Сделать хранение и чтение нескольких таксономий
	 * https://codex.wordpress.org/Function_Reference/register_taxonomy
	 * пока делаем одну таксономию
	 */
	protected function getTaxonomies()
	{
		return array(
			// Отделы компании
			'inug_department' => array(
				'public' => true,
				'labels' => array(
					'name' 							=> __( 'Departments', INUG ),
					'singular_name' 				=> __( 'Department', INUG ),
					'menu_name' 					=> __( 'Departments', INUG ),
					'search_items' 					=> __( 'Search Departments', INUG ),
					'popular_items' 				=> __( 'Popular Departments', INUG ),
					'all_items' 					=> __( 'All Departments', INUG ),
					'edit_item' 					=> __( 'Edit Department', INUG ),
					'update_item' 					=> __( 'Update Department', INUG ),
					'add_new_item' 					=> __( 'Add New Department', INUG ),
					'new_item_name' 				=> __( 'New Department Name', INUG ),
					'separate_items_with_commas'	=> __( 'Separate departments with commas', INUG ),
					'add_or_remove_items' 			=> __( 'Add or remove departments', INUG ),
					'choose_from_most_used' 		=> __( 'Choose from the most popular departments', INUG ),
				),
				'rewrite' => array(
					'with_front' => true,
					'slug' => 'author/department' // Use 'author' (default WP user slug).
				),
				'capabilities' => array(
					'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
					'edit_terms'   => 'edit_users',
					'delete_terms' => 'edit_users',
					'assign_terms' => 'read',
				)
			),
			// Здесь следующая таксономия
		);
	}
}