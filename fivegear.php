<?php
require_once "./simple_html_dom.php";

class Parser
{
    private static function getHeaders()
    {
        # Используем CORS заголовки
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers", "origin, x-requested-with, content-type");
        header("Access-Control-Allow-Methods", "PUT, GET, POST, DELETE, OPTIONS");
    }

    public static function fiveGear($name): string
    {
        self::getHeaders();
        $resulted_json = [];
        if ($name) {
            # Иммитируем работу браузера
            $useragent = "Mozilla/5.0 (Windows NT 6.3; W…) Gecko/20100101 Firefox/57.0";
            $timeout = 5;
            $connecttimeout = 5;
            # Собираем ссылку на нужную страницу
            $site_name = 'https://xn--80aaaoea1ebkq6dxec.xn--p1ai';
            $url = $site_name . '/manufacturers/' . $name;
            # Инициализируем сеанс
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connecttimeout);
            $res = curl_exec($ch);
            #Проверяем респонс на ошибки и нужный код ответа
            if (!curl_errno($ch)) {
                switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                    case 200:
                        $resulted_json['status'] = $http_code;
                        break;
                    default:
                        echo 'Неожиданный код HTTP: ', $http_code, "\n";
                        return false;
                }
            } else {
                echo 'Возникла ошибка: ', curl_errno($ch);
                return false;
            }
            # Парсим нужные данные
            $html = str_get_html($res);
            $brand = $html->find('h1[itemprop=name]', 0);
            $description = $html->find('p[itemprop=description]', 0);
            $brand_logo = $site_name . $html->find('img[itemprop=logo]', 0)->src;
            $brand_sample = $site_name . $html->find('div.manufacturer-sample-photo img', 0)->src;
            $info_names = $html->find('.mfr-prop-name');
            $info_values = $html->find('.mfr-prop-value');
            $info = [];
            for ($i = 0; $i < count($info_names); $i++) {
                if ($info_names[$i] and $info_values[$i]) {
                    # Если есть ссылка - береем ее, иначе - текст
                    if ($info_values[$i]->find('a')) {
                        $info[trim($info_names[$i]->plaintext, ':')] = $info_values[$i]->find('a', 0)->href;
                    } else {
                        $info[trim($info_names[$i]->plaintext, ':')] = $info_values[$i]->plaintext;
                    }
                }
            }
            $result = [];
            # Собираем результаты в массив, проверяя на наличие
            if ($brand) {
                $result['brand'] = $brand->plaintext;
            }
            if ($description) {
                $result[$description->itemprop] = $description->plaintext;
            }
            if ($brand_logo) {
                $result['brand_logo'] = $brand_logo;
            }
            if ($brand_sample) {
                $result['brand_sample'] = $brand_sample;
            }
            if ($info) {
                $result['info'] = $info;
            }
            $resulted_json['result'] = $result;
            # Возвращаем json в Unicode, убираем экранирование слэшей
            echo json_encode($resulted_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return false;
    }
}
# Тестовый запуск
echo Parser::fiveGear("abb-filter");
