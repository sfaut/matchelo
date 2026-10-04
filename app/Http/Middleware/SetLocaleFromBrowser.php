<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applique la langue préférée du navigateur (en-tête Accept-Language) parmi les
 * langues supportées, à défaut la langue de repli de l'application.
 *
 * @todo Faire primer la langue choisie dans le sélecteur ou le profil utilisateur.
 */
class SetLocaleFromBrowser
{
    /**
     * @param list<string> $supportedLocales
     *     langues supportées par l'application (matchelo.locales)
     * @param string $fallbackLocale
     *     langue appliquée quand le navigateur n'en préfère aucune supportée (app.fallback_locale)
     */
    public function __construct(
        #[Config('matchelo.locales')]
        private readonly array $supportedLocales,
        #[Config('app.fallback_locale')]
        private readonly string $fallbackLocale,
    ) {
        //
    }

    /**
     * Fixe la locale de la requête, puis signale aux caches que la réponse dépend
     * de l'en-tête Accept-Language.
     *
     * @param Request $request
     *     requête entrante
     * @param Closure(Request): Response $next
     *     suite de la chaîne de middlewares
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // getPreferredLanguage() renvoie la première langue de la liste quand aucune ne
        // correspond : la langue de repli est donc placée en tête
        $candidate_locales = array_values(array_unique([$this->fallbackLocale, ...$this->supportedLocales]));

        app()->setLocale($request->getPreferredLanguage($candidate_locales) ?? $this->fallbackLocale);

        $response = $next($request);
        $response->setVary('Accept-Language', replace: false);

        return $response;
    }
}
