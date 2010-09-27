<?php
// Wordpress Plugin
if(defined('WP_PLUGIN_DIR')) {
	add_action('comment_post', 'InvisibleCaptchaTest');
	add_action('wp_footer', 'AddInvisibleCaptcha');
	
	function InvisibleCaptchaTest($comment_id) {
		$captcha = new captcha;
		if($captcha->verify()) {
			do_action('wp_set_comment_status', $comment_id, 'approve');
		} else {
			do_action('wp_set_comment_status', $comment_id, 'delete');
		}
	}
	
	function AddInvisibleCaptcha() {
		$captcha = new captcha;
		$captcha->add('commentform',true);
	}
}

// This class requires session_start() be run before calling it, but must be initiated before any non-header data is sent

class captcha {
	function __construct() {
		$this->url = ($_SERVER['https'] == 'on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->referer = ($_SERVER['HTTP_REFERER'] == $this->url?'':$_SERVER['HTTP_REFERER']);
		
		if(!isset($_SESSION['invisibleCaptcha']['code'])) {
			$_SESSION['invisibleCaptcha']['code'] = rand(1111111111,99999999999999);
			$_SESSION['invisibleCaptcha']['jHack'] = rand(222222,333333);
			$_SESSION['invisibleCaptcha']['vURL'] = $this->url;
			$_SESSION['invisibleCaptcha']['vHost'] = $this->referer;
		} elseif(isset($_SESSION['invisibleCaptcha']['code']) && isset($_GET['invisibleCaptcha']) && $_SERVER['HTTP_REFERER'] == $_SESSION['invisibleCaptcha']['vURL']) {
			echo (string) $_SESSION['invisibleCaptcha']['code'];
			die();
		} elseif(isset($_SESSION['invisibleCaptcha']['code']) && isset($_GET['invisibleCaptcha']) && $_SERVER['HTTP_REFERER'] != $_SESSION['invisibleCaptcha']['vURL']) {
			// probable attack, reset information
			unset($_SESSION['invisibleCaptcha']);
		}
	}


	function add($formid,$includejQuery=false) {
		$jHack = $_SESSION['invisibleCaptcha']['jHack'];
		$phpself = $_SERVER['PHP_SELF'];
		if(preg_match('/\?/',$phpself)) {
			$phpself .= '&invisibleCaptcha=true';
		} else {
			$phpself .= '?invisibleCaptcha=true';
		}
		
		if($includejQuery) {
			echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>';
		}
		
		echo <<<jQuery
<script type="text/javascript">
		captchasafetynet = 0;
		$('#$formid').append('<input type="hidden" name="captcha" id="captcha" value="fail" /><input type="text" name="$jHack" value="" style="display: none;" />');
  		$('#$formid :input').keyup(function() {
  			if(captchasafetynet == 0) {
				$.get('$phpself', function(data) {
				  $('#captcha').val(data);
				});
				captchasafetynet++;
			}
		});

</script>
jQuery;
	}
	
	
	function verify() {
		if($_REQUEST['captcha'] == $_SESSION['invisibleCaptcha']['code'] && $_REQUEST[$_SESSION['invisibleCaptcha']['jHack']] == '') {
			$test = true;
		} else {
			$test = false;
		}

		unset($_SESSION['invisibleCaptcha']);
		return $test;
	}

} // END CLASS	
?>