<?php
require_once "./simple_html_dom.php";

class Parser
{
    public static function fiveGear($name): string
    {

        if ($name) {
            $site_name = 'https://xn--80aaaoea1ebkq6dxec.xn--p1ai';
            $url = $site_name . '/manufacturers/' . $name;

            $ch = curl_init(); // Создаём запрос
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);

            $html = str_get_html($res);
            $brand = $html->find('h1[itemprop=name]', 0);
            $description = $html->find('p[itemprop=description]', 0);
            $brand_logo = $site_name . $html->find('img[itemprop=logo]', 0)->src;
            $brand_sample = $site_name . $html->find('div.manufacturer-sample-photo img', 0)->src;
            $info = $html->find('div.manufacturer-properties-table', 0)->find('div.mfr-prop-value');
            foreach ($info as $div)
                echo $div;
        }
        return false;
    }
}

echo Parser::fiveGear("abb-filter");
