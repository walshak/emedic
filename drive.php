<?php

$output = array();
exec('udevadm info --query=property --name=/dev/sda | grep "ID_SERIAL"', $output);

if (!empty($output)) {
    $serial_number = trim(str_replace("ID_SERIAL=", "", $output[0]));
    echo $serial_number;
    echo '<br>';
} else {
    ///echo "Failed to retrieve serial number.";
}

function getMacAddressLinux()
{
    ob_start();
    // Execute the `ifconfig` or `ip link show` command
    system('ifconfig');
    $output = ob_get_clean();

    // Match the MAC address using regex
    preg_match('/ether ([^\s]+)/', $output, $matches);
    return isset($matches[1]) ? trim($matches[1]) : 'MAC Address not found';
}

echo $m = getMacAddressLinux();

// Create a file and save the MAC address and serial number to it
$file = 'device_info.txt';
$file_path = './' . $file;

// Check if the file already exists
if (file_exists($file_path)) {
    // If the file exists, append the MAC address and serial number to it
    $fp = fopen($file_path, 'a');
    fwrite($fp, "MAC Address: $m\n");
    fwrite($fp, "Serial Number: $serial_number\n\n");
    fclose($fp);
} else {
    // If the file does not exist, create it and write the MAC address and serial number to it
    $fp = fopen($file_path, 'w');
    fwrite($fp, "MAC Address: $m\n");
    fwrite($fp, "Serial Number: $serial_number\n");
    fclose($fp);
}

//echo "Device info saved to $file_path";
