<?php

declare(strict_types=1);

/*
 * This file is part of the Laudis Neo4j package.
 *
 * (c) Laudis technologies <http://laudis.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Laudis\Neo4j\Tests\Unit;

use Bolt\structures\Node;
use Bolt\structures\Path;
use Bolt\structures\UnboundRelationship;
use Laudis\Neo4j\Formatter\BoltCypherFormatter;
use PHPUnit\Framework\TestCase;
use stdClass;
use UnexpectedValueException;

final class BoltCypherFormatterTest extends TestCase
{
    private BoltCypherFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new BoltCypherFormatter();
    }

    public function testFormatPath(): void
    {
        $path = new Path(
            [new Node(1, ['a'], ['a' => 1]), new Node(2, ['b'], ['a' => 2]), new Node(3, ['c'], ['a' => 3])],
            [new UnboundRelationship(4, 'a', ['d']), new UnboundRelationship(5, 'a', ['e'])],
            [1, 2, 3, 4, 5]
        );

        $results = [
            [
                $path,
            ],
            [
            ],
        ];
        $result = $this->formatter->formatResult(['fields' => ['a']], $results);

        self::assertEquals(1, $result->count());
        self::assertEquals(1, $result->first()->count());
        self::assertEquals([
            ['a' => 1],
            [0 => 'd'],
            ['a' => 2],
            [0 => 'e'],
            ['a' => 3],
        ], $result->first()->get('a', []));
    }

    public function testInvalidObject(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot handle objects without a properties method. Class given: '.stdClass::class);
        $this->formatter->formatResult(['fields' => ['a']], [[new stdClass()], []]);
    }

    public function testResource(): void
    {
        $resource = fopen('php://temp', 'b');
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Did not expect to receive value of type: resource');
        $this->formatter->formatResult(['fields' => ['a']], [[$resource], []]);
    }
}
