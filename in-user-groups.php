<?php 
/**
 * Plugin Name: IN User Groups
 * Plugin URI: http://in-soft.pro/plugins/in-user-groups/
 * Description: Grouping users and output data of the user groups and users
 * Version: 0.1
 * Author: Ivan Nikitin and partners
 * Author URI: http://ivannikitin.com
 * Text domain: in-user-groups
 *
 * Copyright 2016 Ivan Nikitin  (email: info@ivannikitin.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Напрямую не вызываем!
if ( ! defined( 'ABSPATH' ) ) 
	die( '-1' );


// Определения плагина
define( 'INUG', 		'in-user-groups' );				// Название плагина и текстовый домен
define( 'INUG_PATH', 	plugin_dir_path( __FILE__ ) );	// Путь к папке плагина
define( 'INUG_URL', 	plugin_dir_url( __FILE__ ) );	// URL к папке плагина

// Инициализация плагина
add_action( 'init', 'inug_init' );
function inug_init() 
{
	// Локализация плагина
	load_plugin_textdomain( INUG, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );		
		
	// Проверка наличия плагина wp-handsontable-core
	if ( defined( 'WP_HOT_CORE_VERSION' )) 
	{
		// Классы плагина
		require( INUG_PATH . 'classes/inug_user_taxonomy.php' );
		require( INUG_PATH . 'classes/inug_plugin.php' );
			
		// Инициализация плагина
		new INUG_Plugin( INUG_PATH, INUG_URL );	
	}
}

