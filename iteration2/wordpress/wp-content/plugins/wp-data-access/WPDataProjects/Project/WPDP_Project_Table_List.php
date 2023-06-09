<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use WPDataAccess\Dashboard\WPDA_Dashboard;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\List_Table\WPDA_List_Table;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDP_Project_Table_List extends WPDA_List_Table
	 *
	 * @see WPDA_List_Table
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Table_List extends WPDA_List_Table {

		/**
		 * WPDP_Project_Table_List constructor
		 *
		 * Get and sort list of all database tables
		 *
		 * @param array $args
		 */
		public function __construct( array $args = array() ) {
			$args['column_headers'] = self::column_headers_labels();
			$args['title']          = '';
			$args['allow_import']   = 'off';
			$args['allow_insert']   = 'off';

			parent::__construct( $args );
		}

		/**
		 * Overwrite method add_header_button to add reverse engineering
		 */
		protected function add_header_button() {
			global $wpdb;

			// Check table access to prepare table listbox content
			$user_default_schema = WPDA::get_user_default_scheme();
			if ( $wpdb->dbname === $user_default_schema ) {
				$table_access = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
			} else {
				$table_access = get_option( WPDA::BACKEND_OPTIONNAME_DATABASE_ACCESS . $user_default_schema );
			}
			if ( false === $table_access ) {
				$table_access = 'show';
			}

			// Get available databases
			$schema_names  = WPDA_Dictionary_Lists::get_db_schemas();
			switch ( $table_access ) {
				case 'show':
					$tables = $this->get_all_db_tables( $user_default_schema );
					break;
				case 'hide':
					$tables = $this->get_all_db_tables( $user_default_schema );
					// Remove WordPress tables from listbox content
					$tables_named = array();
					foreach ( $tables as $table ) {
						$tables_named[ $table ] = true;
					}
					foreach ( $wpdb->tables( 'all', true ) as $wp_table ) {
						unset( $tables_named[ $wp_table ] );
					}
					$tables = array();
					foreach ( $tables_named as $key => $value ) {
						array_push( $tables, $key );//phpcs:ignore - 8.1 proof
					}
					break;
				default:
					// Show only selected tables and views
					if ( $wpdb->dbname === $user_default_schema ) {
						$tables = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
					} else {
						$tables = get_option( WPDA::BACKEND_OPTIONNAME_DATABASE_SELECTED . $user_default_schema );
					}
					if ( false === $tables ) {
						$tables = '';
					}
			}

			$wpnonce = wp_create_nonce( WPDP_Project_Table_Form::WPNONCE_SEED . WPDP_Project_Design_Table_Model::get_base_table_name() );
			?>
			<form
				method="post"
				action="?page=<?php echo esc_attr( $this->page ); ?>&tab=tables"
				style="display: block"
			>
				<input type="hidden" name="action" value="reverse_engineering">
				<div id="add_table_to_repository" style="display:none">
					<select name="wpda_schema_name" id="wpda_schema_name">
						<?php
						foreach ( $schema_names as $schema_name ) {
							$selected = $user_default_schema === $schema_name['schema_name'] ? ' selected' : '';
							$database = $user_default_schema === $schema_name['schema_name'] ? "Wordpress database ({$schema_name['schema_name']})" : $schema_name['schema_name'];
							echo "<option value='{$schema_name['schema_name']}'{$selected}>{$database}</option>"; // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
					</select>
					<select name="wpda_table_name" id="wpda_table_name">
						<?php
						if ( is_array( $tables ) ) {
							foreach ( $tables as $table ) {
								?>
								<option value="<?php echo esc_attr( $table ); ?>"><?php echo esc_attr( $table ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<input type="hidden" name="wpnonce"
						   value="<?php echo esc_attr( $wpnonce ); ?>"/>
					<input type="submit"
						   value="<?php echo __( 'Add Template For Selected Table To Repository', 'wp-data-access' ); ?>"
						   class="button button-secondary">
					<input type="button"
						   value="<?php echo __( 'Cancel', 'wp-data-access' ); ?>"
						   class="button button-secondary"
						   onclick="jQuery('#no_repository_buttons').show(); jQuery('#add_table_to_repository').hide(); return false;">
				</div>
			</form>
			<script type='text/javascript'>
				function update_table_list(schema_name) {
					var url = location.pathname + '?action=wpda_get_tables';
					var data = {
						wpdaschema_name: schema_name,
						wpda_wpnonce: '<?php echo esc_attr( wp_create_nonce( 'wpda-getdata-access-' . WPDA::get_current_user_login() ) ); ?>'
					};
					jQuery.post(
						url,
						data,
						function (data) {
							jQuery('#wpda_table_name').empty();

							var tables = JSON.parse(data);
							for (var i = 0; i < tables.length; i++) {
								jQuery('<option/>', {
									value: tables[i].table_name,
									html: tables[i].table_name
								}).appendTo("#wpda_table_name");
							}
						}
					);
				}
				jQuery(function () {
					jQuery('#wpda_schema_name').on('change', function () {
						update_table_list(jQuery('#wpda_schema_name').val());
					});
				});
			</script>
			<?php
		}

		/**
		 * Get all db tables and views
		 *
		 * @param string $database Database schema name
		 *
		 * @return array
		 */
		protected function get_all_db_tables( $database ) {
			$tables    = array();
			$db_tables = WPDA_Dictionary_Lists::get_tables( true, $database ); // select all db tables and views
			foreach ( $db_tables as $db_table ) {
				//phpcs:ignore - 8.1 proof
				array_push( $tables, $db_table['table_name'] ); // add table or view to array
			}

			return $tables;
		}

		/**
		 * Overwrites method get_columns to add checkbox
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array();

			if ( $this->bulk_actions_enabled ) {
				$columns = array( 'cb' => '<input type="checkbox" />' );
			}

			return array_merge( $columns, $this->column_headers );//phpcs:ignore - 8.1 proof
		}

		/**
		 * Overwrites method column_default
		 *
		 * @param array  $item
		 * @param string $column_name
		 *
		 * @return mixed|string
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'table_found':
					if ( 'rdb:' === substr( $item['wpda_schema_name'], 0, 4 ) ) {
						return __( 'N/A', 'wp-data-access' );
					}
					$wpda_dictionary_exist = new WPDA_Dictionary_Exist( $item['wpda_schema_name'], $item['wpda_table_name'] );
					if ( $wpda_dictionary_exist->table_exists() ) {
						return __( 'Yes', 'wp-data-access' );
					} else {
						return __( 'No', 'wp-data-access' );
					}
				case 'table_has_relations':
					$table_design = json_decode( $item['wpda_table_design'] );
					if ( isset( $table_design->relationships ) ) {
						return 'Yes';
					} else {
						return 'No';
					}
				case 'table_rows':
					if ( 'rdb:' === substr( $item['wpda_schema_name'], 0, 4 ) ) {
						return __( 'N/A', 'wp-data-access' );
					}
					global $wpdb;
					$suppress = $wpdb->suppress_errors( true );
					if ( '' === $item['wpda_schema_name'] || null === $item['wpda_schema_name'] ) {
						$query = $wpdb->prepare(
							'select count(*) from `%1s`', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
							array(
								WPDA::remove_backticks( $item['wpda_table_name'] ),
							)
						);
					} else {
						$query = $wpdb->prepare(
							'select count(*) from `%1s`.`%1s`', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
							array(
								WPDA::remove_backticks( $item['wpda_schema_name'] ),
								WPDA::remove_backticks( $item['wpda_table_name'] ),
							)
						);
					}
					$rows = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL
					$wpdb->suppress_errors( $suppress );
					return null === $rows ? '-' : $rows;
				case 'wpda_table_name':
					// Validate schema, table and column names
					$warning = WPDA::validate_names( $item['wpda_schema_name'], $item['wpda_table_name'] );
					if ( '' !== $warning ) {
						$content = $item['wpda_table_name'] . $warning .
							substr( parent::column_default( $item, $column_name ), strlen( $item['wpda_table_name'] ) );

						return $content;
					}
				case 'wpda_schema_name':
					global $wpdb;
					if ( $wpdb->dbname === $item[ $column_name ] ) {
						return "WordPress database ({$item[ $column_name ]})";
					}
				default:
					return parent::column_default( $item, $column_name );
			}
		}

		/**
		 * Overwrite method column_default_add_action to add tab argument
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) {
			parent::column_default_add_action( $item, $column_name, $actions );

			// Add copy table options action.
			$wp_nonce_action = "wpda-copy-{$this->table_name}";
			$wp_nonce        = esc_attr( wp_create_nonce( $wp_nonce_action ) );
			$form_id         = '_' . ( self::$list_number - 1 );
			$copy_form       =
				'<form' .
				" id='copy_form$form_id'" .
				" action='?page=" . esc_attr( $this->page ) . "'" .
				" method='post'>" .
				$this->get_key_input_fields( $item ) .
				$this->add_parent_args_as_string( $item ) .
				"<input type='hidden' name='wpdaschema_name' value='<?php echo esc_attr( $this->schema_name ); ?>' />" .
				"<input type='hidden' name='table_name' value='<?php echo esc_attr( $this->table_name ); ?>' />" .
				"<input type='hidden' name='action' value='copy' />" .
				"<input type='hidden' name='_wpnonce' value='$wp_nonce'>" .
				$this->page_number_item .
				'</form>'
			?>

			<script type='text/javascript'>
				jQuery("#wpda_invisible_container").append("<?php echo $copy_form; // phpcs:ignore WordPress.Security.EscapeOutput ?>");
			</script>

			<?php

			// Add link to submit form.
			$copy_warning    = __( "Copy project template set?\\n\\'Cancel\\' to stop, \\'OK\\' to copy.", 'wp-data-access' );
			$actions['copy'] = sprintf(
				'<a href="javascript:void(0)"
					class="edit wpda_tooltip"
					title="Copy template set"
					onclick="if (confirm(\'%s\')) jQuery(\'#%s\').submit()">
					<span style="white-space:nowrap">
						<i class="fas fa-copy wpda_icon_on_button"></i>
						%s
					</span>
				</a>
				',
				$copy_warning,
				"copy_form$form_id",
				__( 'Copy', 'wp-data-access' )
			);
		}

		/**
		 * Overwrite method to implement copy action
		 *
		 * @return array|void
		 */
		public function process_bulk_action() {
			if ( 'copy' === $this->current_action() ) {
				$wp_nonce_action = "wpda-copy-{$this->table_name}";
				$wp_nonce        = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
					die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_REQUEST['wpda_schema_name'] ) ) {
					$wpda_schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) ); // input var okay.
				}
				if ( isset( $_REQUEST['wpda_table_name'] ) ) {
					$wpda_table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ); // input var okay.
				}
				if ( isset( $_REQUEST['wpda_table_setname'] ) ) {
					$wpda_table_setname = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_setname'] ) ); // input var okay.
				}

				$unique_wpda_table_setname = $this->get_unique_setname( $wpda_schema_name, $wpda_table_name, $wpda_table_setname );

				global $wpdb;
				$wpda_table_design_raw = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM `%1s` WHERE wpda_schema_name = %s AND wpda_table_name = %s AND wpda_table_setname = %s', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
						array(
							WPDA::remove_backticks( $this->table_name ),
							$wpda_schema_name,
							$wpda_table_name,
							$wpda_table_setname,
						)
					),
					'ARRAY_A'
				);
				if ( $wpdb->num_rows > 0 ) {
					$wpda_table_design_raw[0]['wpda_table_setname'] = $unique_wpda_table_setname;
					$rows_inserted                                  = $wpdb->insert(
						$this->table_name,
						$wpda_table_design_raw[0]
					);
					switch ( $rows_inserted ) {
						case 0:
							$msg = new WPDA_Message_Box(
								array(
									'message_text' => __( 'Could not copy table options [source not found]', 'wp-data-access' ),
									'message_type' => 'error',
									'message_is_dismissible' => false,
								)
							);
							$msg->box();
							break;
						case 1:
							$msg = new WPDA_Message_Box(
								array(
									'message_text' => __( 'Table options copied', 'wp-data-access' ),
								)
							);
							$msg->box();
							break;
						default:
							$msg = new WPDA_Message_Box(
								array(
									'message_text' => __( 'Could not copy table options [too many rows]', 'wp-data-access' ),
									'message_type' => 'error',
									'message_is_dismissible' => false,
								)
							);
							$msg->box();
					}
				}
			} else {
				parent::process_bulk_action();
			}
		}

		/**
		 * Create a unique setname for this table
		 *
		 * @param $wpda_schema_name
		 * @param $wpda_table_name
		 * @param $wpda_table_setname
		 *
		 * @return string
		 */
		protected function get_unique_setname( $wpda_schema_name, $wpda_table_name, $wpda_table_setname ) {
			global $wpdb;

			$i     = 2;
			$query = "select 'x' from `%1s` where wpda_schema_name = %s and wpda_table_name = %s and wpda_table_setname = %s";

			$unique_wpda_table_setname = "{$wpda_table_setname}_$i";
			$wpdb->get_results(
				$wpdb->prepare(
					$query, // phpcs:ignore WordPress.DB.PreparedSQL
					array(
						WPDA::remove_backticks( WPDP_Project_Design_Table_Model::get_base_table_name() ),
						$wpda_schema_name,
						$wpda_table_name,
						$unique_wpda_table_setname,
					)
				)
			);
			while ( $wpdb->num_rows > 0 ) {
				// Search until a free options set is found
				$i ++;
				$unique_wpda_table_setname = "{$wpda_table_setname}_$i";
				$wpdb->get_results(
					$wpdb->prepare(
						$query, // phpcs:ignore WordPress.DB.PreparedSQL
						array(
							WPDA::remove_backticks( WPDP_Project_Design_Table_Model::get_base_table_name() ),
							$wpda_schema_name,
							$wpda_table_name,
							$unique_wpda_table_setname,
						)
					)
				);
			}

			return $unique_wpda_table_setname;
		}

		/**
		 * Overwrite method show to add tab argument
		 */
		public function show() {
			parent::show();
			?>
			<script type='text/javascript'>
				var wpda_main_form_action = jQuery('#wpda_main_form').attr('action') + '&tab=tables';
				jQuery('#wpda_main_form').attr('action', wpda_main_form_action);
			</script>
			<?php
		}

		public static function column_headers_labels() {
			return array(
				'wpda_table_name'     => __( 'Table/View Name', 'wp-data-access' ),
				'wpda_schema_name'    => __( 'Database', 'wp-data-access' ),
				'wpda_table_setname'  => __( 'Template Set Name', 'wp-data-access' ),
				'table_found'         => __( 'Exists in DB?', 'wp-data-access' ),
				'table_has_relations' => __( 'Has Relationships?', 'wp-data-access' ),
				'table_rows'          => __( '#Rows', 'wp-data-access' ),
			);
		}

		public function get_bulk_actions() {
			$actions = parent::get_bulk_actions();

			unset(
				$actions['bulk-export'],
				$actions['bulk-export-xml'],
				$actions['bulk-export-json'],
				$actions['bulk-export-excel'],
				$actions['bulk-export-csv']
			);

			return $actions;
		}

	}

}
