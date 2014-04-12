<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk_assist_socket.php 2013-8-18$
 */

namespace yk\helper;

class socket{
	
	//CURLOPT_USERAGENT, CURLOPT_HTTPHEADER, CURLOPT_COOKIE
	public static function curl($url, $post_data, $extra = array(), $timeout = 15){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!empty($extra)) {
			foreach ($extra as $opt => $value){
				curl_setopt($ch, $opt, $value);
			}
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno || ($status['http_code'] != 200 && $status['http_code'] != 400) ){
			return $data;
		} else {
			return $data;
		}
	}
	
	public static function open($url, $post = '', $limit = 0, $cookie = array(), $ip = '', $timeout = 15, $block = true, $encodetype  = 'URLENCODE', $position = 0, $extparams = array()){
	
		$return = '';
	
		$matches = parse_url($url);
		$scheme = $matches['scheme'];
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = !empty($matches['port']) ? $matches['port'] : 80;
		
		$cookie = implode('; ', $cookie);
		if($post) {
			$out = "POST $path HTTP/1.0\r\n";
			$header = "Accept: */*\r\n";
			$header .= "Accept-Language: zh-cn\r\n";
			$boundary = $encodetype == 'URLENCODE' ? '' : '; boundary='.trim(substr(trim($post), 2, strpos(trim($post), "\n") - 2));
			$header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data$boundary\r\n";
			$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$header .= "Host: $host:$port\r\n";
			$header .= 'Content-Length: '.strlen($post)."\r\n";
			$header .= "Connection: Close\r\n";
			$header .= "Cache-Control: no-cache\r\n";
			$header .= "Cookie: $cookie\r\n\r\n";
			$out .= $header.$post;
		} else {
			$out = "GET $path HTTP/1.0\r\n";
			$header = "Accept: */*\r\n";
			$header .= "Accept-Language: zh-cn\r\n";
			$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$header .= "Host: $host:$port\r\n";
			$header .= "Connection: Close\r\n";
			$header .= "Cookie: $cookie\r\n\r\n";
			$out .= $header;
		}
	
		$fpflag = 0;
		if(!$fp = @yfsocketopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout)) {
			$context = array(
					'http' => array(
							'method' => $post ? 'POST' : 'GET',
							'header' => $header,
							'content' => $post,
							'timeout' => $timeout,
					),
			);
			$context = stream_context_create($context);
			$fp = @fopen($scheme.'://'.($ip ? $ip : $host).':'.$port.$path, 'b', false, $context);
			$fpflag = 1;
		}
	
		if(!$fp) {
			return '';
		} else {
			stream_set_blocking($fp, $block);
			stream_set_timeout($fp, $timeout);
			@fwrite($fp, $out);
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out']) {
				while (!feof($fp) && !$fpflag) {
					if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
						break;
					}
				}
	
				if($position) {
					$oldchar = '';
					for($i=0; $i<$position; $i++) {
						$char = fgetc($fp);
						if($char == "\n" && $oldchar != "\r") {
							$i++;
						}
						$oldchar = $char;
					}
				}
	
				if($limit) {
					$return = stream_get_contents($fp, $limit);
				} else {
					$return = stream_get_contents($fp);
				}
			}
			@fclose($fp);
			return $return;
		}
	
	}
	
}