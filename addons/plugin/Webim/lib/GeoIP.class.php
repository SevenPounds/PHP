<?php

/*
    全球 IPv4 地址归属地数据库(17MON.CN 版)
    高春辉(pAUL gAO) <gaochunhui@gmail.com>
    Build 20140310 版权所有 17MON.CN
    (C) 2006 - 2014 保留所有权利
    请注意及时更新 IP 数据库版本
    数据问题请加 QQ 群: 346280296
    Code for PHP 5.3+ only
*/

/**
 * 
 * Fixed by ery.lee at gmail.com to be compatible with php 4.x
 *
 */
class GeoIP {

    var $fp     = NULL;
    var $offset = NULL;
    var $index  = NULL;
    var $cached = array();

    function __construct() {
		register_shutdown_function( array( &$this, '__destruct' ) );
    }

    function IP() {
        $this->__construct();
    }

    function __destruct() {
        if ($this->fp !== NULL) {
            //<b>Warning</b>:  fclose(): 4 is not a valid stream resource on line <b>35</b><br />
            //fclose($this->fp);
        }
    }

    function find($ip) {
        if ( empty($ip) ) return 'N/A';

        $nip   = gethostbyname($ip);
        $ipdot = explode('.', $nip);

        if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4) {
            return 'N/A';
        }

        if (isset($this->cached[$nip]) === TRUE) {
            return $this->cached[$nip];
        }

        if ($this->fp === NULL) {
            $this->init();
        }

        $nip = pack('N', ip2long($nip));

        $tmp_offset = (int)$ipdot[0] * 4;
        $start      = unpack('Vlen', $this->index[$tmp_offset] . $this->index[$tmp_offset + 1] . $this->index[$tmp_offset + 2] . $this->index[$tmp_offset + 3]);

        $index_offset = $index_length = NULL;
        $max_comp_len = $this->offset['len'] - 1028;
        for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
            if ($this->index{$start} . $this->index{$start + 1} . $this->index{$start + 2} . $this->index{$start + 3} >= $nip) {
                $index_offset = unpack('Vlen', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
                $index_length = unpack('Clen', $this->index{$start + 7});

                break;
            }
        }

        if ($index_offset === NULL) {
            return 'N/A';
        }

        fseek($this->fp, $this->offset['len'] + $index_offset['len'] - 1024);

        $this->cached[$nip] = explode("\t", fread($this->fp, $index_length['len']));

        return $this->cached[$nip];
    }

    function init() {
        if ($this->fp === NULL) {

            $this->fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ipdb.dat', 'rb');
            if ($this->fp === FALSE) {
                throw new Exception('Invalid ipdb.dat file!');
            }

            $this->offset = unpack('Nlen', fread($this->fp, 4));
            if ($this->offset['len'] < 4) {
                throw new Exception('Invalid ipdb.dat file!');
            }
            $this->index = fread($this->fp, $this->offset['len'] - 4);
        }
    }

}

?>
