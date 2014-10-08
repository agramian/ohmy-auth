<?php namespace ohmy\Components\Http\Curl;

/*
 * Copyright (c) 2014, Yahoo! Inc. All rights reserved.
 * Copyrights licensed under the New BSD License.
 * See the accompanying LICENSE file for terms.
 */

use ohmy\Components\Http;

class Request implements Http {

    public function POST($url, Array $arguments=array(), Array $headers=array(), $multipart=false) {
        $self = $this;
        return new Response(function($resolve, $reject) use($self, $url, $arguments, $headers, $multipart) {
            # initialize curl
            $handle = curl_init();
			curl_setopt ($handle, CURLOPT_POST, true);				
			if ($multipart == true) {
				# if multipart form and only one parameter passed,
				# meaning it is a custom constructed multipart form,
				# pass the first element instead of an array of 1
				if ($multipart && count($arguments) == 1) {
					$arguments = $arguments[0];
				}				
				curl_setopt ($handle, CURLOPT_POSTFIELDS, $arguments);
			}
			else {
            	$headers['Content-Type'] = 'application/x-www-form-urlencoded';
				curl_setopt ($handle, CURLOPT_POSTFIELDS, http_build_query($arguments, '', '&'));
			}	
            # set curl options
            curl_setopt_array($handle, array(
                CURLOPT_VERBOSE    => false,     
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL        => $url,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HEADER		=> true
            ));		
			if ($multipart == true) {
				curl_setopt ($handle, CURLOPT_HEADER, false);
			}
			curl_setopt ($handle, CURLOPT_HTTPHEADER, $self->_headers($headers));
            # execute curl
            $raw = curl_exec($handle);
			
            # close curl handle
            curl_close($handle);

            # resolve
            $resolve($raw);
        });
    }

    public function GET($url, Array $arguments=array(), Array $headers=array()) {

        $self = $this;
        return new Response(function($resolve, $reject) use($self, $url, $arguments, $headers) {

            # initialize curl
            $handle = curl_init();
            $url = (count($arguments)) ? "$url?".http_build_query($arguments) : $url;

            # set curl options
            curl_setopt_array($handle, array(
                CURLOPT_VERBOSE    => false,
                CURLOPT_URL        => $url,
                CURLOPT_HTTPHEADER => $self->_headers($headers),
                CURLOPT_HEADER     => true,
                CURLOPT_RETURNTRANSFER => true
            ));

            # execute curl
            $raw = curl_exec($handle);

            # close curl handle
            curl_close($handle);

            # resolve
            $resolve($raw);
        });
    }

    public function _headers($headers) {
        $output = array();
        if (!$headers) return $output;
        foreach($headers as $key => $value) {
            array_push($output, "$key: $value");
        }
        return $output;
    }	
}
