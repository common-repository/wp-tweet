<?php
/**
 * @package WP_Tweet
 */

/*
Plugin Name: WP Tweet
Plugin URI: http://developersmind.com/wordpress-plugins/wp-tweet/
Description: Adds the official <a href="http://blog.twitter.com/2010/08/pushing-our-tweet-button.html">Tweet Button</a>. It lets your users share links directly from the page they’re on. When they click on the Tweet Button, a Tweet box will appear -- pre-populated with a shortened link that points to the item that they’re sharing.
Version: 0.1-RC1
Author: Pete Mall
Author URI: http://developersmind.com/
License: GPLv2
*/

if ( !class_exists( 'WP_Tweet' ) ) :

/**
 * Base class.
 * 
 * @package WP_Tweet
 */ 
class WP_Tweet {
	
	/**
	 * Constructor. Adds hooks.
	 */
	function WP_Tweet() {
		//Bail without 3.0
		if ( ! function_exists( '__return_false' ) )
			return;
			
		// Initialize default options and register the uninstall hook.
		register_activation_hook( __FILE__, array( 'WP_Tweet', 'on_activation' ) );
		
		// Textdomain and whitlist options.
		add_action( 'admin_init',  array( &$this, 'action_admin_init') );
		
		// Add Tweet Button.
		add_action( 'the_content', array( &$this, 'action_the_content' ) );
		
		// Add settings page.
		add_action( 'admin_menu',  array( &$this, 'action_admin_menu' ) );
	}
	
	/**
	 * Runs on activation. Initializes the default options if they don't exist and registers the uninstallation hook.
	 */
	function on_activation() {
		$options = get_option( 'wp-tweet_options' );
		if ( ! $options ) {
			$options = array(
				'data-count' => 'horizontal',
				'data-text'  => 'title',
				'data-url'   => 'page',
				'data-lang'  => 'en',
				'align'      => 'left'
			);
			update_option( 'wp-tweet_options', $options );
		}

		// Register uninstall hook.
		register_uninstall_hook( __FILE__, array( 'WP_Tweet', 'on_uninstall' ) );
	}
	
	/**
	 * Runs on uninstall. Removes all options.
	 */
	function on_uninstall() {
		delete_option( 'wp-tweet_options' );
		delete_option( 'wp-tweet_show' );
	}
	
	/**
	 * Attached to admin_init. Loads the textdomain and whitelist options.
	 */
	function action_admin_init() {
		load_plugin_textdomain( 'wp-tweet', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		register_setting( 'wp-tweet_settings', 'wp-tweet_options' );
		register_setting( 'wp-tweet_settings', 'wp-tweet_show' );
	}
	
	/**
	 * Attached to the_content. Adds the Tweet Button.
	 */
	function action_the_content( $content ) {
		$show_options = get_option( 'wp-tweet_show' );
		if ( ( is_front_page() && $show_options['home'] ) || ( is_single() && $show_options['post'] ) || ( is_page() && $show_options['page'] ) ) {

			$options = get_option( 'wp-tweet_options' );
			
			$button = "<div class='wp-tweet' style='" . esc_attr( $show_options['style'] ) . "'>";
			$button .= "<a href='http://twitter.com/share' class='twitter-share-button'";
			
			if ( "title" == $options['data-text'] )
				$button .= " data-text='" . get_the_title() . "'";
				
			$button .= " data-count='{$options['data-count']}' data-via='{$options['data-via']}'";
			
			if ( $options['data-related'] ) {
				$button .= " data-related='". esc_attr( $options['data-related'] );
					if ( $options['data-related-desc'] )
						$button .= ":" . esc_attr( $options['data-related-desc'] );
					$button .= "'";
			}
			
			$button .= " data-lang='{$options['data-lang']}'>Tweet</a></div>";
			
			if ( $options['before'] )
				$content = $button . $content;
			
			if ( $options['after'] )
				$content .= $button;
			echo '<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
		}
		return $content;
	}
	
	/**
	 * Attached to admin_menu. Adds the Tweet Button Settings Page under the Settings Menu.
	 */
	function action_admin_menu() {
	    add_options_page( 'WP Tweet', 'WP Tweet', 'manage_options', 'wp-tweet-settings', array( &$this, 'settings_page' ) );
	}
	
	/**
	 * Displays the settings page.
	 */
	function settings_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'WP Tweet Settings', 'wp-tweet' ); ?></h2>
			<form action="options.php" method="post"> <?php
			settings_fields( 'wp-tweet_settings' );
			$options = get_option( 'wp-tweet_options' );
			$show_options = get_option( 'wp-tweet_show' ); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e( 'Button Style', 'wp-tweet' ); ?></th>
						<td id="button-style">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Button Style', 'wp-tweet' ); ?></span></legend>
								<p><label>
									<input name="wp-tweet_options[data-count]" type="radio" value="vertical" class="tog" <?php checked( 'vertical', $options['data-count'] ); ?> />
									<?php _e( 'Vertical count', 'wp-tweet' ); ?><br/><img style="margin: 5px 0px 10px 20px;" src="http://s.twimg.com/a/1281662294/images/goodies/tweetv.png" />
								</label>
								</p>
								<p><label>
									<input name="wp-tweet_options[data-count]" type="radio" value="horizontal" class="tog" <?php checked( 'horizontal', $options['data-count'] ); ?> />
									<?php _e( 'Horizontal count', 'wp-tweet' ); ?><br/><img style="margin: 5px 0px 10px 20px;" src="http://s.twimg.com/a/1281662294/images/goodies/tweeth.png" />
								</label>
								</p>
								<p><label>
									<input name="wp-tweet_options[data-count]" type="radio" value="none" class="tog" <?php checked( 'none', $options['data-count'] ); ?> />
									<?php _e( 'No count', 'wp-tweet' ); ?><br/><img style="margin: 5px 0px 10px 20px;" src="http://s.twimg.com/a/1281662294/images/goodies/tweetn.png" />
								</label>
								</p>
								
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Tweet Text', 'wp-tweet' ); ?></th>
						<td id="tweet-text">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Tweet Text', 'wp-tweet' ); ?></span></legend>
								<p>
									<label>
										<input name="wp-tweet_options[data-text]" type="radio" value="title" class="tog" <?php checked( 'title', $options['data-text'] ); ?> />
										<?php _e( 'The title of the page the button is on.', 'wp-tweet' ); ?>
									</label>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'URL', 'wp-tweet' ); ?></th>
						<td id="url">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'URL', 'wp-tweet' ); ?></span></legend>
								<p>
									<label>
										<input name="wp-tweet_options[data-url]" type="radio" value="page" class="tog" <?php checked( 'page', $options['data-url'] ); ?> />
										<?php _e( 'The URL for the page the button is on.', 'wp-tweet' ); ?>
									</label>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wp-tweet_options[data-lang]"><?php _e( 'Langugage' ); ?></label></th>
						<td>
							<select id="wp-tweet_options[data-lang]" name="wp-tweet_options[data-lang]"> <?php
								$langs = array(
									'en' => 'English',
									'fr' => 'French',
									'de' => 'German',
									'es' => 'Spanish',
									'ja' => 'Japanese' );
								foreach ( $langs as $code => $lang ) {
									echo "<option value=\"{$code}\" "; selected( $code, $options['data-lang'] ); echo ">{$lang}</option>";
								} ?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Recommend people to follow', 'wp-tweet' ); ?></th>
						<td id="recommend">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Recommend up to two Twitter accounts for users to follow after they share content from your website. These accounts could include your own, or that of a contributor or a partner.', 'wp-tweet' ); ?></span></legend>
								<p>
									<input name="wp-tweet_options[data-via]" value="<?php esc_attr_e( $options['data-via']); ?>" type="text" size="50" />
									<span class="description">This user will be @ mentioned in the suggested Tweet</span>
								</p>
								<p>
									<input name="wp-tweet_options[data-related]" value="<?php esc_attr_e( $options['data-related']); ?>" type="text" size="50" />
									<span class="description">Related account</span>
								</p>
								<p>
									<input name="wp-tweet_options[data-related-desc]" value="<?php esc_attr_e( $options['data-related-desc']); ?>" type="text" size="50" />
									<span class="description">Related account description</span>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Show on', 'wp-tweet' ); ?></th>
						<td id="show-button">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Show on', 'wp-tweet' ); ?></span></legend>
								<p><label>
									<input name="wp-tweet_show[home]" type="checkbox" value="true" class="tog" <?php checked( 'true', $show_options['home'] ); ?> />
									<?php _e( 'Home page', 'wp-tweet' ); ?>
								</label>
								</p>
								<p><label>
									<input name="wp-tweet_show[post]" type="checkbox" value="true" class="tog" <?php checked( 'true', $show_options['post'] ); ?> />
									<?php _e( 'Posts', 'wp-tweet' ); ?>
								</label>
								</p>
								<p><label>
									<input name="wp-tweet_show[page]" type="checkbox" value="true" class="tog" <?php checked( 'true', $show_options['page'] ); ?> />
									<?php _e( 'Pages', 'wp-tweet' ); ?>
								</label>
								</p>								
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Position', 'wp-tweet' ); ?></th>
						<td id="position">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Position', 'wp-tweet' ); ?></span></legend>
								<p><label>
									<input name="wp-tweet_options[before]" type="checkbox" value="true" class="tog" <?php checked( 'true', $options['before'] ); ?> />
									<?php _e( 'Before content', 'wp-tweet' ); ?>
								</label>
								</p>
								<p><label>
									<input name="wp-tweet_options[after]" type="checkbox" value="true" class="tog" <?php checked( 'true', $options['after'] ); ?> />
									<?php _e( 'After content', 'wp-tweet' ); ?>
								</label>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wp-tweet_show[style]"><?php _e( 'Style', 'wp-tweet' ); ?></label></th>
						<td>
							<input name="wp-tweet_show[style]" id="wp-tweet_show[style]" value="<?php esc_attr_e( $show_options['style'] ); ?>" type="text" size="50" />
							<span class="description">CSS style for the div container for the Tweet Button. Eg: <code>float: left;</code></span>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"> 
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" /> 
			</p>
			</form></div>

			</div>
		<?php
	} 
}

// Initialize.
$_GLOABALS['wp_tweet_instance'] = new WP_Tweet();
endif;
?>