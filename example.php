<?php
session_start(); // Must be called prior to initiating the class
require_once('captcha.php');
$captcha = new captcha;

if(!isset($_REQUEST['submit'])) {
?>

<form action="example.php" id="example" method="post">
test <input type="radio" name="ha" value="ha" /> haha <br />
test 1 <input type="text" name="test" /><br />
test 2 <input type="text" name="test1" /><br />
test 3 <input type="text" name="test2" /><br />
test 4 <input type="text" name="test3" /><br />
<input type="submit" name="submit" value="submit" />
</form>

<?php
	// Form ID, Include jQuery from Google (default is false)
	$captcha->add("example",true);

} else {
	if($captcha->verify()) {
		echo 'form success';
	} else {
		echo 'form failed';
	}
	
	echo ' <a href="example.php">retry</a>';
}
?>