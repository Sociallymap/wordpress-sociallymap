<?php

if (!function_exists('media_handle_upload')) {
        require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
}

class MediaWordpressManager
{
    public function integrateMediaToWordpress($temporyFile, $fileExtension)
    {
        $file_array = [];

        // Extract folder & filename
        $tabUrl = explode('/', $temporyFile);
        $filename = $tabUrl[count($tabUrl)-1];

        $file_array['name'] = $filename.$fileExtension;
        $file_array['tmp_name'] = $temporyFile.$fileExtension;


        // Do the validation and storage stuff.
        $id = media_handle_sideload($file_array, 0);

        $data = 'ERROR DOWNLOAD FOR ' . print_r($id, true) . ' | url: ' . $temporyFile . print_r($file_array, true);

        if (gettype($id) != 'integer') {
            throw new FileDownloadException($data);
        }

        $src = wp_get_attachment_url($id);

        if (gettype($src) != 'string') {
            throw new FileDownloadException('ERROR DOWNLOAD FOR ' . $src.' | url: ' . $temporyFile);
        }

        return $src;
    }
}
