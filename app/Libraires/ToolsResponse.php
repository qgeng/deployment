<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Libraires;

use ZipArchive;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

/**
 * Description of ToolsResponse
 *
 * @author LiaoYing
 */
trait ToolsResponse {


    /**
     * 下载文件
     * @param type $source  链接地址
     * @param type $fileName  文件名
     * @return boolean
     */
    public function downFile($source, $fileName) {
        $source = $source;
        $ch = curl_init(); //初始化一个cURL会话
        curl_setopt($ch, CURLOPT_URL, $source); //抓取url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //是否显示头信息
        curl_setopt($ch, CURLOPT_SSLVERSION, 3); //传递一个包含SSL版本的长参数
        $data = curl_exec($ch); // 执行一个cURL会话
        $error = curl_error($ch); //返回一条最近一次cURL操作明确的文本的错误信息。
        curl_close($ch); //关闭一个cURL会话并且释放所有资源
        $destination = base_path() . "/public/uploads/bill/" . $fileName;
        $file = fopen($destination, "w+");
        fputs($file, $data); //写入文件
        fclose($file);
        return true;
    }

    /**
     * 数字转中文
     * @param type $num
     * @return string
     */
    public function numToWord($num) {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('', '十', '百', '千', '万', '亿', '十', '百', '千');
        $chiStr = '';
        $num_str = (string) $num;
        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字
        $chiStr = ''; //拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        } else if ($count > 2) {
            $index = 0;
            for ($i = $count - 1; $i >= 0; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag) {
                        $chiStr = $chiNum[$temp_num] . $chiStr;
                        $last_flag = true;
                    }
                } else {
                    $chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;

                    $zero_flag = false;
                    $last_flag = false;
                }
                $index ++;
            }
        } else {
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }

    /**
     * @desc 根据两点间的经纬度计算距离  
     * @param float $lat 纬度值  
     * @param float $lng 经度值  
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6367000;
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;
        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    /**
     * 搜索范围内的经纬度
     * @param $lat  当前经度
     * @param $lon  当前维度
     * @param $raidus 半径
     * @return array
     */
    public function getRange($lon, $lat, $raidus) {
        $PI = 3.14159265;
        //计算纬度
        $degree = (24901 * 1609) / 360.0;
        $dpmLat = 1 / $degree;
        $radiusLat = $dpmLat * $raidus;
        $minLat = $lat - $radiusLat; //得到最小纬度
        $maxLat = $lat + $radiusLat; //得到最大纬度
        //计算经度
        $mpdLng = $degree * cos($lat * ($PI / 180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng * $raidus;
        $minLng = $lon - $radiusLng; //得到最小经度
        $maxLng = $lon + $radiusLng; //得到最大经度
        //范围
        $range = array(
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLon' => $minLng,
            'maxLon' => $maxLng
        );
        return $range;
    }


    public function sendHttpRequest($url, $data = '', $refererUrl = '', $method = 'POST', $contentType = 'application/json', $timeout = 30, $proxy = false, $header = []) {
        $ch = null;
        if ('POST' === strtoupper($method)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);     //注意，毫秒超时一定要设置这个
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 36000); //超时毫秒，cURL 7.16.2中被加入。从PHP 5.2.3起可使用
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不需要验证证书

            if ($refererUrl) {
                curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
            }
            if ($contentType) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:' . $contentType));
            }
            if (is_string($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }

        } else if ('GET' === strtoupper($method)) {
            if (is_string($data)) {
                $real_url = $url . (strpos($url, '?') === false ? '?' : '') . $data;
            } else {
                $real_url = $url . (strpos($url, '?') === false ? '?' : '') . http_build_query($data);
            }

            $ch = curl_init($real_url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:' . $contentType));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            if ($refererUrl) {
                curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
            }
        } else {
            $args = func_get_args();
            return false;
        }
        if ($proxy) {#设置代理
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $info = curl_getinfo($ch);

        $ret = curl_exec($ch);
        $contents = array(
            'httpInfo' => array(
                'send' => $data,
                'url' => $url,
                'ret' => $ret,
                'http' => $info,
            )
        );
        $body = null;
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($ret, 0, $headerSize);
            $body = substr($ret, $headerSize);
//            MLoger::write('ChinaMobileSecondApi/sendRequest', $data. "\n ".$headerSize. "\n ".$header.$body."\n");
        } else {
            $curlErrorNo = curl_errno($ch);
            $curlError = curl_error($ch);
            $curlInfoCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ['http_curl_error_code' => $curlInfoCode . '-' . $curlErrorNo, 'http_curl_error_msg' => $curlError];
        }
        curl_close($ch);

        return json_decode($body, true);
    }

    function get_client_ip() {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    public function xmlToArray($xml) {
        //禁止引用外部xml实体
        $xml = str_replace('GBK', 'utf-8', $xml);
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    /**
     * 检查银行卡号
     * @param type $card_number
     * @return string
     */
    public function check_bankCard($card_number) {
        $arr_no = str_split($card_number);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if ($x == $last_n) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * 创建文件夹
     * @param type $path
     */
    public function mkFile($path) {
        if (!file_exists($path)) {
            mkdir($path);
        }
    }

    /*     * ********************
     * @file - path to zip file 需要解压的文件的路径
     * @destination - destination directory for unzipped files 解压之后存放的路径
     * @需要使用 ZZIPlib library ，请确认该扩展已经开启
     */

    function unzip_file($file, $destination) {
// 实例化对象 
        $zip = new \ZipArchive();
//打开zip文档，如果打开失败返回提示信息 
        if ($zip->open($file) !== TRUE) {
            die("Could not open archive");
        }
//将压缩文件解压到指定的目录下 
        $zip->extractTo($destination);
//关闭zip文档 
        $zip->close();
        echo 'Archive extracted to directory';
    }

    function addFileToZip($path, $fileList, $zipName) {
        $filename = $path . $zipName . ".zip";
        $zip = new \ZipArchive();
        $zip->open($filename, ZipArchive::CREATE);   //打开压缩包
        foreach ($fileList as $file) {
            $zip->addFile($file, basename($file));   //向压缩包中添加文件
        }
        $zip->close();  //关闭压缩包
    }

    function cardZip($path, $name) {
        $zip = new ZipArchive();
        $res = $zip->open($path . ".zip", ZipArchive::OVERWRITE | ZipArchive::CREATE);
        if ($res) {
            $this->compressDir($path . "/", $zip);
            $zip->close();
        }
        $this->del_dir($path);
    }

    function compressDir($dir, $zip) {
        $handler = opendir($dir);
        $basename = basename($dir);

        $zip->addEmptyDir($basename);
        while ($file = readdir($handler)) {
            $realpath = $dir . '/' . $file;
            if (is_dir($realpath)) {
                if ($file !== '.' && $file !== '..') {
                    $zip->addEmptyDir($basename . '/' . $file);
                    compressDir($realpath, $zip, $basename);
                }
            } else {
                $zip->addFile($realpath, $basename . '/' . $file);
            }
        }

        closedir($handler);
        return null;
    }

    function del_dir($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                is_dir("$dir/$file") ? del_dir("$dir/$file") : @unlink("$dir/$file");
            }
        }
        if (readdir($handle) == false) {
            closedir($handle);
            @rmdir($dir);
        }
    }

    /**
     * 将16进制内容转为文件
     * @param String $hexstr 16进制内容
     * @param String $file 保存的文件路径
     */
    function hexToFile($hexstr, $file) {
        if ($hexstr) {
            $data = pack('H*', $hexstr);
            file_put_contents($file, $data, true);
        }
    }

    /**
     * 将文件内容转为16进制输出
     * @param String $file 文件路径
     * @return String
     */
    function fileToHex($file) {
        if (file_exists($file)) {
            $data = file_get_contents($file);
            return bin2hex($data);
        }
        return '';
    }


    /**
     * 写入头部信息
     * @param array $title 文件头
     * @return string 文件名
     */
    public function export_title($title,$path) {
        //建立文件持续写入
        if (!file_exists($path)) { //不存在则建立
            mkdir($path, '0777');
        }
        $time = date("YmdHis");
        $fileName = $time . ".csv"; //文件名
        $name = $path . $fileName; //文件路径
        $name = iconv('utf-8', 'gbk', $name);   //转义
        $file = fopen($name, "w");  //初始化 w写入
        foreach ($title as $i => $v) {
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $head[$i] = mb_convert_encoding($v, "GBK", "utf-8");
        }
        fputcsv($file, $head); //写入头部文件
        return $fileName;
    }
    
    /**
     * csv写入数据
     * @param type $fileName 写入文件名
     * @param type $exportData 数据
     */
    public function export_data($fileName, $exportData,$path) {
        if (count($exportData) > 20000) {
            exit("每次限制导入20000条数据，若超出限制则循环写入");
        }
        $name = $path . $fileName; //文件路径
        $name = iconv('utf-8', 'gbk', $name);   //转义
        $file = fopen($name, "a+");  //csv表格末尾追加数据
        //计数器
        $num = 0;
        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 10000;
        //逐行取出数据，不浪费内存
        $counts = count($exportData); //DATA为二维数组
        for ($c = 0; $c < $counts; $c++) {
            $num++;
            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $num) {
                ob_flush();  //缓存  
                flush();
                $num = 0;
            }
            $row = $exportData[$c];
            foreach ($row as $key => $value) {
                $row[$key] = mb_convert_encoding($value, "GBK", "utf-8");
            }
            fputcsv($file, $row);
        }
    }

}
