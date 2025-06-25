<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;

class WxController extends Controller
{
    protected $only;
    protected $except;
    public function __construct()
    {
        $options = [];
        if (!is_null($this->only)) {
            $options['only'] = $this->only;
        }
        if (!is_null($this->except)) {
            $options['except'] = $this->except;
        }
        $this->middleware('auth:wx', $options);
    }
    protected function codeReturn(array $codeResponse, $data = null, $info = "")
    {
        list($errno, $errmsg) = $codeResponse;
        $ret = [
            'errno' => $errno,
            'errmsg' => $info ?: $errmsg,
        ];
        if (!is_null($data)) {
            $ret['data'] = $data;
        }
        return response()->json($ret);
    }

    protected function success($data = null)
    {
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }

    protected function fail(array $codeResponse, $info = "")
    {
        return $this->codeReturn($codeResponse, null, $info);
    }
}
