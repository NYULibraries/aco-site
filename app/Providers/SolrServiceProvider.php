<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SolrServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {

            $config = config('solr');

            return new Client(
                new Curl,
                new EventDispatcher,
                [
                    'endpoint' => [
                        'default' => [
                            'host' => $config['host'],
                            'port' => $config['port'],
                            'path' => $config['path'],
                            'core' => $config['core'],
                        ],
                    ],
                ]
            );
        });
    }

    public function boot() {}

    /**
     * Build and execute a Solr search query.
     */
    public function search(array $options, string $scopeIs = 'matches', string $sortBy = 'score desc')
    {

        $client = app(Client::class);

        $fq = [];

        $fields = [
            'title' => ['match' => ['tks_title_long', 'tks_ar_title_long'], 'contains' => ['tus_title_long', 'ts_title_long', 'tusar_title_long']],
            'author' => ['match' => ['tkm_author', 'tkm_ar_author'], 'contains' => ['tum_author', 'tm_author', 'tumar_author']],
            'pubplace' => ['match' => ['tks_publocation', 'tks_ar_publocation'], 'contains' => ['tus_publocation', 'ts_publocation', 'tusar_publocation']],
            'publisher' => ['match' => ['tkm_publisher', 'tkm_ar_publisher'], 'contains' => ['tum_publisher', 'tm_publisher', 'tumar_publisher']],
            'category' => ['match' => ['tkm_topic', 'tkm_ar_topic'], 'contains' => ['tum_topic', 'tm_topic', 'tumar_topic']],
            'provider' => ['match' => ['tkm_provider_label'], 'contains' => ['tum_provider_label', 'tm_provider_label']],
            'subject' => ['match' => ['tkm_subject_label'], 'contains' => ['tum_subject_label', 'tm_subject_label']],
        ];

        foreach ($fields as $key => $map) {
            if (empty($options[$key])) {
                continue;
            }

            $value = trim($options[$key]);
            if ($scopeIs === 'matches') {
                $parts = array_map(fn ($f) => "$f:\"$value\"", $map['match']);
                $fq[] = '('.implode(' OR ', $parts).')';
            } else {
                $words = preg_split('/\s+/', $value);
                $clauses = [];
                foreach ($words as $w) {
                    $sub = array_map(fn ($f) => "$f:\"$w\"", $map['contains']);
                    $clauses[] = '('.implode(' OR ', $sub).')';
                }
                $fq[] = '('.implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $clauses).')';
            }
        }

        // Build main query
        $query = $client->createSelect();

        $query->setQuery('*:*');

        foreach ($fq as $filter) {
            $query->createFilterQuery(md5($filter))->setQuery($filter);
        }

        if (! empty($options['q'])) {
            $q = trim($options['q']);
            if ($scopeIs === 'matches') {
                $query->setQuery("(content_und:\"$q\" OR content_und_ws:\"$q\" OR content_en:\"$q\" OR content:\"$q\")");
            } else {
                $words = preg_split('/\s+/', $q);
                $parts = [];
                foreach ($words as $w) {
                    $parts[] = "(content_und:$w OR content_und_ws:$w OR content_en:$w OR content:$w)";
                }
                $query->setQuery('('.implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $parts).')');
            }
        }

        $query->setStart($options['start'] ?? 0);

        $query->setRows($options['rpp'] ?? 10);

        // $query->addSort($sortBy);

        return $client->select($query);

    }
}
