<?php

namespace App\Routing;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class LocaleUrlGenerator extends BaseUrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        if ($this->shouldPreferBanglaRoute($name)) {
            $name = 'bn.'.$name;
        }

        return parent::route($name, $parameters, $absolute);
    }

    protected function shouldPreferBanglaRoute(string $name): bool
    {
        if (app()->getLocale() !== 'bn') {
            return false;
        }

        if (str_starts_with($name, 'bn.') || str_starts_with($name, 'admin.')) {
            return false;
        }

        return $this->routes->hasNamedRoute('bn.'.$name);
    }
}
