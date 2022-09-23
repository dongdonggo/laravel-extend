<?php

namespace Dongdonggo\LaravelExtend\Providers;

use App\Exceptions\Api\ApiException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Recall\Services\Rsa\Rsa;

class ValidatorMacroServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->extend();
    }

    /**
     * 扩展
     */
    private function extend()
    {
        $this->stringValidate();
        $this->idcardValidate();
        $this->phoneChValidate();
    }

    /**
     * @Notice: 密码验证的扩展
     * @Date: 2022/7/26 15:41
     * @Author: dongdong
     * @dcument  使用方式    'password' => 'required|password_validate:3,A,a,must,1,@',
     * @ductment  第1个参数 是 验证的种类， 后面 是要验证的 选择代表参数
     */
    public function stringValidate()
    {
        // 成功
        Validator::extend('password_validate', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {


            $erroryKey =  $attribute.'.password_validate';
            // 第一个参数验证
            $number =  $parameters[0]??null;
            if (!is_numeric($number)){
                throw new ApiException("password_validate first param is error!");
            }

            if ($number ==null){
                throw new ApiException("password_validate first param is null!");
            }

            // 基础验证数据
            $arr = [
                '1' =>  [
                    'rule' => '[0-9]',
                    'message' => '密码 至少包含一个整数',
                ],
                'a' => [
                    'rule' => '[a-z]',
                    'message' => '密码  至少包含一个小写字母'
                ],
                'A' => [
                    'rule' =>  '[A-Z]',
                    'message' => '密码 至少包含一个大写字母'
                ],
                '@' =>   [
                    'rule' =>  '[.!@#$%^&|*()]',
                    'message' => '密码 至少包含一个特殊字符'
                ],
                'A-a' => [
                    'rule' =>  '[A-Za-z]',
                    'message' => '密码 至少包含一个大写或小写字母'
                ]
            ];
            unset($parameters[0]);

            $errorMessage = [];

            $mustParameters = [];
            $joinMust = false;
            foreach ($parameters as $item){
                if (strpos($item, 'must') !== false) {
                    $joinMust = true;
                    continue;
                }

                if ($joinMust) { // 加入must 验证
                    $mustParameters[$item] = $item;
                }

                if (!isset($arr[$item])) {
                    throw new ApiException("password_validate  param {$item}   not found!");
                }

                if (! (bool) preg_match('/' . $arr[$item]['rule'] . '/', $value)) {
                    $error =  $arr[$item]['message'];
                    $errorMessage[$item] = $error;
                }
            }

            // 必须有的字符 验证  错误提示
            $intersect = array_intersect_key($mustParameters, $errorMessage);

            if(!empty($intersect)) {
               $mustErrorMessage =  Arr::only($errorMessage, $intersect);

                if (!isset($validator->customMessages[$erroryKey])) {
                    // 设置错误提示
                    $validator->setCustomMessages([
                        $attribute.'.password_validate' =>  implode(' 且 ', $mustErrorMessage),
                    ]);
                }
                return false;
            }

            $success =  count($parameters) - count($errorMessage);
            if ($success >= $number) {
                return  true;
            }


            if (!isset($validator->customMessages[$erroryKey])) {
                $validator->setCustomMessages([
                    $erroryKey  =>  implode(' 或 ', $errorMessage),
                ]);
            }


            return false;
        });

        //字符验证
        Validator::extend('string_format', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {


            $erroryKey =  $attribute.'.string_format';
            // 第一个参数验证
            $number =  $parameters[0]??null;
            if (!is_numeric($number)){
                throw new ApiException("string_format first param is error!");
            }

            if ($number ==null){
                throw new ApiException("password_validate first param is null!");
            }

            // 基础验证数据
            $arr = [
                '1' =>  [
                    'rule' => '[0-9]',
                    'message' => ' 至少包含一个整数',
                ],
                'a' => [
                    'rule' => '[a-z]',
                    'message' => '  至少包含一个小写字母'
                ],
                'A' => [
                    'rule' =>  '[A-Z]',
                    'message' => ' 至少包含一个大写字母'
                ],
                '@' =>   [
                    'rule' =>  '[.!@#$%^&|*()]',
                    'message' => ' 至少包含一个特殊字符'
                ],
                'A-a' => [
                    'rule' =>  '[A-Za-z]',
                    'message' => ' 至少包含一个大写或小写字母'
                ]
            ];
            unset($parameters[0]);

            $errorMessage = [];

            $mustParameters = [];
            $joinMust = false;
            foreach ($parameters as $item){
                if (strpos($item, 'must') !== false) {
                    $joinMust = true;
                    continue;
                }

                if ($joinMust) { // 加入must 验证
                    $mustParameters[$item] = $item;
                }

                if (!isset($arr[$item])) {
                    throw new ApiException("string_format  param {$item}   not found!");
                }

                if (! (bool) preg_match('/' . $arr[$item]['rule'] . '/', $value)) {
                    $error =  $arr[$item]['message'];
                    $errorMessage[$item] = $error;
                }
            }

            // 必须有的字符 验证  错误提示
            $intersect = array_intersect_key($mustParameters, $errorMessage);

            if(!empty($intersect)) {
                $mustErrorMessage =  Arr::only($errorMessage, $intersect);

                if (!isset($validator->customMessages[$erroryKey])) {
                    // 设置错误提示
                    $validator->setCustomMessages([
                        $attribute.'.string_format' =>  implode(' 且 ', $mustErrorMessage),
                    ]);
                }
                return false;
            }

            $success =  count($parameters) - count($errorMessage);
            if ($success >= $number) {
                return  true;
            }


            if (!isset($validator->customMessages[$erroryKey])) {
                $validator->setCustomMessages([
                    $erroryKey  =>  implode(' 或 ', $errorMessage),
                ]);
            }


            return false;
        });
    }

    /**
     * @Notice:身份证验证
     * @Date: 2022/9/18 15:42
     * @Author: dongdong
     */
    public function idcardValidate()
    {
        Validator::extend('idcard', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {

            $erroryKey =  $attribute.'.idcard';
            $g = '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx])|([1−9]\d{5}\d{2}((0[1−9])|(10|11|12))(([0−2][1−9])|10|20|30|31)\d{2}[0−9Xx])|([1−9]\d{5}\d{2}((0[1−9])|(10|11|12))(([0−2][1−9])|10|20|30|31)\d{2}[0−9Xx])$/i';
            if (preg_match($g, $value)) {
                // 转化为大写，如出现x
                $number = strtoupper($value);
                //加权因子
                $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                //校验码串
                $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                //按顺序循环处理前17位
                $sigma = 0;
                for ($i = 0; $i < 17; $i++) {
                    //提取前17位的其中一位，并将变量类型转为实数
                    if (!is_numeric($number[$i])) {
                        return false;
                    }
                    $b = (int)$number[$i];

                    //提取相应的加权因子
                    $w = $wi[$i];

                    //把从身份证号码中提取的一位数字和加权因子相乘，并累加
                    $sigma += $b * $w;
                }
                //计算序号
                $snumber = $sigma % 11;

                //按照序号从校验码串中提取相应的字符。
                $check_number = $ai[$snumber];

                if ($number[17] == $check_number) {
                    return true;
                } else {
                    if (!isset($validator->customMessages[$erroryKey])) {
                        $validator->setCustomMessages([
                            $erroryKey  =>  $value." 证件号错误",
                        ]);
                    }
                    return false;
                }
            } else {
                if (!isset($validator->customMessages[$erroryKey])) {
                    $validator->setCustomMessages([
                        $erroryKey  =>  $value." 证件号错误，不是正确的身份证号",
                    ]);
                }
                return false;
            }

        });

    }


    /**
     * @Notice:中国手机号 验证
     * @Date: 2022/9/18 15:42
     * @Author: dongdong
     */
    public function phoneChValidate()
    {

        Validator::extend('phone_ch', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {

            $erroryKey =  $attribute.'.phone_ch';
            $preg_phone='/^1[3456789]\d{9}$/ims';
            if(preg_match($preg_phone,$value)){
                return true;
            }else{
                $validator->setCustomMessages([
                    $erroryKey  =>  $value." 手机号格式不正确!",
                ]);
                return false;
            }


        });

    }
}
