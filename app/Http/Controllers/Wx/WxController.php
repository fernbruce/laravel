<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\VerifyRequestInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Exception;

class WxController extends Controller
{
    use VerifyRequestInput;

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

    /**
     * @return JsonResponse
     */
    protected function badArgument(): JsonResponse
    {
        return $this->fail(CodeResponse::PARAM_ILLEGAL);
    }

    /**
     * @return JsonResponse
     */
    protected function badArgumentValue(): JsonResponse
    {
        return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
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

    public function isLogin()
    {
        return !is_null($this->user());
    }

    public function userId()
    {
        return $this->user()->getAuthIdentifier();
    }

    protected function successPaginate($page)
    {
        return $this->success($this->paginate($page));
    }

    protected function paginate($page, $list = null)
    {
        if ($page instanceof LengthAwarePaginator) {
            $total = $page->total();
            return [
                'total' => $page->total(),
                'page' => $total === 0 ? 0 : $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $total === 0 ? 0 : $page->lastPage(),
                'list' => $list ?? $page->items()

            ];
        }
        if ($page instanceof Collection) {
            $page = $page->toArray();
        }
        if (!is_array($page)) {
            return $page;
        }
        $total = count($page);
        return [
            'total' => $total,
            'page' => $total === 0 ? 0 : 1,
            'limit' => $total,
            'pages' => $total === 0 ? 0 : 1,
            'list' => $page
        ];
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     * @throws BusinessException
     */
//    public function verifyId($key, $default = null){
//        $value = request()->input($key, $default);
//         $validator = Validator::make([
//             'key' => $value,
//         ],[
//             $key=>'integer|digits_between:1,20',
//         ]);
//         if($validator->fails()){
//            throw new BusinessException(CodeResponse::PARAM_VALUE_ILLEGAL); ;
//         }
//         return $value;
//    }
}
