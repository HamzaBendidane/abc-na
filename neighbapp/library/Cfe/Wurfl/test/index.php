<?php
// Include the main class file
require_once('../TeraWurfl.php');

// instantiate a new TeraWurfl object
$wurflObj = new TeraWurfl();

// Get the capabilities of the current client.
//$wurflObj->getDeviceCapabilitiesFromRequest();
$wurflObj->getDeviceCapabilitiesFromAgent("Mozilla/5.0 (Linux; U; Android 4.0.4; en-gb; GT-I9300 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30");

echo "Brand Name: ".$wurflObj->getDeviceCapability('brand_name') ."\n";
echo "model_name: ".$wurflObj->getDeviceCapability('model_name') ."\n";
echo "marketing_name: ".$wurflObj->getDeviceCapability('marketing_name'). "\n";

die;
$is_wireless = $wurflObj->getDeviceCapability('is_wireless_device');
$is_smarttv = $wurflObj->getDeviceCapability('is_smarttv');
$is_tablet = $wurflObj->getDeviceCapability('is_tablet');
$is_phone = $wurflObj->getDeviceCapability('can_assign_phone_number');
$is_mobile_device = ($is_wireless || $is_tablet);

if (!$is_mobile_device) {
	if ($is_smarttv) {
		echo "This is a Smart TV";
	} else {
		echo "This is a Desktop Web Browser";
	}
} else {
	if ($is_tablet) {
		echo "This is a Tablet";
	} else if ($is_phone) {
		echo "This is a Mobile Phone";
	} else {
		echo "This is a Mobile Device";
	}
}
?>