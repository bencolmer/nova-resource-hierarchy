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

    #[DataProvider('provideHierarchyTestCases')]
    public function test_build_hierarchy(
        array|ArrayAccess $input,
        array $expected,
        string $idField = 'id',
        string $parentIdField = 'parent_id'
    ): void {
        $result = $this->buildHierarchy(
            $input,
            fn($item) => $item instanceof Model ? $item->toArray() : $item,
            $idField,
            $parentIdField
        );

        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public static function provideHierarchyTestCases(): array
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
                'idField' => 'key',
                'parentIdField' => 'relatedID',
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
}
