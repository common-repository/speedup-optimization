<?php
/**
 * File firewall.speedup.php
 * Class SG_SpeedUp
 * 
 * ver.: 1.3
 * date: 1 Feb 2022
 */
/* USAGE:

Insert into index.php

on the top:

<?php 
// OPTIMIZATION START - TOP - Added by SiteGuarding.com (21D467F52F85)
$SG_SPEEDUP_CLASS_FILE = dirname(__FILE__)."/webanalyze/firewall/firewall.speedup.php";
if (file_exists($SG_SPEEDUP_CLASS_FILE)) {
    require_once($SG_SPEEDUP_CLASS_FILE);
    $SG_SpeedUp = new SG_SpeedUp();
    $SG_SpeedUp->Start();
} else die("Error! Absent: ".$SG_SPEEDUP_CLASS_FILE);
// OPTIMIZATION END - TOP - Added by SiteGuarding.com
?>


on the bottom:

<?php 
// OPTIMIZATION START - BOTTOM - Added by SiteGuarding.com (555092211981)
if (class_exists("SG_SpeedUp")) $SG_SpeedUp->Finalize();
// OPTIMIZATION END - BOTTOM - Added by SiteGuarding.com
?>




.htaccess


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




*/
class SG_SpeedUp {
    
    var $debug = true; 
    var $enabled = true; 
    var $store_cache = true; 
    var $dir_cache = ''; 
    var $url = ''; 
    var $cached_filename = ''; 
    var $settings_ini = array(); 


    public function Start()
    {
        $this->dir_cache = dirname(__FILE__).'/'.'speedup_cache';
        
        $file_on = dirname(__FILE__).'/firewall.speedup.on';
        $file_off = dirname(__FILE__).'/firewall.speedup.off';
        
        if (file_exists($file_off))
        {
            $this->enabled = false;
            unlink($file_off);
            if (file_exists($file_on)) unlink($file_on);
            return;
        }
        
        if (!file_exists(dirname(__FILE__).'/firewall.speedup.on')) $this->enabled = false;
        
        if (isset( $_GET['nocache'] ) ) $this->enabled = false;
        if (!$this->enabled) return;
        
        // Read firewall.speedup.ini
        $this->settings_ini = $this->ReadSettingsINI();
        
        // Check for debug mode
        if (isset($this->settings_ini['debug']) && $this->settings_ini['debug'] == 1) $this->debug = true;
        else $this->debug = false;
        
                
        // Init
        $this->url = $_SERVER['REQUEST_URI'];
        if ($this->url == '/index.php')    
        {
            // Rewrite some URLs
            $this->url = '/';
            
            if ($this->debug) $this->SaveDebug('Rewrite URL /index.php -> /');
        }
        
        // Check for excluded cookies
        if ($this->isExcludedCookieExists())
        {
            
            if ($this->debug) $this->SaveDebug('Exclude cookie found: '.$this->url);
            $this->enabled = false;
            return;
        }

        
        // Check for excluded URLs
        if ($this->isExcludedURL($this->url))
        {
            if ($this->debug) $this->SaveDebug('Exclude URL: '.$this->url);
            $this->enabled = false;
            return;
        }

        
        
        $this->cached_filename = md5($this->url).'.dat';
        
        if (!file_exists($this->dir_cache))
        {
            mkdir($this->dir_cache);
            
            $fp = fopen($this->dir_cache.'/'.'.htaccess', 'w');
            $line = 'order deny,allow'."\n".'deny from all'."\n";
            fwrite($fp, $line);
            fclose($fp);
        }

        
        ini_set("zlib.output_compression", 1);
        
        if ($_SERVER["REQUEST_METHOD"] == 'POST')
        {
            $this->store_cache = false;
                
            if ($this->debug) $this->SaveDebug('Detected POST method page: '.$this->url);
        }
        
        if ($this->store_cache)
        {
            if ($this->store_cache && file_exists($this->dir_cache.'/'.$this->cached_filename))
            {
            	$fp = fopen($this->dir_cache.'/'.$this->cached_filename, "r");
            	$html = fread($fp, filesize($this->dir_cache.'/'.$this->cached_filename));
            	fclose($fp);
                
                if ($this->debug) $this->SaveDebug('Load cached page: '.$this->cached_filename.', '.$this->url);
            	
            	die($html);
            }
            else {
                if ($this->debug) $this->SaveDebug('Open live page (cache status: '.$this->store_cache.'): '.$this->url);
                
				ob_start(array($this, 'FinalizePreSave'));
            }
        }
        else {
            if ($this->debug) $this->SaveDebug('Open live page (cache status: '.$this->store_cache.'): '.$this->url);
        }

    }


	public function FinalizePreSave($buffer)
	{
		if (strlen($buffer) == 0) return $buffer;
		
        $fp = fopen($this->dir_cache.'/'.$this->cached_filename, 'a');
        fwrite($fp, $buffer."\n".'<!-- Created by SpeedUp '.date("Y-m-d H:i:s").' -->'."\n");
        fclose($fp);
		
		return $buffer;
	}



    public function Finalize()
    {
        if (!$this->store_cache || !$this->enabled) return;
        
        //$html = $this->html_parts.ob_get_contents();
		ob_flush();
        ob_end_clean();
		
		$cached_filename = $this->dir_cache.'/'.$this->cached_filename;
		
		if (file_exists($cached_filename))
		{
			$fp = fopen($cached_filename, "r");
			$html = fread($fp, filesize($cached_filename));
			fclose($fp);
		}
		else $html = '';
        
        
        // Create cache file
        $http_status = http_response_code();
        if ($this->isExcludedHTTPstatus($http_status))
        {
			if ($html != '') unlink($cached_filename);	// Delete created cache file
			
            if ($this->debug) $this->SaveDebug('Skip http status: '.$http_status.', '.$this->url);			
        }
        else {
            if ($this->store_cache)
            {
                /*$fp = fopen($this->dir_cache.'/'.$this->cached_filename, 'w');
                fwrite($fp, $html);
                fclose($fp);*/
                
                // Save index
                $fp = fopen($this->dir_cache.'/'.'_index.log', 'a');
                $line = $this->cached_filename.','.$this->url."\n";
                fwrite($fp, $line);
                fclose($fp);
                
                if ($this->debug) $this->SaveDebug('Store page: '.$this->cached_filename.', '.$this->url.', (http status: '.$http_status.')');
            }
            else if ($this->debug) 
				{
					if ($html != '') unlink($cached_filename);	// Delete created cache file
					
					$this->SaveDebug('Skip store page: '.$this->cached_filename.', '.$this->url.', (http status: '.$http_status.')');
				}
        }
        
		//if ($html == '') $html = 'Speedup cache file is absent';
		
        die($html);
    }
    
    
    public function SaveDebug($txt, $error = false)
    {
        $fp = fopen($this->dir_cache.'/'.'_debug.log', 'a');
        $line = date("Y-m-d H:i:s").' '.$this->Get_visitor_IP_address().' '.$txt."\n";
        if ($error) $line = '!!! ERROR !!! '.$line."\n";
        fwrite($fp, $line);
        fclose($fp);
    }
    
    public function ReadSettingsINI()
    {
        $ini_file = dirname(__FILE__).'/firewall.speedup.ini';
        if (file_exists($ini_file))
        {
            return parse_ini_file($ini_file);
        }
        else return array();
    }
    
    
    public function isExcludedURL($REQUEST_URI)
    {
        if (!isset($this->settings_ini['exclude_urls']) || count($this->settings_ini['exclude_urls']) == 0) return false;  // not excluded
        
        foreach ($this->settings_ini['exclude_urls'] as $rule_url)
        {
            $rule_url_clean = str_replace("*", "", $rule_url);
            if ($rule_url != '' && $rule_url[0] == '*')
            {
                if ($rule_url[strlen($rule_url)-1] == '*')  // e.g. *xxx*
                {
                    if (stripos($REQUEST_URI, $rule_url_clean) !== false)
                    {
                        return true;    // excluded
                    }
                }
                else {
                    $tmp_pos = stripos($REQUEST_URI, $rule_url_clean);
                    if ($tmp_pos !== false && $tmp_pos + strlen($rule_url_clean) == strlen($REQUEST_URI))     // e.g. *xxx
                    {
                        return true;    // excluded
                    }
                }
            }
            else {
                if ($rule_url[strlen($rule_url)-1] == '*')  // e.g. /xxx*
                {
                    $tmp_pos = stripos($REQUEST_URI, $rule_url_clean);
                    if ( $tmp_pos !== false && $tmp_pos == 0)
                    {
                        return true;    // excluded
                    }
                }
                else {
                    if ($rule_url == $REQUEST_URI)  // e.g. /xxx/
                    {
                        return true;    // excluded
                    }
                }
            }
        }
        
        return false;  // not excluded
    }

    
    public function isExcludedHTTPstatus($http_response_code)
    {
        if (!isset($this->settings_ini['skip_http_response_code']) || count($this->settings_ini['skip_http_response_code']) == 0) return false;  // not excluded
        
        foreach ($this->settings_ini['skip_http_response_code'] as $setting_response_code)
        {
            if ($setting_response_code == $http_response_code) return true;
        }
        
        return false;  // not excluded
    }
    
    
    public function isExcludedCookieExists()
    {
        if (!isset($this->settings_ini['exclude_cookies']) || count($this->settings_ini['exclude_cookies']) == 0) return false;  // no excluded cookies
        
        if (count($_COOKIE))
        {
            foreach ($this->settings_ini['exclude_cookies'] as $setting_excluded_cookie_info)
            {
                $setting_excluded_cookie_array = explode(',', $setting_excluded_cookie_info);
                $setting_excluded_cookie = trim($setting_excluded_cookie_array[0]);
                if (isset($setting_excluded_cookie_array[1])) $setting_excluded_cookie_size = intval($setting_excluded_cookie_array[1]);
                else $setting_excluded_cookie_size = false;
                
                if (substr($setting_excluded_cookie, -1) == '*')
                {
                    $setting_excluded_cookie = substr($setting_excluded_cookie, 0, -1); // without * in the end
                    
                    foreach ($_COOKIE as $cookie_name => $v)
                    {
                        $tmp_i = strpos($cookie_name, $setting_excluded_cookie);
                        if ($tmp_i !== false && $tmp_i == 0)
                        {
                            if ($setting_excluded_cookie_size !== false)
                            {
                                // Check for cookie size
                                if (strlen($v) >= $setting_excluded_cookie_size) 
                                {
                                    return true;  // excluded cookie found with size
                                }
                                else {
                                }
                            }
                            else return true;  // excluded cookie found
                        }
                    }
                }
                else {
                    foreach ($_COOKIE as $cookie_name => $v)
                    {
                        if ($cookie_name == $setting_excluded_cookie) 
                        {
                            if ($setting_excluded_cookie_size !== false)
                            {
                                // Check for cookie size
                                if (strlen($v) >= $setting_excluded_cookie_size) return true;  // excluded cookie found with size
                            }
                            else return true;  // excluded cookie found
                        }
                    }
                }
            }
        }
        
        return false;  // no excluded cookies
    }
    
    public function Get_visitor_IP_address()
    {
        $ip_address = $_SERVER["REMOTE_ADDR"];
        if (isset($_SERVER["HTTP_X_REAL_IP"]) && filter_var($_SERVER["HTTP_X_REAL_IP"], FILTER_VALIDATE_IP)) $ip_address = $_SERVER["HTTP_X_REAL_IP"];
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && filter_var($_SERVER["HTTP_X_FORWARDED_FOR"], FILTER_VALIDATE_IP)) $ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_VALIDATE_IP)) $ip_address = $_SERVER["HTTP_CF_CONNECTING_IP"];
        
        return $ip_address;
    }

}