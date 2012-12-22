<?php
define ('WORDPRESS_20', 3308); // WP 2.0
define ('WORDPRESS_21', 4772); // WP 2.1
define ('WORDPRESS_23', 5495); // WP 2.3
define ('WORDPRESS_27', 9872); // WP 2.7
define ('WORDPRESS_28', 11548); // WP 2.8

if (!function_exists('bestThinkingMenuExists'))
{
function bestThinkingMenuExists()
{
	global $menu;
	foreach( $menu as $m )
	{
		if( in_array('bt-main-menu',$m) )
		{
			return true;
		}
	}
	return false;	
}
}

if (!function_exists('bestThinkingTestWpVersion'))
{
function bestThinkingTestWpVersion ($minVer)
{
	global $wp_db_version;
	$ver = (isset($wp_db_version) ? $wp_db_version : 0);
	return ($ver >= $minVer);
}
}

if (!function_exists('bestThinkingDebugMessage'))
{
function bestThinkingDebugMessage( $message )
{
	if( BESTTHINKING_DEBUG == 1 )
	{
		$fh = fopen( dirname(__FILE__) . '/debug.txt', 'a+') or die("can't open file");
		fwrite( $fh, $message );
		fwrite( $fh, "\n" );
		fclose( $fh );
	}
}
}

if (!function_exists('bestThinkingCrosspostActive'))
{
function bestThinkingCrosspostActive()
{
  return is_plugin_active('bestthinking/bt_crosspost.php');
}
}

if (!function_exists('bestThinkingSyndicationActive'))
{
function bestThinkingSyndicationActive()
{
  return is_plugin_active('btsyndication/bestthinkingsyndication.php');
}
}

if (!function_exists('bestThinkingAbout'))
{
function bestThinkingAbout()
{
  ?>
<div class="wrap">
  <!--<div class="icon32" id="icon-options-general">
    <br />
  </div>-->
  <h2>About BestThinking Plugins</h2>
  <p>The BestThinking family of plugins make it easy to share quality content between your WordPress blog and BestThinking.com.</p>

<!-- List the status of all available BT plugins (offer to fetch any that are missing/inactive) -->
  <ul style="list-style-type: disc; padding-left: 30px;">
    <li>
      <?php
      if( bestThinkingCrosspostActive() )
      { ?>
      The <b>BestThinking Crossposting Plugin</b> is installed and active.
      <?php }
      else
      { ?>
      <a href="http://content.bestthinking.com/content/downloads/wp/bestthinking.zip" target="_blank">
        Click here to download the <b>BestThinking Crossposting Plugin</b>.
      </a>
      <?php } ?>
    </li>
    <li>
      <?php
      if( bestThinkingSyndicationActive() )
      { ?>
      The <b>BestThinking Syndication Plugin</b> is installed and active.
      <?php }
      else
      { ?>
      <a href="http://www.bestthinking.com/content/downloads/btsyndication.zip" target="_blank">
        Click here to download the <b>BestThinking Syndication Plugin</b>.
      </a>
      <?php } ?>
    </li>
  </ul>
  <br />

  <?php if (bestThinkingCrosspostActive() && bestThinkingSyndicationActive())
  { ?>
  <p>
      <a href="#general-FAQ">General FAQ</a> |
      <!--<a href="#crosspost-FAQ">Crosspost FAQ</a> |-->
      <a href="#syndication-FAQ">Syndication FAQ</a>
  </p>
  <?php } ?>

  <?php if (bestThinkingCrosspostActive() && bestThinkingSyndicationActive())
  { ?>
  <hr />
  <h3 id="general-FAQ">General - Frequently Asked Questions</h3>
  <dl>
    <dt>
      <br/>
      <i style="font-size: 110%;">What WordPress plugins are available from BestThinking?</i>
    </dt>
    <dd>
      <p>
        There are currently two plugins available, one for syndication and one for crossposting. In order to use either
        plugin, you need to have an active user account on <a href="http://www.bestthinking.com/" target="_blank">BestThinking.com</a>.
      </p>
    </dd>

    <dt>
      <br/>
      <i style="font-size: 110%;">What's the difference between the crossposting and syndication plugins?</i>
    </dt>
    <dd>
      <p>
        The two plugins provide different ways of sharing content. Some people will only need one of these, but both 
        plugins make a powerful combination for increasing your audience and the "stickiness" of your WordPress blog.
      </p>
      <p>
        The <b>Crossposting Plugin</b> makes it easy for you to post your WordPress blog entries to your BestThinking.com 
        Thinker blog. This expands your audience and increases your visibility.
      </p>
      <p>
        The <b>Syndication Plugin</b> allows you to supplement your WordPress blog with high quality content from 
        identity verified Thinkers.
      </p>      
    </dd>
  </dl>
  <br />
  <?php } ?>

<?php if (bestThinkingSyndicationActive())
{ ?>
  <hr />
  <h3 id="syndication-FAQ">Syndication - Frequently Asked Questions</h3>
  <dl>
    <dt>
      <br/>
      <i style="font-size: 110%;">What's the difference between the syndication plugin and the syndication widget?</i>
    </dt>
    <dd>
      <p>
        The <b>syndication plugin</b> stores the syndicated content in your 
        WordPress database where it will be indexed by search engines. This delivery method supports WordPress features such 
        as comments, trackbacks and categories.
      </p>
      <p>
        When using the <b>syndication widget</b>, the content will not be imported into your 
        WordPress database. Search engines will be able to index the syndicated content on your site, but WordPress features 
        such as comments, trackbacks and categories will not be supported. Your WordPress blog must allow plugins in order to 
        use this method.
      </p>

    </dd>
  </dl>
  <p>
    &copy; 2010 Best Thinking, Inc. - All Rights Reserved.
  </p>
  <br />
  <?php } ?>

</div>

<?php
}
}
?>
