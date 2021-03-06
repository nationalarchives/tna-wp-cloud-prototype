<?php
/**
 * TNA Cloud functions
 *
 */

function tna_cloud_init() {

	$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());

	if ( ! WP_Filesystem($creds) ) {
		return false;
	}

	global $wp_filesystem;

	if( !$wp_filesystem->is_dir(ABSPATH . HTML_DIR) ) {
		$wp_filesystem->mkdir(ABSPATH . HTML_DIR);
	}
}

function notice_function() {
	?>
	<div class="notice">
		<p>Did not work!!!!</p>
	</div>
	<?php
}

function get_rendered_html($page_url) {
	$content = file_get_contents($page_url);

	$content = str_replace( network_site_url(), '/'.HTML_DIR, $content );

	return $content;
}

function render_page_as_html( $ID ) {

	$permalink = get_permalink( $ID );

	if ( $permalink !== network_site_url() ) {
		$path_dir = str_replace( network_site_url(), '', $permalink );
	} else {
		$path_dir = null;
	}

	$path_parts = explode('/', rtrim($path_dir, '/'));

	$html = get_rendered_html($permalink);

	$directory = ABSPATH . HTML_DIR . $path_dir;

	$access_type = get_filesystem_method();
	if($access_type === 'direct') {

		tna_cloud_init();

		global $wp_filesystem;

		$counter = count($path_parts);

		$pre_path = '';

		for ( $i=0 ; $i<=$counter ; $i++ ) {

			$wp_filesystem->mkdir(ABSPATH . HTML_DIR . $pre_path . $path_parts[$i]);

			$pre_path .= $path_parts[$i] . '/';

		}

		$wp_filesystem->put_contents(
			$directory.'index.html',
			$html,
			FS_CHMOD_FILE
		);
	} else {
		/* don't have direct write access. Prompt user with our notice */
		add_action('admin_notices', 'notice_function');
	}

}
