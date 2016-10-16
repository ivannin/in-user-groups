<?php
/**
 * Класс реализует таксономию для пользователей Wordpress
 */
class INUG_User_Taxonomy
{
	/**
	 * Имя таксономии
	 * @var string
	 */
	public $name;
	
	/**
	 * Параметры таксономии
	 * @var mixed
	 */
	public $params;
	
	/**
	 * Конструктор
	 * Инициализация таксономии
	 * 
	 * @param string	$taxonomy	// Имя таксономии
	 * @param mixed		@params		// Параметры, см. https://codex.wordpress.org/Function_Reference/register_taxonomy
	 */
	public function __construct( $name, $params )
	{
		$this->name = $name;
		$this->params = $params;
		
		//  Коллбек для подсчета числа пользователей
		$this->params['update_count_callback'] = array( $this, 'getUserCount' );
		
		// Регистрация таксономии
		$this->register();

		// Если это админка
		if ( is_admin() )
		{
			// Добавление страницы админки
			$this->addAdminPage();

			// Хуки
			add_filter( 'manage_edit-' . $this->name . '_columns', array( $this, 'addUsersColumn' ) );
			add_action( 'manage_' . $this->name . '_custom_column', array( $this, 'showUsersColumn' ), 10, 3 );
			add_action( 'show_user_profile', array( $this, 'showProfileSection' ) );
			add_action( 'edit_user_profile', array( $this, 'showProfileSection' ) );
		}

	}
	
	/**
	 * Регистрация таксономии
	 */
	public function register()
	{
		$object = 'user';
		register_taxonomy( $this->name, $object, $this->params );
	}
	
	/**
	 * Обновление данных о количестве пользователей в таксономии
	 * Вызывается колбеком
	 * http://justintadlock.com/archives/2011/10/20/custom-user-taxonomies-in-wordpress
	 * 
	 * @param mixed		$terms
	 * @param string 	$taxonomy
	 */
	public function getUserCount( $terms, $taxonomy )
	{
		global $wpdb;
		
		foreach ( (array) $terms as $term ) 
		{
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
			
			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}
	
	/**
	 * Формирование страницы управление таксономией
	 */
	public function addAdminPage()
	{
		$tax = get_taxonomy( $this->name );
		add_users_page(
			esc_attr( $tax->labels->menu_name ),
			esc_attr( $tax->labels->menu_name ),
			$tax->cap->manage_terms,
			'edit-tags.php?taxonomy=' . $tax->name
		);		
	}
	
	/**
	 * Колонка в таблице термов таксономии
	 * @static
	 */
	const USER_COLUMN = 'users';
	
	/**
	 * Добавляет колонку Пользователи в таблицу вывода списка термов таксономии
	 * 
	 * @param mixed 	$columns 	An array of columns to be shown in the manage terms table.
	 */ 
	public function addUsersColumn( $columns ) 
	{
		unset( $columns['posts'] );
		$columns[self::USER_COLUMN] = __( 'Users', INUG );
		return $columns;
	}
	
	/**
	 * Показывает число пользователей в колонке
	 *
	 * @param string 	$display 	WP just passes an empty string here.
	 * @param string 	$column 	The name of the custom column.
	 * @param int 		$term_id 	The ID of the term being displayed in the table.
	 */
	public function showUsersColumn( $display, $column, $term_id ) {
		
		if ( $column == self::USER_COLUMN ) 
		{
			$term = get_term( $term_id, $this->name );
			echo $term->count;
		}
	}
	
	/**
	 * Показывает дополнительную секцию в профиле пользователя для управления таксономией
	 *
	 * @param object 	$user 	Объект пользователя.
	 */	
	public function showProfileSection( $user )
	{
		// Текущая таксономия
		$tax = get_taxonomy( $this->name );
		
		// Проверим права пользователя
		if ( ! current_user_can( $tax->cap->assign_terms ) )
			return;
		
		// Список возможных значений - термов
		$terms = get_terms( array(
			'taxonomy'		=> $this->name,
			'hide_empty' 	=> false,
		) ); 
		
		?>
<h3><?php esc_html_e( $this->params['labels']['singular_name']) ?></h3>
<table class="form-table <?php esc_attr_e( $this->name )?>">
	<tr>
		<th>
			<label for="<?php esc_attr_e( $this->name )?>">
				<?php _e( 'Select', INUG ); esc_html_e( ' ' . $this->params['labels']['singular_name']) ?>
			</label>
		</th>
		<td><?php
			/* If there are any profession terms, loop through them and display checkboxes. */
			if ( ! empty( $terms ) ) 
			{
				foreach ( $terms as $term ) 
				{ 
					//echo '<pre>'; var_dump($term); '</pre>';
					$termIdHTML = $this->name . '-' . $term->slug;
					?>
					<input type="checkbox" name="<?php esc_attr_e( $this->name ) ?>" id="<?php esc_attr_e( $termIdHTML ) ?>" value="<?php echo esc_attr( $term->slug ) ?>" <?php checked( true, is_object_in_term( $user->ID, $this->name, $term->slug ) ) ?> /> 
					<label for="<?php echo esc_attr( $termIdHTML ); ?>">
						<?php esc_html_e( $term->name ) ?>
					</label> <br />
					<?php }
			}
			/* If there are no profession terms, display a message. */
			else {
				_e( 'There is no data available.', INUG );
			}
		?></td>
	</tr>
</table>
	<?php		
	}
}