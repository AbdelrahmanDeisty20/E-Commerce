<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // الحصول على اللغة من الهيدر أو البارامتر، مع ضبط الإنجليزية كافتراضية
        $lang = $request->header('Accept-Language', $request->get('lang', 'en'));

        // التأكد أن اللغة صحيحة (متاحة ضمن اللغات المدعومة)
        if (!in_array($lang, ['en', 'ar'])) {
            $lang = 'en';
        }

        // تعيين اللغة في التطبيق
        App::setLocale($lang);

        return $next($request);
    }
}
