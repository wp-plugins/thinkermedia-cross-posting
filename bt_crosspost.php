<?php
/*
Plugin Name: BestThinking
Plugin URI: http://www.bestthinking.com
Description: Automatically copies new posts to Best Thinking as they are published.
Version: 1.5
Author: <a href="http://www.bestthinking.com">Best Thinking</a>
Author Blog: http://www.bestthinking.com/
Author Company: http://www.bestthinking.com/
*/
include_once( dirname(__FILE__) . '/bt-common.php');
include_once( dirname(__FILE__) . '/xmlrpc.inc');
$GLOBALS['xmlrpc_internalencoding']='UTF-8';

preg_match ('|/wp-content/plugins/(.+)$|',dirname(__FILE__),$ref); //Unix Path
if (isset($ref[1]))
{
	$btCrossPostPath = $ref[1];
}
else
{
	preg_match ('|\\\wp-content\\\plugins\\\(.+)$|',dirname(__FILE__),$ref); //Windows Path
	if (isset($ref[1]))
	{
		$btCrossPostPath = $ref[1];
	}
}


function bt_postapi($post_id)
{
	if(!$post_id)
	{
		return $post_id;
	}

	if( $_POST['bestthinking_noncename'] != null )
	{
		if ( !wp_verify_nonce( $_POST['bestthinking_noncename'], plugin_basename(__FILE__) ))
		{
			return $post_id;
		}
	}

	$post = & get_post($post_id);
	// Note: Future posts appear when the time is correct.
	if(($post->post_status != "publish" && $post->post_status != "future") || $post ->post_password != "")
	{
		$btpost = get_post_meta($post_id, "_bt_blog_id", true);
		if( $btpost == null || $btpost == 0 )
		{
			return $post_id;
		}

		$bt_api_path = "http://www.bestthinking.com/MetaWeblogApi";
		$bt_username = get_settings('bt_user');
		$bt_password = get_settings('bt_pass');

		$c = new xmlrpc_client($bt_api_path);
		//$c->debug = true; // Uncomment this line for debugging info

		$x = new xmlrpcmsg("blogger.deletePost",
		        array(php_xmlrpc_encode("btappkey"),
		        php_xmlrpc_encode((string)$btpost),
		        php_xmlrpc_encode($bt_username),
		        php_xmlrpc_encode($bt_password),
		        php_xmlrpc_encode(true)));

		$c->return_type = 'phpvals';
		$r =$c->send($x);

        update_post_meta($post_id, "_bt_blog_id", 0 );
		return $post_id;
	}
	if($post->post_type != "post")
	{
		return $post_id;
	}

	if( $_POST['bt_crosspost'] != null )
	{
		if( $_POST['bt_crosspost'] == 0 )
		{
			update_post_meta($post_id, "_bt_no_cross", true );
			return $post_id;
		}
		update_post_meta($post_id, "_bt_no_cross", false );
	}
	else
	{
		if( get_post_meta($post_id, "_bt_no_cross", false) == true )
		{
			return $post_id;
		}
	}

	$bt_api_path = "http://www.bestthinking.com/MetaWeblogApi";
	$bt_username = get_settings('bt_user');
	$bt_password = get_settings('bt_pass');

	$c = new xmlrpc_client($bt_api_path);
	//$c->debug = true; // Uncomment this line for debugging info

	$content['title']=$post->post_title;
	$cats = wp_get_post_categories($post_id);
	$sendcats = array();
	foreach ( (array) $cats as $cat )
	{
		$catname = get_cat_name($cat);
		$sendcats[] = $catname;
	}
	$content['categories'] = $sendcats;

	$tags = wp_get_post_tags($post_id);
	$sendtags = array();
	foreach ( (array) $tags as $tag )
	{
		$sendtags[] = $tag->name;
	}
	$content['mt_keywords'] = implode(',',$sendtags);
	$content['dateCreatedStr'] = $post->post_date;

	if ($post->post_content == null)
	{
		$content['description'] = '';
	}
	else
	{
		$content['description'] = $post->post_content;
	}
	$content['postid'] = (string)$post_id;

	if( $post->comment_status != "closed" )
	{
		$content["mt_allow_comments"] = "1";
	}
	else
	{
		$content["mt_allow_comments"] = "0";
	}
	$btpost = get_post_meta($post_id, "_bt_blog_id", true);
	if( $btpost == null || $btpost == 0 )
	{
		$x = new xmlrpcmsg("metaWeblog.newPost",
		        array(php_xmlrpc_encode("1"),
		        php_xmlrpc_encode($bt_username),
		        php_xmlrpc_encode($bt_password),
		        php_xmlrpc_encode($content),
		        php_xmlrpc_encode(true)));
	}
	else
	{
		$x = new xmlrpcmsg("metaWeblog.editPost",
		        array(php_xmlrpc_encode((string)$btpost),
		        php_xmlrpc_encode($bt_username),
		        php_xmlrpc_encode($bt_password),
		        php_xmlrpc_encode($content),
		        php_xmlrpc_encode(true)));
	}

	$c->return_type = 'phpvals';
	$r =$c->send($x);
/*
	if ($r->errno=="0")
	{
	    echo "<br>Successfully Posted ";
	    print_r($r);
	}
	else
	{
	    echo "<br>There was an error";
	    print_r($r);
	}
*/
	if( $btpost == null || $btpost == 0 )
	{
		update_post_meta($post_id, "_bt_blog_id", $r->val );
	}
	return $post_id;
}

function bt_postdelete( $post_id  )
{
	if(!$post_id)
	{
		return $post_id;
	}
	$btpost = get_post_meta($post_id, "_bt_blog_id", true);
	if( $btpost == null || $btpost == 0 )
	{
		return $post_id;
	}

	$bt_api_path = "http://www.bestthinking.com/MetaWeblogApi";
	$bt_username = get_settings('bt_user');
	$bt_password = get_settings('bt_pass');

	$c = new xmlrpc_client($bt_api_path);
	//$c->debug = true; // Uncomment this line for debugging info
	$x = new xmlrpcmsg("blogger.deletePost",
	        array(php_xmlrpc_encode("btappkey"),
	        php_xmlrpc_encode((string)$btpost),
	        php_xmlrpc_encode($bt_username),
	        php_xmlrpc_encode($bt_password),
	        php_xmlrpc_encode(true)));

	$c->return_type = 'phpvals';
	$r =$c->send($x);

/*
	if ($r->errno=="0")
	    echo "<br>Successfully Deleted ";
	else {
	    echo "<br>There was an error";
	    print_r($r);
	}
*/
	return $post_id;

}

function bt_testpostapi()
{
	$bt_api_path = "http://www.bestthinking.com/MetaWeblogApi";
	$bt_username = get_settings('bt_user');
	$bt_password = get_settings('bt_pass');

	$c = new xmlrpc_client($bt_api_path);
	//$c->debug = true; // Uncomment this line for debugging info

	$content['title']='crosspost login test';
	$content['description'] = '';
	$content['postid'] = '0';
	$content["mt_allow_comments"] = "0";
		$x = new xmlrpcmsg("metaWeblog.newPost",
		        array(php_xmlrpc_encode("1"),
		        php_xmlrpc_encode($bt_username),
		        php_xmlrpc_encode($bt_password),
		        php_xmlrpc_encode($content),
		        php_xmlrpc_encode(true)));
	$c->return_type = 'phpvals';
	$r =$c->send($x);
	echo '<div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong>';
	_e('Options saved. - ');
	if ($r->errno=="0")
	{
		_e(' Connection to BestThinking.com successful!');
		echo '</strong></p></div>';
	}
	else
	{
		_e('<font color="#ff0000"> Error connecting to BestThinking.com.  The error returned was: ');
		_e( $r->errstr );
		_e('</font>');
		echo '</strong></p></div>';

		echo '<div id="bt-meta">';
			echo '<div class="hidden" id="bt-details-wrap">';
				echo '<p>';
					print_r($r);
				echo '</p>';
			echo '</div>';
		echo '</div>';

	}
	return;
}

function bt_add_plugindata() {
	global $btCrossPostPath;
	if( !bestThinkingMenuExists() )
	{
		if (bestThinkingTestWpVersion(WORDPRESS_27))
		{
			add_menu_page('BestThinking About', 'BestThinking', 'manage_options', 'bt-main-menu', 'bestThinkingAbout', WP_PLUGIN_URL . '/' . $btCrossPostPath . '/logobt.gif');
		}
		else
		{
			add_menu_page('BestThinking About Settings', 'BestThinking', 'manage_options', 'bt-main-menu', 'bestThinkingAbout');
		}
		add_submenu_page('bt-main-menu', 'BestThinking About', 'About', 'manage_options', 'bt-main-menu', 'bestThinkingAbout');
	}

	add_submenu_page('bt-main-menu', 'BestThinking Crosspost Settings', 'Crossposting', 'manage_options', 'bt-crosspost-page', 'bt_submenu');
	if( get_option('bt_user') != "" )
	{
   		add_meta_box( 'bestthinkingdiv', 'BestThinking.com', 'bt_entry_fields', 'post', 'side' );
   		add_meta_box( 'bestthinkingdiv', 'BestThinking.com', 'bt_entry_fields', 'page', 'side' );
		add_action('delete_post', 'bt_postdelete' );
	}
}

add_action('admin_menu', 'bt_add_plugindata');

add_action('pending_to_publish', 'bt_postapi');
add_action('private_to_publish', 'bt_postapi');
add_action('draft_to_publish', 'bt_postapi');

add_action('publish_to_publish', 'bt_postapi');

add_action('publish_to_draft', 'bt_postapi');
add_action('publish_to_pending', 'bt_postapi');
add_action('publish_to_private', 'bt_postapi');

add_action('future_to_publish', 'bt_postapi');

function bt_submenu() {
	if ($_REQUEST['save']) {
		if(!get_settings('bt_user')) {
			delete_option('bt_user');
			add_option('bt_user', $_POST['bt_user'], 'BestThinkining User ID', 'yes');
			add_option('bt_pass', $_POST['bt_pass'], 'BestThinkining Password', 'yes');
		}
		else {
			update_option('bt_user', $_POST['bt_user']);
			if( $_REQUEST['bt_pass'] != "" ) {
				update_option('bt_pass', $_POST['bt_pass']);
			}
		}
		bt_testpostapi();
	}
?>

    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <div class="wrap" id="bt_options">
        <fieldset id="bt_info">
            <h3>
                BestThinking Options</h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">
                            <label for="bt_user">
                                BestThinking.com User Name (Email address)</label>
                        </th>
                        <td>
                            <input type="input" name="bt_user" id="bt_user" class="regular-text" value="<?php echo get_settings('bt_user'); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="bt_pass">
                                BestThinking.com Password</label>
                        </th>
                        <td>
                            <input type="input" class="regular-text" name="bt_pass" value="" />
                            <span class="setting-description"><?php _e('Only enter a value if you wish to change
                                the stored password. Leaving this field blank will not erase any passwords already
                                stored.'); ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            (c) 2009 Best Thinking, Inc. - All Rights Reserved.
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input name="save" id="save" class="button-primary" tabindex="3" value="Save Changes"
                    type="submit" /></p>
        </fieldset>
    </div>
    </form>

<?php
}
function bt_postoption() {
	global $post;
	echo '<div class="dbx-b-ox-wrapper">' . "\n";
	echo '<fieldset id="bestthinking_fs" class="dbx-box">' . "\n";
	echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' . __( 'BestThinking.com' ) . "</h3></div>";
	echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
	bt_entry_fields();
	echo "</div></div></fieldset></div>\n";
}

function bt_entry_fields() {
	global $post;
	if( $post->ID == '' )
	{
		$check = 0;
	}
	else
	{
		$check = get_post_meta($post->ID, "_bt_no_cross", true);
		if( $check == '' )
		{
			$check = 0;
		}
	}

	echo '<input type="hidden" name="bestthinking_noncename" id="bestthinking_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	echo '<label for="bt_crosspost" class="selectit">' . "\n";
	echo '<input id="bt_crosspost" type="radio" name="bt_crosspost" value="1" ';
	echo checked($check, 0) . '/>' . "\n";
	echo 'Crosspost</label>' . "\n";

	echo '<label for="bt_nocrosspost" class="selectit">' . "\n";
	echo '<input id="bt_nocrosspost" type="radio" name="bt_crosspost" value="0" ';
	echo checked($check, 1) . '/>' . "\n";
	echo 'Do not crosspost</label>' . "\n";
}

?>
