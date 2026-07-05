<?php


class UploadHandler
{
    protected $Vq1qo413wdi0;
    
    
    protected $Vrg2c3lxblsv = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height'
    );

    function __construct($Vq1qo413wdi0 = null, $Vjcvmwvwpyn2 = true, $Vrg2c3lxblsv = null) {
        $this->options = array(
            'script_url' => $this->get_full_url().'/',
            'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/files/',
            'upload_url' => $this->get_full_url().'/files/',
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'param_name' => 'files',
            
            
            'delete_type' => 'DELETE',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            
            
            
            
            
            
            'download_via_php' => false,
            
            
            'readfile_chunk_size' => 10 * 1024 * 1024, 
            
            'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
            
            'accept_file_types' => '/.+$/i',
            
            
            'max_file_size' => null,
            'min_file_size' => 1,
            
            'max_number_of_files' => null,
            
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            
            'discard_aborted_uploads' => true,
            
            'orient_image' => true,
            'image_versions' => array(
                
                
                
                
                
                'thumbnail' => array(
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    'max_width' => 80,
                    'max_height' => 80
                )
            )
        );
        if ($Vq1qo413wdi0) {
            $this->options = $Vq1qo413wdi0 + $this->options;
        }
        if ($Vrg2c3lxblsv) {
            $this->error_messages = $Vrg2c3lxblsv + $this->error_messages;
        }
        if ($Vjcvmwvwpyn2) {
            $this->initialize();
        }
    }

    protected function initialize() {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            case 'DELETE':
                $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function get_full_url() {
        $Vmdvdqtp24jl = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0;
        return
            ($Vmdvdqtp24jl ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($Vmdvdqtp24jl && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id() {
        @session_start();
        return session_id();
    }

    protected function get_user_path() {
        if ($this->options['user_dirs']) {
            return $this->get_user_id().'/';
        }
        return '';
    }

    protected function get_upload_path($Vwev2rrcid0i = null, $Vhbfz5dolbwk = null) {
        $Vwev2rrcid0i = $Vwev2rrcid0i ? $Vwev2rrcid0i : '';
        if (empty($Vhbfz5dolbwk)) {
            $Vhbfz5dolbwk_path = '';
        } else {
            $Vhbfz5dolbwk_dir = @$this->options['image_versions'][$Vhbfz5dolbwk]['upload_dir'];
            if ($Vhbfz5dolbwk_dir) {
                return $Vhbfz5dolbwk_dir.$this->get_user_path().$Vwev2rrcid0i;
            }
            $Vhbfz5dolbwk_path = $Vhbfz5dolbwk.'/';
        }
        return $this->options['upload_dir'].$this->get_user_path()
            .$Vhbfz5dolbwk_path.$Vwev2rrcid0i;
    }

    protected function get_query_separator($Vbfzdwdco10f) {
        return strpos($Vbfzdwdco10f, '?') === false ? '?' : '&';
    }

    protected function get_download_url($Vwev2rrcid0i, $Vhbfz5dolbwk = null, $Vyn25x5n1k1w = false) {
        if (!$Vyn25x5n1k1w && $this->options['download_via_php']) {
            $Vbfzdwdco10f = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .'file='.rawurlencode($Vwev2rrcid0i);
            if ($Vhbfz5dolbwk) {
                $Vbfzdwdco10f .= '&version='.rawurlencode($Vhbfz5dolbwk);
            }
            return $Vbfzdwdco10f.'&download=1';
        }
        if (empty($Vhbfz5dolbwk)) {
            $Vhbfz5dolbwk_path = '';
        } else {
            $Vhbfz5dolbwk_url = @$this->options['image_versions'][$Vhbfz5dolbwk]['upload_url'];
            if ($Vhbfz5dolbwk_url) {
                return $Vhbfz5dolbwk_url.$this->get_user_path().rawurlencode($Vwev2rrcid0i);
            }
            $Vhbfz5dolbwk_path = rawurlencode($Vhbfz5dolbwk).'/';
        }
        return $this->options['upload_url'].$this->get_user_path()
            .$Vhbfz5dolbwk_path.rawurlencode($Vwev2rrcid0i);
    }

    protected function set_additional_file_properties($Vohv1lwbezvd) {
        $Vohv1lwbezvd->deleteUrl = $this->options['script_url']
            .$this->get_query_separator($this->options['script_url'])
            .$this->get_singular_param_name()
            .'='.rawurlencode($Vohv1lwbezvd->name);
        $Vohv1lwbezvd->deleteType = $this->options['delete_type'];
        if ($Vohv1lwbezvd->deleteType !== 'DELETE') {
            $Vohv1lwbezvd->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials']) {
            $Vohv1lwbezvd->deleteWithCredentials = true;
        }
    }

    
    
    protected function fix_integer_overflow($V5oahsp53wzd) {
        if ($V5oahsp53wzd < 0) {
            $V5oahsp53wzd += 2.0 * (PHP_INT_MAX + 1);
        }
        return $V5oahsp53wzd;
    }

    protected function get_file_size($Vohv1lwbezvd_path, $Vmsbps13nd4x = false) {
        if ($Vmsbps13nd4x) {
            clearstatcache(true, $Vohv1lwbezvd_path);
        }
        return $this->fix_integer_overflow(filesize($Vohv1lwbezvd_path));

    }

    protected function is_valid_file_object($Vwev2rrcid0i) {
        $Vohv1lwbezvd_path = $this->get_upload_path($Vwev2rrcid0i);
        if (is_file($Vohv1lwbezvd_path) && $Vwev2rrcid0i[0] !== '.') {
            return true;
        }
        return false;
    }

    protected function get_file_object($Vwev2rrcid0i) {
        if ($this->is_valid_file_object($Vwev2rrcid0i)) {
            $Vohv1lwbezvd = new stdClass();
            $Vohv1lwbezvd->name = $Vwev2rrcid0i;
            $Vohv1lwbezvd->size = $this->get_file_size(
                $this->get_upload_path($Vwev2rrcid0i)
            );
            $Vohv1lwbezvd->url = $this->get_download_url($Vohv1lwbezvd->name);
            foreach($this->options['image_versions'] as $Vhbfz5dolbwk => $Vq1qo413wdi0) {
                if (!empty($Vhbfz5dolbwk)) {
                    if (is_file($this->get_upload_path($Vwev2rrcid0i, $Vhbfz5dolbwk))) {
                        $Vohv1lwbezvd->{$Vhbfz5dolbwk.'Url'} = $this->get_download_url(
                            $Vohv1lwbezvd->name,
                            $Vhbfz5dolbwk
                        );
                    }
                }
            }
            $this->set_additional_file_properties($Vohv1lwbezvd);
            return $Vohv1lwbezvd;
        }
        return null;
    }

    protected function get_file_objects($Ve0x4e3f5a0u = 'get_file_object') {
        $Vutq0dsngwgh = $this->get_upload_path();
        if (!is_dir($Vutq0dsngwgh)) {
            return array();
        }
        return array_values(array_filter(array_map(
            array($this, $Ve0x4e3f5a0u),
            scandir($Vutq0dsngwgh)
        )));
    }

    protected function count_file_objects() {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    protected function create_scaled_image($Vwev2rrcid0i, $Vhbfz5dolbwk, $Vq1qo413wdi0) {
        $Vohv1lwbezvd_path = $this->get_upload_path($Vwev2rrcid0i);
        if (!empty($Vhbfz5dolbwk)) {
            $Vhbfz5dolbwk_dir = $this->get_upload_path(null, $Vhbfz5dolbwk);
            if (!is_dir($Vhbfz5dolbwk_dir)) {
                mkdir($Vhbfz5dolbwk_dir, $this->options['mkdir_mode'], true);
            }
            $Vz5hlmp44wmv = $Vhbfz5dolbwk_dir.'/'.$Vwev2rrcid0i;
        } else {
            $Vz5hlmp44wmv = $Vohv1lwbezvd_path;
        }
        if (!function_exists('getimagesize')) {
            error_log('Function not found: getimagesize');
            return false;
        }
        list($Vuuiroo44yqu, $V5tvs3u4ouqy) = @getimagesize($Vohv1lwbezvd_path);
        if (!$Vuuiroo44yqu || !$V5tvs3u4ouqy) {
            return false;
        }
        $Vdxpr33jyofy = $Vq1qo413wdi0['max_width'];
        $Vibuysofe3lb = $Vq1qo413wdi0['max_height'];
        $Vemq0uby5sz4 = min(
            $Vdxpr33jyofy / $Vuuiroo44yqu,
            $Vibuysofe3lb / $V5tvs3u4ouqy
        );
        if ($Vemq0uby5sz4 >= 1) {
            if ($Vohv1lwbezvd_path !== $Vz5hlmp44wmv) {
                return copy($Vohv1lwbezvd_path, $Vz5hlmp44wmv);
            }
            return true;
        }
        if (!function_exists('imagecreatetruecolor')) {
            error_log('Function not found: imagecreatetruecolor');
            return false;
        }
        if (empty($Vq1qo413wdi0['crop'])) {
            $Vndzaoxkgw4l = $Vuuiroo44yqu * $Vemq0uby5sz4;
            $Vgs1nwsx1v3a = $V5tvs3u4ouqy * $Vemq0uby5sz4;
            $Vq4hgae20v2l = 0;
            $Vktfqgv2titj = 0;
            $V11qrl4cgk4s = imagecreatetruecolor($Vndzaoxkgw4l, $Vgs1nwsx1v3a);
        } else {
            if (($Vuuiroo44yqu / $V5tvs3u4ouqy) >= ($Vdxpr33jyofy / $Vibuysofe3lb)) {
                $Vndzaoxkgw4l = $Vuuiroo44yqu / ($V5tvs3u4ouqy / $Vibuysofe3lb);
                $Vgs1nwsx1v3a = $Vibuysofe3lb;
            } else {
                $Vndzaoxkgw4l = $Vdxpr33jyofy;
                $Vgs1nwsx1v3a = $V5tvs3u4ouqy / ($Vuuiroo44yqu / $Vdxpr33jyofy);
            }
            $Vq4hgae20v2l = 0 - ($Vndzaoxkgw4l - $Vdxpr33jyofy) / 2;
            $Vktfqgv2titj = 0 - ($Vgs1nwsx1v3a - $Vibuysofe3lb) / 2;
            $V11qrl4cgk4s = imagecreatetruecolor($Vdxpr33jyofy, $Vibuysofe3lb);
        }
        switch (strtolower(substr(strrchr($Vwev2rrcid0i, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $Vss0r1xgs2hf = imagecreatefromjpeg($Vohv1lwbezvd_path);
                $Vlt0ioqpab2z = 'imagejpeg';
                $Vs025ew4weu1 = isset($Vq1qo413wdi0['jpeg_quality']) ?
                    $Vq1qo413wdi0['jpeg_quality'] : 75;
                break;
            case 'gif':
                imagecolortransparent($V11qrl4cgk4s, imagecolorallocate($V11qrl4cgk4s, 0, 0, 0));
                $Vss0r1xgs2hf = imagecreatefromgif($Vohv1lwbezvd_path);
                $Vlt0ioqpab2z = 'imagegif';
                $Vs025ew4weu1 = null;
                break;
            case 'png':
                imagecolortransparent($V11qrl4cgk4s, imagecolorallocate($V11qrl4cgk4s, 0, 0, 0));
                imagealphablending($V11qrl4cgk4s, false);
                imagesavealpha($V11qrl4cgk4s, true);
                $Vss0r1xgs2hf = imagecreatefrompng($Vohv1lwbezvd_path);
                $Vlt0ioqpab2z = 'imagepng';
                $Vs025ew4weu1 = isset($Vq1qo413wdi0['png_quality']) ?
                    $Vq1qo413wdi0['png_quality'] : 9;
                break;
            default:
                imagedestroy($V11qrl4cgk4s);
                return false;
        }
        $Vjm0vbbm2pt1 = imagecopyresampled(
            $V11qrl4cgk4s,
            $Vss0r1xgs2hf,
            $Vq4hgae20v2l,
            $Vktfqgv2titj,
            0,
            0,
            $Vndzaoxkgw4l,
            $Vgs1nwsx1v3a,
            $Vuuiroo44yqu,
            $V5tvs3u4ouqy
        ) && $Vlt0ioqpab2z($V11qrl4cgk4s, $Vz5hlmp44wmv, $Vs025ew4weu1);
        
        imagedestroy($Vss0r1xgs2hf);
        imagedestroy($V11qrl4cgk4s);
        return $Vjm0vbbm2pt1;
    }

    protected function get_error_message($Vgo51ruiz5cy) {
        return array_key_exists($Vgo51ruiz5cy, $this->error_messages) ?
            $this->error_messages[$Vgo51ruiz5cy] : $Vgo51ruiz5cy;
    }

    function get_config_bytes($Voatwj4ahvts) {
        $Voatwj4ahvts = trim($Voatwj4ahvts);
        $Vepvkhmwxlhw = strtolower($Voatwj4ahvts[strlen($Voatwj4ahvts)-1]);
        switch($Vepvkhmwxlhw) {
            case 'g':
                $Voatwj4ahvts *= 1024;
            case 'm':
                $Voatwj4ahvts *= 1024;
            case 'k':
                $Voatwj4ahvts *= 1024;
        }
        return $this->fix_integer_overflow($Voatwj4ahvts);
    }

    protected function validate($Vva4rxumv0kc, $Vohv1lwbezvd, $Vgo51ruiz5cy, $Vxud52rqsu52) {
        if ($Vgo51ruiz5cy) {
            $Vohv1lwbezvd->error = $this->get_error_message($Vgo51ruiz5cy);
            return false;
        }
        $V0clsm5p1tje = $this->fix_integer_overflow(intval(
            $this->get_server_var('CONTENT_LENGTH')
        ));
        $Vwc4h12i3c3e = $this->get_config_bytes(ini_get('post_max_size'));
        if ($Vwc4h12i3c3e && ($V0clsm5p1tje > $Vwc4h12i3c3e)) {
            $Vohv1lwbezvd->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $Vohv1lwbezvd->name)) {
            $Vohv1lwbezvd->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($Vva4rxumv0kc && is_uploaded_file($Vva4rxumv0kc)) {
            $Vohv1lwbezvd_size = $this->get_file_size($Vva4rxumv0kc);
        } else {
            $Vohv1lwbezvd_size = $V0clsm5p1tje;
        }
        if ($this->options['max_file_size'] && (
                $Vohv1lwbezvd_size > $this->options['max_file_size'] ||
                $Vohv1lwbezvd->size > $this->options['max_file_size'])
            ) {
            $Vohv1lwbezvd->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] &&
            $Vohv1lwbezvd_size < $this->options['min_file_size']) {
            $Vohv1lwbezvd->error = $this->get_error_message('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (
                $this->count_file_objects() >= $this->options['max_number_of_files'])
            ) {
            $Vohv1lwbezvd->error = $this->get_error_message('max_number_of_files');
            return false;
        }
        list($Vuuiroo44yqu, $V5tvs3u4ouqy) = @getimagesize($Vva4rxumv0kc);
        if (is_int($Vuuiroo44yqu)) {
            if ($this->options['max_width'] && $Vuuiroo44yqu > $this->options['max_width']) {
                $Vohv1lwbezvd->error = $this->get_error_message('max_width');
                return false;
            }
            if ($this->options['max_height'] && $V5tvs3u4ouqy > $this->options['max_height']) {
                $Vohv1lwbezvd->error = $this->get_error_message('max_height');
                return false;
            }
            if ($this->options['min_width'] && $Vuuiroo44yqu < $this->options['min_width']) {
                $Vohv1lwbezvd->error = $this->get_error_message('min_width');
                return false;
            }
            if ($this->options['min_height'] && $V5tvs3u4ouqy < $this->options['min_height']) {
                $Vohv1lwbezvd->error = $this->get_error_message('min_height');
                return false;
            }
        }
        return true;
    }

    protected function upcount_name_callback($Vfmpm1b5duew) {
        $Vxud52rqsu52 = isset($Vfmpm1b5duew[1]) ? intval($Vfmpm1b5duew[1]) + 1 : 1;
        $Vfhs2zbc5lto = isset($Vfmpm1b5duew[2]) ? $Vfmpm1b5duew[2] : '';
        return ' ('.$Vxud52rqsu52.')'.$Vfhs2zbc5lto;
    }

    protected function upcount_name($Vsiq10w1mvm1) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $Vsiq10w1mvm1,
            1
        );
    }

    protected function get_unique_filename($Vsiq10w1mvm1,
            $Vwqo1gaq5yki = null, $Vxud52rqsu52 = null, $Vd1z0m5l2uxx = null) {
        while(is_dir($this->get_upload_path($Vsiq10w1mvm1))) {
            $Vsiq10w1mvm1 = $this->upcount_name($Vsiq10w1mvm1);
        }
        
        $Vrpc0eahgwbh = $this->fix_integer_overflow(intval($Vd1z0m5l2uxx[1]));
        while(is_file($this->get_upload_path($Vsiq10w1mvm1))) {
            if ($Vrpc0eahgwbh === $this->get_file_size(
                    $this->get_upload_path($Vsiq10w1mvm1))) {
                break;
            }
            $Vsiq10w1mvm1 = $this->upcount_name($Vsiq10w1mvm1);
        }
        return $Vsiq10w1mvm1;
    }

    protected function trim_file_name($Vsiq10w1mvm1,
            $Vwqo1gaq5yki = null, $Vxud52rqsu52 = null, $Vd1z0m5l2uxx = null) {
        
        
        
        $Vsiq10w1mvm1 = trim(basename(stripslashes($Vsiq10w1mvm1)), ".\x00..\x20");
        
        if (!$Vsiq10w1mvm1) {
            $Vsiq10w1mvm1 = str_replace('.', '-', microtime(true));
        }
        
        if (strpos($Vsiq10w1mvm1, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $Vwqo1gaq5yki, $Vfmpm1b5duew)) {
            $Vsiq10w1mvm1 .= '.'.$Vfmpm1b5duew[1];
        }
        return $Vsiq10w1mvm1;
    }

    protected function get_file_name($Vsiq10w1mvm1,
            $Vwqo1gaq5yki = null, $Vxud52rqsu52 = null, $Vd1z0m5l2uxx = null) {
        return $this->get_unique_filename(
            $this->trim_file_name($Vsiq10w1mvm1, $Vwqo1gaq5yki, $Vxud52rqsu52, $Vd1z0m5l2uxx),
            $Vwqo1gaq5yki,
            $Vxud52rqsu52,
            $Vd1z0m5l2uxx
        );
    }

    protected function handle_form_data($Vohv1lwbezvd, $Vxud52rqsu52) {
        
    }

    protected function imageflip($V3uam3mmpxgt, $Vy2smdladb25) {
        if (function_exists('imageflip')) {
            return imageflip($V3uam3mmpxgt, $Vy2smdladb25);
        }
        $Vndzaoxkgw4l = $Vnb1ar2qftbt = imagesx($V3uam3mmpxgt);
        $Vgs1nwsx1v3a = $Vomvjd0en3ku = imagesy($V3uam3mmpxgt);
        $V11qrl4cgk4s = imagecreatetruecolor($Vndzaoxkgw4l, $Vgs1nwsx1v3a);
        $V3kn5a2ezm24 = 0;
        $V03g1mm452ur = 0;
        switch ($Vy2smdladb25) {
            case '1': 
                $V03g1mm452ur = $Vgs1nwsx1v3a - 1;
                $Vomvjd0en3ku = -$Vgs1nwsx1v3a;
                break;
            case '2': 
                $V3kn5a2ezm24  = $Vndzaoxkgw4l - 1;
                $Vnb1ar2qftbt = -$Vndzaoxkgw4l;
                break;
            case '3': 
                $V03g1mm452ur = $Vgs1nwsx1v3a - 1;
                $Vomvjd0en3ku = -$Vgs1nwsx1v3a;
                $V3kn5a2ezm24  = $Vndzaoxkgw4l - 1;
                $Vnb1ar2qftbt = -$Vndzaoxkgw4l;
                break;
            default:
                return $V3uam3mmpxgt;
        }
        imagecopyresampled(
            $V11qrl4cgk4s,
            $V3uam3mmpxgt,
            0,
            0,
            $V3kn5a2ezm24,
            $V03g1mm452ur,
            $Vndzaoxkgw4l,
            $Vgs1nwsx1v3a,
            $Vnb1ar2qftbt,
            $Vomvjd0en3ku
        );
        
        imagedestroy($V3uam3mmpxgt);
        return $V11qrl4cgk4s;
    }

    protected function orient_image($Vohv1lwbezvd_path) {
        if (!function_exists('exif_read_data')) {
            return false;
        }
        $V0adbmswezbs = @exif_read_data($Vohv1lwbezvd_path);
        if ($V0adbmswezbs === false) {
            return false;
        }
        $Vlffrk0bfsnw = intval(@$V0adbmswezbs['Orientation']);
        if ($Vlffrk0bfsnw < 2 || $Vlffrk0bfsnw > 8) {
            return false;
        }
        $V3uam3mmpxgt = imagecreatefromjpeg($Vohv1lwbezvd_path);
        switch ($Vlffrk0bfsnw) {
            case 2:
                $V3uam3mmpxgt = $this->imageflip(
                    $V3uam3mmpxgt,
                    defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2
                );
                break;
            case 3:
                $V3uam3mmpxgt = imagerotate($V3uam3mmpxgt, 180, 0);
                break;
            case 4:
                $V3uam3mmpxgt = $this->imageflip(
                    $V3uam3mmpxgt,
                    defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1
                );
                break;
            case 5:
                $V3uam3mmpxgt = $this->imageflip(
                    $V3uam3mmpxgt,
                    defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1
                );
                $V3uam3mmpxgt = imagerotate($V3uam3mmpxgt, 270, 0);
                break;
            case 6:
                $V3uam3mmpxgt = imagerotate($V3uam3mmpxgt, 270, 0);
                break;
            case 7:
                $V3uam3mmpxgt = $this->imageflip(
                    $V3uam3mmpxgt,
                    defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2
                );
                $V3uam3mmpxgt = imagerotate($V3uam3mmpxgt, 270, 0);
                break;
            case 8:
                $V3uam3mmpxgt = imagerotate($V3uam3mmpxgt, 90, 0);
                break;
            default:
                return false;
        }
        $Vjm0vbbm2pt1 = imagejpeg($V3uam3mmpxgt, $Vohv1lwbezvd_path);
        
        imagedestroy($V3uam3mmpxgt);
        return $Vjm0vbbm2pt1;
    }

    protected function handle_image_file($Vohv1lwbezvd_path, $Vohv1lwbezvd) {
        if ($this->options['orient_image']) {
            $this->orient_image($Vohv1lwbezvd_path);
        }
        $Va5a3jglkyzv = array();
        foreach($this->options['image_versions'] as $Vhbfz5dolbwk => $Vq1qo413wdi0) {
            if ($this->create_scaled_image($Vohv1lwbezvd->name, $Vhbfz5dolbwk, $Vq1qo413wdi0)) {
                if (!empty($Vhbfz5dolbwk)) {
                    $Vohv1lwbezvd->{$Vhbfz5dolbwk.'Url'} = $this->get_download_url(
                        $Vohv1lwbezvd->name,
                        $Vhbfz5dolbwk
                    );
                } else {
                    $Vohv1lwbezvd->size = $this->get_file_size($Vohv1lwbezvd_path, true);
                }
            } else {
                $Va5a3jglkyzv[] = $Vhbfz5dolbwk;
            }
        }
        switch (count($Va5a3jglkyzv)) {
            case 0:
                break;
            case 1:
                $Vohv1lwbezvd->error = 'Failed to create scaled version: '
                    .$Va5a3jglkyzv[0];
                break;
            default:
                $Vohv1lwbezvd->error = 'Failed to create scaled versions: '
                    .implode($Va5a3jglkyzv,', ');
        }
    }

    protected function handle_file_upload($Vva4rxumv0kc, $Vsiq10w1mvm1, $V5oahsp53wzd, $Vwqo1gaq5yki, $Vgo51ruiz5cy,
            $Vxud52rqsu52 = null, $Vd1z0m5l2uxx = null) {
        $Vohv1lwbezvd = new stdClass();
        $Vohv1lwbezvd->name = $this->get_file_name($Vsiq10w1mvm1, $Vwqo1gaq5yki, $Vxud52rqsu52, $Vd1z0m5l2uxx);
        $Vohv1lwbezvd->size = $this->fix_integer_overflow(intval($V5oahsp53wzd));
        $Vohv1lwbezvd->type = $Vwqo1gaq5yki;
        if ($this->validate($Vva4rxumv0kc, $Vohv1lwbezvd, $Vgo51ruiz5cy, $Vxud52rqsu52)) {
            $this->handle_form_data($Vohv1lwbezvd, $Vxud52rqsu52);
            $Vutq0dsngwgh = $this->get_upload_path();
            if (!is_dir($Vutq0dsngwgh)) {
                mkdir($Vutq0dsngwgh, $this->options['mkdir_mode'], true);
            }
            $Vohv1lwbezvd_path = $this->get_upload_path($Vohv1lwbezvd->name);
            $Vewu44fewedv = $Vd1z0m5l2uxx && is_file($Vohv1lwbezvd_path) &&
                $Vohv1lwbezvd->size > $this->get_file_size($Vohv1lwbezvd_path);
            if ($Vva4rxumv0kc && is_uploaded_file($Vva4rxumv0kc)) {
                
                if ($Vewu44fewedv) {
                    file_put_contents(
                        $Vohv1lwbezvd_path,
                        fopen($Vva4rxumv0kc, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($Vva4rxumv0kc, $Vohv1lwbezvd_path);
                }
            } else {
                
                file_put_contents(
                    $Vohv1lwbezvd_path,
                    fopen('php://input', 'r'),
                    $Vewu44fewedv ? FILE_APPEND : 0
                );
            }
            $Vohv1lwbezvd_size = $this->get_file_size($Vohv1lwbezvd_path, $Vewu44fewedv);
            if ($Vohv1lwbezvd_size === $Vohv1lwbezvd->size) {
                $Vohv1lwbezvd->url = $this->get_download_url($Vohv1lwbezvd->name);
                list($Vuuiroo44yqu, $V5tvs3u4ouqy) = @getimagesize($Vohv1lwbezvd_path);
                if (is_int($Vuuiroo44yqu) &&
                        preg_match($this->options['inline_file_types'], $Vohv1lwbezvd->name)) {
                    $this->handle_image_file($Vohv1lwbezvd_path, $Vohv1lwbezvd);
                }
            } else {
                $Vohv1lwbezvd->size = $Vohv1lwbezvd_size;
                if (!$Vd1z0m5l2uxx && $this->options['discard_aborted_uploads']) {
                    unlink($Vohv1lwbezvd_path);
                    $Vohv1lwbezvd->error = 'abort';
                }
            }
            $this->set_additional_file_properties($Vohv1lwbezvd);
        }
        return $Vohv1lwbezvd;
    }

    protected function readfile($Vohv1lwbezvd_path) {
        $Vohv1lwbezvd_size = $this->get_file_size($Vohv1lwbezvd_path);
        $Vlo0crtmtkv4 = $this->options['readfile_chunk_size'];
        if ($Vlo0crtmtkv4 && $Vohv1lwbezvd_size > $Vlo0crtmtkv4) {
            $Vceue1jtlks3 = fopen($Vohv1lwbezvd_path, 'rb');
            while (!feof($Vceue1jtlks3)) {
                echo fread($Vceue1jtlks3, $Vlo0crtmtkv4);
                ob_flush();
                flush();
            }
            fclose($Vceue1jtlks3);
            return $Vohv1lwbezvd_size;
        }
        return readfile($Vohv1lwbezvd_path);
    }

    protected function body($Vet4qc5sbphx) {
        echo $Vet4qc5sbphx;
    }
    
    protected function header($Vet4qc5sbphx) {
        header($Vet4qc5sbphx);
    }

    protected function get_server_var($V5v1rmmzxlwq) {
        return isset($_SERVER[$V5v1rmmzxlwq]) ? $_SERVER[$V5v1rmmzxlwq] : '';
    }

    protected function generate_response($V2n2omqe0wvu, $V301axpb4o23 = true) {
        if ($V301axpb4o23) {
            $Vbhciilk5ggk = json_encode($V2n2omqe0wvu);
            $Vpnnnm2eikkw = isset($_REQUEST['redirect']) ?
                stripslashes($_REQUEST['redirect']) : null;
            if ($Vpnnnm2eikkw) {
                $this->header('Location: '.sprintf($Vpnnnm2eikkw, rawurlencode($Vbhciilk5ggk)));
                return;
            }
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $Vohv1lwbezvds = isset($V2n2omqe0wvu[$this->options['param_name']]) ?
                    $V2n2omqe0wvu[$this->options['param_name']] : null;
                if ($Vohv1lwbezvds && is_array($Vohv1lwbezvds) && is_object($Vohv1lwbezvds[0]) && $Vohv1lwbezvds[0]->size) {
                    $this->header('Range: 0-'.(
                        $this->fix_integer_overflow(intval($Vohv1lwbezvds[0]->size)) - 1
                    ));
                }
            }
            $this->body($Vbhciilk5ggk);
        }
        return $V2n2omqe0wvu;
    }

    protected function get_version_param() {
        return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
    }

    protected function get_singular_param_name() {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function get_file_name_param() {
        $Vsiq10w1mvm1 = $this->get_singular_param_name();
        return isset($_GET[$Vsiq10w1mvm1]) ? basename(stripslashes($_GET[$Vsiq10w1mvm1])) : null;
    }

    protected function get_file_names_params() {
        $Vw2gor1sgtrm = isset($_GET[$this->options['param_name']]) ?
            $_GET[$this->options['param_name']] : array();
        foreach ($Vw2gor1sgtrm as $Vzzgsb4i5jlb => $Voatwj4ahvtsue) {
            $Vw2gor1sgtrm[$Vzzgsb4i5jlb] = basename(stripslashes($Voatwj4ahvtsue));
        }
        return $Vw2gor1sgtrm;
    }

    protected function get_file_type($Vohv1lwbezvd_path) {
        switch (strtolower(pathinfo($Vohv1lwbezvd_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function download() {
        switch ($this->options['download_via_php']) {
            case 1:
                $Vpnnnm2eikkw_header = null;
                break;
            case 2:
                $Vpnnnm2eikkw_header = 'X-Sendfile';
                break;
            case 3:
                $Vpnnnm2eikkw_header = 'X-Accel-Redirect';
                break;
            default:
                return $this->header('HTTP/1.1 403 Forbidden');
        }
        $Vwev2rrcid0i = $this->get_file_name_param();
        if (!$this->is_valid_file_object($Vwev2rrcid0i)) {
            return $this->header('HTTP/1.1 404 Not Found');
        }
        if ($Vpnnnm2eikkw_header) {
            return $this->header(
                $Vpnnnm2eikkw_header.': '.$this->get_download_url(
                    $Vwev2rrcid0i,
                    $this->get_version_param(),
                    true
                )
            );
        }
        $Vohv1lwbezvd_path = $this->get_upload_path($Vwev2rrcid0i, $this->get_version_param());
        
        $this->header('X-Content-Type-Options: nosniff');
        if (!preg_match($this->options['inline_file_types'], $Vwev2rrcid0i)) {
            $this->header('Content-Type: application/octet-stream');
            $this->header('Content-Disposition: attachment; filename="'.$Vwev2rrcid0i.'"');
        } else {
            $this->header('Content-Type: '.$this->get_file_type($Vohv1lwbezvd_path));
            $this->header('Content-Disposition: inline; filename="'.$Vwev2rrcid0i.'"');
        }
        $this->header('Content-Length: '.$this->get_file_size($Vohv1lwbezvd_path));
        $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($Vohv1lwbezvd_path)));
        $this->readfile($Vohv1lwbezvd_path);
    }

    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    public function get($V301axpb4o23 = true) {
        if ($V301axpb4o23 && isset($_GET['download'])) {
            return $this->download();
        }
        $Vwev2rrcid0i = $this->get_file_name_param();
        if ($Vwev2rrcid0i) {
            $Vpql2sasatuc = array(
                $this->get_singular_param_name() => $this->get_file_object($Vwev2rrcid0i)
            );
        } else {
            $Vpql2sasatuc = array(
                $this->options['param_name'] => $this->get_file_objects()
            );
        }
        return $this->generate_response($Vpql2sasatuc, $V301axpb4o23);
    }

    public function post($V301axpb4o23 = true) {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete($V301axpb4o23);
        }
        $Vmlpxo0wbysh = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        
        $Vwev2rrcid0i = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $this->get_server_var('HTTP_CONTENT_DISPOSITION')
            )) : null;
        
        
        $Vd1z0m5l2uxx = $this->get_server_var('HTTP_CONTENT_RANGE') ?
            preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
        $V5oahsp53wzd =  $Vd1z0m5l2uxx ? $Vd1z0m5l2uxx[3] : null;
        $Vohv1lwbezvds = array();
        if ($Vmlpxo0wbysh && is_array($Vmlpxo0wbysh['tmp_name'])) {
            
            
            foreach ($Vmlpxo0wbysh['tmp_name'] as $Vxud52rqsu52 => $Voatwj4ahvtsue) {
                $Vohv1lwbezvds[] = $this->handle_file_upload(
                    $Vmlpxo0wbysh['tmp_name'][$Vxud52rqsu52],
                    $Vwev2rrcid0i ? $Vwev2rrcid0i : $Vmlpxo0wbysh['name'][$Vxud52rqsu52],
                    $V5oahsp53wzd ? $V5oahsp53wzd : $Vmlpxo0wbysh['size'][$Vxud52rqsu52],
                    $Vmlpxo0wbysh['type'][$Vxud52rqsu52],
                    $Vmlpxo0wbysh['error'][$Vxud52rqsu52],
                    $Vxud52rqsu52,
                    $Vd1z0m5l2uxx
                );
            }
        } else {
            
            
            $Vohv1lwbezvds[] = $this->handle_file_upload(
                isset($Vmlpxo0wbysh['tmp_name']) ? $Vmlpxo0wbysh['tmp_name'] : null,
                $Vwev2rrcid0i ? $Vwev2rrcid0i : (isset($Vmlpxo0wbysh['name']) ?
                        $Vmlpxo0wbysh['name'] : null),
                $V5oahsp53wzd ? $V5oahsp53wzd : (isset($Vmlpxo0wbysh['size']) ?
                        $Vmlpxo0wbysh['size'] : $this->get_server_var('CONTENT_LENGTH')),
                isset($Vmlpxo0wbysh['type']) ?
                        $Vmlpxo0wbysh['type'] : $this->get_server_var('CONTENT_TYPE'),
                isset($Vmlpxo0wbysh['error']) ? $Vmlpxo0wbysh['error'] : null,
                null,
                $Vd1z0m5l2uxx
            );
        }
        return $this->generate_response(
            array($this->options['param_name'] => $Vohv1lwbezvds),
            $V301axpb4o23
        );
    }

    public function delete($V301axpb4o23 = true) {
        $Vwev2rrcid0is = $this->get_file_names_params();
        if (empty($Vwev2rrcid0is)) {
            $Vwev2rrcid0is = array($this->get_file_name_param());
        }
        $Vpql2sasatuc = array();
        foreach($Vwev2rrcid0is as $Vwev2rrcid0i) {
            $Vohv1lwbezvd_path = $this->get_upload_path($Vwev2rrcid0i);
            $Vjm0vbbm2pt1 = is_file($Vohv1lwbezvd_path) && $Vwev2rrcid0i[0] !== '.' && unlink($Vohv1lwbezvd_path);
            if ($Vjm0vbbm2pt1) {
                foreach($this->options['image_versions'] as $Vhbfz5dolbwk => $Vq1qo413wdi0) {
                    if (!empty($Vhbfz5dolbwk)) {
                        $Vohv1lwbezvd = $this->get_upload_path($Vwev2rrcid0i, $Vhbfz5dolbwk);
                        if (is_file($Vohv1lwbezvd)) {
                            unlink($Vohv1lwbezvd);
                        }
                    }
                }
            }
            $Vpql2sasatuc[$Vwev2rrcid0i] = $Vjm0vbbm2pt1;
        }
        return $this->generate_response($Vpql2sasatuc, $V301axpb4o23);
    }

}
