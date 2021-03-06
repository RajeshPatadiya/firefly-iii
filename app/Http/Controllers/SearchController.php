<?php
/**
 * SearchController.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use FireflyIII\Support\CacheProperties;
use FireflyIII\Support\Search\SearchInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use View;

/**
 * Class SearchController.
 */
class SearchController extends Controller
{
    /**
     * SearchController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware(
            function ($request, $next) {
                app('view')->share('mainTitleIcon', 'fa-search');
                app('view')->share('title', trans('firefly.search'));

                return $next($request);
            }
        );
    }

    /**
     * @param Request         $request
     * @param SearchInterface $searcher
     *
     * @return View
     */
    public function index(Request $request, SearchInterface $searcher)
    {
        $fullQuery = strval($request->get('q'));

        // parse search terms:
        $searcher->parseQuery($fullQuery);
        $query    = $searcher->getWordsAsString();
        $subTitle = trans('breadcrumbs.search_result', ['query' => $query]);

        return view('search.index', compact('query', 'fullQuery', 'subTitle'));
    }

    /**
     * @param Request         $request
     * @param SearchInterface $searcher
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function search(Request $request, SearchInterface $searcher)
    {
        $fullQuery    = strval($request->get('query'));
        $transactions = new Collection;
        // cache
        $cache = new CacheProperties;
        $cache->addProperty('search');
        $cache->addProperty($fullQuery);

        if ($cache->has()) {
            $transactions = $cache->get(); // @codeCoverageIgnore
        }

        if (!$cache->has()) {
            // parse search terms:
            $searcher->parseQuery($fullQuery);
            $searcher->setLimit(intval(env('SEARCH_RESULT_LIMIT', 50)));
            $transactions = $searcher->searchTransactions();
            $cache->store($transactions);
        }

        $html = view('search.search', compact('transactions'))->render();

        return response()->json(['count' => $transactions->count(), 'html' => $html]);
    }
}
