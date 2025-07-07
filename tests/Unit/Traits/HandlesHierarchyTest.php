<?php

namespace BenColmer\NovaResourceHierarchy\Tests\Unit\Traits;

use ArrayAccess;
use BenColmer\NovaResourceHierarchy\Traits\HandlesHierarchy;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HandlesHierarchyTest extends TestCase
{
    use HandlesHierarchy;

    #[DataProvider('provideFlatArrayCases')]
    public function test_build_hierarchy(
        array|ArrayAccess $input,
        array $expected,
        string $idKey = 'id',
        string $parentKey = 'parent_id'
    ): void {
        $result = $this->buildHierarchy(
            $input,
            fn($item) => $item instanceof Model ? $item->toArray() : $item,
            null,
            $idKey,
            $parentKey
        );

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    #[DataProvider('provideHierarchyTestCases')]
    public function test_parse_hierarchy(
        array|ArrayAccess $input,
        array $expected,
        mixed $defaultParentValue = null,
        string $idKey = 'id',
        string $parentKey = 'parent_id',
        string $orderKey = 'rank'
    ): void {
        $result = $this->parseHierarchy(
            $input,
            fn($item, $parentId, $rank) => [
                $idKey => $item[$idKey],
                $parentKey => $parentId,
                $orderKey => $rank,
            ],
            $idKey,
            $defaultParentValue
        );

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public static function provideFlatArrayCases(): array
    {
        $modelClass = new class extends Model {
            protected $fillable = [
                'id',
                'parent_id'
            ];
        };

        return [
            'empty input' => [
                'input' => [],
                'expected' => [],
            ],
            'array of arrays' => [
                'input' => [
                    ['id' => 1, 'parent_id' => 0],
                    ['id' => 10, 'parent_id' => 2],
                    ['id' => 2, 'parent_id' => 0],
                    ['id' => 3, 'parent_id' => 10],
                    ['id' => 4, 'parent_id' => 0],
                    ['id' => 11, 'parent_id' => 1],
                    ['id' => 5, 'parent_id' => 0],
                    ['id' => 6, 'parent_id' => 1],
                    ['id' => 8, 'parent_id' => 11],
                    ['id' => 9, 'parent_id' => 0],
                    ['id' => 7, 'parent_id' => 0],
                ],
                'expected' => [
                    [
                        'id' => 1,
                        'parent_id' => 0,
                        'children' => [
                            [
                                'id' => 11,
                                'parent_id' => 1,
                                'children' => [
                                    [
                                        'id' => 8,
                                        'parent_id' => 11,
                                        'children' => [],
                                    ]
                                ]
                            ],
                            [
                                'id' => 6,
                                'parent_id' => 1,
                                'children' => [],
                            ],
                        ]
                    ],
                    [
                        'id' => 2,
                        'parent_id' => 0,
                        'children' => [
                            [
                                'id' => 10,
                                'parent_id' => 2,
                                'children' => [
                                    [
                                        'id' => 3,
                                        'parent_id' => 10,
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 4,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                    [
                        'id' => 5,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                    [
                        'id' => 7,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                    [
                        'id' => 9,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                ],
            ],
            'alternate id / parent_id keys' => [
                'input' => [
                    ['key' => 1, 'relatedID' => 0],
                    ['key' => 10, 'relatedID' => 2],
                    ['key' => 2, 'relatedID' => 0],
                    ['key' => 3, 'relatedID' => 10],
                    ['key' => 4, 'relatedID' => 0],
                    ['key' => 11, 'relatedID' => 1],
                    ['key' => 5, 'relatedID' => 0],
                    ['key' => 6, 'relatedID' => 1],
                    ['key' => 8, 'relatedID' => 11],
                    ['key' => 9, 'relatedID' => 0],
                    ['key' => 7, 'relatedID' => 0],
                ],
                'expected' => [
                    [
                        'key' => 1,
                        'relatedID' => 0,
                        'children' => [
                            [
                                'key' => 11,
                                'relatedID' => 1,
                                'children' => [
                                    [
                                        'key' => 8,
                                        'relatedID' => 11,
                                        'children' => [],
                                    ]
                                ]
                            ],
                            [
                                'key' => 6,
                                'relatedID' => 1,
                                'children' => [],
                            ],
                        ]
                    ],
                    [
                        'key' => 2,
                        'relatedID' => 0,
                        'children' => [
                            [
                                'key' => 10,
                                'relatedID' => 2,
                                'children' => [
                                    [
                                        'key' => 3,
                                        'relatedID' => 10,
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'key' => 4,
                        'relatedID' => 0,
                        'children' => [],
                    ],
                    [
                        'key' => 5,
                        'relatedID' => 0,
                        'children' => [],
                    ],
                    [
                        'key' => 7,
                        'relatedID' => 0,
                        'children' => [],
                    ],
                    [
                        'key' => 9,
                        'relatedID' => 0,
                        'children' => [],
                    ],
                ],
                'idKey' => 'key',
                'parentKey' => 'relatedID',
            ],
            'collection of models' => [
                'input' => collect([
                    new $modelClass(['id' => 1, 'parent_id' => null]),
                    new $modelClass(['id' => 2, 'parent_id' => null]),
                    new $modelClass(['id' => 3, 'parent_id' => 2]),
                    new $modelClass(['id' => 4, 'parent_id' => 2]),
                    new $modelClass(['id' => 5, 'parent_id' => 2]),
                    new $modelClass(['id' => 6, 'parent_id' => null]),
                    new $modelClass(['id' => 7, 'parent_id' => 6]),
                    new $modelClass(['id' => 8, 'parent_id' => 7]),
                    new $modelClass(['id' => 9, 'parent_id' => 8]),
                    new $modelClass(['id' => 10, 'parent_id' => 7]),
                    new $modelClass(['id' => 11, 'parent_id' => 10]),
                    new $modelClass(['id' => 12, 'parent_id' => 6]),
                ]),
                'expected' => [
                    [
                        'id' => 1,
                        'parent_id' => null,
                        'children' => []
                    ],
                    [
                        'id' => 2,
                        'parent_id' => null,
                        'children' => [
                            [
                                'id' => 3,
                                'parent_id' => 2,
                                'children' => []
                            ],
                            [
                                'id' => 4,
                                'parent_id' => 2,
                                'children' => []
                            ],
                            [
                                'id' => 5,
                                'parent_id' => 2,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'id' => 6,
                        'parent_id' => null,
                        'children' => [
                            [
                                'id' => 7,
                                'parent_id' => 6,
                                'children' => [
                                    [
                                        'id' => 8,
                                        'parent_id' => 7,
                                        'children' => [
                                            [
                                                'id' => 9,
                                                'parent_id' => 8,
                                                'children' => []
                                            ]
                                        ]
                                    ],
                                    [
                                        'id' => 10,
                                        'parent_id' => 7,
                                        'children' => [
                                            [
                                                'id' => 11,
                                                'parent_id' => 10,
                                                'children' => []
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 12,
                                'parent_id' => 6,
                                'children' => []
                            ]
                        ]
                    ]
                ],
            ],
        ];
    }

    public static function provideHierarchyTestCases(): array
    {
        return [
            'empty input' => [
                'input' => [],
                'expected' => [],
            ],
            'nested hierarchy (3 depth)' => [
                'input' => [
                    [
                        'id' => 1,
                        'parent_id' => 0,
                        'children' => [
                            [
                                'id' => 11,
                                'parent_id' => 1,
                                'children' => [
                                    [
                                        'id' => 8,
                                        'parent_id' => 11,
                                        'children' => [],
                                    ]
                                ]
                            ],
                            [
                                'id' => 6,
                                'parent_id' => 1,
                                'children' => [],
                            ],
                        ]
                    ],
                    [
                        'id' => 2,
                        'parent_id' => 0,
                        'children' => [
                            [
                                'id' => 10,
                                'parent_id' => 2,
                                'children' => [
                                    [
                                        'id' => 3,
                                        'parent_id' => 10,
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 4,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                    [
                        'id' => 5,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                    [
                        'id' => 7,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                    [
                        'id' => 9,
                        'parent_id' => 0,
                        'children' => [],
                    ],
                ],
                'expected' => [
                    ['id' => 1, 'parent_id' => 0, 'rank' => 0],
                    ['id' => 11, 'parent_id' => 1, 'rank' => 0],
                    ['id' => 8, 'parent_id' => 11, 'rank' => 0],
                    ['id' => 6, 'parent_id' => 1, 'rank' => 1],
                    ['id' => 2, 'parent_id' => 0, 'rank' => 1],
                    ['id' => 10, 'parent_id' => 2, 'rank' => 0],
                    ['id' => 3, 'parent_id' => 10, 'rank' => 0],
                    ['id' => 4, 'parent_id' => 0, 'rank' => 2],
                    ['id' => 5, 'parent_id' => 0, 'rank' => 3],
                    ['id' => 7, 'parent_id' => 0, 'rank' => 4],
                    ['id' => 9, 'parent_id' => 0, 'rank' => 5],
                ],
            ],
            'nested hierarchy (5 depth)' => [
                'input' => [
                    [
                        'id' => 1,
                        'parent_id' => null,
                        'children' => []
                    ],
                    [
                        'id' => 2,
                        'parent_id' => null,
                        'children' => [
                            [
                                'id' => 3,
                                'parent_id' => 2,
                                'children' => []
                            ],
                            [
                                'id' => 4,
                                'parent_id' => 2,
                                'children' => []
                            ],
                            [
                                'id' => 5,
                                'parent_id' => 2,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'id' => 6,
                        'parent_id' => null,
                        'children' => [
                            [
                                'id' => 7,
                                'parent_id' => 6,
                                'children' => [
                                    [
                                        'id' => 8,
                                        'parent_id' => 7,
                                        'children' => [
                                            [
                                                'id' => 9,
                                                'parent_id' => 8,
                                                'children' => [
                                                    [
                                                        'id' => 13,
                                                        'parent_id' => 9,
                                                        'children' => []
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        'id' => 10,
                                        'parent_id' => 7,
                                        'children' => [
                                            [
                                                'id' => 11,
                                                'parent_id' => 10,
                                                'children' => []
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 12,
                                'parent_id' => 6,
                                'children' => []
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    ['id' => 1, 'parent_id' => null, 'rank' => 0],
                    ['id' => 2, 'parent_id' => null, 'rank' => 1],
                    ['id' => 3, 'parent_id' => 2, 'rank' => 0],
                    ['id' => 4, 'parent_id' => 2, 'rank' => 1],
                    ['id' => 5, 'parent_id' => 2, 'rank' => 2],
                    ['id' => 6, 'parent_id' => null, 'rank' => 2],
                    ['id' => 7, 'parent_id' => 6, 'rank' => 0],
                    ['id' => 12, 'parent_id' => 6, 'rank' => 1],
                    ['id' => 8, 'parent_id' => 7, 'rank' => 0],
                    ['id' => 10, 'parent_id' => 7, 'rank' => 1],
                    ['id' => 9, 'parent_id' => 8, 'rank' => 0],
                    ['id' => 13, 'parent_id' => 9, 'rank' => 0],
                    ['id' => 11, 'parent_id' => 10, 'rank' => 0],
                ],
            ],
            'alternate id / parent_id keys' => [
                'input' => [
                    [
                        'key' => 1,
                        'children' => [
                            [
                                'key' => 11,
                                'children' => [
                                    [
                                        'key' => 8,
                                        'children' => [],
                                    ]
                                ]
                            ],
                            [
                                'key' => 6,
                                'children' => [],
                            ],
                        ]
                    ],
                    [
                        'key' => 2,
                        'children' => [
                            [
                                'key' => 10,
                                'children' => [
                                    [
                                        'key' => 3,
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'key' => 4,
                        'children' => [],
                    ],
                    [
                        'key' => 5,
                        'children' => [],
                    ],
                    [
                        'key' => 7,
                        'children' => [],
                    ],
                    [
                        'key' => 9,
                        'children' => [],
                    ],
                ],
                'expected' => [
                    ['key' => 1, 'relatedID' => 0, 'orderNum' => 0],
                    ['key' => 11, 'relatedID' => 1, 'orderNum' => 0],
                    ['key' => 8, 'relatedID' => 11, 'orderNum' => 0],
                    ['key' => 6, 'relatedID' => 1, 'orderNum' => 1],
                    ['key' => 2, 'relatedID' => 0, 'orderNum' => 1],
                    ['key' => 10, 'relatedID' => 2, 'orderNum' => 0],
                    ['key' => 3, 'relatedID' => 10, 'orderNum' => 0],
                    ['key' => 4, 'relatedID' => 0, 'orderNum' => 2],
                    ['key' => 5, 'relatedID' => 0, 'orderNum' => 3],
                    ['key' => 7, 'relatedID' => 0, 'orderNum' => 4],
                    ['key' => 9, 'relatedID' => 0, 'orderNum' => 5],
                ],
                'defaultParentValue' => 0,
                'idKey' => 'key',
                'parentKey' => 'relatedID',
                'orderKey' => 'orderNum',
            ],
        ];
    }
}
