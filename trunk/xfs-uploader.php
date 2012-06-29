#!/usr/bin/php
<?php

define('USERNAME', 'free');	// feel 'free' for non-member upload
define('PASSWORD', 'free'); 	// feel 'free' for non-member upload
define('CURL_STARTURL', 'http://www.maknyos.com/');

define('CURL_USERAGENTS', 'XFS-FSUploader');
define('CURL_BIN', '/usr/bin/curl');

define('UPLOAD_FILE', 'file_0'); // dont touch this, except you know what the mean!

include('lib/xml.php');
$filepath = '';

function br2() {
	return PHP_EOL;
}

function print_log($message, $function='', $type=0, $is_exit=false) {
	$log_type = '';
	if($type == 0) {
		$log_type = '[ERROR]';
	} else 
	if($type == 1) {
		$log_type = '[INFO]';
	}
	
	echo $log_type . ' ' . ($type === 0 ? $function : '+') . ': ' . $message . br2();
	
	if($is_exit) {
		do_exit();
	}
	
}

function do_exit() {
	remove_link();
	exit();
}

function remove_link() {
	global $filepath;
	
	if(!empty($filepath)) {
		$path_parts = pathinfo($filepath);
				
		exec('/bin/rm -f ' . $path_parts['dirname'] . '/' . UPLOAD_FILE . ' > /dev/null 2>&1');
	}
}

function create_link() {
	global $filepath;
	
	$path_parts = pathinfo($filepath);
	chdir($path_parts['dirname']);
	
	$exec = exec('/bin/ln -s ' . $filepath . ' ' . UPLOAD_FILE);
	if(!empty($exec)) {
		print_log('Cannot create symbolic link (Reason: ' . $exec . '). Exit.', '', 0, true);
	}
	
}

function check_args() {
	global $filepath;
	
	if(empty($filepath)) {
		return false;
	}
	return true;
}

function get_auth() {
	global $filepath;
	
	if(USERNAME == 'free' && PASSWORD == 'free') {
		$command = CURL_BIN . ' -A ' . CURL_USERAGENTS .' -s -F "op=api_get_limits" ' . CURL_STARTURL;
	} else {
		$command = CURL_BIN . ' -A ' . CURL_USERAGENTS .' -s -F "op=api_get_limits" -F "login=' . USERNAME . '" -F "password=' . PASSWORD . '" ' . CURL_STARTURL;
	}
	$xml = exec($command, $out);
	$str = implode("\n", $out);

	$xml = xml2array($str);
	
	if(!array_key_exists('Data', $xml)) {
		print_log('Cannot retrieve XML data. Exit.', 'get_auth', 0, true);
	}
	
	if(empty($xml['Data']['ServerURL']) || empty($xml['Data']['MaxUploadFilesize'])) {
		print_log('Important XML value is empty.', 'get_auth', 0, false);
		print_log('Uploading aborted. Exit.', 'get_auth', 0, true);
	}
	
	$xml_error 		= $xml['Data']['Error'];
	$xml_sessid 	= $xml['Data']['SessionID'];
	$xml_server 	= $xml['Data']['ServerURL'];
	$xml_maxsize 	= $xml['Data']['MaxUploadFilesize'];
	
	print_log('---------------------------------------------------------------------------', 'get_auth', 1, false);
	print_log('ServerURL : ' . $xml_server, 'get_auth', 1, false);
	print_log('SessionID : ' . $xml_sessid, 'get_auth', 1, false);
	print_log('MaxSize   : ' . $xml_maxsize . ' MB', 'get_auth', 1, false);
	print_log('---------------------------------------------------------------------------', 'get_auth', 1, false);
	
	if(empty($xml_error)) {
		if(empty($xml_sessid)) {
			print_log('Uploading with free user account.', 'get_auth', 1, false);
			do_upload($filepath, $xml_sessid, $xml_server, $xml_maxsize);
		} else {
			print_log('Uploading with registered user account.', 'get_auth', 1, false);
			do_upload($filepath, $xml_sessid, $xml_server, $xml_maxsize, false);
		}
	}
	else if($xml_error == 'auth_error') {
		print_log('Username/password invalid. Exit.', 'get_auth', 0, true);
	}

}

function do_upload($path, $sess_id, $server, $max_size, $is_member=true) {
	$size = filesize($path);
	
	if($size > $max_size*(1024*1024)) {
		print_log('Your file is too big (Reason: max size is '. $max_size .' MB)', 'do_upload', 0, true);
	}

	$out = array();
	$md5_sid = substr(md5(time()), 0, 16);

	$path_parts = pathinfo($path);
	chdir($path_parts['dirname']);
	
	print_log('Please wait, uploading...', 'do_upload', 1, false);
	
	$command = CURL_BIN . ' -F "sid=' . $md5_sid . '" -F "file=@' . UPLOAD_FILE . '" ' . $server . '/up.cgi';
	$xml = exec($command, $out);
	
	if(is_array($out)) {
		if($out[0] == '<OK>') {
			print_log('File uploaded to the server successfully. Getting links...', 'do_upload', 1, false);
			do_get_links($path, $sess_id, $server, $md5_sid);
		} else {
			print_log('File cannot be uploaded to the server. (Reason: ' . $out[0] . '). Exit.', 'do_upload', 0, true);
		}
	} else {
		print_log('Invalid responds. Exit', 'get_auth', 0, true);
	}
}

function do_get_links($path, $sess_id, $server, $md5) {
	$out = array();
	
	$md5_sid = substr(md5(time()), 0, 16);
	
	$command = CURL_BIN . ' -A ' . CURL_USERAGENTS . ' -s -F "sid=' . $md5. '" -F "session_id=' . $sess_id . '" -F "fname=' . basename($path) . '" -F "op=compile" ' . $server . '/api.cgi';
	
	exec($command, $out);
	
	if(is_array($out)) {
		$str = implode('', $out);
		$str = str_replace('#', '', $str);
		$xml = xml2array($str);
		
		if(array_key_exists('Links', $xml)) {
			print_log('File uploaded successfully.', '+', 1, false);
			print_log('---------------------------------------------------------------------------', '+', 1, false);
			print_log('Link        : ' . $xml['Links']['Link'], '+', 1, false);
			print_log('Delete Link : ' . $xml['Links']['DelLink'], '+', 1, false);
			print_log('---------------------------------------------------------------------------', '+', 1, true);
		} else
		if(array_key_exists('Error', $xml)) {
			print_log('Cannot compile uploaded file. (Reason: ' . $xml['Error'] . '). Exit', 'do_get_links', 0, true);
		} else {
			print_log('Invalid responds. (Reason: unknown XML). Exit', 'do_get_links', 0, true);
		}
	} else {
		print_log('Invalid responds. Exit', 'do_get_links', 0, true);
	}
}

if(!empty($argv[1])) {
	$filepath = $argv[1];
}

if(check_args()) {
	if(!file_exists($filepath)) {
		print_log('File is not exists. Exit.', '', 0, true);
	}
	
	$chkpath = str_split($filepath);
	if($chkpath[0] != '/') {
		print_log('Path must be an absolute/full path. Exit.', 'check_path', 0, true);
	}
	
	remove_link();
	create_link();
	
	get_auth();
} else {
	print_log('Must be execute with \'path filename\' arguments. Exit.', 'check_args', 0, true);
}

