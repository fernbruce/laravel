<?php


namespace App\Services\Order;


use App\Services\BaseServices;

class ExpressServices extends BaseServices
{
    private $appUrl = 'https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

    public function getExpressName($code)
    {
        return [
            "ZTO" => "中通快递",
            "YTO" => "圆通速递",
            "YD" => "韵达速递",
            "YZPY" => "邮政快递包裹",
            "EMS" => "EMS",
            "DBL" => "德邦快递",
            "FAST" => "快捷快递",
            "ZJS" => "宅急送",
            "TNT" => "TNT快递",
            "UPS" => "UPS",
            "DHL" => "DHL",
            "FEDEX" => "FEDEX联邦(国内件)",
            "FEDEX_GJ" => "FEDEX联邦(国际件)",
        ][$code] ?? '';
    }

    /**
     * 查询订单物流轨迹
     * @param $com
     * @param $code
     * @return mixed|string
     */
    function getOrderTraces($com, $code)
    {
        $appId = env('EXPRESS_APP_ID');
        $appKey = env('EXPRESS_APP_KEY');
        // 组装应用级参数
        $requestData = "{" .
            "'CustomerName': ''," .
            "'OrderCode': ''," .
            "'ShipperCode': '{$com}'," .
            "'LogisticCode': '{$code}'," .
            "}";
        // 组装系统级参数
        $datas = array(
            'EBusinessID' => $appId,
            'RequestType' => '8001', //免费即时查询接口指令1002/在途监控即时查询接口指令8001/地图版即时查询接口指令8003
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $appKey);
        //以form表单形式提交post请求，post请求体中包含了应用级参数和系统级参数
        $result = $this->sendPost($this->appUrl, $datas);

        //根据公司业务处理返回的信息......

        return $result;
    }



    /**
     *  post提交数据
     * @param  string  $url  请求Url
     * @param  array  $datas  提交的数据
     * @return url响应返回的html
     */
    private function sendPost($url, $datas)
    {
        $postdata = http_build_query($datas);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**
     * 电商Sign签名生成
     * @param  data 内容
     * @param  ApiKey ApiKey
     * @return DataSign签名
     */
    private function encrypt($data, $ApiKey)
    {
        return urlencode(base64_encode(md5($data . $ApiKey)));
    }
}
