<?php

namespace App\Indexation;

use App\Database\Model\Geoname\ExecutiveSchema\Localization;
use App\Database\Model\Geoname\ExecutiveSchema\LocalizationModel;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use PommProject\Foundation\Where;
use Symfony\Component\Console\Style\SymfonyStyle;

class LocalizationBuilder
{
    const INDEX_NAME = 'localizations';
    const INDEX_TYPE = 'localization';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LocalizationModel
     */
    private $model;

    /**
     * @param Client $client
     * @param LocalizationModel $model
     */
    public function __construct(Client $client, LocalizationModel $model)
    {
        $this->client = $client;
        $this->model = $model;
    }


    /**
     * @inheritdoc
     */
    public function create(): Index
    {
        $index = $this->client->getIndex(self::INDEX_NAME);

        $settings = [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                'analysis' => [
                    'analyzer' => [
                        'index_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => [
                                'stopwords', 'asciifolding', 'lowercase', 'snowball', 'elision', 'stemmer'
                            ],
                        ],
                        'search_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'nGram',
                            'filter' => [
                                'stopwords', 'asciifolding', 'lowercase', 'snowball', 'elision', 'stemmer'
                            ],
                        ],
                    ],
                    'tokenizer' => [
                        'nGram' => [
                            'type' => 'nGram',
                            'min_gram' => 2,
                            'max_gram' => 20
                        ],
                    ],
                    'filter' => [
                        'elision' => [
                            'type' => 'elision',
                            'articles_case' => true,
                            'articles' => [
                                'l', 'm', 't', 'qu', 'n', 's',
                                'j', 'd', 'c', 'jusqu', 'quoiqu',
                                'lorsqu', 'puisqu'
                            ]
                        ],
                        'snowball' => [
                            'type' => 'snowball',
                            'language' => 'French'
                        ],
                        'stopwords' => [
                            'type' => 'stop',
                            'stopwords' => '_french_',
                            'ignore_case' => true
                        ],
                        'stemmer' => [
                            'type' => 'stemmer',
                            'language' => 'light_french'
                        ]
                    ]
                ]
            ],
            'mappings' => [
                self::INDEX_TYPE => [
                    'properties' => [
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'index_analyzer',
                            'fields' => [
                                'search' => [
                                    'type' => 'text',
                                    'analyzer' => 'search_analyzer'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $index->create($settings, true);

        return $index;
    }

    /**
     * @param Localization $localization
     *
     * @return Document
     * @throws \PommProject\ModelManager\Exception\ModelException
     */
    public function buildDocument(Localization $localization): Document
    {
        return new Document(
            $localization->get('uuid'),
            [
                'name' => $localization->get('name'),
                'parent' => $localization->get('id_parent'),
            ],
            self::INDEX_TYPE
        );
    }

    /**
     * @param SymfonyStyle $io
     * @throws \PommProject\ModelManager\Exception\ModelException
     */
    public function indexAllDocuments(SymfonyStyle $io = null): void
    {
        $limit = 1000;
        $count = $this->model->countWhere(Where::create());
        $index = $this->client->getIndex(self::INDEX_NAME);

        if (!is_null($io)) {
            $io->note(sprintf('Need to index %s documents by batch to %s.', $count, $limit));
            $io->progressStart($count);
        }

        for ($i = 0; $i <= ceil($count/$limit); $i++) {
            $documents = [];
            $localizations = iterator_to_array($this->model->findAllWithPagination(Where::create('id_parent IS NOT NULL'), $i * $limit, $limit));
            foreach ($localizations as $localization) {
                $documents[] = $this->buildDocument($localization);
            }
            unset($localizations);

            $index->addDocuments($documents);
            $index->refresh();
            unset($documents);

            if (!is_null($io)) {
                $io->progressAdvance($limit);
            }
        }

        if (!is_null($io)) {
            $io->progressFinish();
            $io->success('Indexation finished.');
        }
    }
}
