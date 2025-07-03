<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

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
            if (is_array($data)) {
                $data = array_filter($data, function ($item) {
                    return $item !== null;
                });
            }
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

    protected function failOrSuccess($isSuccess, array $codeResponse, $data = null, $info = "")
    {
        if ($isSuccess) {
            return $this->success($data);
        } else {
            return $this->fail($codeResponse, $info);
        }
    }

    /**
     *
     * @return User|null
     */
    protected function user()
    {
        return Auth::guard('wx')->user();
    }

    protected function successPaginate($page)
    {
        return $this->success($this->paginate($page));
    }
    protected function paginate($page)
    {
        if ($page instanceof LengthAwarePaginator) {
            return [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $page->lastPage(),
                'list' => $page->items()

            ];
        } elseif (is_array($page)) {
            $total = count($page);
            return [
                'total' => $total,
                'page' => 1,
                'limit' => $total,
                'pages' => 1,
                'list' => $page
            ];
        }
        return $page;
    }
}
