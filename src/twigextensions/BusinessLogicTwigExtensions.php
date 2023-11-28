<?php

namespace conversionia\leadflex\twigextensions;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use yii\web\Cookie;

class BusinessLogicTwigExtensions extends AbstractExtension implements GlobalsInterface
{
    private function buildCookie($key, $value, $duration) : Cookie
    {
        $expiry = new \DateTime();
        $expiry = $expiry->modify($duration);
        return new Cookie([
            'name'=> $key,
            'value'=> $value,
            'path' => '/',
            'domain'=>'',
            'expire'=> $expiry->getTimestamp(),
            'secure' => true,
            'httpOnly' => true,
        ]);
    }

    private function buildCookieValue($key, $name, $defaultCookieValue, $duration = "+1 hour"): ?string
    {
        $requestCookies = Craft::$app->request->getCookies();
        $requestCookiesValue = $requestCookies->getValue($name);

        $responseCookies = Craft::$app->response->cookies;
        $urlValue = Craft::$app->request->getQueryParam($key);

        if (!is_null($urlValue)){
            $value = $urlValue;
            $cookie = $this->buildCookie($name, $value, $duration);

            $responseCookies->add($cookie);
            $_COOKIE[$name] = $value;
        } elseif (!is_null($requestCookiesValue))
        {
            $value = $requestCookiesValue;
        } else {
            $value = $defaultCookieValue;
            if (is_null($value)) {
                return null;
            }
            $cookie = $this->buildCookie($name, $value, $duration);
            Craft::$app->getResponse()->getCookies()->add($cookie);
            $responseCookies->add($cookie);
            $_COOKIE[$name] = $value;
        };

        return $value;
    }

    public function getGlobals(): array
    {
        return [
            'referrer'      =>  $this->buildCookieValue('r','cookie-monster', "lf_direct"),
            'utmSource'     =>  $this->buildCookieValue('utm_source','cookie-monster-utm-source', "leadflex"),
            'utmMedium'     =>  $this->buildCookieValue('utm_medium','cookie-monster-utm-medium', "direct"),
            'utmCampaign'   =>  $this->buildCookieValue('utm_campaign','cookie-monster-utm-campaign', "lf_direct"),
            'utmContent'    =>  $this->buildCookieValue('utm_content','cookie-monster-utm-content', null),
            'ebeSource'     =>  $this->buildCookieValue('source','cookie-monster-ebe-source', 'LeadFlex Direct/Organic'),
            'ebeSourceId'   =>  $this->buildCookieValue('sourceId','cookie-monster-ebe-source-id', '230')
        ];
    }
}
