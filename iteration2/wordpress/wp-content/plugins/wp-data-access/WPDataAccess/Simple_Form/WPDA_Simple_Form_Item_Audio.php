<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	class WPDA_Simple_Form_Item_Audio extends WPDA_Simple_Form_Item_Media {

		/**
		 * WPDA_Simple_Form_Item_Audio constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = array() ) {
			parent::__construct( $args );

			$this->media_frame_title  = __( 'Upload or select audio from your WordPress media library', 'wp-data-access' );
			$this->media_frame_remove = __( 'Remove audio', 'wp-data-access' );
			$this->media_types        = 'audio';
		}

		protected function show_item_media() {
			if ( 'number' === esc_attr( $this->data_type ) ) {
				// Column supports only one media file
				$url = wp_get_attachment_url( esc_attr( $this->item_value ) );
				if ( false !== $url ) {
					$url = wp_get_attachment_url( esc_attr( $this->item_value ) );
					$this->create_item_audio( $url );
				}
			} else {
				// Column supports multiple media files
				if ( null !== $this->item_value && '' !== $this->item_value ) {
					$media_ids = explode( ',', $this->item_value );//phpcs:ignore - 8.1 proof
					foreach ( $media_ids as $media_id ) {
						$url = wp_get_attachment_url( esc_attr( $media_id ) );
						if ( false !== $url ) {
							$this->create_item_audio( $url );
						}
					}
				}
			}
		}

		private function create_item_audio( $url ) {
			?>
			<span class="wpda_media_container wpda_audio">
				<audio src="<?php echo esc_url( $url ); ?>" controls></audio>
			</span>
			<?php

		}

	}

}
