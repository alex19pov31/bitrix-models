<?php

namespace Alex19pov31\BitrixModel\Traits;

use CUtil;

trait SefUrlTrait
{
    protected static $normalizedList;
    abstract protected function getPropertyCodeList(): array;

    public static function getNormalizedPropertyCodeList(): array
    {
        if (static::$normalizedList) {
            return static::$normalizedList;
        }

        $list = static::getPropertyCodeList();
        $nomalizeList = [];
        foreach ($list as $property) {
            $code = str_replace('property_', '', CUtil::translit($property, 'en'));
            $nomalizeList[$code] = $property;
        }

        return static::$normalizedList = $nomalizeList;
    }

    protected static function getOriginalCodeProperty(string $code): string
    {
        $preffix = '';
        if (strpos($code, 'lt_eq_') === 0) {
            $code = str_replace('lt_eq_', '', $code);
            $preffix = '<=';
        } elseif (strpos($code, 'rt_eq_') === 0) {
            $code = str_replace('rt_eq_', '', $code);
            $preffix = '>=';
        } elseif (strpos($code, 'lt_') === 0) {
            $code = str_replace('lt_', '', $code);
            $preffix = '<';
        } elseif (strpos($code, 'rt_') === 0) {
            $code = str_replace('rt_', '', $code);
            $preffix = '>';
        } elseif (strpos($code, 'eq_') === 0) {
            $code = str_replace('eq_', '', $code);
            $preffix = '=';
        } elseif (strpos($code, 'not_') === 0) {
            $code = str_replace('not_', '', $code);
            $preffix = '!';
        } elseif (strpos($code, 'like_') === 0) {
            $code = str_replace('like_', '', $code);
            $preffix = '%';
        }

        $list = static::getNormalizedPropertyCodeList();
        return (string) $preffix . $list[$code];
    }

    protected static function getNormalizedCodeProperty(string $code): string
    {
        $preffix = '';
        if (strpos($code, '<=') === 0) {
            $code = str_replace('<=', '', $code);
            $preffix = 'lt_eq_';
        } elseif (strpos($code, '>=') === 0) {
            $code = str_replace('>=', '', $code);
            $preffix = 'rt_eq_';
        } elseif (strpos($code, '<') === 0) {
            $code = str_replace('<', '', $code);
            $preffix = 'lt_';
        } elseif (strpos($code, '>') === 0) {
            $code = str_replace('>', '', $code);
            $preffix = 'rt_';
        } elseif (strpos($code, '=') === 0) {
            $code = str_replace('=', '', $code);
            $preffix = 'eq_';
        } elseif (strpos($code, 'not_') === 0) {
            $code = str_replace('not_', '', $code);
            $preffix = '!';
        } elseif (strpos($code, '%') === 0) {
            $code = str_replace('%', '', $code);
            $preffix = 'like_';
        }

        $list = array_flip(static::getNormalizedPropertyCodeList());
        return (string) $preffix . $list[$code];
    }

    /**
     * Возвращает URL фильтра
     *
     * @param array $filter
     * @param string $baseUrl
     * @param string $preffix
     * @param string $postfix
     * @return string
     */
    public static function getSefUrlFilter(array $filter, string $baseUrl = '', string $preffix = 'filter', string $postfix = 'apply'): string
    {
        $props = [$preffix];
        if (empty($baseUrl)) {
            $baseUrl = appInstance()->getContext()->getRequest()->getRequestedPageDirectory();
        }

        foreach ($filter as $code => $value) {
            $code = static::getNormalizedCodeProperty($code);
            if (empty($code)) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as &$val) {
                    if (urlencode($val) != $val) {
                        $val = 'tr_' . base64_encode($val);
                    }
                }

                $props[] = $code . '/' . implode('__', $value);
                continue;
            }

            if (urlencode($value) != $value) {
                $value = 'tr_' . str_replace('%', '',strtolower(urlencode($value)));
            }

            $props[] = $code . '/' . $value;
        }

        return $baseUrl . '/' . implode('/prop_', $props) . '/' . $postfix;
    }

    /**
     * Возвращает фильтр из URL
     *
     * @param string $url
     * @param string $preffix
     * @param string $postfix
     * @return array
     */
    public static function parseSefUrlFilter(string $url = '', string $preffix = 'filter', string $postfix = 'apply'): array
    {
        if (empty($url)) {
            $request = appInstance()->getContext()->getRequest();
            $url = $request->getDecodedUri();
        }

        $arUrl = explode($preffix, $url);
        if (empty($arUrl) || count($arUrl) < 2) {
            return [];
        }

        $arUrl = explode($postfix, $arUrl[1]);
        if (empty($arUrl) || count($arUrl) < 2) {
            return [];
        }

        $partUrl = $arUrl[0];
        $data = explode('/prop_', $partUrl);
        $filter = [];
        foreach ($data as $item) {
            if (empty($item)) {
                continue;
            }

            $arItem = explode('/', $item);
            if (empty($arItem) || count($arItem) < 2) {
                throw new \Exception('eror parse sefUrlFilter');
            }

            $code = $arItem[0];
            $code = static::getOriginalCodeProperty($code);
            $value = explode('__', $arItem[1]);
            foreach ($value as &$val) {
                if (strpos($val, 'tr_') === 0) {
                    $val = str_replace('tr_', '', $val);
                    $val = urldecode(preg_replace("/(.{2})/", "%$1", strtoupper($val)));
                }
            }

            if (count($value) === 1) {
                $value = current($value);
            }
            if (strpos($value, 'tr_') === 0) {
                $value = str_replace('tr_', '', $value);
                $value = urldecode(preg_replace("/(.{2})/", "%$1", strtoupper($value)));
            }
            $filter[$code] = $value;
        }

        return $filter;
    }
}
