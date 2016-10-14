<?php
/**
 * Класс расширяет класс параметров и добавляет интерфейс в админке
 */
class INUG_Plugin_Settings extends INUG_Settings
{
	/**
	 * Экземпляр класса плагина, нужен, чтобы вызывать методы плагина
	 * @var INUG_Plugin
	 */
	protected $plugin;
		
	/**
	 * Конструктор
	 * инициализирует параметры, загружает данные и формирует страницу параметров
	 * @param string 			optionName		Название опции в Wordpress, по умолчанию используется имя класса
	 * @param INWCSYNC_Plugin 	plugin			Экземпляр класса плагина
	 */
	public function __construct( $optionName = '', $plugin )
	{
		parent::__construct( $optionName );
		
		$this->plugin = $plugin;
		
		// Работа в админке
		if ( is_admin() )
		{
			// Страница параметров
			add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
			
			// Загрузка CSS плагина
			wp_enqueue_style( INUG, INUG_URL . 'admin.css');
			
			// Загрузка jQuery UI в админку
			global $wp_scripts;
			
			// load jQuery UI tabs
			wp_enqueue_script('jquery-ui-tabs');
			
			// get registered script object for jquery-ui
			$ui = $wp_scripts->query('jquery-ui-core');
			
			// load the Smoothness theme from CDN
			$protocol = is_ssl() ? 'https' : 'http';
			$url = "$protocol://code.jquery.com/ui/{$ui->ver}/themes/smoothness/jquery-ui.css";
			wp_enqueue_style('jquery-ui-smoothness', $url, array( 'jquery-ui-style' ), null);
		}
	}
	
	/**
	 * Добавляет страницу параметров
	 */
	public function addSettingsPage()
	{
		add_options_page(
			__( 'IN Woocommerce CSV Sync', 	INUG ), // page_title
			__( 'Woocommerce CSV Sync', 	INUG ), // menu_title
			'manage_options',										// capability
			INUG,									// menu_slug - совпадает с текстовым доменом
			array( $this, 'renderSettingsPage')						// function
		);		
	}	

	/**
	 * Сохранение параметра сиспользованием sanitize_text_field
	 * @param string	$param		Название параметра
	 * @param mixed 	$value		Значение параметра
	 */
	public function set( $param, $value )
	{
		$this->_params[ $param ] = sanitize_text_field( $value );
	}
	
	
	/**
	 * Формирует страницу параметров
	 */
	public function renderSettingsPage()
	{
		// Сохранение параметров при передаче формы
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			// Проверяем nonce
			check_admin_referer( get_class( $this ) );
			// Проверяем права
			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'You have no permissions to do this!', 	INUG ) );
			
			// Сохраняем данные из POST
			$this->set('folder', (isset( $_POST['inwcsync_folder'] )) ? $_POST['inwcsync_folder'] : '' );
			$this->set('csv_file', (isset( $_POST['inwcsync_csv_file'] )) ? $_POST['inwcsync_csv_file'] : '' );
			$this->set('locale', (isset( $_POST['inwcsync_locale'] )) ? $_POST['inwcsync_locale'] : '' );
			$this->set('encoding', (isset( $_POST['inwcsync_encoding'] )) ? $_POST['inwcsync_encoding'] : '' );
			$this->set('delimiter', (isset( $_POST['inwcsync_delimiter'] )) ? wp_unslash( $_POST['inwcsync_delimiter']) : '' );
			$this->set('skip1stline', (isset( $_POST['inwcsync_skip1line'] )) ? $_POST['inwcsync_skip1line'] : '0' );
			$this->save();
			
			// Если запуск, запускаем плагин
			if ( isset( $_POST['run_now'] ))
				$this->plugin->run();
			
			
		}
			
		$inwcsync_folder 	= $this->get('folder', $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/' . INUG . '/');
		$inwcsync_csv_file 	= $this->get('csv_file', 'efdata.csv');
		$inwcsync_locale 	= $this->get('locale', 'ru_RU.cp1251');
		$inwcsync_encoding 	= $this->get('encoding', 'CP1251');
		$inwcsync_delimiter	= $this->get('delimiter', ';');
		$inwcsync_skip1line	= $this->get('skip1stline', '0');
?>
	<h1><?php esc_html_e( 'IN Woocommerce CSV Sync', INUG ) ?></h1>
	<form id="in-wc-sync-csv" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
		<?php wp_nonce_field( get_class( $this ) ) ?>
		<script>
			jQuery( function($) 
			{
				$( "#tabs" ).tabs();
			});
		</script>
		<div id="tabs">
			<ul>
				<li><a href="#common"><?php esc_html_e( 'Common', INUG ) ?></a></li>
				<li><a href="#schedule"><?php esc_html_e( 'Schedule', INUG ) ?></a></li>
				<?php if ( WP_DEBUG ): ?>
				<li><a href="#log"><?php esc_html_e( 'Log', INUG ) ?></a></li>
				<?php endif ?>				
			</ul>
			<fieldset id="common">
				<table>
					<tr>
						<td><label for="inwcsync_folder"><?php esc_html_e( 'Data Folder', INUG ) ?></label></td>
						<td><input id="inwcsync_folder" type="text" name="inwcsync_folder" value="<?php echo $inwcsync_folder ?>" placeholder="<?php esc_html_e( 'The folder should be writable', INUG ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_csv_file"><?php esc_html_e( 'Data CSV File', INUG ) ?></label></td>
						<td><input id="inwcsync_csv_file" type="text" name="inwcsync_csv_file" value="<?php echo $inwcsync_csv_file ?>" placeholder="<?php esc_html_e( 'The CSV file with data', INUG ) ?>" /></td>
					</tr>					
					<tr>
						<td><label for="inwcsync_locale"><?php esc_html_e( 'Locale for reading file', INUG ) ?></label></td>
						<td><input id="inwcsync_locale" type="text" name="inwcsync_locale" value="<?php echo $inwcsync_locale ?>" placeholder="<?php esc_html_e( 'Locale for reading CSV', INUG ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_encoding"><?php esc_html_e( 'Encoding of CSV file', INUG ) ?></label></td>
						<td><input id="inwcsync_encoding" type="text" name="inwcsync_encoding" value="<?php echo $inwcsync_encoding ?>" placeholder="<?php esc_html_e( 'Encoding of CSV file', INUG ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_delimiter"><?php esc_html_e( 'Fields Delimeter in CSV', INUG ) ?></label></td>
						<td><input id="inwcsync_delimiter" type="text" name="inwcsync_delimiter" value="<?php echo $inwcsync_delimiter ?>" placeholder="<?php esc_html_e( 'Only One Char', INUG ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_skip1line"><?php esc_html_e( 'Skip the 1st line', INUG ) ?></label></td>
						<td>
							<input id="inwcsync_skip1line" type="checkbox" name="inwcsync_skip1line" value="1" <?php checked($inwcsync_skip1line, '1', 'checked') ?> />
							<span><?php esc_html_e( 'If the first line contains headers, check this option', INUG ) ?></span>
						</td>
					</tr>					
				</table>
			</fieldset>
			<fieldset id="schedule">
				<h2><?php esc_html_e( 'Run immediately', INUG ) ?></h2>
				<?php submit_button( __('Run now!', INUG), 'primary', 'run_now' ) ?>
			</fieldset>
			<?php if ( WP_DEBUG ): ?>
			<fieldset id="log">
				<h2><?php esc_html_e( 'The Last Run Log', INUG ) ?></h2>
				<pre><?php 
					$logFile = $inwcsync_folder . $this->get( 'log', INUG . '.log' );
					if ( file_exists ( $logFile ) )
						esc_html_e( file_get_contents( $logFile ) ); 
				?></pre>
			</fieldset>			
			<?php endif ?>
		</div>
		<?php submit_button() ?>
	</form>
<?php	
	
	}

	
}