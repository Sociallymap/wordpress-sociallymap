<?php

class ImageUploader
{
    public function upload($targetUrl)
    {
        // check target url not empty
        if (empty($targetUrl)) {
            error_log(PHP_EOL.'# WARNING : Upload class called but target URL is empty #'.PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');
            return 0;
        }

        // $targetUrl = urldecode($targetUrl);

        $parts = parse_url($targetUrl);
        $urlbase = parse_url($targetUrl, PHP_URL_SCHEME).'://'.parse_url($targetUrl, PHP_URL_HOST);
        parse_str($parts['query'], $query);
        var_dump($parts['query']);
        var_dump($query['url']);


        // check for facebook
        if (isset($query['url']) && $urlbase == 'https://external.xx.fbcdn.net') {
            $targetUrl = $query['url'];
            error_log(PHP_EOL.'# WARNING UPLOAD #'.PHP_EOL.'UPLOAD '.$query['url'].PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');
        }

        $allExeptLast = explode('/', $targetUrl);
        array_pop($allExeptLast);
        $targetUrl = preg_replace_callback('#https?://.+/([^?]+)#', function ($match) {
            return join('/', array_map('rawurlencode', explode('/', $match[1])));
        }, $targetUrl);

        $targetUrl = implode("/", $allExeptLast).'/'.$targetUrl;

        error_log(PHP_EOL.'# TRY UPLOAD => '.print_r($targetUrl, true).PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');
        $file = $this->uploadWordpress($targetUrl, 0, null, 'src');
        error_log(PHP_EOL.'# RESULT UPLOAD => '.print_r($file, true).PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');

        // check for error upload
        if (!gettype($file) == "string") {
            error_log(PHP_EOL.'# ERROR UPLOAD #'.PHP_EOL.'MESSAGE ERR : '.print_r($file->errors, true).PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');
        }

        return $file;
    }


    public function uploadWordpress($file, $post_id, $desc = null, $return = 'html')
    {
        if (! empty($file)) {
            // Set variables for storage, fix file filename for query strings.
            preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
            if (! $matches) {
                return new WP_Error('image_sideload_failed', __('Invalid image URL'));
            }

            $file_array = [];
            $file_array['name'] = md5(microtime().rand()).'.'.$matches[1];

            // Download file to temp location.
            $file_array['tmp_name'] = download_url($file);

            error_log(PHP_EOL.'# DETAIL UPLOAD TMP #'.PHP_EOL.print_r($file_array['tmp_name'], true).PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');


            // If error storing temporarily, return the error.
            if (is_wp_error($file_array['tmp_name'])) {
                return $file_array['tmp_name'];
            }

            // Do the validation and storage stuff.
            $id = media_handle_sideload($file_array, $post_id, $desc);

            // If error storing permanently, unlink.
            if (is_wp_error($id)) {
                error_log(PHP_EOL.'# UPLOAD ERROR : #'.PHP_EOL.print_r($file_array['tmp_name'], true).PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');
                @unlink($file_array['tmp_name']);
                return $id;
            }

            $src = wp_get_attachment_url($id);
        }

        // Finally, check to make sure the file has been saved, then return the HTML.
        if (! empty($src)) {
            if ($return === 'src') {
                return $src;
            }

            $alt = isset($desc) ? esc_attr($desc) : '';
            $html = "<img src='$src' alt='$alt' />";
            error_log(PHP_EOL.'# DETAIL UPLOAD TMP #'.PHP_EOL.print_r($file_array['tmp_name'], true).PHP_EOL, 3, plugin_dir_path(__FILE__).'../logs/error.log');
            return $html;
        } else {
            return new WP_Error('image_sideload_failed');
        }
    }
}
